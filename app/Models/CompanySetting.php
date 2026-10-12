<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'company_phone',
        'logo_path',
        'program_status',
    ];

    /**
     * Get the global singleton company settings instance.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'Kayan Fashion Accessories',
            'company_phone' => null,
            'logo_path' => null,
            'program_status' => 'active',
        ]);
    }

    /**
     * Get the full URL to the company logo if stored.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/'.$this->logo_path);
        }

        return null;
    }

    /**
     * Check if program is set to maintenance status.
     */
    public function isMaintenance(): bool
    {
        return $this->program_status === 'maintenance';
    }
}
