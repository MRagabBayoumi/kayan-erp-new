<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\PrintTemplate;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\Printing\PythonPrintEngineService;
use App\Services\PrintTemplateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseInvoiceController extends Controller
{
    /**
     * Display a paginated listing of purchase invoices.
     */
    public function index(Request $request): View
    {
        $query = PurchaseInvoice::with(['supplier', 'warehouse', 'creator', 'items.product']);

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', (int) $request->input('warehouse_id'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->input('supplier_id'));
        }

        $invoices = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        $metrics = [
            'total_count' => PurchaseInvoice::count(),
            'posted_total' => (float) PurchaseInvoice::where('status', PurchaseInvoice::STATUS_POSTED)->sum('total_amount'),
            'draft_count' => PurchaseInvoice::where('status', PurchaseInvoice::STATUS_DRAFT)->count(),
        ];

        return view('purchases.index', [
            'title' => 'فواتير الشراء والتوريد',
            'moduleName' => 'المشتريات والموردين',
            'invoices' => $invoices,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'metrics' => $metrics,
            'currentSearch' => $request->input('search'),
            'currentStatus' => $request->input('status'),
            'currentWarehouseId' => $request->input('warehouse_id'),
            'currentSupplierId' => $request->input('supplier_id'),
        ]);
    }

    /**
     * Show the form for creating a new purchase invoice.
     */
    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? $warehouses->first();
        $products = Product::active()->orderBy('name')->get();
        $suggestedNumber = PurchaseInvoice::generateInvoiceNumber();

        return view('purchases.create', [
            'title' => 'فاتورة شراء جديدة',
            'moduleName' => 'المشتريات والموردين',
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'defaultWarehouse' => $defaultWarehouse,
            'products' => $products,
            'suggestedNumber' => $suggestedNumber,
        ]);
    }

    /**
     * Store a newly created purchase invoice in storage.
     */
    public function store(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'invoice_date' => ['required', 'date'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'supplier_id.required' => 'يرجى اختيار المورد.',
            'supplier_id.exists' => 'المورد المختار غير موجود في النظام.',
            'warehouse_id.required' => 'يرجى تحديد مستودع الاستلام.',
            'warehouse_id.exists' => 'المستودع المختار غير موجود في النظام.',
            'invoice_date.required' => 'تاريخ الفاتورة مطلوب.',
            'items.required' => 'يجب إضافة صنف واحد على الأقل في الفاتورة.',
            'items.min' => 'يجب إضافة صنف واحد على الأقل في الفاتورة.',
            'items.*.product_id.required' => 'يرجى اختيار الصنف.',
            'items.*.quantity.required' => 'الكمية مطلوبة.',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من الصفر.',
            'items.*.unit_cost.required' => 'سعر التكلفة مطلوب.',
            'items.*.unit_cost.min' => 'سعر التكلفة لا يمكن أن يكون سالباً.',
        ]);

        $shippingCost = (float) ($validated['shipping_cost'] ?? 0.00);

        $invoice = DB::transaction(function () use ($validated, $shippingCost, $request, $inventoryService) {
            $subtotal = 0.00;
            $itemsData = [];

            foreach ($validated['items'] as $itemInput) {
                $qty = (float) $itemInput['quantity'];
                $cost = (float) $itemInput['unit_cost'];
                $lineSubtotal = round($qty * $cost, 2);
                $subtotal += $lineSubtotal;

                $itemsData[] = [
                    'product_id' => (int) $itemInput['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $lineSubtotal,
                    'notes' => $itemInput['notes'] ?? null,
                ];
            }

            $totalAmount = $subtotal + $shippingCost;

            $invoice = PurchaseInvoice::create([
                'invoice_number' => PurchaseInvoice::generateInvoiceNumber(),
                'supplier_id' => (int) $validated['supplier_id'],
                'warehouse_id' => (int) $validated['warehouse_id'],
                'invoice_date' => $validated['invoice_date'],
                'status' => PurchaseInvoice::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($itemsData as $row) {
                $row['purchase_invoice_id'] = $invoice->id;
                PurchaseInvoiceItem::create($row);
            }

            // If user chose to post directly upon creation
            if ($request->input('action') === 'post') {
                $this->executePosting($invoice, $inventoryService);
            }

            return $invoice;
        });

        $message = $invoice->status === PurchaseInvoice::STATUS_POSTED
            ? 'تم حفظ فاتورة الشراء وترحيل أصنافها للمخازن كدفعات واردة بنجاح.'
            : 'تم حفظ مسودة فاتورة الشراء بنجاح.';

        return redirect()->route('purchases.show', $invoice)
            ->with('status', 'success')
            ->with('message', $message);
    }

    /**
     * Display the specified purchase invoice.
     */
    public function show(PurchaseInvoice $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'creator', 'items.product.category', 'batches']);

        return view('purchases.show', [
            'title' => 'فاتورة شراء: '.$purchase->invoice_number,
            'moduleName' => 'المشتريات والموردين',
            'invoice' => $purchase,
        ]);
    }

    /**
     * Display a dedicated, template-driven printable view of the purchase invoice.
     * ZERO data mutation, strictly read-only and authorization verified.
     */
    public function print(Request $request, PurchaseInvoice $purchase, PrintTemplateService $templateService): View
    {
        $purchase->loadMissing(['supplier', 'warehouse', 'creator', 'items.product.category']);

        $format = (string) $request->query('format', $request->query('paper_format', PrintTemplate::FORMAT_A4));
        if (! in_array($format, PrintTemplateService::SUPPORTED_FORMATS, true)) {
            $format = PrintTemplate::FORMAT_A4;
        }

        $template = $templateService->getEffectiveTemplate(PrintTemplate::DOC_PURCHASE_INVOICE, $format);
        $companySetting = CompanySetting::getSettings();
        $autoPrint = $request->boolean('autoprint', false);

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

        return view('purchases.print', [
            'title' => 'طباعة فاتورة شراء: '.$purchase->invoice_number,
            'invoice' => $purchase,
            'template' => $template,
            'settings' => $template->settings,
            'format' => $format,
            'colorClass' => $colorClass,
            'company' => $companySetting,
            'autoPrint' => $autoPrint,
        ]);
    }

    /**
     * Direct print purchase invoice receipt via the centralized Python printing engine.
     */
    public function directPrint(Request $request, PurchaseInvoice $purchase, PythonPrintEngineService $printService): JsonResponse
    {
        $validated = $request->validate([
            'printer' => ['nullable', 'string', 'max:120'],
        ]);

        $printer = $validated['printer'] ?? null;
        $result = $printService->printPurchaseInvoice($purchase, $printer);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Post a draft purchase invoice and receive items into the warehouse atomically.
     */
    public function post(PurchaseInvoice $purchase, InventoryService $inventoryService): RedirectResponse
    {
        if ($purchase->status !== PurchaseInvoice::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن ترحيل هذه الفاتورة لأنها ليست في حالة مسودة.');
        }

        DB::transaction(function () use ($purchase, $inventoryService) {
            $this->executePosting($purchase, $inventoryService);
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('status', 'success')
            ->with('message', 'تم ترحيل الفاتورة بنجاح وتوليد دفعات المخزون وفق سياسة الوارد أولاً يصرف أولاً (FIFO).');
    }

    /**
     * Cancel a draft purchase invoice.
     */
    public function cancel(PurchaseInvoice $purchase): RedirectResponse
    {
        if ($purchase->status !== PurchaseInvoice::STATUS_DRAFT) {
            return back()->with('error', 'يمكن فقط إلغاء الفواتير التي في حالة مسودة.');
        }

        $purchase->update(['status' => PurchaseInvoice::STATUS_CANCELLED]);

        return redirect()->route('purchases.show', $purchase)
            ->with('status', 'success')
            ->with('message', 'تم إلغاء فاتورة الشراء.');
    }

    /**
     * Internal atomic posting helper.
     */
    private function executePosting(PurchaseInvoice $invoice, InventoryService $inventoryService): void
    {
        $invoice->loadMissing('items.product');

        foreach ($invoice->items as $item) {
            $inventoryService->addStock(
                productId: $item->product_id,
                warehouseId: $invoice->warehouse_id,
                quantity: (float) $item->quantity,
                costPrice: (float) $item->unit_cost,
                notes: 'فاتورة شراء رقم: '.$invoice->invoice_number,
                referenceType: 'purchase_invoice',
                referenceId: $invoice->id,
                userId: auth()->id(),
                receivedAt: Carbon::parse($invoice->invoice_date),
                supplierId: $invoice->supplier_id
            );
        }

        $invoice->update([
            'status' => PurchaseInvoice::STATUS_POSTED,
            'posted_at' => now(),
        ]);
    }
}
