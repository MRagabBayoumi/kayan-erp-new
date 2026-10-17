@extends('layouts.portal', ['title' => 'دليل العملاء'])

@section('content')
<div class="space-y-4">

    <!-- Top Action & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>قائمة وعملاء المندوب</span>
                </h1>
                <div class="text-xs text-slate-400 mt-0.5 font-numeric">إجمالي عملائك: {{ $totalMyCustomers }} عميل</div>
            </div>

            <a href="{{ route('portal.customers.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>عميل جديد</span>
            </a>
        </div>

        <form method="GET" action="{{ route('portal.customers.index') }}">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="ابحث باسم العميل أو الشركة أو الهاتف..."
                    class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                @if($search)
                    <a href="{{ route('portal.customers.index') }}" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">إلغاء</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customers Responsive Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($customers as $customer)
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 leading-snug">{{ $customer->name }}</h2>
                            @if($customer->company_name)
                                <div class="text-xs text-slate-500 font-medium">{{ $customer->company_name }}</div>
                            @endif
                        </div>

                        <!-- Pricing Tier Badge -->
                        @php
                            $tierClasses = match($customer->pricing_tier) {
                                'distributor' => 'bg-emerald-100 text-emerald-800',
                                'wholesale' => 'bg-indigo-100 text-indigo-800',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $tierLabel = match($customer->pricing_tier) {
                                'distributor' => 'موزع',
                                'wholesale' => 'جملة',
                                default => 'قطاعي',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tierClasses }}">
                            {{ $tierLabel }}
                        </span>
                    </div>

                    <!-- Meta details: Phone, City, Balance -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 my-2.5">
                        <div class="flex items-center gap-1.5 font-numeric">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span>{{ $customer->phone }}</span>
                        </div>

                        <div class="flex items-center gap-1.5 justify-end">
                            <span class="text-slate-400 text-[11px]">الرصيد:</span>
                            <span class="font-bold font-numeric {{ (float)$customer->current_balance > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                                {{ number_format($customer->current_balance, 2) }} ج.م
                            </span>
                        </div>

                        @if($customer->city || $customer->address)
                            <div class="col-span-2 flex items-center gap-1.5 text-slate-400 text-[11px] truncate">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ implode(' - ', array_filter([$customer->city, $customer->address])) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Toolbar: Phone, WhatsApp, Maps, New Order -->
                <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 gap-1.5">
                    <div class="flex items-center gap-1.5">
                        <!-- Direct Phone Call -->
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" title="اتصال هاتفي" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </a>
                        @endif

                        <!-- WhatsApp Direct -->
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '2' . $cleanPhone;
                            }
                        @endphp
                        @if($cleanPhone)
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" title="محادثة واتساب" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>
                        @endif

                        <!-- Google Maps GPS Link -->
                        @if($customer->latitude && $customer->longitude)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank" title="عرض الموقع على خرائط جوجل" class="p-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </a>
                        @endif
                    </div>

                    <!-- Direct Order Action -->
                    <a href="{{ route('portal.orders.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-600 hover:text-white text-sky-600 font-bold text-xs transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>إنشاء طلب</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200/80">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <div class="text-sm font-bold text-slate-700">لم يتم العثور على عملاء</div>
                <div class="text-xs text-slate-400 mt-1">اضغط على «عميل جديد» لتسجيل عميل جديد من الميدان</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $customers->links() }}
    </div>

</div>
@endsection
