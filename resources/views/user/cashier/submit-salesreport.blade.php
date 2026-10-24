<x-app-layout>
    @section('page_title', 'Submit Sales Report')
    @php
        $summary = $summary ?? [
            'completed' => 0, 'ar' => 0, 'cancelled' => 0,
            'paid_sales' => 0, 'ar_unpaid' => 0, 'sales_report_form' => 0,
            'total_sales' => 0, 'total_collections' => 0, 'ar_2307' => 0,
        ];
        $money = fn ($value) => '₱ ' . number_format((float) $value, 2, '.', ',');
    @endphp

    <section class="lg:ml-64 p-4 sm:p-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Cashier</p>
                    <h1 class="text-2xl font-bold text-slate-800">Submit Sales Report</h1>
                    <p class="mt-1 text-sm text-slate-500">Review the totals before submitting this report.</p>
                </div>
                <form method="GET" action="{{ route('submit-salesreport.index') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label for="start_date" class="mb-1 block text-xs font-medium text-slate-600">From</label>
                        <input id="start_date" name="start_date" type="date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label for="end_date" class="mb-1 block text-xs font-medium text-slate-600">To</label>
                        <input id="end_date" name="end_date" type="date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm shadow-sm">
                    </div>
                    <button type="submit" class="h-10 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white hover:bg-slate-800">View</button>
                </form>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            @if(session('message'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('message') }}</div>
            @endif

            @if($existingReport)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p class="font-semibold">A sales report already exists for this date.</p>
                    <p class="mt-1">
                        Saving this report will void the first saved report
                        <span class="font-semibold">{{ $existingReport->trans_number }}</span>
                        and mark it as deleted. The new report will replace it.
                    </p>
                </div>
            @endif

            <div class="grid gap-5 lg:grid-cols-3">
                <article class="overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm">
                    <div class="border-b border-blue-100 bg-blue-50 px-5 py-4">
                        <h2 class="font-bold text-blue-900">Transactions</h2>
                        <p class="text-xs text-blue-700">Transaction count</p>
                    </div>
                    <dl class="space-y-4 p-5">
                        <div class="flex items-center justify-between"><dt class="text-sm text-slate-600">Completed (Paid)</dt><dd class="text-xl font-bold text-emerald-600">{{ number_format($summary['completed']) }}</dd></div>
                        <div class="flex items-center justify-between"><dt class="text-sm text-slate-600">AR (Unpaid)</dt><dd class="text-xl font-bold text-amber-600">{{ number_format($summary['ar']) }}</dd></div>
                        <div class="flex items-center justify-between"><dt class="text-sm text-slate-600">Cancelled (Void)</dt><dd class="text-xl font-bold text-red-600">{{ number_format($summary['cancelled']) }}</dd></div>
                    </dl>
                </article>

                <article class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
                    <div class="border-b border-emerald-100 bg-emerald-50 px-5 py-4">
                        <h2 class="font-bold text-emerald-900">Sales Report</h2>
                        <p class="text-xs text-emerald-700">Sales totals</p>
                    </div>
                    <dl class="space-y-4 p-5">
                        <div class="flex items-center justify-between gap-4"><dt class="text-sm text-slate-600">Paid Sales</dt><dd class="font-bold text-emerald-600">{{ $money($summary['paid_sales']) }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-sm text-slate-600">AR Unpaid Sales</dt><dd class="font-bold text-amber-600">{{ $money($summary['ar_unpaid']) }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-sm text-slate-600">2307</dt><dd class="font-bold text-blue-600">{{ $money($summary['sales_report_form']) }}</dd></div>
                        <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-4"><dt class="font-semibold text-slate-700">Total Sales</dt><dd class="text-lg font-bold text-slate-900">{{ $money($summary['total_sales']) }}</dd></div>
                    </dl>
                </article>

                <article class="overflow-hidden rounded-xl border border-purple-100 bg-white shadow-sm">
                    <div class="border-b border-purple-100 bg-purple-50 px-5 py-4">
                        <h2 class="font-bold text-purple-900">AR Collections</h2>
                        <p class="text-xs text-purple-700">Collection totals</p>
                    </div>
                    <dl class="space-y-4 p-5">
                        <div class="flex items-center justify-between gap-4"><dt class="text-sm text-slate-600">Total Collections</dt><dd class="font-bold text-purple-600">{{ $money($summary['total_collections']) }}</dd></div>
                        <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-4"><dt class="text-sm text-slate-600">2307</dt><dd class="font-bold text-blue-600">{{ $money($summary['ar_2307']) }}</dd></div>
                    </dl>
                </article>
            </div>

            <form method="POST" action="{{ route('submit-salesreport.store') }}" onsubmit="return confirm(@js($existingReport ? 'A sales report (' . $existingReport->trans_number . ') already exists for this date. Saving this report will void the first saved report and mark it as deleted. Continue?' : 'Submit this sales report? This action cannot be undone.'));" class="mt-6 flex justify-end">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                    <i class="uil uil-check-circle mr-1"></i> Submit Sales Report
                </button>
            </form>
        </div>
    </section>
</x-app-layout>