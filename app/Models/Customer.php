<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'customer_type',
        'pricing_tier',
        'opening_balance',
        'credit_limit',
        'current_balance',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
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
            default => 'سعر الجملة',
        };
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class)->latest('invoice_date');
    }
}
