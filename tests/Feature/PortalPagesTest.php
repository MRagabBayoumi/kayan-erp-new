<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'مدير النظام',
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->salesRepRole = Role::firstOrCreate(
        ['slug' => 'sales_rep'],
        [
            'name' => 'مندوب مبيعات خارجي / بياع',
            'description' => 'الوصول لبوابة المندوب من الموبايل',
            'is_system' => true,
        ]
    );

    $this->salesRep = User::factory()->create([
        'name' => 'أحمد المندوب',
        'email' => 'rep@kayan.com',
        'role' => User::ROLE_SALES_REP,
        'role_id' => $this->salesRepRole->id,
        'commission_rate' => 5.00,
        'commission_type' => 'percentage',
        'is_active' => true,
    ]);
});

test('admin can access all portal pages without 500 errors', function () {
    $this->actingAs($this->admin);

    $this->get(route('portal.index'))->assertOk();
    $this->get(route('portal.catalog'))->assertOk();
    $this->get(route('portal.customers.index'))->assertOk();
    $this->get(route('portal.customers.create'))->assertOk();
    $this->get(route('portal.orders.index'))->assertOk();
    $this->get(route('portal.orders.create'))->assertOk();
    $this->get(route('portal.commissions'))->assertOk();
});

test('sales rep can access all portal pages without 500 errors', function () {
    $this->actingAs($this->salesRep);

    $this->get(route('portal.index'))->assertOk();
    $this->get(route('portal.catalog'))->assertOk();
    $this->get(route('portal.customers.index'))->assertOk();
    $this->get(route('portal.customers.create'))->assertOk();
    $this->get(route('portal.orders.index'))->assertOk();
    $this->get(route('portal.orders.create'))->assertOk();
    $this->get(route('portal.commissions'))->assertOk();
});
