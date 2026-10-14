<?php

use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create([
        'name' => 'إكسسوارات حريمي',
        'code' => 'ACC',
        'is_active' => true,
    ]);

    $this->warehouse = Warehouse::create([
        'code' => 'WH-MAIN',
        'name' => 'المستودع الرئيسي',
        'location' => 'العتبة',
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->supplier = Supplier::create([
        'code' => 'SUP-CHINA-01',
        'name' => 'مصنع كوانزو للمصوغات والإكسسوارات',
        'country' => 'الصين',
        'city' => 'Guangzhou',
        'phone' => '+8613800138000',
        'is_active' => true,
    ]);

    $this->product1 = Product::create([
        'sku' => 'PUR-ITEM-01',
        'name' => 'طوق شعر لؤلؤ فاخر',
        'category_id' => $this->category->id,
        'cost_price' => 15.00,
        'wholesale_price' => 25.00,
        'retail_price' => 40.00,
        'unit' => 'قطعة',
        'min_stock_alert' => 10,
        'is_active' => true,
    ]);

    $this->product2 = Product::create([
        'sku' => 'PUR-ITEM-02',
        'name' => 'دبوس شعر كريستال',
        'category_id' => $this->category->id,
        'cost_price' => 8.50,
        'wholesale_price' => 15.00,
        'retail_price' => 25.00,
        'unit' => 'قطعة',
        'min_stock_alert' => 20,
        'is_active' => true,
    ]);
});

test('purchases index page loads and shows metrics and invoice listings', function () {
    $response = $this->get(route('purchases.index'));

    $response->assertStatus(200);
    $response->assertSee('فواتير الشراء والتوريد');
    $response->assertSee('فاتورة شراء جديدة');
    $response->assertSee('إجمالي فواتير الشراء');
});

test('purchases create page loads with suppliers, warehouses, and products', function () {
    $response = $this->get(route('purchases.create'));

    $response->assertStatus(200);
    $response->assertSee('فاتورة شراء جديدة');
    $response->assertSee('مصنع كوانزو للمصوغات والإكسسوارات');
    $response->assertSee('المستودع الرئيسي (الافتراضي)');
    $response->assertSee('طوق شعر لؤلؤ فاخر');
});

test('purchase invoice can be stored as draft without affecting inventory', function () {
    $response = $this->post(route('purchases.store'), [
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => '2026-10-14',
        'shipping_cost' => 250.00,
        'notes' => 'شحنة تجريبية بحرية',
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 200,
                'unit_cost' => 14.00,
                'notes' => 'كرتونة 1',
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 100,
                'unit_cost' => 8.00,
                'notes' => 'كرتونة 2',
            ],
        ],
    ]);

    $invoice = PurchaseInvoice::first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(PurchaseInvoice::STATUS_DRAFT)
        ->and((float) $invoice->subtotal)->toEqual(3600.00) // (200*14) + (100*8) = 2800 + 800 = 3600
        ->and((float) $invoice->shipping_cost)->toEqual(250.00)
        ->and((float) $invoice->total_amount)->toEqual(3850.00)
        ->and($invoice->items)->toHaveCount(2);

    $response->assertRedirect(route('purchases.show', $invoice));

    // Verify NO inventory batches or movements are created yet
    expect(InventoryBatch::where('purchase_invoice_id', $invoice->id)->count())->toBe(0);
    expect(StockBalance::sum('quantity'))->toEqual(0.00);
});

