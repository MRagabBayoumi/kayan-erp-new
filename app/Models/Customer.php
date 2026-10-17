<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'phone_alt',
        'city',
        'address',
        'latitude',
        'longitude',
        'customer_type',
        'pricing_tier',
        'opening_balance',
        'credit_limit',
        'current_balance',
        'notes',
        'is_active',
        'sales_rep_id',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function scopeForSalesRep(Builder $query, int $repId): Builder
    {
        return $query->where('sales_rep_id', $repId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWholesale(Builder $query): Builder
    {
        return $query->where('customer_type', 'wholesale');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%");
        });
    }

    public function getTypeLabel(): string
    {
        return match ($this->customer_type) {
            'wholesale' => 'تاجر جملة',
            'retail' => 'عميل تجزئة',
            'distributor' => 'موزع معتمد',
            default => 'عميل',
        };
    }

    public function getPricingTierLabel(): string
    {
        return match ($this->pricing_tier) {
            'wholesale' => 'سعر الجملة',
            'retail' => 'سعر التجزئة',
            'distributor' => 'سعر الموزع',
            default => 'سعر الجملة',
        };
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class)->latest('invoice_date');
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class)->latest('payment_date');
    }
}
