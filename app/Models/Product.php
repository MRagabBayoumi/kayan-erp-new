<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'barcode',
        'cost_price',
        'wholesale_price',
        'retail_price',
        'distributor_price',
        'unit',
        'pieces_per_package',
        'min_stock_alert',
        'opening_quantity',
        'opening_cost',
        'opening_warehouse_id',
        'opening_balance_date',
        'image_path',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'retail_price' => 'decimal:2',
            'distributor_price' => 'decimal:2',
            'pieces_per_package' => 'integer',
            'min_stock_alert' => 'integer',
            'opening_quantity' => 'decimal:2',
            'opening_cost' => 'decimal:2',
            'opening_balance_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Category that this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Warehouse where the opening balance was recorded.
     */
    public function openingWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'opening_warehouse_id');
    }

    /**
     * Stock balances across all warehouses.
     */
    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    /**
     * Inventory batches (FIFO).
     */
    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Alias for batches().
     */
    public function inventoryBatches(): HasMany
    {
        return $this->batches();
    }

    /**
     * Stock movements history.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Total stock quantity across all warehouses.
     */
    public function getTotalStockAttribute(): float
    {
        if ($this->relationLoaded('stockBalances')) {
            return (float) $this->stockBalances->sum('quantity');
        }

        return (float) $this->stockBalances()->sum('quantity');
    }

    /**
     * Conversion factor of the product's registered unit (e.g. 12 for dozen, 1 for piece).
     */
    public function getUnitFactorAttribute(): float
    {
        if ($this->pieces_per_package && $this->pieces_per_package > 0) {
            return (float) $this->pieces_per_package;
        }

        if (! empty($this->unit)) {
            $unitModel = Unit::where('name', $this->unit)->first();
            if ($unitModel && (float) $unitModel->conversion_factor > 0) {
                return (float) $unitModel->conversion_factor;
            }
        }

        return 1.0;
    }

    /**
     * Format a given stock quantity into a compound human-readable string.
     * e.g. "54 دستة و 11 قطعة (659 قطعة)" or "55 دستة (660 قطعة)".
     */
    public function formatStockBreakdown(?float $stockQty = null): string
    {
        $qty = $stockQty !== null ? $stockQty : $this->total_stock;
        $factor = (int) round($this->unit_factor);
        $unitName = $this->unit ?: 'قطعة';

        if ($qty <= 0) {
            return "0 {$unitName}";
        }

        if ($factor <= 1) {
            $formatted = rtrim(rtrim(number_format($qty, 2), '0'), '.');

            return "{$formatted} {$unitName}";
        }

        $totalPieces = (int) round($qty * $factor);
        $packages = intdiv($totalPieces, $factor);
        $remainderPieces = $totalPieces % $factor;

        if ($remainderPieces === 0) {
            return "{$packages} {$unitName} ({$totalPieces} قطعة)";
        }

        if ($packages === 0) {
            return "{$remainderPieces} قطعة";
        }

        return "{$packages} {$unitName} و {$remainderPieces} قطعة ({$totalPieces} قطعة)";
    }

    /**
     * Get price for a specific tier scaled to a target unit factor.
     */
    public function getPriceForUnit(string $tier = 'retail', ?float $targetUnitFactor = null): float
    {
        $basePrice = match ($tier) {
            'wholesale' => (float) ($this->wholesale_price ?? $this->retail_price ?? 0),
            'distributor' => (float) ($this->distributor_price ?? $this->wholesale_price ?? $this->retail_price ?? 0),
            default => (float) ($this->retail_price ?? 0),
        };

        if ($targetUnitFactor === null) {
            return $basePrice;
        }

        $productFactor = $this->unit_factor;
        if ($productFactor <= 0) {
            $productFactor = 1.0;
        }

        return round($basePrice * ($targetUnitFactor / $productFactor), 2);
    }

    /**
     * Check if product is low on stock based on min_stock_alert threshold.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock <= (float) $this->min_stock_alert;
    }

    /**
     * Image URL accessor.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return asset('storage/'.$this->image_path);
        }

        return null;
    }

    /**
     * Scope search query across name, SKU, and barcode.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    /**
     * Scope query to only active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query by category ID.
     */
    public function scopeByCategory(Builder $query, ?int $categoryId): Builder
    {
        if (! $categoryId) {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    /**
     * Generate an internal collision-resistant barcode.
     *
     * Note: This is an internal proprietary barcode format (prefix 20 + 10 random numeric digits),
     * NOT a certified global GS1 barcode.
     */
    public static function generateInternalBarcode(): string
    {
        $prefix = BarcodeSetting::getSettings()->barcode_prefix ?: '02';
        $maxAttempts = 15;
        $attempt = 0;

        do {
            $attempt++;
            $candidate = $prefix.str_pad((string) random_int(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
            $exists = static::where('barcode', $candidate)->exists();
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            // High-entropy fallback
            $candidate = $prefix.substr((string) microtime(true), -8).random_int(10, 99);
        }

        return $candidate;
    }
}