test('draft purchase invoice can be posted and atomically generates FIFO batches and stock movements', function () {
    $invoice = PurchaseInvoice::create([
        'invoice_number' => 'PUR-TEST-POST-01',
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => '2026-10-14',
        'status' => PurchaseInvoice::STATUS_DRAFT,
        'subtotal' => 1500.00,
        'shipping_cost' => 100.00,
        'total_amount' => 1600.00,
    ]);

    PurchaseInvoiceItem::create([
        'purchase_invoice_id' => $invoice->id,
        'product_id' => $this->product1->id,
        'quantity' => 100,
        'unit_cost' => 15.00,
        'subtotal' => 1500.00,
    ]);

    $response = $this->post(route('purchases.post', $invoice));

    $response->assertRedirect(route('purchases.show', $invoice));
    $response->assertSessionHas('status', 'success');

    $invoice->refresh();
    expect($invoice->status)->toBe(PurchaseInvoice::STATUS_POSTED)
        ->and($invoice->posted_at)->not->toBeNull();

    // Verify FIFO Batch
    $batch = InventoryBatch::where('purchase_invoice_id', $invoice->id)->first();
    expect($batch)->not->toBeNull()
        ->and($batch->product_id)->toBe($this->product1->id)
        ->and($batch->warehouse_id)->toBe($this->warehouse->id)
        ->and((float) $batch->initial_quantity)->toEqual(100.00)
        ->and((float) $batch->remaining_quantity)->toEqual(100.00)
        ->and((float) $batch->cost_price)->toEqual(15.00);

    // Verify Stock Movement
    $movement = StockMovement::where('reference_type', 'purchase_invoice')
        ->where('reference_id', $invoice->id)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe(StockMovement::TYPE_IN)
        ->and($movement->to_warehouse_id)->toBe($this->warehouse->id)
        ->and($movement->supplier_id)->toBe($this->supplier->id)
        ->and((float) $movement->quantity)->toEqual(100.00)
        ->and((float) $movement->running_balance)->toEqual(100.00)
        ->and((float) $movement->unit_cost)->toEqual(15.00);

    // Verify Warehouse Stock Balance
    $balance = StockBalance::where('product_id', $this->product1->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->first();

    expect($balance)->not->toBeNull()
        ->and((float) $balance->quantity)->toEqual(100.00);
});

test('purchase invoice can be created and posted directly in one atomic step', function () {
    $response = $this->post(route('purchases.store'), [
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => '2026-10-14',
        'shipping_cost' => 50.00,
        'action' => 'post', // Direct posting
        'items' => [
            [
                'product_id' => $this->product2->id,
                'quantity' => 80,
                'unit_cost' => 8.50,
            ],
        ],
    ]);

    $invoice = PurchaseInvoice::first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(PurchaseInvoice::STATUS_POSTED);

    $batch = InventoryBatch::where('purchase_invoice_id', $invoice->id)->first();
    expect($batch)->not->toBeNull()
        ->and((float) $batch->remaining_quantity)->toEqual(80.00);

    $balance = StockBalance::where('product_id', $this->product2->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->first();

    expect((float) $balance->quantity)->toEqual(80.00);
});

test('draft invoice can be cancelled', function () {
    $invoice = PurchaseInvoice::create([
        'invoice_number' => 'PUR-CANCEL-01',
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => '2026-10-14',
        'status' => PurchaseInvoice::STATUS_DRAFT,
        'total_amount' => 500.00,
    ]);

    $response = $this->post(route('purchases.cancel', $invoice));

    $response->assertRedirect(route('purchases.show', $invoice));
    expect($invoice->fresh()->status)->toBe(PurchaseInvoice::STATUS_CANCELLED);
});

test('already posted invoice cannot be cancelled', function () {
    $invoice = PurchaseInvoice::create([
        'invoice_number' => 'PUR-POSTED-01',
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => '2026-10-14',
        'status' => PurchaseInvoice::STATUS_POSTED,
        'total_amount' => 500.00,
    ]);

    $response = $this->post(route('purchases.cancel', $invoice));

    $response->assertSessionHas('error');
    expect($invoice->fresh()->status)->toBe(PurchaseInvoice::STATUS_POSTED);
});

test('product movement ledger displays running balance and movement history', function () {
    // Record opening stock
    StockMovement::create([
        'movement_number' => 'MV-INIT-01',
        'type' => StockMovement::TYPE_IN,
        'product_id' => $this->product1->id,
        'to_warehouse_id' => $this->warehouse->id,
        'quantity' => 100,
        'unit_cost' => 15.00,
        'running_balance' => 100.00,
        'reference_type' => 'opening_balance',
    ]);

    // Record deduction
    StockMovement::create([
        'movement_number' => 'MV-SALE-01',
        'type' => StockMovement::TYPE_OUT,
        'product_id' => $this->product1->id,
        'from_warehouse_id' => $this->warehouse->id,
        'quantity' => 30,
        'unit_cost' => 15.00,
        'running_balance' => 70.00,
        'reference_type' => 'sale_order',
    ]);

    $response = $this->get(route('products.ledger', $this->product1));

    $response->assertStatus(200);
    $response->assertSee('كشف حركة الصنف: طوق شعر لؤلؤ فاخر');
    $response->assertSee('MV-INIT-01');
    $response->assertSee('MV-SALE-01');
    $response->assertSee('100.00');
    $response->assertSee('70.00');
    $response->assertSee('رصيد افتتاحي');
});
