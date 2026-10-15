<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'is_active', 'warehouse_id', 'can_access_all_warehouses'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_WAREHOUSE = 'warehouse';

    public const ROLE_CASHIER = 'cashier';

    public const ROLES = [
        self::ROLE_ADMIN => 'مدير النظام (Admin)',
        self::ROLE_WAREHOUSE => 'أمين مخزن (Warehouse)',
        self::ROLE_CASHIER => 'كاشير (Cashier)',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_access_all_warehouses' => 'boolean',
        ];
    }

    /**
     * Primary assigned workplace / warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Check if user is allowed to access and process transactions from specific warehouse.
     */
    public function canAccessWarehouse(int|string|null $warehouseId): bool
    {
        if ($this->isAdmin() || $this->can_access_all_warehouses) {
            return true;
        }

        // Users with no specific warehouse assigned are unrestricted
        if (empty($this->warehouse_id)) {
            return true;
        }

        if (empty($warehouseId)) {
            return false;
        }

        return (int) $this->warehouse_id === (int) $warehouseId;
    }

    /**
     * Get collection of allowed warehouses for transactions (POS / sales / inventory).
     */
    public function getAllowedWarehouses()
    {
        if ($this->isAdmin() || $this->can_access_all_warehouses || empty($this->warehouse_id)) {
            return Warehouse::active()->orderBy('name')->get();
        }

        $wh = Warehouse::where('id', $this->warehouse_id)->active()->get();
        if ($wh->isNotEmpty()) {
            return $wh;
        }

        return Warehouse::active()->orderBy('name')->get();
    }

    /**
     * Associated dynamic role.
     */
    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Determine whether the user is an admin or company manager.
     */
    public function isAdmin(): bool
    {
        $roleSlug = $this->roleRelation?->slug ?? $this->role;

        return $roleSlug === self::ROLE_ADMIN
            || $roleSlug === 'مدير_للشركه'
            || $roleSlug === 'company_manager'
            || str_contains((string) $roleSlug, 'admin')
            || str_contains((string) $roleSlug, 'مدير');
    }

    /**
     * Determine whether the user is a warehouse keeper.
     */
    public function isWarehouse(): bool
    {
        return $this->role === self::ROLE_WAREHOUSE || $this->roleRelation?->slug === self::ROLE_WAREHOUSE;
    }

    /**
     * Determine whether the user is a cashier.
     */
    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER || $this->roleRelation?->slug === self::ROLE_CASHIER;
    }

    /**
     * Check if user has specific role or one of given roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $rolesList = (array) $roles;
        $currentRole = $this->roleRelation?->slug ?? $this->role;

        return in_array($currentRole, $rolesList, true);
    }

    /**
     * Check if user has a specific permission dynamically assigned through their role.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->roleRelation) {
            return $this->roleRelation->hasPermission($permissionSlug);
        }

        if ($this->role) {
            $role = Role::where('slug', $this->role)->first();

            return $role?->hasPermission($permissionSlug) ?? false;
        }

        return false;
    }

    /**
     * Human-readable role label in Arabic.
     */
    public function getRoleLabelAttribute(): string
    {
        if ($this->roleRelation) {
            return $this->roleRelation->name;
        }

        return self::ROLES[$this->role] ?? 'مستخدم';
    }
}
