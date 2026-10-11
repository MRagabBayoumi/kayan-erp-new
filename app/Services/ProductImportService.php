<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class ProductImportService
{
    /**
     * Standard column mapping dictionary supporting both Arabic and English headers.
     */
    protected array $headerAliases = [
        'sku' => ['sku', 'كود الصنف', 'كود المنتج', 'الرمز', 'code', 'الكود'],
        'name' => ['name', 'اسم المنتج', 'اسم الصنف', 'الاسم', 'title', 'الصنف'],
        'category' => ['category', 'category_name', 'التصنيف', 'القسم', 'الفئة', 'المجموعة'],
        'barcode' => ['barcode', 'الباركود', 'باركود', 'كود الباركود'],
        'cost_price' => ['cost_price', 'cost', 'سعر التكلفة', 'التكلفة', 'سعر الشراء', 'تكلفة'],
        'wholesale_price' => ['wholesale_price', 'wholesale', 'سعر الجملة', 'الجملة', 'سعر بيع الجملة'],
        'retail_price' => ['retail_price', 'retail', 'سعر التجزئة', 'القطاعي', 'سعر البيع', 'التجزئة'],
        'unit' => ['unit', 'الوحدة', 'وحدة القياس', 'نوع الوحدة'],
        'min_stock_alert' => ['min_stock_alert', 'حد الطلب', 'الحد الأدنى', 'حد النواقص', 'تنبيه النواقص', 'الحد الادنى'],
    ];

    /**
     * Generate standard CSV template content with UTF-8 BOM for perfect Excel Arabic rendering.
     */
    public function generateTemplateCsv(): string
    {
        $headers = [
            'كود الصنف (SKU)',
            'اسم المنتج',
            'التصنيف',
            'الباركود (اختياري)',
            'سعر التكلفة (ج.م)',
            'سعر الجملة (ج.م)',
            'سعر التجزئة (ج.م)',
            'وحدة القياس',
            'حد الطلب الأدنى',
        ];

        $sampleRows = [
            [
                'KY-26-1001',
                'توكة شعر لؤلؤ أبيض كوري فاخر',
                'توك شعر',
                '6920011223344',
                '14.50',
                '25.00',
                '40.00',
                'دستة',
                '10',
            ],
            [
                'KY-26-1002',
                'طوق شعر ستان كلاسيك وردي',
                'أطواق وبكلات',
                '6920011223355',
                '18.00',
                '32.00',
                '50.00',
                'قطعة',
                '5',
            ],
            [
                'KY-26-1003',
                'بكلة شعر فراشة مرصعة كريستال',
                'بكلات كريستال',
                '',
                '11.00',
                '20.00',
                '35.00',
                'قطعة',
                '12',
            ],
        ];

        // Output with UTF-8 BOM so Microsoft Excel opens Arabic text cleanly without distortion
        $output = "\xEF\xBB\xBF";
        $handle = fopen('php://memory', 'r+');

        fputcsv($handle, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $output .= stream_get_contents($handle);
        fclose($handle);

        return $output;
    }

    /**
     * Parse uploaded file (CSV or XLSX) into normalized associative rows.
     */
    public function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx') {
            return $this->parseXlsx($file->getRealPath());
        }

        return $this->parseCsv($file->getRealPath());
    }

    /**
     * Parse CSV file with auto-delimiter detection and UTF-8 handling.
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        // Read first line to detect BOM and delimiter
        $firstLine = fgets($handle);
        if (! $firstLine) {
            fclose($handle);

            return [];
        }

        // Strip UTF-8 BOM if present
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        // Detect delimiter: comma, semicolon, tab
        $delimiters = [',', ';', "\t"];
        $chosenDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $chosenDelimiter = $delim;
            }
        }

        rewind($handle);
        // Strip BOM from stream if present
        $bomCheck = fread($handle, 3);
        if ($bomCheck !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rawHeaders = fgetcsv($handle, 0, $chosenDelimiter);
        if (! $rawHeaders) {
            fclose($handle);

            return [];
        }

        $headerMap = $this->mapHeaders($rawHeaders);
        $rows = [];
        $lineNum = 1;

        while (($data = fgetcsv($handle, 0, $chosenDelimiter)) !== false) {
            $lineNum++;
            // Skip empty rows
            if (empty(array_filter($data, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $rows[] = $this->extractRowData($data, $headerMap, $lineNum);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Lightweight native XLSX parsing using PHP's ZipArchive and SimpleXML without external packages.
     */
    protected function parseXlsx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return $this->parseCsv($path); // Fallback to CSV reader
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $stringsXmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXmlContent) {
            $stringsXml = simplexml_load_string($stringsXmlContent);
            if ($stringsXml && isset($stringsXml->si)) {
                foreach ($stringsXml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Read sheet1
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXmlContent) {
            return [];
        }

        $sheetXml = simplexml_load_string($sheetXmlContent);
        if (! $sheetXml || ! isset($sheetXml->sheetData->row)) {
            return [];
        }

        $grid = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rowValues = [];
            foreach ($row->c as $cell) {
                $cellType = (string) $cell['t'];
                $val = (string) $cell->v;

                if ($cellType === 's' && isset($sharedStrings[(int) $val])) {
                    $rowValues[] = $sharedStrings[(int) $val];
                } else {
                    $rowValues[] = $val;
                }
            }
            if (! empty(array_filter($rowValues, fn ($v) => trim((string) $v) !== ''))) {
                $grid[] = $rowValues;
            }
        }

        if (empty($grid)) {
            return [];
        }

        $rawHeaders = array_shift($grid);
        $headerMap = $this->mapHeaders($rawHeaders);
        $rows = [];
        $lineNum = 1;

        foreach ($grid as $data) {
            $lineNum++;
            $rows[] = $this->extractRowData($data, $headerMap, $lineNum);
        }

        return $rows;
    }

    /**
     * Map header column indexes to normalized property keys.
     */
    protected function mapHeaders(array $rawHeaders): array
    {
        $map = [];

        foreach ($rawHeaders as $idx => $header) {
            $cleanHeader = trim(mb_strtolower((string) $header));
            // Remove parenthetical notes e.g. "كود الصنف (SKU)" -> "كود الصنف"
            $normalized = trim(preg_replace('/\s*\(.*?\)\s*/u', '', $cleanHeader));

            foreach ($this->headerAliases as $standardKey => $aliases) {
                foreach ($aliases as $alias) {
                    if ($cleanHeader === $alias || $normalized === $alias || Str::contains($cleanHeader, $alias)) {
                        if (! isset($map[$standardKey])) {
                            $map[$standardKey] = $idx;
                            break 2;
                        }
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Extract normalized row data using the header map.
     */
    protected function extractRowData(array $data, array $headerMap, int $lineNum): array
    {
        $row = [
            '_line_number' => $lineNum,
            'sku' => isset($headerMap['sku']) && isset($data[$headerMap['sku']]) ? trim((string) $data[$headerMap['sku']]) : null,
            'name' => isset($headerMap['name']) && isset($data[$headerMap['name']]) ? trim((string) $data[$headerMap['name']]) : null,
            'category' => isset($headerMap['category']) && isset($data[$headerMap['category']]) ? trim((string) $data[$headerMap['category']]) : null,
            'barcode' => isset($headerMap['barcode']) && isset($data[$headerMap['barcode']]) ? trim((string) $data[$headerMap['barcode']]) : null,
            'cost_price' => isset($headerMap['cost_price']) && isset($data[$headerMap['cost_price']]) ? trim((string) $data[$headerMap['cost_price']]) : '0',
            'wholesale_price' => isset($headerMap['wholesale_price']) && isset($data[$headerMap['wholesale_price']]) ? trim((string) $data[$headerMap['wholesale_price']]) : '0',
            'retail_price' => isset($headerMap['retail_price']) && isset($data[$headerMap['retail_price']]) ? trim((string) $data[$headerMap['retail_price']]) : '0',
            'unit' => isset($headerMap['unit']) && isset($data[$headerMap['unit']]) && trim((string) $data[$headerMap['unit']]) !== '' ? trim((string) $data[$headerMap['unit']]) : 'قطعة',
            'min_stock_alert' => isset($headerMap['min_stock_alert']) && isset($data[$headerMap['min_stock_alert']]) ? (int) $data[$headerMap['min_stock_alert']] : 5,
        ];

        return $row;
    }

    /**
     * Audit and compare parsed rows against active database `kayan_erp`.
     *
     * Classifies each row into:
     * - new: product does not exist in DB, ready for insertion
     * - update: product already exists in DB (matched by SKU or Barcode), ready for updating
     * - invalid: row has validation issues (missing name, negative price, etc.)
     */
    public function auditRows(array $parsedRows): array
    {
        $audited = [];
        $existingSkus = Product::pluck('id', 'sku')->toArray();
        $existingBarcodes = Product::whereNotNull('barcode')->pluck('id', 'barcode')->toArray();
        $categoriesByName = Category::pluck('id', 'name')->toArray();

        $stats = [
            'total' => count($parsedRows),
            'new_count' => 0,
            'update_count' => 0,
            'invalid_count' => 0,
        ];

        $seenInFileSkus = [];
        $seenInFileBarcodes = [];

        foreach ($parsedRows as $row) {
            $line = $row['_line_number'];
            $errors = [];
            $action = 'new';
            $existingProduct = null;

            // 1. Validate Product Name
            if (empty($row['name'])) {
                $errors[] = "السطر {$line}: اسم المنتج مفقود ولا يمكن تركه فارغاً.";
            }

            // 2. Validate/Auto-generate SKU
            if (empty($row['sku'])) {
                $errors[] = "السطر {$line}: كود الصنف (SKU) مطلوب.";
            } elseif (isset($seenInFileSkus[$row['sku']])) {
                $errors[] = "السطر {$line}: كود الصنف '{$row['sku']}' مكرر أكثر من مرة في نفس الملف.";
            } else {
                $seenInFileSkus[$row['sku']] = true;
            }

            // 3. Validate Barcode
            $barcode = ! empty($row['barcode']) ? $row['barcode'] : null;
            if ($barcode) {
                if (isset($seenInFileBarcodes[$barcode])) {
                    $errors[] = "السطر {$line}: الباركود '{$barcode}' مكرر داخل نفس الملف.";
                } else {
                    $seenInFileBarcodes[$barcode] = true;
                }
            }

            // 4. Validate Prices
            $costPrice = is_numeric($row['cost_price']) ? (float) $row['cost_price'] : -1;
            $wholesalePrice = is_numeric($row['wholesale_price']) ? (float) $row['wholesale_price'] : -1;
            $retailPrice = is_numeric($row['retail_price']) ? (float) $row['retail_price'] : -1;
            $minAlert = is_numeric($row['min_stock_alert']) ? (int) $row['min_stock_alert'] : -1;

            if ($costPrice < 0) {
                $errors[] = "السطر {$line}: سعر التكلفة غير صالح أو سالب ({$row['cost_price']}).";
            }
            if ($wholesalePrice < 0) {
                $errors[] = "السطر {$line}: سعر الجملة غير صالح أو سالب ({$row['wholesale_price']}).";
            }
            if ($retailPrice < 0) {
                $errors[] = "السطر {$line}: سعر التجزئة غير صالح أو سالب ({$row['retail_price']}).";
            }
            if ($minAlert < 0) {
                $errors[] = "السطر {$line}: حد الطلب غير صالح أو سالب.";
            }

            // 5. Check existence in database
            if (empty($errors)) {
                $matchIdBySku = ! empty($row['sku']) && isset($existingSkus[$row['sku']]) ? $existingSkus[$row['sku']] : null;
                $matchIdByBarcode = $barcode && isset($existingBarcodes[$barcode]) ? $existingBarcodes[$barcode] : null;

                if ($matchIdBySku || $matchIdByBarcode) {
                    $action = 'update';
                    $matchedId = $matchIdBySku ?: $matchIdByBarcode;
                    $existingProduct = Product::find($matchedId);
                }
            }

            if (! empty($errors)) {
                $action = 'invalid';
                $stats['invalid_count']++;
            } elseif ($action === 'update') {
                $stats['update_count']++;
            } else {
                $stats['new_count']++;
            }

            $audited[] = [
                'line' => $line,
                'action' => $action,
                'sku' => $row['sku'],
                'name' => $row['name'],
                'category_name' => $row['category'] ?: 'عام',
                'barcode' => $barcode,
                'cost_price' => max(0, $costPrice),
                'wholesale_price' => max(0, $wholesalePrice),
                'retail_price' => max(0, $retailPrice),
                'unit' => $row['unit'] ?: 'قطعة',
                'min_stock_alert' => max(0, $minAlert),
                'errors' => $errors,
                'existing_product' => $existingProduct ? [
                    'id' => $existingProduct->id,
                    'name' => $existingProduct->name,
                    'old_cost_price' => (float) $existingProduct->cost_price,
                    'old_wholesale_price' => (float) $existingProduct->wholesale_price,
                    'old_retail_price' => (float) $existingProduct->retail_price,
                ] : null,
            ];
        }

        return [
            'stats' => $stats,
            'rows' => $audited,
        ];
    }

    /**
     * Execute the verified import safely inside an atomic database transaction.
     */
    public function executeImport(array $reviewedRows, bool $updateExisting = true, bool $createCategories = true): array
    {
        return DB::transaction(function () use ($reviewedRows, $updateExisting, $createCategories) {
            $insertedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            // Cache categories
            $categories = Category::all()->keyBy('name');

            foreach ($reviewedRows as $item) {
                if ($item['action'] === 'invalid') {
                    $skippedCount++;

                    continue;
                }

                // Resolve Category
                $categoryName = trim((string) ($item['category_name'] ?: 'عام'));
                $category = $categories->get($categoryName);

                if (! $category) {
                    if ($createCategories) {
                        $category = Category::create([
                            'name' => $categoryName,
                            'slug' => Str::slug($categoryName, '-', null) ?: 'cat-'.Str::random(8),
                            'description' => 'تم إنشاؤه تلقائياً أثناء استيراد الأصناف',
                            'is_active' => true,
                        ]);
                        $categories->put($categoryName, $category);
                    } else {
                        $category = Category::first() ?: Category::create(['name' => 'عام', 'slug' => 'general']);
                    }
                }

                $payload = [
                    'sku' => $item['sku'],
                    'name' => $item['name'],
                    'category_id' => $category->id,
                    'barcode' => ! empty($item['barcode']) ? $item['barcode'] : null,
                    'cost_price' => $item['cost_price'],
                    'wholesale_price' => $item['wholesale_price'],
                    'retail_price' => $item['retail_price'],
                    'unit' => $item['unit'] ?: 'قطعة',
                    'min_stock_alert' => $item['min_stock_alert'] ?? 5,
                    'is_active' => true,
                ];

                if ($item['action'] === 'update') {
                    if ($updateExisting) {
                        $product = Product::where('sku', $item['sku'])
                            ->orWhere(function ($q) use ($item) {
                                if (! empty($item['barcode'])) {
                                    $q->where('barcode', $item['barcode']);
                                }
                            })->first();

                        if ($product) {
                            $product->update($payload);
                            $updatedCount++;
                        } else {
                            Product::create($payload);
                            $insertedCount++;
                        }
                    } else {
                        $skippedCount++;
                    }
                } else {
                    Product::create($payload);
                    $insertedCount++;
                }
            }

            return [
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
            ];
        });
    }
}
