<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the official BayoSoft ERP Applications Hub (Home Launcher).
     */
    public function home(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isSalesRep()) {
            return redirect()->route('portal.index');
        }

        $activeShift = CashierShift::getActiveShift($user->id);

        return view('home', [
            'title' => 'تطبيقات BayoSoft ERP',
            'user' => $user,
            'activeShift' => $activeShift,
        ]);
    }

    /**
     * Display the Administrative KPIs Dashboard.
     * Cashiers are strictly blocked and redirected to Home.
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isSalesRep()) {
            return redirect()->route('portal.index');
        }

        if ($user->isCashier() || ! Gate::allows('access-dashboard')) {
            return redirect()->route('home')->with('error', 'لوحة التحكم الإحصائية مخصصة للإدارة فقط.');
        }

        return view('dashboard', [
            'title' => 'لوحة التحكم والمؤشرات العامة',
            'metrics' => [
                'products_count' => rescue(fn () => Product::count(), 0, false),
                'customers_count' => rescue(fn () => Customer::count(), 0, false),
                'suppliers_count' => rescue(fn () => Supplier::count(), 0, false),
                'sales_today' => rescue(fn () => (float) SalesInvoice::whereDate('created_at', today())->where('status', SalesInvoice::STATUS_POSTED)->sum('total_amount'), 0.0, false),
                'open_shifts_count' => rescue(fn () => CashierShift::open()->count(), 0, false),
            ],
        ]);
    }
}
