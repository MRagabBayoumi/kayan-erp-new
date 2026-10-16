<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\StockBalance;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin);

    $this->category = Category::create([
        'name' => 'إكسسوارات الشعر',
        'code' => 'HAIR-ACC',
        'is_active' => true,
    ]);

    $this->warehouse = Warehouse::create([
        'code' => 'WH-MAIN',
        'name' => 'المستودع الرئيسي',
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->customer = Customer::create([
        'name' => 'عميل نقدي',
        'phone' => '01011112222',
        'pricing_tier' => 'retail',
        'is_active' => true,
    ]);

    $this->unitPiece = Unit::create([
        'name' => 'قطعة',
        'code' => 'PCS',
        'operator' => '*',
        'conversion_factor' => 1.0000,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->unitDozen = Unit::create([
        'name' => 'دستة',
        'code' => 'DZ',
        'base_unit_id' => $this->unitPiece->id,
        'operator' => '*',
        'conversion_factor' => 12.0000,
        'is_active' => true,
        'is_default' => false,
    ]);

    $this->unitCarton = Unit::create([
        'name' => 'كرتونة',
        'code' => 'CTN',
        'base_unit_id' => $this->unitPiece->id,
        'operator' => '*',
        'conversion_factor' => 24.0000,
        'is_active' => true,
        'is_default' => false,
    ]);
});

test('product formatStockBreakdown accurately computes dozens and pieces', function () {
    $product = Product::create([
        'sku' => 'HAIR-001',
        'name' => 'طوق شعر ستان كلاسيك',
        'category_id' => $this->category->id,
        'cost_price' => 24.00,
        'retail_price' => 120.00,
        'unit' => 'دستة',
        'pieces_per_package' => 12,
        'min_stock_alert' => 5,
        'is_active' => true,
    ]);

    expect($product->unit_factor)->toBe(12.0);

    // Initial stock 55 dozens
    expect($product->formatStockBreakdown(55.0))->toBe('55 دستة (660 قطعة)');

    // 1 piece deducted: 55 - 1/12 = 54.916667 dozens -> 54 dozens and 11 pieces
    $stockAfterOnePiece = 55.0 - (1 / 12);
    expect($product->formatStockBreakdown($stockAfterOnePiece))->toBe('54 دستة و 11 قطعة (659 قطعة)');

    // 2 pieces deducted: 54 dozens and 10 pieces (658 pieces)
    $stockAfterTwoPieces = 55.0 - (2 / 12);
    expect($product->formatStockBreakdown($stockAfterTwoPieces))->toBe('54 دستة و 10 قطعة (658 قطعة)');

    // 1 dozen deducted: 54 dozens
    expect($product->formatStockBreakdown(54.0))->toBe('54 دستة (648 قطعة)');

    // 5 pieces only
    expect($product->formatStockBreakdown(5 / 12))->toBe('5 قطعة');

    // 0 stock
    expect($product->formatStockBreakdown(0.0))->toBe('0 دستة');
});

test('product getPriceForUnit scales price correctly between dozens and pieces', function () {
    $product = Product::create([
        'sku' => 'HAIR-002',
        'name' => 'توكة لؤلؤ',
        'category_id' => $this->category->id,
        'cost_price' => 36.00,
        'retail_price' => 120.00, // 120 EGP per dozen
        'wholesale_price' => 96.00, // 96 EGP per dozen
        'unit' => 'دستة',
        'pieces_per_package' => 12,
        'min_stock_alert' => 5,
        'is_active' => true,
    ]);

    // Dozen (factor 12)
    expect($product->getPriceForUnit('retail', 12.0))->toBe(120.00);
    expect($product->getPriceForUnit('wholesale', 12.0))->toBe(96.00);

    // Piece (factor 1)
    // Retail: 120 / 12 = 10.00
    // Wholesale: 96 / 12 = 8.00
    expect($product->getPriceForUnit('retail', 1.0))->toBe(10.00);
    expect($product->getPriceForUnit('wholesale', 1.0))->toBe(8.00);

    // Carton (factor 24)
    // Retail: 120 * (24 / 12) = 240.00
    expect($product->getPriceForUnit('retail', 24.0))->toBe(240.00);
});

test('selling 1 dozen of a dozen-based product deducts 1 dozen from stock', function () {
    $inventoryService = app(InventoryService::class);

    $product = Product::create([
        'sku' => 'HAIR-003',
        'name' => 'طوق شعر ستان كلاسيك وردي',
        'category_id' => $this->category->id,
        'cost_price' => 18.00,
        'retail_price' => 50.00,
        'unit' => 'دستة',
        'pieces_per_package' => 12,
        'min_stock_alert' => 5,
        'is_active' => true,
    ]);

    // Add initial stock: 55 dozens
    $inventoryService->addStock(
        productId: $product->id,
        warehouseId: $this->warehouse->id,
        quantity: 55.00,
        costPrice: 18.00,
        retailPrice: 50.00,
        referenceType: 'opening_balance'
    );

    expect((float) StockBalance::where('product_id', $product->id)->value('quantity'))->toBe(55.0);

    // Sell 1 Dozen
    $response = $this->post(route('sales.store'), [
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => now()->toDateString(),
        'price_tier' => 'retail',
        'action' => 'post',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $this->unitDozen->id,
                'conversion_factor' => 12.0,
                'quantity' => 1,
                'unit_price' => 50.00,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $invoice = SalesInvoice::latest('id')->firstOrFail();
    expect($invoice->status)->toBe(SalesInvoice::STATUS_POSTED);

    $balance = StockBalance::where('product_id', $product->id)->value('quantity');
    expect((float) $balance)->toBe(54.0);
    expect($product->fresh()->formatStockBreakdown())->toBe('54 دستة (648 قطعة)');
});

test('selling 1 piece of a dozen-based product deducts 1/12 dozen from stock', function () {
    $inventoryService = app(InventoryService::class);

    $product = Product::create([
        'sku' => 'HAIR-004',
        'name' => 'طوق شعر ستان كلاسيك وردي',
        'category_id' => $this->category->id,
        'cost_price' => 18.00,
        'retail_price' => 50.00,
        'unit' => 'دستة',
        'pieces_per_package' => 12,
        'min_stock_alert' => 5,
        'is_active' => true,
    ]);

    // Initial stock: 55 dozens (660 pieces)
    $inventoryService->addStock(
        productId: $product->id,
        warehouseId: $this->warehouse->id,
        quantity: 55.00,
        costPrice: 18.00,
        retailPrice: 50.00,
        referenceType: 'opening_balance'
    );

    // Sell 1 Piece at 4.17 EGP
    $response = $this->post(route('sales.store'), [
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => now()->toDateString(),
        'price_tier' => 'retail',
        'action' => 'post',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $this->unitPiece->id,
                'conversion_factor' => 1.0,
                'quantity' => 1,
                'unit_price' => 4.17,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $invoice = SalesInvoice::latest('id')->firstOrFail();
    expect($invoice->status)->toBe(SalesInvoice::STATUS_POSTED);

    $balance = (float) StockBalance::where('product_id', $product->id)->value('quantity');
    // 55 - 1/12 = 54.9167 dozens
    expect($balance)->toBeGreaterThan(54.91)->toBeLessThan(54.92);

    // Compound stock breakdown must say: "54 دستة و 11 قطعة (659 قطعة)"
    expect($product->fresh()->formatStockBreakdown())->toBe('54 دستة و 11 قطعة (659 قطعة)');
});

test('selling a piece-based product in dozens correctly deducts 12 pieces from stock', function () {
    $inventoryService = app(InventoryService::class);

    $product = Product::create([
        'sku' => 'SHMP-001',
        'name' => 'شامبو أطفال صغير',
        'category_id' => $this->category->id,
        'cost_price' => 8.00,
        'retail_price' => 15.00, // 15 EGP per piece
        'unit' => 'قطعة',
        'pieces_per_package' => 1,
        'min_stock_alert' => 10,
        'is_active' => true,
    ]);

    // Initial stock: 100 pieces
    $inventoryService->addStock(
        productId: $product->id,
        warehouseId: $this->warehouse->id,
        quantity: 100.00,
        costPrice: 8.00,
        retailPrice: 15.00,
        referenceType: 'opening_balance'
    );

    // Sell 1 Dozen (12 pieces) at 180 EGP (12 * 15)
    $response = $this->post(route('sales.store'), [
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->warehouse->id,
        'invoice_date' => now()->toDateString(),
        'price_tier' => 'retail',
        'action' => 'post',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $this->unitDozen->id,
                'conversion_factor' => 12.0,
                'quantity' => 1,
                'unit_price' => 180.00,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $balance = (float) StockBalance::where('product_id', $product->id)->value('quantity');
    expect($balance)->toBe(88.0); // 100 - 12 = 88 pieces
});
