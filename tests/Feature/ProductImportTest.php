<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('user can download pre-formatted CSV template with Arabic UTF-8 BOM', function () {
    $response = $this->get(route('products.import.template'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename="kayan_products_template.csv"');

    $content = $response->getContent();
    // Must start with UTF-8 BOM for Microsoft Excel Arabic rendering
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('كود الصنف')
        ->and($content)->toContain('اسم المنتج')
        ->and($content)->toContain('سعر الجملة');
});

test('import page renders upload interface and template download link', function () {
    $response = $this->get(route('products.import.form'));

    $response->assertStatus(200);
    $response->assertSee('استيراد المنتجات من إكسيل');
    $response->assertSee('تحميل القالب المنسق');
    $response->assertSee('name="import_file"', false);
});

test('uploading CSV audits rows against database and categorizes new, update, and invalid rows', function () {
    $category = Category::create(['name' => 'إكسسوارات شعر', 'slug' => 'hair']);

    // Pre-existing product in database
    Product::create([
        'sku' => 'KY-EXISTING-01',
        'name' => 'الصنف القديم المسجل',
        'category_id' => $category->id,
        'barcode' => '691112223334',
        'cost_price' => 10.00,
        'wholesale_price' => 20.00,
        'retail_price' => 30.00,
        'unit' => 'قطعة',
        'min_stock_alert' => 5,
    ]);

    // CSV with: 1 new product, 1 existing product (update), 1 invalid product (negative price)
    $csvData = "\xEF\xBB\xBF"."كود الصنف,اسم المنتج,التصنيف,الباركود,سعر التكلفة,سعر الجملة,سعر التجزئة,الوحدة,حد الطلب\n"
        ."KY-NEW-001,توكة لؤلؤ جديدة,توك شعر,699999999999,15.00,28.00,45.00,دستة,10\n"
        ."KY-EXISTING-01,الصنف القديم المحدث,إكسسوارات شعر,691112223334,12.00,25.00,38.00,قطعة,8\n"
        ."KY-INVALID-01,منتج بسعر سالب,عام,,10.00,-5.00,20.00,قطعة,5\n";

    $file = UploadedFile::fake()->createWithContent('products_test.csv', $csvData);

    $response = $this->post(route('products.import.review'), [
        'import_file' => $file,
    ]);

    $response->assertStatus(200);
    $response->assertSee('مراجعة وتدقيق بيانات الاستيراد');

    $reviewData = session('import_review_data');
    expect($reviewData)->not->toBeNull()
        ->and($reviewData['stats']['total'])->toBe(3)
        ->and($reviewData['stats']['new_count'])->toBe(1)
        ->and($reviewData['stats']['update_count'])->toBe(1)
        ->and($reviewData['stats']['invalid_count'])->toBe(1);

    // Assert UI displays status badges
    $response->assertSee('جديد');
    $response->assertSee('تحديث');
    $response->assertSee('خطأ');
    $response->assertSee('KY-NEW-001');
    $response->assertSee('KY-EXISTING-01');
    $response->assertSee('سعر الجملة غير صالح أو سالب');
});

test('confirming import inserts new records, updates existing records, and creates categories atomically', function () {
    $existingCategory = Category::create(['name' => 'خواتم', 'slug' => 'rings']);

    $existingProduct = Product::create([
        'sku' => 'KY-RING-01',
        'name' => 'خاتم كوري قديم',
        'category_id' => $existingCategory->id,
        'cost_price' => 10.00,
        'wholesale_price' => 20.00,
        'retail_price' => 30.00,
        'unit' => 'قطعة',
        'min_stock_alert' => 5,
    ]);

    // Prepare audited review session data
    $reviewData = [
        'stats' => ['total' => 2, 'new_count' => 1, 'update_count' => 1, 'invalid_count' => 0],
        'rows' => [
            [
                'line' => 2,
                'action' => 'new',
                'sku' => 'KY-BRC-01',
                'name' => 'إسورة خرز ملون',
                'category_name' => 'أساور جديدة', // category doesn't exist yet!
                'barcode' => '698887776665',
                'cost_price' => 8.50,
                'wholesale_price' => 16.00,
                'retail_price' => 25.00,
                'unit' => 'قطعة',
                'min_stock_alert' => 10,
                'errors' => [],
            ],
            [
                'line' => 3,
                'action' => 'update',
                'sku' => 'KY-RING-01',
                'name' => 'خاتم كوري محدث المواصفات',
                'category_name' => 'خواتم',
                'barcode' => '691112223334',
                'cost_price' => 12.00,
                'wholesale_price' => 24.00, // Updated wholesale price
                'retail_price' => 35.00,
                'unit' => 'قطعة',
                'min_stock_alert' => 8,
                'errors' => [],
            ],
        ],
    ];

    session(['import_review_data' => $reviewData]);

    $response = $this->post(route('products.import.confirm'), [
        'update_existing' => '1',
        'create_categories' => '1',
    ]);

    $response->assertRedirect(route('products.index'));
    $response->assertSessionHas('status', 'success');

    // 1. Verify New Category was automatically created
    $this->assertDatabaseHas('categories', [
        'name' => 'أساور جديدة',
    ]);

    // 2. Verify New Product was inserted
    $this->assertDatabaseHas('products', [
        'sku' => 'KY-BRC-01',
        'name' => 'إسورة خرز ملون',
        'barcode' => '698887776665',
        'wholesale_price' => 16.00,
    ]);

    // 3. Verify Existing Product was updated
    $this->assertDatabaseHas('products', [
        'id' => $existingProduct->id,
        'sku' => 'KY-RING-01',
        'name' => 'خاتم كوري محدث المواصفات',
        'wholesale_price' => 24.00,
    ]);

    // Session review data is cleared
    expect(session('import_review_data'))->toBeNull();
});
