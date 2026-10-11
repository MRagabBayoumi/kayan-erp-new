<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'unit',
        'min_stock_alert',
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
            'min_stock_alert' => 'integer',
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
