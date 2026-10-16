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

    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_CREDIT = 'credit';

    public const PAYMENT_PARTIAL = 'partial';

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'warehouse_id',
        'invoice_date',
        'payment_type',
        'status',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'notes',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'posted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            self::PAYMENT_CASH => 'نقدي',
            self::PAYMENT_CREDIT => 'آجل',
            self::PAYMENT_PARTIAL => 'دفع جزئي',
            default => 'نقدي',
        };
    }

    public function getPaymentTypeBadgeClassAttribute(): string
    {
        return match ($this->payment_type) {
            self::PAYMENT_CASH => 'erp-badge-success',
            self::PAYMENT_CREDIT => 'erp-badge-danger',
            self::PAYMENT_PARTIAL => 'erp-badge-warning',
            default => 'erp-badge-neutral',
        };
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
        return \App\Services\DocumentSequenceService::getNextNumber('purchase_invoice', 'PUR-', 6);
    }
}
