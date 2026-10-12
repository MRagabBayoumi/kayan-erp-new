<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'product_id',
        'warehouse_id',
        'purchase_invoice_id',
        'initial_quantity',
        'remaining_quantity',
        'cost_price',
        'received_at',
        'is_exhausted',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'initial_quantity' => 'decimal:2',
            'remaining_quantity' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'received_at' => 'datetime',
            'is_exhausted' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'batch_id');
    }

    /**
     * Scope query to active (non-exhausted) batches ordered by FIFO (oldest received first).
     */
    public function scopeAvailableFIFO(Builder $query): Builder
    {
        return $query->where('is_exhausted', false)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Generate unique batch number.
     */
    public static function generateBatchNumber(): string
    {
        $prefix = 'BATCH-'.date('Ymd').'-';
        $random = strtoupper(bin2hex(random_bytes(2)));
        $count = static::whereDate('created_at', today())->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT).'-'.$random;
    }
}
