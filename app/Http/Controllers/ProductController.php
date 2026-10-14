<?php

namespace App\Http\Controllers;

use App\Models\BarcodeSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\BarcodeGeneratorService;
use App\Services\InventoryService;
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

        $products = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'title' => 'المنتجات والأصناف',
            'moduleName' => 'المخزون والمنتجات',
            'products' => $products,
            'categories' => $categories,
            'currentSearch' => $request->input('search'),
            'currentCategoryId' => $request->input('category_id'),
            'currentStatus' => $request->input('status'),
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

        // Validate opening stock logic
        $openingQty = isset($validated['opening_quantity']) ? (float) $validated['opening_quantity'] : 0.00;
        if ($openingQty > 0) {
            if (empty($validated['opening_warehouse_id'])) {
                return back()->withInput()->withErrors(['opening_warehouse_id' => 'يرجى تحديد المستودع المستلم للرصيد الافتتاحي.']);
            }
            if (!isset($validated['opening_cost']) || $validated['opening_cost'] === '' || $validated['opening_cost'] === null) {
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
                    receivedAt: $receivedDate
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
        $product->load('category');
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
        if ($size === '38x15') {
            $widthMm = 38;
            $heightMm = 15;
        } elseif ($size === '38x25') {
            $widthMm = 38;
            $heightMm = 25;
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
        if ($size === '38x15') {
            $widthMm = 38;
            $heightMm = 15;
        } elseif ($size === '38x25') {
            $widthMm = 38;
            $heightMm = 25;
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
}
