<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withCount('stockBalances')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('warehouses.index', [
            'title' => 'إدارة المستودعات',
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'كود المستودع مطلوب.',
            'code.unique' => 'كود المستودع مسجل مسبقاً.',
            'name.required' => 'اسم المستودع مطلوب.',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        // If this is the very first warehouse, make it default automatically
        if (Warehouse::count() === 0) {
            $isDefault = true;
        }

        Warehouse::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'location' => $validated['location'] ?? null,
            'is_default' => $isDefault,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'تم إضافة المستودع بنجاح.');
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'كود المستودع مطلوب.',
            'code.unique' => 'كود المستودع مسجل مسبقاً.',
            'name.required' => 'اسم المستودع مطلوب.',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && ! $warehouse->is_default) {
            Warehouse::where('id', '!=', $warehouse->id)->update(['is_default' => false]);
        }

        $warehouse->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'location' => $validated['location'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $warehouse->is_active,
            'is_default' => $isDefault || $warehouse->is_default,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'تم تحديث بيانات المستودع بنجاح.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->is_default) {
            return redirect()->route('warehouses.index')->with('error', 'لا يمكن حذف المستودع الافتراضي للنظام.');
        }

        $hasStock = $warehouse->stockBalances()->where('quantity', '>', 0)->exists();
        if ($hasStock) {
            return redirect()->route('warehouses.index')->with('error', 'لا يمكن حذف المستودع لوجود أرصدة أصناف ومخزون به.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'تم حذف المستودع بنجاح.');
    }
}
