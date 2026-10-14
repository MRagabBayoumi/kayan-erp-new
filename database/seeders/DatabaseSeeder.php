<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin / Test User
        User::firstOrCreate(
            ['email' => 'admin@kayan-erp.com'],
            [
                'name' => 'مدير النظام',
                'password' => bcrypt('password'),
            ]
        );

        // Default Main Warehouse
        Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name' => 'المستودع الرئيسي - العتبة',
                'location' => 'شارع عبد العزيز - العتبة - القاهرة',
                'is_active' => true,
                'is_default' => true,
                'notes' => 'المستودع المركزي لفرز وتوزيع الإكسسوارات',
            ]
        );

        // Default Print Templates (4 document types x 2 paper formats = 8 defaults)
        $this->call(PrintTemplateSeeder::class);
    }
}
