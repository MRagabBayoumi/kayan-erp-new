<?php

namespace App\Http\Controllers;

use App\Models\BarcodeSetting;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\PrintTemplate;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\BarcodeGeneratorService;
use App\Services\InventoryService;
use App\Services\Printing\PythonPrintEngineService;
use App\Services\PrintTemplateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of products with search and category filters.
     */
    public function index(Request $request): View
    {
        $categories = Category::active()->orderBy('name')->get();

        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('category_id')) {
            $query->byCategory((int) $request->input('category_id'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $products = $query->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', [
            'title' => 'المنتجات والأصناف',
            'moduleName' => 'المخزون والمنتجات',
            'products' => $products,
            'categories' => $categories,
            'currentSearch' => $request->input('search'),
            'currentCategoryId' => $request->input('category_id'),
            'currentStatus' => $request->input('status'),
            'currentPerPage' => $perPage,
            'barcodeSettings' => BarcodeSetting::getSettings(),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? $warehouses->first();

        // Generate a suggested SKU format e.g. KY-26-0001
        $suggestedSku = 'KY-'.date('y').'-'.str_pad((string) (Product::max('id') + 1), 4, '0', STR_PAD_LEFT);

        return view('products.create', [
            'title' => 'إضافة منتج جديد',
            'moduleName' => 'المخزون والمنتجات',
            'categories' => $categories,
            'warehouses' => $warehouses,
            'units' => Unit::active()->orderBy('id')->get(),
            'defaultWarehouse' => $defaultWarehouse,
            'suggestedSku' => $suggestedSku,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['required', 'numeric', 'min:0'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'distributor_price' => ['nullable', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:50'],
            'pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'opening_quantity' => ['nullable', 'numeric', 'min:0'],
            'opening_cost' => ['nullable', 'numeric', 'min:0'],
            'opening_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'opening_balance_date' => ['nullable', 'date'],
            'product_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'sku.required' => 'كود الصنف (SKU) مطلوب.',
            'sku.unique' => 'كود الصنف مستخدم بالفعل في صنف آخر.',
            'name.required' => 'اسم المنتج مطلوب ولا يمكن تركه فارغاً.',
            'category_id.required' => 'يرجى اختيار تصنيف المنتج.',
            'category_id.exists' => 'التصنيف المختار غير موجود في النظام.',
            'barcode.unique' => 'الباركود المدخل مستخدم بالفعل في صنف آخر.',
            'cost_price.required' => 'سعر التكلفة مطلوب.',
            'cost_price.min' => 'سعر التكلفة لا يمكن أن يكون سالباً.',
            'wholesale_price.required' => 'سعر الجملة مطلوب.',
            'wholesale_price.min' => 'سعر الجملة لا يمكن أن يكون سالباً.',
            'retail_price.required' => 'سعر التجزئة مطلوب.',
            'retail_price.min' => 'سعر التجزئة لا يمكن أن يكون سالباً.',
            'distributor_price.min' => 'سعر الموزع يجب أن يكون أكبر من الصفر.',
            'distributor_price.numeric' => 'سعر الموزع يجب أن يكون قيمة رقمية صحيحة.',
            'pieces_per_package.min' => 'عدد القطع في الطرد يجب أن يكون 1 على الأقل.',
            'unit.required' => 'وحدة القياس مطلوبة.',
            'min_stock_alert.required' => 'حد الطلب الأدنى مطلوب.',
            'min_stock_alert.min' => 'حد الطلب الأدنى لا يمكن أن يكون سالباً.',
            'opening_quantity.min' => 'الكمية الافتتاحية لا يمكن أن تكون سالبة.',
            'opening_cost.min' => 'تكلفة الرصيد الافتتاحي لا يمكن أن تكون سالبة.',
            'opening_warehouse_id.exists' => 'المستودع المحدد للرصيد الافتتاحي غير موجود.',
            'product_image.image' => 'الملف المرفوع يجب أن يكون صورة صالحة.',
            'product_image.mimes' => 'صيغ الصور المدعومة هي: PNG, JPG, JPEG, WebP فقط.',
            'product_image.max' => 'الحد الأقصى لحجم صورة المنتج هو 2 ميجابايت.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Auto-fill pieces_per_package from unit conversion factor if omitted
        if (empty($validated['pieces_per_package']) && ! empty($validated['unit'])) {
            $matchedUnit = Unit::where('name', $validated['unit'])->first();
            if ($matchedUnit && (float) $matchedUnit->conversion_factor > 1) {
                $validated['pieces_per_package'] = (int) round((float) $matchedUnit->conversion_factor);
            }
        }

        // Normalize empty barcode string to null so MySQL unique constraint permits it
        if (empty($validated['barcode'])) {
            $validated['barcode'] = null;
        }

        // Handle image upload
        if ($request->hasFile('product_image')) {
            $file = $request->file('product_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'prod_'.Str::random(24).'.'.$extension;
            $validated['image_path'] = $file->storeAs('products', $filename, 'public');
        }

        // Enforce role-based price definition protection
        if (! $request->user()?->can('edit-product-prices')) {
            $priceFields = ['cost_price', 'wholesale_price', 'retail_price', 'distributor_price'];
            foreach ($priceFields as $field) {
                if (isset($validated[$field]) && (float) $validated[$field] > 0) {
                    abort(403, 'غير مصرح لك بتحديد أو تعديل أسعار المنتجات. هذه الصلاحية مخصصة لمدير النظام فقط.');
                }
            }
            if (isset($validated['opening_cost']) && (float) $validated['opening_cost'] > 0) {
                abort(403, 'غير مصرح لك بتحديد تكلفة الرصيد الافتتاحي. هذه الصلاحية مخصصة لمدير النظام فقط.');
            }
        }

        // Validate opening stock logic
        $openingQty = isset($validated['opening_quantity']) ? (float) $validated['opening_quantity'] : 0.00;
        if ($openingQty > 0) {
            if (empty($validated['opening_warehouse_id'])) {
                return back()->withInput()->withErrors(['opening_warehouse_id' => 'يرجى تحديد المستودع المستلم للرصيد الافتتاحي.']);
            }
            if (! isset($validated['opening_cost']) || $validated['opening_cost'] === '' || $validated['opening_cost'] === null) {
                $validated['opening_cost'] = $validated['cost_price'];
            }
            if (empty($validated['opening_balance_date'])) {
                $validated['opening_balance_date'] = now()->toDateString();
            }
        } else {
            $validated['opening_quantity'] = 0.00;
            $validated['opening_cost'] = null;
            $validated['opening_warehouse_id'] = null;
            $validated['opening_balance_date'] = null;
        }

        DB::transaction(function () use ($validated, $inventoryService, $openingQty) {
            $product = Product::create($validated);

            if ($openingQty > 0) {
                $warehouseId = (int) $validated['opening_warehouse_id'];
                $openingCost = (float) $validated['opening_cost'];
                $receivedDate = Carbon::parse($validated['opening_balance_date']);

                $inventoryService->addStock(
                    productId: $product->id,
                    warehouseId: $warehouseId,
                    quantity: $openingQty,
                    costPrice: $openingCost,
                    notes: 'رصيد افتتاحي عند تعريف الصنف: '.$product->name,
                    referenceType: 'opening_balance',
                    referenceId: $product->id,
                    userId: auth()->id(),
                    receivedAt: $receivedDate,
                    retailPrice: ! is_null($product->retail_price) ? (float) $product->retail_price : null,
                    wholesalePrice: ! is_null($product->wholesale_price) ? (float) $product->wholesale_price : null,
                    distributorPrice: ! is_null($product->distributor_price) ? (float) $product->distributor_price : (! is_null($product->wholesale_price) ? (float) $product->wholesale_price : null),
                    totalInitialCost: round($openingQty * $openingCost, 2),
                    sourceType: 'opening_balance',
                    sourceId: $product->id
                );
            }
        });

        return redirect()->route('products.index')
            ->with('status', 'success')
            ->with('message', 'تمت إضافة المنتج والرصيد الافتتاحي بنجاح.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product, BarcodeGeneratorService $barcodeService): View
    {
        $product->load(['category', 'stockBalances.warehouse']);
        $recentMovements = StockMovement::where('product_id', $product->id)
            ->with(['fromWarehouse', 'toWarehouse', 'user', 'customer', 'supplier'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $barcodeSettings = BarcodeSetting::getSettings();
        $barcodeValue = $barcodeService->getBarcodeValue($product);
        $barcodeSvg = $barcodeService->generateSvg($barcodeValue);
        $completeLabelSvg = $barcodeService->generateCompleteLabelSvg(
            $product->name,
            $barcodeValue,
            $product->sku,
            (int) $barcodeSettings->label_width_mm,
            (int) $barcodeSettings->label_height_mm,
            $barcodeSettings,
            (float) $product->retail_price,
            (float) $product->wholesale_price
        );

        return view('products.show', [
            'title' => $product->name,
            'moduleName' => 'المخزون والمنتجات',
            'product' => $product,
            'recentMovements' => $recentMovements,
            'barcodeValue' => $barcodeValue,
            'barcodeSvg' => $barcodeSvg,
            'completeLabelSvg' => $completeLabelSvg,
            'barcodeSettings' => $barcodeSettings,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $product->load(['category', 'openingWarehouse', 'batches', 'stockBalances.warehouse']);

        return view('products.edit', [
            'title' => 'تعديل: '.$product->name,
            'moduleName' => 'المخزون والمنتجات',
            'product' => $product,
            'categories' => $categories,
            'units' => Unit::active()->orderBy('id')->get(),
            'barcodeSettings' => BarcodeSetting::getSettings(),
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product->id)],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['required', 'numeric', 'min:0'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'distributor_price' => ['nullable', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:50'],
            'pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'product_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'sku.required' => 'كود الصنف (SKU) مطلوب.',
            'sku.unique' => 'كود الصنف مستخدم بالفعل في صنف آخر.',
            'name.required' => 'اسم المنتج مطلوب ولا يمكن تركه فارغاً.',
            'category_id.required' => 'يرجى اختيار تصنيف المنتج.',
            'category_id.exists' => 'التصنيف المختار غير موجود في النظام.',
            'barcode.unique' => 'الباركود المدخل مستخدم بالفعل في صنف آخر.',
            'cost_price.required' => 'سعر التكلفة مطلوب.',
            'cost_price.min' => 'سعر التكلفة لا يمكن أن يكون سالباً.',
            'wholesale_price.required' => 'سعر الجملة مطلوب.',
            'wholesale_price.min' => 'سعر الجملة لا يمكن أن يكون سالباً.',
            'retail_price.required' => 'سعر التجزئة مطلوب.',
            'retail_price.min' => 'سعر التجزئة لا يمكن أن يكون سالباً.',
            'distributor_price.min' => 'سعر الموزع يجب أن يكون أكبر من الصفر.',
            'distributor_price.numeric' => 'سعر الموزع يجب أن يكون قيمة رقمية صحيحة.',
            'pieces_per_package.min' => 'عدد القطع في الطرد يجب أن يكون 1 على الأقل.',
            'unit.required' => 'وحدة القياس مطلوبة.',
            'min_stock_alert.required' => 'حد الطلب الأدنى مطلوب.',
            'min_stock_alert.min' => 'حد الطلب الأدنى لا يمكن أن يكون سالباً.',
            'product_image.image' => 'الملف المرفوع يجب أن يكون صورة صالحة.',
            'product_image.mimes' => 'صيغ الصور المدعومة هي: PNG, JPG, JPEG, WebP فقط.',
            'product_image.max' => 'الحد الأقصى لحجم صورة المنتج هو 2 ميجابايت.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Auto-fill pieces_per_package from unit conversion factor if omitted
        if (empty($validated['pieces_per_package']) && ! empty($validated['unit'])) {
            $matchedUnit = Unit::where('name', $validated['unit'])->first();
            if ($matchedUnit && (float) $matchedUnit->conversion_factor > 1) {
                $validated['pieces_per_package'] = (int) round((float) $matchedUnit->conversion_factor);
            }
        }

        // Enforce role-based price modification protection
        if (! $request->user()?->can('edit-product-prices')) {
            $priceFields = ['cost_price', 'wholesale_price', 'retail_price', 'distributor_price'];
            foreach ($priceFields as $field) {
                $oldVal = (float) ($product->{$field} ?? 0);
                $newVal = (float) ($validated[$field] ?? 0);
                if (abs($oldVal - $newVal) > 0.0001) {
                    abort(403, 'غير مصرح لك بتعديل أسعار المنتجات. هذه الصلاحية مخصصة لمدير النظام فقط.');
                }
            }
        }

        // Disallow modifying opening balance values via product update
        unset(
            $validated['opening_quantity'],
            $validated['opening_cost'],
            $validated['opening_warehouse_id'],
            $validated['opening_balance_date']
        );

        // Normalize empty barcode string to null
        if (empty($validated['barcode'])) {
            $validated['barcode'] = null;
        }

        // Handle image upload and cleanup of old image
        if ($request->hasFile('product_image')) {
            $file = $request->file('product_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'prod_'.Str::random(24).'.'.$extension;
            $newPath = $file->storeAs('products', $filename, 'public');

            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $newPath;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('status', 'success')
            ->with('message', 'تم تحديث بيانات المنتج بنجاح.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('status', 'success')
            ->with('message', 'تم حذف المنتج بنجاح.');
    }

    /**
     * Generate an internal collision-resistant barcode via AJAX.
     */
    public function generateBarcode(): JsonResponse
    {
        return response()->json([
            'barcode' => Product::generateInternalBarcode(),
        ]);
    }

    /**
     * Real-time barcode lookup endpoint for scanners and search.
     */
    public function lookupBarcode(Request $request): JsonResponse
    {
        $barcode = trim((string) $request->query('barcode'));

        if (blank($barcode)) {
            return response()->json([
                'found' => false,
                'message' => 'يرجى تمرير كود الباركود المطلوب.',
            ], 422);
        }

        $product = Product::with('category')
            ->where('barcode', $barcode)
            ->first();

        if (! $product) {
            return response()->json([
                'found' => false,
                'message' => 'المنتج غير موجود بهذا الباركود.',
            ], 404);
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category_name' => $product->category?->name,
                'barcode' => $product->barcode,
                'cost_price' => (float) $product->cost_price,
                'wholesale_price' => (float) $product->wholesale_price,
                'retail_price' => (float) $product->retail_price,
                'unit' => $product->unit,
                'min_stock_alert' => $product->min_stock_alert,
                'image_url' => $product->image_url,
                'is_active' => $product->is_active,
            ],
        ]);
    }

    /**
     * Print thermal barcode label(s) for a single product.
     */
    public function printLabel(Request $request, Product $product, BarcodeGeneratorService $barcodeService): View
    {
        $barcodeSettings = BarcodeSetting::getSettings();
        $quantity = max(1, min(500, (int) $request->input('quantity', 1)));

        $widthMm = (int) $barcodeSettings->label_width_mm;
        $heightMm = (int) $barcodeSettings->label_height_mm;

        $size = $request->input('size');
        if ($size && preg_match('/^(\d+)x(\d+)$/', $size, $matches)) {
            $widthMm = (int) $matches[1];
            $heightMm = (int) $matches[2];
        }

        $barcodeValue = $barcodeService->getBarcodeValue($product);
        $svg = $barcodeService->generateSvg($barcodeValue);
        $labelSvg = $barcodeService->generateCompleteLabelSvg(
            $product->name,
            $barcodeValue,
            $product->sku,
            $widthMm,
            $heightMm,
            $barcodeSettings,
            (float) $product->retail_price,
            (float) $product->wholesale_price
        );

        return view('products.print-label', [
            'title' => 'طباعة ملصق باركود: '.$product->name,
            'items' => [
                [
                    'product' => $product,
                    'barcode_value' => $barcodeValue,
                    'svg' => $svg,
                    'label_svg' => $labelSvg,
                    'quantity' => $quantity,
                ],
            ],
            'totalQuantity' => $quantity,
            'autoPrint' => $request->boolean('autoprint', false),
            'widthMm' => $widthMm,
            'heightMm' => $heightMm,
            'size' => $widthMm.'x'.$heightMm,
            'barcodeSettings' => $barcodeSettings,
        ]);
    }

    /**
     * Print thermal barcode labels for a batch of selected products.
     */
    public function printBatchLabels(Request $request, BarcodeGeneratorService $barcodeService): View|RedirectResponse
    {
        $barcodeSettings = BarcodeSetting::getSettings();
        $itemRequests = [];

        $widthMm = (int) $barcodeSettings->label_width_mm;
        $heightMm = (int) $barcodeSettings->label_height_mm;

        $size = $request->input('size');
        if ($size && preg_match('/^(\d+)x(\d+)$/', $size, $matches)) {
            $widthMm = (int) $matches[1];
            $heightMm = (int) $matches[2];
        }

        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $item) {
                if (isset($item['id']) && is_numeric($item['id'])) {
                    $itemRequests[(int) $item['id']] = max(1, min(500, (int) ($item['qty'] ?? 1)));
                }
            }
        } elseif ($request->has('products') && is_array($request->input('products'))) {
            foreach ($request->input('products') as $id => $qty) {
                if (is_numeric($id)) {
                    $itemRequests[(int) $id] = max(1, min(500, (int) $qty));
                }
            }
        } elseif ($request->has('ids')) {
            $ids = is_array($request->input('ids')) ? $request->input('ids') : explode(',', (string) $request->input('ids'));
            $defaultQty = max(1, min(500, (int) $request->input('quantity', 1)));
            foreach ($ids as $id) {
                if (is_numeric($id)) {
                    $itemRequests[(int) $id] = $defaultQty;
                }
            }
        }

        if (empty($itemRequests)) {
            return redirect()->route('products.index')
                ->with('status', 'error')
                ->with('message', 'لم يتم تحديد أي أصناف لطباعة ملصقات الباركود.');
        }

        $products = Product::whereIn('id', array_keys($itemRequests))->get()->keyBy('id');

        $items = [];
        $totalQuantity = 0;

        foreach ($itemRequests as $id => $qty) {
            if ($product = $products->get($id)) {
                $barcodeValue = $barcodeService->getBarcodeValue($product);
                $items[] = [
                    'product' => $product,
                    'barcode_value' => $barcodeValue,
                    'svg' => $barcodeService->generateSvg($barcodeValue),
                    'label_svg' => $barcodeService->generateCompleteLabelSvg(
                        $product->name,
                        $barcodeValue,
                        $product->sku,
                        $widthMm,
                        $heightMm,
                        $barcodeSettings,
                        (float) $product->retail_price,
                        (float) $product->wholesale_price
                    ),
                    'quantity' => $qty,
                ];
                $totalQuantity += $qty;
            }
        }

        if (empty($items)) {
            return redirect()->route('products.index')
                ->with('status', 'error')
                ->with('message', 'الأصناف المحددة غير موجودة في النظام.');
        }

        return view('products.print-label', [
            'title' => 'طباعة ملصقات باركود مجمعة ('.$totalQuantity.' ملصق)',
            'items' => $items,
            'totalQuantity' => $totalQuantity,
            'autoPrint' => $request->boolean('autoprint', false),
            'widthMm' => $widthMm,
            'heightMm' => $heightMm,
            'size' => $widthMm.'x'.$heightMm,
            'barcodeSettings' => $barcodeSettings,
        ]);
    }

    /**
     * Return SVG barcode preview for modal display via JSON.
     */
    public function barcodeSvg(Product $product, BarcodeGeneratorService $barcodeService): JsonResponse
    {
        $barcodeValue = $barcodeService->getBarcodeValue($product);
        $svg = $barcodeService->generateSvg($barcodeValue);

        return response()->json([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'barcode' => $barcodeValue,
            'svg' => $svg,
        ]);
    }

    /**
     * Direct print barcode sticker via the centralized Python printing engine.
     */
    public function directPrint(Request $request, Product $product, PythonPrintEngineService $printService): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'printer' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'regex:/^\d+x\d+$/'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $printer = $validated['printer'] ?? null;
        $overrides = [];

        if (! empty($validated['size']) && preg_match('/^(\d+)x(\d+)$/', $validated['size'], $matches)) {
            $overrides['width_mm'] = (float) $matches[1];
            $overrides['height_mm'] = (float) $matches[2];
        }

        $result = $printService->printBarcodeLabel($product, $quantity, $printer, $overrides);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Direct print multiple product labels via the centralized Python printing engine.
     */
    public function directPrintBatch(Request $request, PythonPrintEngineService $printService): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['nullable', 'integer', 'min:1', 'max:500'],
            'printer' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'regex:/^\d+x\d+$/'],
        ]);

        $printer = $validated['printer'] ?? null;
        $overrides = [];
        if (! empty($validated['size']) && preg_match('/^(\d+)x(\d+)$/', $validated['size'], $matches)) {
            $overrides['width_mm'] = (float) $matches[1];
            $overrides['height_mm'] = (float) $matches[2];
        }

        $batchItems = [];
        $totalRequestedQty = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
            if (! $product) {
                continue;
            }

            $qty = max(1, min(500, (int) ($item['qty'] ?? 1)));
            $batchItems[] = [
                'product' => $product,
                'qty' => $qty,
            ];
            $totalRequestedQty += $qty;
        }

        if (empty($batchItems)) {
            return response()->json([
                'success' => false,
                'status' => 'EMPTY_BATCH',
                'message' => 'لم يتم العثور على أي أصناف صالحة للطباعة.',
            ], 422);
        }

        $res = $printService->printBarcodeLabelBatch($batchItems, $printer, $overrides);

        $isSuccess = (bool) ($res['success'] ?? false);
        $totalCopies = (int) ($res['total_labels'] ?? $totalRequestedQty);
        $printedCount = (int) ($res['total_products'] ?? count($batchItems));

        return response()->json([
            'success' => $isSuccess,
            'status' => $res['status'] ?? ($isSuccess ? 'SPOOLED' : 'FAILED'),
            'message' => $isSuccess
                ? "تم إرسال {$totalCopies} ملصق لعدد {$printedCount} صنف للطباعة بنجاح."
                : ($res['message'] ?? 'فشل إرسال أمر الطباعة المجمعة.'),
            'total_copies' => $totalCopies,
            'printed_products_count' => $printedCount,
            'printer' => $res['printer'] ?? $printer,
            'items_summary' => $res['items_summary'] ?? [],
        ], $isSuccess ? 200 : 422);
    }

    /**
     * Display a dedicated, template-driven printable view of the product stock movement ledger.
     * ZERO data mutation, strictly read-only, respects filters, and supports A4, A5, and Thermal 80mm.
     */
    public function printLedger(Request $request, Product $product, PrintTemplateService $templateService): View
    {
        $product->loadMissing(['category', 'stockBalances.warehouse']);

        $query = StockMovement::where('product_id', $product->id)
            ->with(['fromWarehouse', 'toWarehouse', 'batch', 'user', 'customer', 'supplier']);

        $selectedWarehouse = null;
        if ($request->filled('warehouse_id')) {
            $wid = (int) $request->input('warehouse_id');
            $selectedWarehouse = Warehouse::find($wid);
            $query->where(function ($q) use ($wid) {
                $q->where('from_warehouse_id', $wid)
                    ->orWhere('to_warehouse_id', $wid);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Print order: allow ascending (chronological) or descending
        $sortOrder = $request->input('sort', 'asc');
        if ($sortOrder === 'desc') {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
        }

        // Retrieve unpaginated movements up to safe report limit (1000 items)
        $movements = $query->limit(1000)->get();

        // Paper format resolution
        $format = (string) $request->query('format', PrintTemplate::FORMAT_A4);
        if (! in_array($format, PrintTemplateService::SUPPORTED_FORMATS, true)) {
            $format = PrintTemplate::FORMAT_A4;
        }

        $template = $templateService->getEffectiveTemplate(PrintTemplate::DOC_PURCHASE_INVOICE, $format);
        $companySetting = CompanySetting::getSettings();
        $autoPrint = $request->boolean('autoprint', false);

        // Movement totals
        $totalIn = (float) $movements->where('type', StockMovement::TYPE_IN)->sum('quantity');
        $totalOut = (float) $movements->where('type', StockMovement::TYPE_OUT)->sum('quantity');
        $totalTransfer = (float) $movements->where('type', StockMovement::TYPE_TRANSFER)->sum('quantity');
        $totalAdjustment = (float) $movements->where('type', StockMovement::TYPE_ADJUSTMENT)->sum('quantity');

        $colorHex = $template->getSetting('general.primary_color', '#1e293b');
        $colorClass = match ($colorHex) {
            '#059669', '#10b981' => 'theme-emerald',
            '#2563eb', '#3b82f6' => 'theme-blue',
            '#7c3aed', '#8b5cf6' => 'theme-purple',
            '#e11d48', '#f43f5e' => 'theme-rose',
            '#d97706', '#f59e0b' => 'theme-amber',
            '#000000' => 'theme-black',
            default => 'theme-navy',
        };

        return view('products.ledger-print', [
            'title' => 'طباعة كشف حركة الصنف: '.$product->name,
            'product' => $product,
            'movements' => $movements,
            'movementsCount' => $movements->count(),
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'totalTransfer' => $totalTransfer,
            'totalAdjustment' => $totalAdjustment,
            'selectedWarehouse' => $selectedWarehouse,
            'currentWarehouseId' => $request->input('warehouse_id'),
            'currentType' => $request->input('type'),
            'currentDateFrom' => $request->input('date_from'),
            'currentDateTo' => $request->input('date_to'),
            'template' => $template,
            'settings' => $template->settings,
            'format' => $format,
            'colorClass' => $colorClass,
            'company' => $companySetting,
            'autoPrint' => $autoPrint,
            'sortOrder' => $sortOrder,
        ]);
    }

    /**
     * Display the granular stock movement ledger for a specific product.
     */
    public function ledger(Product $product, Request $request): View
    {
        $product->load(['category', 'stockBalances.warehouse']);

        $query = StockMovement::where('product_id', $product->id)
            ->with(['fromWarehouse', 'toWarehouse', 'batch', 'user', 'customer', 'supplier']);

        if ($request->filled('warehouse_id')) {
            $wid = (int) $request->input('warehouse_id');
            $query->where(function ($q) use ($wid) {
                $q->where('from_warehouse_id', $wid)
                    ->orWhere('to_warehouse_id', $wid);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $movements = $query->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('products.ledger', [
            'title' => 'كشف حركة الصنف: '.$product->name,
            'moduleName' => 'المخزون والمنتجات',
            'product' => $product,
            'movements' => $movements,
            'warehouses' => $warehouses,
            'currentWarehouseId' => $request->input('warehouse_id'),
            'currentType' => $request->input('type'),
            'currentDateFrom' => $request->input('date_from'),
            'currentDateTo' => $request->input('date_to'),
        ]);
    }
}
