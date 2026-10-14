# Kayan ERP — Enterprise Resource Planning System

> **Languages & Documentation:** [النسخة العربية (README.md)](README.md) · [Implementation Roadmap & Full Gap Analysis (ROADMAP.md)](ROADMAP.md)

Enterprise Resource Planning (**ERP**) web platform built for **Kayan**, an import and distribution enterprise specializing in women's fashion accessories, hair supplies, beauty accessories, and novelty items imported from China to domestic wholesale and retail merchants.

The user interface and user experience are engineered following the architectural and interaction standards of the **Odoo Web Client**, featuring full Arabic localization and native Right-to-Left (RTL) layout support.

---

## 📌 Project Phase Status & Roadmap

Every phase is strictly classified into one of four unambiguous engineering states:
- **Verified Complete:** Fully implemented, verified against the database, and covered by automated tests.
- **Partially Implemented:** Application shell or visual layout constructed, awaiting underlying business dependencies.
- **Planned:** Fully scoped, documented in the roadmap, and scheduled in dependency-safe order.
- **Blocked:** Technically blocked from live completion until preceding transactional tables and models exist.

| Phase | Module & Scope | Official Status |
| :--- | :--- | :---: |
| **Phase 1 — Project Audit & Database Isolation** | Toolchain audit, PHP 8.5 environment verification, `kayan_erp` target confirmation and legacy database isolation. | ✅ **Verified Complete** |
| **Phase 2 — Design System & Application Shell** | Master layout, Cairo typography, Light/Dark themes, Enterprise density switcher, modular Blade components. | ✅ **Verified Complete** |
| **Visual QA & Sidebar Bugfix** | Multi-screen browser verification via Chrome CDP, desktop collapse & mobile sliding drawer toggle fix. | ✅ **Verified Complete** |
| **Phase 3A — Company & Barcode Settings (`/settings`)** | Dedicated `/settings` module: company branding, international phone, logo upload with live preview, and thermal barcode printer settings. | ✅ **Verified Complete** |
| **Phase 3B — Dynamic Live Dashboard** | Real-time master dashboard connected to MySQL database with live counters for products, customers, and suppliers. | ✅ **Verified Complete** |
| **Phase 4 — Products, Categories, Excel Import & Thermal Printing** | Catalog schema, multi-tier pricing, SKU generator, indexed barcode, Excel/CSV importer, and direct thermal barcode label printing engine. | ✅ **Verified Complete** |
| **Phase 5 — Warehouses & Multi-Location Inventory** | Multi-warehouse management, stock balances, stock movements (in/out/transfer/adjustment), and low-stock reorder alerts. | ✅ **Verified Complete** |
| **Phase 6 — Wholesale Customers & Suppliers Directory** | Customer ledger accounts, credit terms, China manufacturers with WeChat ID & foreign currency, and shipping agents directory. | ✅ **Verified Complete** |
| **Phase 9 (Part 1) — Purchases & FIFO Receipt Batches** | Purchase invoices, line item entry, automatic inventory batch generation (`inventory_batches`), invoice posting, and document printing. | ✅ **Verified Complete** |
| **Production Architecture & Deployment** | Separate dev/live environments, background auto-start on Windows boot, and automated 1-click zero-downtime deployment script. | ✅ **Verified Complete** |
| **Phase 7 & 8 — Cashier, Point of Sale (POS) & Sales Invoicing** | Fast POS interface, barcode scanner (laser/camera), instant stock deduction, shift & cash drawer management, and thermal receipt printing. | ⏳ **Planned** *(Immediate Next Phase)* |
| **Phase 10 — Cash Register & Treasury Accounting** | Cash boxes, operating expense vouchers, supplier payments, profit & loss statements, and balance sheets. | 📋 **Planned** |
| **Phase 11 — Live Dynamic Dashboard & BI Analytics** | Interactive financial charts, real-time sales KPIs, and aggregated analytics derived from real business transactions. | 📋 **Planned** |
| **Phase 12 — Production Hardening & Deployment** | End-to-end integration testing, concurrency lock tests, security audit, and deployment pipeline. | 📋 **Planned** |

---

## 🛠️ Technology Stack

