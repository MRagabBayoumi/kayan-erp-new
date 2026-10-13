<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('type') && in_array($request->input('type'), ['china_factory', 'local_supplier', 'shipping_agent'], true)) {
            $query->where('supplier_type', $request->input('type'));
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        $suppliers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total_suppliers' => Supplier::count(),
            'china_factories_count' => Supplier::where('supplier_type', 'china_factory')->count(),
            'local_suppliers_count' => Supplier::where('supplier_type', 'local_supplier')->count(),
            'shipping_agents_count' => Supplier::where('supplier_type', 'shipping_agent')->count(),
        ];

        $countries = Supplier::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();

        return view('suppliers.index', [
            'title' => 'الموردون ومصانع الصين',
            'suppliers' => $suppliers,
            'metrics' => $metrics,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supplier_type' => ['required', 'in:china_factory,local_supplier,shipping_agent'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'wechat_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'in:CNY,EGP,USD'],
            'current_balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم المورد أو المصنع مطلوب.',
            'supplier_type.required' => 'نوع المورد مطلوب.',
            'currency.required' => 'عملة التعامل مطلوبة.',
        ]);

        Supplier::create([
            'name' => trim($validated['name']),
            'supplier_type' => $validated['supplier_type'],
            'contact_person' => filled($validated['contact_person'] ?? null) ? trim($validated['contact_person']) : null,
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
            'wechat_id' => filled($validated['wechat_id'] ?? null) ? trim($validated['wechat_id']) : null,
            'email' => filled($validated['email'] ?? null) ? trim($validated['email']) : null,
            'country' => filled($validated['country'] ?? null) ? trim($validated['country']) : 'China',
            'city' => filled($validated['city'] ?? null) ? trim($validated['city']) : null,
            'address' => filled($validated['address'] ?? null) ? trim($validated['address']) : null,
            'currency' => $validated['currency'],
            'current_balance' => (float) ($validated['current_balance'] ?? 0.00),
            'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'تم تسجيل المورد بنجاح.');
    }

    public function show(Supplier $supplier): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'supplier' => $supplier,
            ]);
        }

        return view('suppliers.show', [
            'title' => $supplier->name,
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supplier_type' => ['required', 'in:china_factory,local_supplier,shipping_agent'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'wechat_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'in:CNY,EGP,USD'],
            'current_balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم المورد أو المصنع مطلوب.',
            'supplier_type.required' => 'نوع المورد مطلوب.',
            'currency.required' => 'عملة التعامل مطلوبة.',
        ]);

        $supplier->update([
            'name' => trim($validated['name']),
            'supplier_type' => $validated['supplier_type'],
            'contact_person' => filled($validated['contact_person'] ?? null) ? trim($validated['contact_person']) : null,
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
            'wechat_id' => filled($validated['wechat_id'] ?? null) ? trim($validated['wechat_id']) : null,
            'email' => filled($validated['email'] ?? null) ? trim($validated['email']) : null,
            'country' => filled($validated['country'] ?? null) ? trim($validated['country']) : $supplier->country,
            'city' => filled($validated['city'] ?? null) ? trim($validated['city']) : null,
            'address' => filled($validated['address'] ?? null) ? trim($validated['address']) : null,
            'currency' => $validated['currency'],
            'current_balance' => (float) ($validated['current_balance'] ?? 0.00),
            'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $supplier->is_active,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'تم تحديث بيانات المورد بنجاح.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ((float) $supplier->current_balance != 0.0) {
            return redirect()->route('suppliers.index')
                ->with('error', 'لا يمكن حذف المورد لوجود مستحقات مالية معلقة.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد بنجاح.');
    }

    public function lookup(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));

        $suppliers = Supplier::query()
            ->active()
            ->search($term)
            ->limit(20)
            ->get(['id', 'name', 'supplier_type', 'contact_person', 'phone', 'wechat_id', 'country', 'currency', 'current_balance']);

        return response()->json([
            'success' => true,
            'results' => $suppliers,
        ]);
    }
}
