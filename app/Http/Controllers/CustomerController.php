<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('type') && in_array($request->input('type'), ['wholesale', 'retail', 'distributor'], true)) {
            $query->where('customer_type', $request->input('type'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        $customers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total_customers' => Customer::count(),
            'wholesale_count' => Customer::where('customer_type', 'wholesale')->count(),
            'retail_count' => Customer::where('customer_type', 'retail')->count(),
            'total_receivables' => (float) Customer::where('current_balance', '>', 0)->sum('current_balance'),
        ];

        $cities = Customer::whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        return view('customers.index', [
            'title' => 'دليل العملاء وتجار الجملة',
            'customers' => $customers,
            'metrics' => $metrics,
            'cities' => $cities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'phone_alt' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', 'in:wholesale,retail,distributor'],
            'pricing_tier' => ['required', 'in:wholesale,retail'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'current_balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم العميل مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل.',
            'customer_type.required' => 'نوع العميل مطلوب.',
            'pricing_tier.required' => 'فئة التسعير مطلوبة.',
        ]);

        Customer::create([
            'name' => trim($validated['name']),
            'company_name' => filled($validated['company_name'] ?? null) ? trim($validated['company_name']) : null,
            'phone' => trim($validated['phone']),
            'phone_alt' => filled($validated['phone_alt'] ?? null) ? trim($validated['phone_alt']) : null,
            'city' => filled($validated['city'] ?? null) ? trim($validated['city']) : null,
            'address' => filled($validated['address'] ?? null) ? trim($validated['address']) : null,
            'customer_type' => $validated['customer_type'],
            'pricing_tier' => $validated['pricing_tier'],
            'credit_limit' => (float) ($validated['credit_limit'] ?? 0.00),
            'current_balance' => (float) ($validated['current_balance'] ?? 0.00),
            'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل بنجاح.');
    }

    public function show(Customer $customer): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
            ]);
        }

        return view('customers.show', [
            'title' => $customer->name,
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'phone_alt' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', 'in:wholesale,retail,distributor'],
            'pricing_tier' => ['required', 'in:wholesale,retail'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'current_balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم العميل مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل.',
            'customer_type.required' => 'نوع العميل مطلوب.',
            'pricing_tier.required' => 'فئة التسعير مطلوبة.',
        ]);

        $customer->update([
            'name' => trim($validated['name']),
            'company_name' => filled($validated['company_name'] ?? null) ? trim($validated['company_name']) : null,
            'phone' => trim($validated['phone']),
            'phone_alt' => filled($validated['phone_alt'] ?? null) ? trim($validated['phone_alt']) : null,
            'city' => filled($validated['city'] ?? null) ? trim($validated['city']) : null,
            'address' => filled($validated['address'] ?? null) ? trim($validated['address']) : null,
            'customer_type' => $validated['customer_type'],
            'pricing_tier' => $validated['pricing_tier'],
            'credit_limit' => (float) ($validated['credit_limit'] ?? 0.00),
            'current_balance' => (float) ($validated['current_balance'] ?? 0.00),
            'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $customer->is_active,
        ]);

        return redirect()->route('customers.index')->with('success', 'تم تحديث بيانات العميل بنجاح.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ((float) $customer->current_balance != 0.0) {
            return redirect()->route('customers.index')
                ->with('error', 'لا يمكن حذف العميل لوجود رصيد حساب مالي معلق.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'تم حذف العميل بنجاح.');
    }

    public function lookup(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));

        $customers = Customer::query()
            ->active()
            ->search($term)
            ->limit(20)
            ->get(['id', 'name', 'company_name', 'phone', 'city', 'customer_type', 'pricing_tier', 'current_balance', 'credit_limit']);

        return response()->json([
            'success' => true,
            'results' => $customers,
        ]);
    }
}