- **Backend Framework:** [Laravel 13](https://laravel.com) · [PHP 8.5](https://php.net) (powered by Laravel Herd Lite)
- **Database:** [MySQL 8.0](https://mysql.com) — Active database: `kayan_erp`
- **Frontend Architecture:** Reusable Blade Components & Partials
- **Styling:** CSS Design Tokens (`erp.css`) + Tailwind CSS v4 + Vite v8
- **Typography:** [Cairo](https://fonts.google.com/specimen/Cairo) Arabic font with tabular numeric figures (`tabular-nums`) for barcode and accounting data
- **Testing & Quality Assurance:** [Pest PHP](https://pestphp.com) (**165/165 automated tests passing with 1,124 assertions**) + [Laravel Pint](https://laravel.com/docs/pint)
- **Direct Thermal Printing Engine:** Embedded Python thermal printing engine for label stickers and invoices (TSPL / ESC-POS / ZPL).

---

## 🎨 UI/UX System & Odoo-Inspired Highlights

### 1. Odoo Web Client Experience
- **Universal Topbar:** Deep burgundy/plum signature bar (`#714B67`) equipped with global search (`Ctrl+K`), quick notification center, and user profile drawer.
- **3×3 App Launcher Modal:** Instant full-screen navigation modal between core ERP modules, supporting `Escape` key dismissal and backdrop blur.
- **Control Panel Bar:** Breadcrumb trails, contextual action buttons (Save, Back, Export), and view mode toggles (List, Kanban, Graph).
- **Responsive Sidebar:** Collapsible to 64px icon-only rail on desktop, transforming into a sliding drawer with dark overlay on mobile viewports.

### 2. Dual Themes & Density Modes
- **Light Theme:** Subtle gray canvas (`#F0F2F5`), clean white surfaces, and crisp borders optimized for daylight work.
- **Dark Theme:** Deep carbon theme (`#121316`) with high-contrast text and warm borders to minimize eye strain.
- **Enterprise Density Mode:** Independent density control toggle switching between comfortable spacing and dense enterprise table views for high data volume.
- **Zero-Flicker Persistence:** Settings persist instantly in `localStorage` with `<head>` pre-initialization script preventing flash of unstyled theme (FOUC).

### 3. Fully Verified Functional Modules
- **Company & System Settings (Phase 3A: `/settings`):** Global company branding, international phone formatting, logo upload with live preview and orphan file cleanup, and maintenance status badge.
- **Products & Excel Import Engine (Phase 4: `/products`, `/categories`, `/products/import`):**
  - Odoo-style product catalog table with search by Name, SKU, and Barcode, plus category and status filters.
  - **Universal Excel / CSV Import System (`/products/import`):**
    - One-click optional template download (`/products/import/template`) pre-formatted with UTF-8 BOM for Microsoft Excel Arabic compatibility.
    - Pre-import database audit and comparison against `kayan_erp` before committing to disk.
    - Automatic row classification: New Records (green), Existing Records to update (amber), and Validation Errors (red).
    - Atomic execution within database transactions (`DB::transaction`).
  - Unique SKU enforcement and nullable unique barcode indexing allowing multiple products with no barcode without collisions.
  - Three-tier pricing structure: Cost Price (China import), Wholesale Price, and Retail Price, with gross profit margin display.
  - Internal collision-resistant barcode generator for products without pre-printed barcodes.
  - Fast indexed barcode lookup endpoint (`/products/lookup-barcode`) benchmarked at **1.30 ms** average response time.

---

## 📁 Project Directory Structure

```text
app/
├── Http/
│   └── Controllers/
│       ├── CategoryController.php       # Product categories controller
│       ├── CompanySettingController.php # Company & system settings controller
│       ├── Controller.php
│       ├── ProductController.php        # Products, barcode generation & lookup controller
│       └── ProductImportController.php  # Excel / CSV import & review controller
├── Models/
│   ├── Category.php                     # Hierarchical category model
│   ├── CompanySetting.php               # Singleton company settings model
│   ├── Product.php                      # Product model with barcode & pricing logic
│   └── User.php
├── Providers/
│   └── AppServiceProvider.php           # Nullsafe global view composer for settings
└── Services/
    └── ProductImportService.php         # Excel/CSV parser, DB auditor, and import engine

database/
└── migrations/
    ├── 0001_01_01_000000_create_users_table.php
    ├── 2026_10_10_144004_create_company_settings_table.php
    ├── 2026_10_11_170001_create_categories_table.php
    └── 2026_10_11_170002_create_products_table.php

resources/
├── css/
│   ├── app.css                          # Tailwind CSS & Cairo font entry point
│   └── erp.css                          # Design tokens, table styles, and themes
├── js/
│   └── app.js                           # Theme, density, sidebar & launcher interactions
└── views/
    ├── categories/
    │   └── index.blade.php              # Category management view with product counts
    ├── components/
    │   ├── app-launcher.blade.php       # Odoo 3x3 app launcher modal
    │   ├── control-panel.blade.php      # Breadcrumbs, action buttons, view toggles
    │   ├── sidebar.blade.php            # Multi-module responsive navigation drawer
    │   └── topbar.blade.php             # Global application header
    ├── layouts/
    │   └── app.blade.php                # Master HTML5/Blade application shell
    ├── products/
    │   ├── create.blade.php             # New product form with barcode generator
    │   ├── edit.blade.php               # Product edit form with image replacement
    │   ├── import.blade.php             # Excel upload & database review audit screen
    │   ├── index.blade.php              # Products list table with filters & import button
    │   └── show.blade.php               # Product details & profit margin breakdown
    ├── settings/
    │   └── index.blade.php              # Company & system settings view
    └── dashboard.blade.php              # Dashboard visual shell (empty state metrics)
```

---

## 🚀 Getting Started & Local Development

### Prerequisites
- PHP >= 8.5 (verified via Laravel Herd Lite)
- Composer >= 2.8
- Node.js >= 20 & npm
- MySQL 8.0 with dedicated database `kayan_erp`

### Installation & Run Steps

1. **Install PHP and JavaScript dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Compile frontend assets:**
   ```bash
   npm run build
   # Or during active development:
   npm run dev
   ```

3. **Start local development server:**
   ```bash
   php artisan serve
   ```
   *Application serves at: `http://127.0.0.1:8000`*

4. **Execute test suite and code linting:**
   ```bash
   # Run Pest feature tests
   php artisan test --compact

   # Check code formatting with Laravel Pint
   vendor/bin/pint --format agent
   ```

---

## 🔒 Critical Safety & Data Integrity Rules

- **Target Database:** The application strictly connects to `kayan_erp`.
- **Legacy Database Isolation:** Under no circumstances should the legacy `kayan` database (used by the separate desktop application) be touched, modified, migrated, or reset.
- **Zero Fabricated Data:** Never display hardcoded mock figures as real business indicators.
- **Secrets Protection:** Do not commit or display database credentials, `.env` variables, or application secrets in source control or UI views.
- **Transactional Stock Operations:** Inventory must never be deducted merely from barcode scanning or draft creation. All inventory decrements require confirmed sales orders wrapped in atomic database transactions (`DB::transaction`).
