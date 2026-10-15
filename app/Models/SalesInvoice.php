<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_POSTED = 'posted';

    public const STATUS_VOIDED = 'voided';

    public const TIER_RETAIL = 'retail';

    public const TIER_WHOLESALE = 'wholesale';

    public const TIER_DISTRIBUTOR = 'distributor';

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'warehouse_id',
        'cashier_shift_id',
        'invoice_date',
        'price_tier',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'total_cogs',
        'total_profit',
        'notes',
        'created_by',
        'posted_at',
        'posted_by',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'posted_at' => 'datetime',
            'voided_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'total_cogs' => 'decimal:2',
            'total_profit' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class, 'cashier_shift_id');
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Ymd').'-';
        $count = static::whereDate('created_at', today())->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
