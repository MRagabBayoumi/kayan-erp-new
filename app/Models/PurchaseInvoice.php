<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_POSTED = 'posted';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'warehouse_id',
        'invoice_date',
        'status',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'notes',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'posted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'purchase_invoice_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_POSTED => 'مُرحلة للمخازن',
            self::STATUS_CANCELLED => 'ملغاة',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'erp-badge-warning',
            self::STATUS_POSTED => 'erp-badge-success',
            self::STATUS_CANCELLED => 'erp-badge-danger',
            default => 'erp-badge-secondary',
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'PUR-'.date('Ymd').'-';
        $count = static::whereDate('created_at', today())->count() + 1;
        $random = strtoupper(bin2hex(random_bytes(2)));

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT).'-'.$random;
    }
}
