<?php

namespace App\Services\Printing;

use App\Models\BarcodeSetting;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Services\BarcodeGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

class PythonPrintEngineService
{
    protected string $pythonPath;

    protected string $engineScript;

    protected string $defaultPrinter;

    protected int $timeout;

    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = (bool) config('printing.enabled', config('services.printing.enabled', true));
        $this->pythonPath = (string) config('printing.python_path', config('services.printing.python_path', 'C:\\Users\\PC\\Desktop\\اعلانات\\barcode_printer\\.venv\\Scripts\\python.exe'));
        $this->engineScript = (string) config('printing.engine_script', config('services.printing.engine_script', base_path('scripts/printing/print_engine.py')));
        $this->defaultPrinter = (string) config('printing.default_printer', config('services.printing.default_printer', 'Xprinter XP-233B'));
        $this->timeout = (int) config('printing.timeout', config('services.printing.timeout', 15));
    }

    /**
     * Check if the Python print engine is enabled and executable.
     */
    public function isAvailable(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (! file_exists($this->engineScript)) {
            return false;
        }

        if (file_exists($this->pythonPath)) {
            return true;
        }

        if (in_array(strtolower($this->pythonPath), ['python', 'python.exe', 'py', 'py.exe'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Execute a print job via the centralized Python print engine.
     *
     * @param  string  $templateId  Registered template identifier
     * @param  array<string, mixed>  $data  Template data dictionary
     * @param  int  $copies  Number of copies (min: 1, max: 500)
     * @param  string|null  $printer  Target Windows printer name
     * @param  bool  $dryRun  If true, render only without sending to Windows spooler
     * @param  string|null  $outputImage  Optional file path to save rendered PNG
     * @return array<string, mixed>
     */
    public function executeJob(
        string $templateId,
        array $data,
        int $copies = 1,
        ?string $printer = null,
        bool $dryRun = false,
        ?string $outputImage = null
    ): array {
        $copies = max(1, min(500, $copies));
        $targetPrinter = $printer ?: $this->defaultPrinter;
        $userId = Auth::id() ?? 'system';

        if (! $this->isAvailable()) {
            $msg = 'محرك الطباعة المركزي غير متاح أو لم يتم ضبط مسار بايثون بشكل صحيح.';
            Log::warning('[PrintEngine] Engine unavailable', [
                'user_id' => $userId,
                'python_path' => $this->pythonPath,
                'engine_script' => $this->engineScript,
                'enabled' => $this->enabled,
            ]);

            return [
                'success' => false,
                'status' => 'UNAVAILABLE',
                'message' => $msg,
            ];
        }

        $jobPayload = [
            'template_id' => $templateId,
            'data' => $data,
            'copies' => $copies,
            'printer_name' => $targetPrinter,
        ];

        $command = [$this->pythonPath, $this->engineScript, '--stdin'];

        if ($dryRun) {
            $command[] = '--dry-run';
        }

        if ($outputImage) {
            $command[] = '--output-image';
            $command[] = $outputImage;
        }

        $jsonInput = json_encode($jobPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info('[PrintEngine] Dispatching print job', [
            'user_id' => $userId,
            'template_id' => $templateId,
            'copies' => $copies,
            'printer' => $targetPrinter,
            'dry_run' => $dryRun,
        ]);

        $process = new Process($command, null, ['PYTHONIOENCODING' => 'utf-8']);
        $process->setInput($jsonInput);
        $process->setTimeout($this->timeout);

        try {
            $process->run();
            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());

            if (! empty($errorOutput)) {
                Log::debug('[PrintEngine] Engine stderr', ['stderr' => $errorOutput]);
            }

            $decoded = json_decode($output, true);

            if (! is_array($decoded)) {
                Log::error('[PrintEngine] Invalid JSON response from engine', [
                    'exit_code' => $process->getExitCode(),
                    'raw_output' => $output,
                    'stderr' => $errorOutput,
                ]);

                return [
                    'success' => false,
                    'status' => 'INVALID_OUTPUT',
                    'message' => 'استجابة غير صالحة من محرك الطباعة.',
                    'exit_code' => $process->getExitCode(),
                ];
            }

            if (! ($decoded['success'] ?? false)) {
                Log::error('[PrintEngine] Job execution failed', [
                    'template_id' => $templateId,
                    'error' => $decoded['error'] ?? 'Unknown error',
                    'details' => $decoded['details'] ?? null,
                ]);

                return [
                    'success' => false,
                    'status' => $decoded['status'] ?? 'ERROR',
                    'message' => $decoded['message'] ?? 'فشل تنفيذ أمر الطباعة.',
                    'error' => $decoded['error'] ?? null,
                    'details' => $decoded['details'] ?? null,
                ];
            }

            Log::info('[PrintEngine] Job completed successfully', [
                'template_id' => $templateId,
                'status' => $decoded['status'] ?? 'SUCCESS',
                'printer' => $decoded['printer'] ?? $targetPrinter,
            ]);

            return [
                'success' => true,
                'status' => $decoded['status'] ?? 'SUCCESS',
                'message' => $decoded['message'] ?? 'تم إرسال أمر الطباعة بنجاح.',
                'printer' => $decoded['printer'] ?? $targetPrinter,
                'copies' => $copies,
                'template_id' => $templateId,
            ];
        } catch (ProcessTimedOutException $e) {
            Log::error('[PrintEngine] Execution timed out', [
                'timeout' => $this->timeout,
                'template_id' => $templateId,
            ]);

            return [
                'success' => false,
                'status' => 'TIMEOUT',
                'message' => 'تجاوزت عملية الطباعة المهلة الزمنية المحددة ('.$this->timeout.' ثانية).',
            ];
        } catch (Throwable $e) {
            Log::error('[PrintEngine] Unexpected exception during print job', [
                'exception' => $e->getMessage(),
                'template_id' => $templateId,
            ]);

            return [
                'success' => false,
                'status' => 'EXCEPTION',
                'message' => 'حدث خطأ غير متوقع أثناء معالجة أمر الطباعة.',
            ];
        }
    }

    /**
     * Print a thermal barcode sticker for a product.
     *
     * @param  Product  $product  Authoritative product instance
     * @param  int  $quantity  Number of copies
     * @param  string|null  $printer  Target printer
     * @param  array<string, mixed>  $overrides  Optional dimensions or overrides
     * @param  bool  $dryRun  If true, render only without sending to spooler
     * @return array<string, mixed>
     */
    public function printBarcodeLabel(Product $product, int $quantity = 1, ?string $printer = null, array $overrides = [], bool $dryRun = false): array
    {
        $barcodeSettings = BarcodeSetting::getSettings();
        $barcodeService = app(BarcodeGeneratorService::class);
        $barcodeValue = $barcodeService->getBarcodeValue($product);

        $widthMm = (float) ($overrides['width_mm'] ?? $barcodeSettings->label_width_mm ?? 38);
        $heightMm = (float) ($overrides['height_mm'] ?? $barcodeSettings->label_height_mm ?? 15);
        $targetPrinter = $printer ?: ($barcodeSettings->printer_name ?: null);

        $data = [
            'product_name' => $product->name,
            'barcode' => $barcodeValue,
            'sku' => $product->sku ?? '',
            'retail_price' => (float) ($product->retail_price ?? 0),
            'wholesale_price' => (float) ($product->wholesale_price ?? 0),
            'currency' => 'ج.م',
            'show_company_name' => (bool) $barcodeSettings->show_company_name,
            'company_name' => $barcodeSettings->getEffectiveCompanyName(),
            'show_product_name' => (bool) $barcodeSettings->show_product_name,
            'show_barcode_bars' => (bool) $barcodeSettings->show_barcode_bars,
            'show_item_sku' => (bool) $barcodeSettings->show_item_sku,
            'print_price_type' => (string) ($barcodeSettings->print_price_type ?? 'none'),
            'product_font_size' => (int) $barcodeSettings->product_font_size,
            'details_font_size' => (int) $barcodeSettings->details_font_size,
            'barcode_width_px' => (int) $barcodeSettings->barcode_width_px,
            'barcode_height_px' => (int) $barcodeSettings->barcode_height_px,
            'width_mm' => $widthMm,
            'height_mm' => $heightMm,
        ];

        // Apply any caller overrides directly
        foreach ($overrides as $k => $v) {
            $data[$k] = $v;
        }

        return $this->executeJob('barcode_label', $data, $quantity, $targetPrinter, $dryRun);
    }

    /**
     * Print a batch of multiple product barcode labels in a single atomic RAW print job.
     * Prevents blank label feeds between products by maintaining a single continuous stream.
     *
     * @param  array<int, array{product: Product, qty?: int, quantity?: int}>  $items  List of product items and quantities
     * @param  string|null  $printer  Target printer
     * @param  array<string, mixed>  $overrides  Optional dimensions or overrides
     * @param  bool  $dryRun  If true, render only without sending to spooler
     * @return array<string, mixed>
     */
    public function printBarcodeLabelBatch(array $items, ?string $printer = null, array $overrides = [], bool $dryRun = false): array
    {
        if (empty($items)) {
            return [
                'success' => false,
                'status' => 'EMPTY_BATCH',
                'message' => 'لا توجد أصناف صالحة للطباعة في الدفعة.',
            ];
        }

        $barcodeSettings = BarcodeSetting::getSettings();
        $barcodeService = app(BarcodeGeneratorService::class);
        $targetPrinter = $printer ?: ($barcodeSettings->printer_name ?: null);

        $widthMm = (float) ($overrides['width_mm'] ?? $barcodeSettings->label_width_mm ?? 38);
        $heightMm = (float) ($overrides['height_mm'] ?? $barcodeSettings->label_height_mm ?? 15);

        $batchPayload = [];
        $totalQty = 0;

        foreach ($items as $entry) {
            $product = $entry['product'] ?? null;
            if (! $product instanceof Product) {
                continue;
            }

            $quantity = max(1, min(500, (int) ($entry['qty'] ?? $entry['quantity'] ?? 1)));
            $barcodeValue = $barcodeService->getBarcodeValue($product);

            $data = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'barcode' => $barcodeValue,
                'sku' => $product->sku ?? '',
                'retail_price' => (float) ($product->retail_price ?? 0),
                'wholesale_price' => (float) ($product->wholesale_price ?? 0),
                'currency' => 'ج.م',
                'show_company_name' => (bool) $barcodeSettings->show_company_name,
                'company_name' => $barcodeSettings->getEffectiveCompanyName(),
                'show_product_name' => (bool) $barcodeSettings->show_product_name,
                'show_barcode_bars' => (bool) $barcodeSettings->show_barcode_bars,
                'show_item_sku' => (bool) $barcodeSettings->show_item_sku,
                'print_price_type' => (string) ($barcodeSettings->print_price_type ?? 'none'),
                'product_font_size' => (int) $barcodeSettings->product_font_size,
                'details_font_size' => (int) $barcodeSettings->details_font_size,
                'barcode_width_px' => (int) $barcodeSettings->barcode_width_px,
                'barcode_height_px' => (int) $barcodeSettings->barcode_height_px,
                'width_mm' => $widthMm,
                'height_mm' => $heightMm,
            ];

            foreach ($overrides as $k => $v) {
                $data[$k] = $v;
            }

            $batchPayload[] = [
                'template_id' => 'barcode_label',
                'copies' => $quantity,
                'data' => $data,
            ];

            $totalQty += $quantity;
        }

        if (empty($batchPayload)) {
            return [
                'success' => false,
                'status' => 'EMPTY_BATCH',
                'message' => 'لم يتم العثور على أصناف صالحة في الدفعة.',
            ];
        }

        return $this->executeBatchJob($batchPayload, $targetPrinter, $dryRun);
    }

    /**
     * Execute a batch print job payload via the Python printing engine.
     *
     * @param  array<int, array<string, mixed>>  $batchItems  Array of batch entries
     * @param  string|null  $printer  Target Windows printer name
     * @param  bool  $dryRun  If true, render only without sending to spooler
     * @return array<string, mixed>
     */
    public function executeBatchJob(array $batchItems, ?string $printer = null, bool $dryRun = false): array
    {
        $targetPrinter = $printer ?: $this->defaultPrinter;
        $userId = Auth::id() ?? 'system';

        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'status' => 'UNAVAILABLE',
                'message' => 'محرك الطباعة المركزي غير متاح أو لم يتم ضبط مسار بايثون بشكل صحيح.',
            ];
        }

        $jobPayload = [
            'printer_name' => $targetPrinter,
            'batch' => $batchItems,
        ];

        $command = [$this->pythonPath, $this->engineScript, '--stdin'];
        if ($dryRun) {
            $command[] = '--dry-run';
        }

        $jsonInput = json_encode($jobPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info('[PrintEngine] Dispatching batch print job', [
            'user_id' => $userId,
            'total_items' => count($batchItems),
            'printer' => $targetPrinter,
            'dry_run' => $dryRun,
        ]);

        $process = new Process($command, null, ['PYTHONIOENCODING' => 'utf-8']);
        $process->setInput($jsonInput);
        $process->setTimeout($this->timeout);

        try {
            $process->run();
            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());

            if (! empty($errorOutput)) {
                Log::debug('[PrintEngine] Engine stderr', ['stderr' => $errorOutput]);
            }

            $decoded = json_decode($output, true);
            if (! is_array($decoded)) {
                return [
                    'success' => false,
                    'status' => 'INVALID_OUTPUT',
                    'message' => 'استجابة غير صالحة من محرك الطباعة.',
                    'exit_code' => $process->getExitCode(),
                ];
            }

            if (! ($decoded['success'] ?? false)) {
                return [
                    'success' => false,
                    'status' => $decoded['status'] ?? 'ERROR',
                    'message' => $decoded['message'] ?? 'فشل تنفيذ أمر الطباعة المجمعة.',
                    'error' => $decoded['error'] ?? null,
                ];
            }

            return [
                'success' => true,
                'status' => $decoded['status'] ?? 'SUCCESS',
                'batch_mode' => true,
                'message' => $decoded['message'] ?? 'تم إرسال أمر الطباعة المجمعة بنجاح.',
                'printer' => $decoded['printer'] ?? $targetPrinter,
                'total_products' => $decoded['total_products'] ?? count($batchItems),
                'total_labels' => $decoded['total_labels'] ?? count($batchItems),
                'items_summary' => $decoded['items_summary'] ?? [],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status' => 'EXCEPTION',
                'message' => 'حدث خطأ أثناء الاتصال بمحرك الطباعة: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Print an 80mm thermal receipt for a purchase invoice.
     *
     * @param  PurchaseInvoice  $invoice  Authoritative invoice instance
     * @param  string|null  $printer  Target printer (e.g. XP-80C)
     * @param  bool  $dryRun  If true, render only without sending to spooler
     * @return array<string, mixed>
     */
    public function printPurchaseInvoice(PurchaseInvoice $invoice, ?string $printer = null, bool $dryRun = false): array
    {
        $invoice->loadMissing(['supplier', 'warehouse', 'items.product']);

        $items = $invoice->items->map(function ($item) {
            return [
                'name' => $item->product?->name ?? 'صنف غير محدد',
                'quantity' => (float) $item->quantity,
                'price' => (float) ($item->unit_cost ?? $item->unit_price ?? 0),
                'total' => (float) ($item->subtotal ?? $item->total_price ?? 0),
            ];
        })->toArray();

        $data = [
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'),
            'supplier_name' => $invoice->supplier?->name ?? 'مورد عام',
            'warehouse_name' => $invoice->warehouse?->name ?? 'المخزن الرئيسي',
            'total_amount' => (float) $invoice->total_amount,
            'paid_amount' => (float) ($invoice->paid_amount ?? 0),
            'remaining_amount' => (float) ($invoice->remaining_amount ?? 0),
            'items' => $items,
        ];

        $receiptPrinter = $printer ?: config('printing.default_receipt_printer', 'XP-80C');

        return $this->executeJob('purchase_invoice_receipt', $data, 1, $receiptPrinter, $dryRun);
    }

    /**
     * Retrieve list of registered templates from the Python engine.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listTemplates(): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $process = new Process([$this->pythonPath, $this->engineScript, '--list-templates']);
        $process->setTimeout(5);

        try {
            $process->run();
            $decoded = json_decode($process->getOutput(), true);

            return is_array($decoded) && isset($decoded['templates']) ? $decoded['templates'] : [];
        } catch (Throwable $e) {
            Log::error('[PrintEngine] Failed to list templates', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Retrieve list of installed Windows printers from the Python engine.
     *
     * @return array<int, string>
     */
    public function listPrinters(): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $process = new Process([$this->pythonPath, $this->engineScript, '--list-printers']);
        $process->setTimeout(5);

        try {
            $process->run();
            $decoded = json_decode($process->getOutput(), true);

            return is_array($decoded) && isset($decoded['printers']) ? $decoded['printers'] : [];
        } catch (Throwable $e) {
            Log::error('[PrintEngine] Failed to list printers', ['error' => $e->getMessage()]);

            return [];
        }
    }
}
