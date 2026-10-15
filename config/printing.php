<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Centralized Python Print Engine Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration drives the centralized Python printing engine
    | located in scripts/printing/print_engine.py.
    |
    */

    'enabled' => (bool) env('PRINTING_ENGINE_ENABLED', true),

    'python_path' => env('PRINTING_PYTHON_PATH', 'C:\\Users\\PC\\Desktop\\اعلانات\\barcode_printer\\.venv\\Scripts\\python.exe'),

    'engine_script' => env('PRINTING_ENGINE_SCRIPT', base_path('scripts/printing/print_engine.py')),

    'timeout' => (int) env('PRINTING_TIMEOUT', 15),

    'default_printer' => env('PRINTING_DEFAULT_PRINTER', 'Xprinter XP-233B'),

    'default_receipt_printer' => env('PRINTING_DEFAULT_RECEIPT_PRINTER', 'XP-80C'),

    'default_document_printer' => env('PRINTING_DEFAULT_DOCUMENT_PRINTER', 'TOSHIBA Generic Printer XL'),

];
