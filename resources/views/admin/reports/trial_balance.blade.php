<x-app-layout>
    @section('page_title', 'Trial Balance Report')

    <div class="p-2 lg:pt-2 {{ (Auth::user()->role == 2) ? '' : ' lg:ml-64' }}">
        <div class="space-y-1.5">
            <!-- Filter Section -->
            <section class="rounded-2xl border border-slate-200 bg-white pl-3 pr-3 pb-3 shadow-sm">
                <form method="GET" action="{{ route('report.trial_balance') }}" id="filterForm">
                    <input type="hidden" name="generate" value="1">

                    <div class="mt-2 flex flex-wrap justify-center items-center gap-1.5">
                        <div class="w-full max-w-[220px]">
                            <label for="branch" class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Branch
                            </label>
                            <select id="branch" name="branch"
                                class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 text-xs text-slate-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">-- Select Branch --</option>
                                @foreach ($branchs as $branch)
                                    @if ($branch->id != 1)
                                        <option value="{{ $branch->store_code }}" {{ (string)($selectedBranch ?? '') === (string)$branch->store_code ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="w-full max-w-[220px]">
                            <label for="month" class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Month
                            </label>
                            <select id="month" name="month"
                                class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 text-xs text-slate-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">-- Select Month --</option>
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}" {{ (string)($selectedMonth ?? '') === (string)$num ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full max-w-[220px]">
                            <label for="year" class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Year
                            </label>
                            <select id="year" name="year"
                                class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 text-xs text-slate-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">-- Select Year --</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ (string)($selectedYear ?? '') === (string)$year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full max-w-[220px]">
                            <label class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-transparent">
                                Action
                            </label>
                            <button type="submit"
                                class="mt-1 h-8 w-full rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-700">
                                Generate
                            </button>
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap justify-center items-center gap-1.5">
                        <label class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] text-slate-700 shadow-sm hover:border-slate-300">
                            <input type="checkbox" name="include_zero_balances" value="1" {{ $includeZeroBalances ? 'checked' : '' }}
                                class="h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>No zero balances</span>
                        </label>

                        <label class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] text-slate-700 shadow-sm hover:border-slate-300">
                            <input type="checkbox" name="detail_by_account" value="1" {{ $detailByAccount ? 'checked' : '' }}
                                class="h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Only Balances</span>
                        </label>

                        <label class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] text-slate-700 shadow-sm hover:border-slate-300">
                            <input type="checkbox" name="show_closed" value="1" {{ $showClosed ? 'checked' : '' }}
                                class="h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Show</span>
                        </label>

                        <div class="relative inline-block text-left" id="columnDropdownWrapper">
                            <button type="button" id="columnDropdownBtn"
                                class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] text-slate-700 shadow-sm hover:border-slate-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                Columns
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="columnDropdownMenu"
                                class="hidden absolute right-0 z-30 mt-1 w-48 origin-top-right rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                                <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Toggle columns</p>
                                <div class="space-y-0.5">
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="0" checked>
                                        <span>Industry</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="1" checked>
                                        <span>Sub Account</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="2" checked>
                                        <span>Account Code</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="3" checked>
                                        <span>Account Name</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="4" checked>
                                        <span>Category</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="5" checked>
                                        <span>Journal</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="6" checked>
                                        <span>Beginning Balance</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="7" checked>
                                        <span>Debit</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="8" checked>
                                        <span>Credit</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" class="column-toggle h-3 w-3 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-column-index="9" checked>
                                        <span>Balance</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="exportCSV()"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] text-slate-700 shadow-sm hover:border-slate-300">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export CSV
                        </button>
                        <button type="button" onclick="window.print()"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] text-slate-700 shadow-sm hover:border-slate-300">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                    </div>
                </form>
            </section>

            <!-- Results Section -->
            <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50" id="trialBalanceTable">
                    <table class="min-w-full border-separate border-spacing-0 text-xs text-slate-700">
                        <thead class="bg-slate-100 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                            <tr>
                                <th class="px-3 py-2" colspan="5">
                                    <h2 class="text-lg font-semibold text-slate-900">Trial Balance</h2>
                                </th>
                                <th class="px-3 py-2 text-center" colspan="4">
                                    @php
                                        $periodDate = $toDate ? date('F Y', strtotime($toDate)) : '—';
                                    @endphp
                                    <span class="font-medium text-slate-900">Period:</span>
                                    {{ $periodDate }}
                                </th>
                            </tr>
                            <tr>
                                <th class="px-3 py-2 text-slate-900" rowspan="2">Industry</th>
                                <th class="px-3 py-2 text-slate-900" rowspan="2">Sub Account</th>
                                <th class="px-3 py-2 text-slate-900" rowspan="2">Account Code</th>
                                <th class="px-3 py-2 text-slate-900" rowspan="2">Account Name</th>
                                <th class="px-3 py-2 text-slate-900" rowspan="2">Category</th>
                                <th class="px-3 py-2 text-slate-900" rowspan="2">Journal</th>
                                <th class="px-3 py-1.5 text-right text-slate-900">Beginning Balance</th>
                                <th class="px-3 py-1.5 text-right text-slate-900">Debit</th>
                                <th class="px-3 py-1.5 text-right text-slate-900">Credit</th>
                                <th class="px-3 py-1.5 text-right text-slate-900">
                                    {{ $toDate ? strtoupper(date('F j', strtotime($toDate))) : '—' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @if(!empty($showResults) && !empty($trialBalances))
                                @foreach($trialBalances as $item)
                                    @php
                                        $isPriority = in_array(strtolower(trim($item->account_name ?? '')), ['cash on hand', 'cash on bank'], true);

                                        $hasDebit  = ($item->balance_debit  ?? 0) > 0;
                                        $hasCredit = ($item->balance_credit ?? 0) > 0;

                                        if ($hasDebit) {
                                            $lastColValue = number_format($item->balance_debit, 2);
                                            $lastColClass = 'text-rose-600';
                                        } elseif ($hasCredit) {
                                            $lastColValue = number_format($item->balance_credit, 2);
                                            $lastColClass = 'text-emerald-700';
                                        } else {
                                            $lastColValue = '0.00';
                                            $lastColClass = 'text-slate-400';
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-200 cursor-pointer transition account-row {{ $isPriority ? 'bg-emerald-50/40' : '' }}"
                                        data-account-id="{{ $item->id }}"
                                        data-account-name="{{ $item->account_name }}"
                                        data-month="{{ $selectedMonth }}"
                                        data-year="{{ $selectedYear }}"
                                        data-branch="{{ $selectedBranch ?? '' }}">
                                        <td class="px-3 py-2 font-medium text-slate-800 border-r-2 border-slate-900">
                                            <span class="inline-flex items-center gap-1.5">
                                                {{ $item->industry ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 font-medium text-slate-800 border-r-2 border-slate-900">{{ $item->sub_account ?? '—' }}</td>
                                        <td class="px-3 py-2 font-medium text-slate-800">{{ $item->account_code ?? '—' }}</td>
                                        <td class="px-3 py-2 font-bold text-slate-900 border-r-2 border-slate-900">
                                            @if($isPriority)
                                                <span class="inline-flex items-center gap-1">
                                                    {{ $item->account_name ?? '—' }}
                                                </span>
                                            @else
                                                {{ $item->account_name ?? '—' }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 font-medium text-slate-800 border-r-2 border-slate-900">
                                            {{ $item->chart_category ?? '—' }}
                                        </td>
                                        <td class="px-3 py-2 font-medium text-slate-800 border-r-2 border-slate-900">
                                            {{ $item->journal ?? ($item->classification ?? '—') }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-slate-700 border-r-2 border-slate-900">
                                            ₱ {{ number_format($item->beginning_balance ?? 0, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-rose-600">
                                            @if(($item->period_debit ?? 0) > 0)
                                                ₱ {{ number_format($item->period_debit, 2) }}
                                            @else
                                                <span class="text-slate-300">₱ 0.00</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right text-emerald-700 border-r-2 border-slate-900">
                                            @if(($item->period_credit ?? 0) > 0)
                                                ₱ {{ number_format($item->period_credit, 2) }}
                                            @else
                                                <span class="text-slate-300">₱ 0.00</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold {{ $lastColClass }} border-r border-slate-200">
                                            ₱ {{ $lastColValue }}
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif(!empty($showResults) && empty($trialBalances))
                                <tr>
                                    <td colspan="10" class="px-3 py-6 text-center text-xs text-slate-500">No accounts found matching your criteria.</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="10" class="px-3 py-6 text-center text-xs text-slate-500">Click Generate to load chart of accounts into the trial balance.</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-200 text-xs text-slate-700">
                            <tr class="border-t border-slate-200">
                                <td colspan="6" class="px-3 py-2 font-semibold text-right text-slate-900 border-r border-white">TOTAL</td>
                                <td class="px-3 py-2 text-right font-semibold">
                                    ₱ {{ number_format($totals['beginning_balance'] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-rose-700">
                                    ₱ {{ number_format($totals['period_debit'] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-emerald-700">
                                    ₱ {{ number_format($totals['period_credit'] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold">
                                    @php
                                        $grandDebit  = $totals['period_debit']  ?? 0;
                                        $grandCredit = $totals['period_credit'] ?? 0;
                                        $diff        = $grandDebit - $grandCredit;
                                    @endphp
                                    @if(abs($diff) < 0.01)
                                        <span class="text-slate-700">₱ 0.00</span>
                                    @elseif($diff > 0)
                                        <span class="text-rose-700">₱ {{ number_format($diff, 2) }}</span>
                                    @else
                                        <span class="text-emerald-700">-₱ {{ number_format(abs($diff), 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- =========================================================== -->
    <!-- BREAKDOWN MODAL                                             -->
    <!-- =========================================================== -->
    <div id="breakdownModal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="breakdownModalTitle">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeBreakdownModal()"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative w-full max-w-7xl rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 overflow-hidden pointer-events-auto"
                 style="height: 88vh; max-height: 88vh; display: flex; flex-direction: column;">

                <div class="flex items-center justify-between gap-3 flex-shrink-0 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 to-emerald-100 px-4 py-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <h4 id="breakdownModalTitle" class="text-xs sm:text-sm font-bold uppercase tracking-wider text-emerald-800 truncate">
                            Breakdown: <span id="breakdownAccountName" class="text-emerald-900"></span>
                        </h4>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span id="breakdownCount" class="hidden text-[10px] font-semibold text-emerald-700 bg-white/70 px-2 py-0.5 rounded-full"></span>

                        <div class="inline-flex items-center rounded-full border border-emerald-300 bg-white/70 p-0.5 shadow-sm"
                             role="tablist" aria-label="Breakdown view mode">
                            <button type="button"
                                    id="viewToggleCombined"
                                    data-view="combined"
                                    class="view-toggle-btn rounded-full px-2.5 py-0.5 text-[10px] font-semibold transition bg-emerald-600 text-white shadow-sm">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    </svg>
                                    Combined
                                </span>
                            </button>
                            <button type="button"
                                    id="viewToggleSide"
                                    data-view="side"
                                    class="view-toggle-btn rounded-full px-2.5 py-0.5 text-[10px] font-semibold transition text-emerald-700 hover:bg-emerald-50">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4v16M15 4v16M4 4h16v16H4z"/>
                                    </svg>
                                    Side-by-Side
                                </span>
                            </button>
                        </div>

                        <button type="button" onclick="closeBreakdownModal()"
                            class="rounded-lg p-1.5 text-slate-500 transition hover:bg-white/70 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            aria-label="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="breakdownModalBody"
                     class="p-4"
                     style="flex: 1 1 auto; min-height: 0; overflow: hidden; display: flex; flex-direction: column;">
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownBtn = document.getElementById('columnDropdownBtn');
            const dropdownMenu = document.getElementById('columnDropdownMenu');
            const dropdownWrapper = document.getElementById('columnDropdownWrapper');

            dropdownBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!dropdownWrapper.contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    dropdownMenu.classList.add('hidden');
                }
            });

            function applyColumnVisibility() {
                const table = document.querySelector('#trialBalanceTable > table');
                if (!table) return;

                const toggles = document.querySelectorAll('.column-toggle');
                const totalColumns = 10; // 0..9

                const visibility = {};
                toggles.forEach(t => {
                    visibility[parseInt(t.dataset.columnIndex, 10)] = t.checked;
                });

                // Header row 2 has 10 columns (0..9)
                const headerRow = table.querySelector('thead tr:nth-child(2)');
                if (headerRow) {
                    const headerCells = headerRow.querySelectorAll('th');
                    headerCells.forEach((cell, idx) => {
                        cell.style.display = (visibility[idx] === false) ? 'none' : '';
                    });
                }

                // Body rows
                const mainBodyRows = table.querySelectorAll('tbody > tr.account-row');
                mainBodyRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                        const visibleCount = Object.values(visibility).filter(Boolean).length;
                        cells[0].setAttribute('colspan', visibleCount);
                        return;
                    }
                    cells.forEach((cell, idx) => {
                        if (idx < totalColumns) {
                            cell.style.display = (visibility[idx] === false) ? 'none' : '';
                        }
                    });
                });

                // Empty/placeholder rows
                const emptyRows = table.querySelectorAll('tbody > tr:not(.account-row)');
                emptyRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                        const visibleCount = Object.values(visibility).filter(Boolean).length;
                        cells[0].setAttribute('colspan', visibleCount);
                    }
                });

                // Footer — first 6 cols are the "TOTAL" label span; remaining 4 are values
                const footerRow = table.querySelector('tfoot tr');
                if (footerRow) {
                    const footerCells = footerRow.querySelectorAll('td');
                    if (footerCells[0]) {
                        const firstSixVisible = [0, 1, 2, 3, 4, 5].filter(i => visibility[i] !== false);
                        if (firstSixVisible.length > 0) {
                            footerCells[0].style.display = '';
                            footerCells[0].setAttribute('colspan', firstSixVisible.length);
                        } else {
                            footerCells[0].style.display = 'none';
                        }
                    }
                    // Remaining footer cells correspond to columns 6,7,8,9
                    for (let i = 1; i < footerCells.length; i++) {
                        const colIndex = i + 5; // 6,7,8,9
                        footerCells[i].style.display = (visibility[colIndex] === false) ? 'none' : '';
                    }
                }
            }

            document.querySelectorAll('.column-toggle').forEach(toggle => {
                toggle.addEventListener('change', applyColumnVisibility);
            });

            applyColumnVisibility();
        });

        function exportCSV() {
            const table = document.querySelector('#trialBalanceTable > table');
            if (!table) return;

            const toggles = document.querySelectorAll('.column-toggle');
            const visibility = {};
            toggles.forEach(t => {
                visibility[parseInt(t.dataset.columnIndex, 10)] = t.checked;
            });

            const columnDefs = [
                { key: 'industry',   label: 'Industry',            idx: 0 },
                { key: 'sub',        label: 'Sub Account',         idx: 1 },
                { key: 'code',       label: 'Account Code',        idx: 2 },
                { key: 'name',       label: 'Account Name',        idx: 3 },
                { key: 'category',   label: 'Category',            idx: 4 },
                { key: 'journal',    label: 'Journal',             idx: 5 },
                { key: 'beginning',  label: 'Beginning Balance',   idx: 6 },
                { key: 'debit',      label: 'Debit',               idx: 7 },
                { key: 'credit',     label: 'Credit',              idx: 8 },
                { key: 'balance',    label: 'Balance',             idx: 9 },
            ];

            const activeCols = columnDefs.filter(c => visibility[c.idx] !== false);

            let csvContent = activeCols.map(c => `"${c.label}"`).join(',') + '\n';

            const rows = table.querySelectorAll('tbody > tr.account-row');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length < 10) return;

                const values = [];
                activeCols.forEach(c => {
                    let text = cells[c.idx] ? cells[c.idx].textContent.trim() : '';
                    text = text.replace(/₱/g, '').replace(/,/g, '').trim();
                    values.push('"' + text.replace(/"/g, '""') + '"');
                });
                csvContent += values.join(',') + '\n';
            });

            // Totals row
            const footerRow = table.querySelector('tfoot tr');
            if (footerRow) {
                const footerCells = footerRow.querySelectorAll('td');
                const totalCols = activeCols.length;
                const totalRow = [];
                for (let i = 0; i < totalCols; i++) {
                    if (i === 0) {
                        totalRow.push('"TOTAL"');
                    } else {
                        const cellIdx = activeCols[i].idx;
                        // Map to footer cell: footer cell 0 is the TOTAL label (covers cols 0-5)
                        // footer cells 1..4 correspond to cols 6..9
                        let footerCell = null;
                        if (cellIdx >= 6) {
                            footerCell = footerCells[cellIdx - 5];
                        }
                        let text = footerCell ? footerCell.textContent.trim() : '';
                        text = text.replace(/₱/g, '').replace(/,/g, '').trim();
                        totalRow.push('"' + text.replace(/"/g, '""') + '"');
                    }
                }
                csvContent += totalRow.join(',') + '\n';
            }

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            const date = new Date();
            const dateStr = date.getFullYear() + '-' +
                           String(date.getMonth() + 1).padStart(2, '0') + '-' +
                           String(date.getDate()).padStart(2, '0');
            const filename = 'Trial_Balance_' + dateStr + '.csv';

            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        let breakdownFetchController = null;
        let lastBreakdownData = null;
        let lastBreakdownAccountName = '';
        let currentViewMode = 'combined';


        function openBreakdownModal(accountName) {
            const modal = document.getElementById('breakdownModal');
            const nameEl = document.getElementById('breakdownAccountName');
            const bodyEl = document.getElementById('breakdownModalBody');
            const countEl = document.getElementById('breakdownCount');

            nameEl.textContent = accountName || '';

            countEl.classList.add('hidden');
            countEl.textContent = '';

            bodyEl.innerHTML = `
                <div class="flex items-center justify-center gap-2 py-10 text-slate-500 text-xs" style="flex:1;">
                    <svg class="animate-spin w-4 h-4 text-emerald-600"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Loading breakdown...
                </div>
            `;

            // Show modal immediately
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Focus the Close button on the next rendering frame
            requestAnimationFrame(() => {
                const closeBtn = modal.querySelector('button[aria-label="Close"]');
                closeBtn?.focus({ preventScroll: true });
            });
        }


        function closeBreakdownModal() {
            const modal = document.getElementById('breakdownModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';

            if (breakdownFetchController) {
                breakdownFetchController.abort();
                breakdownFetchController = null;
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('breakdownModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeBreakdownModal();
                }
            }
        });

        function setViewMode(mode) {
            currentViewMode = (mode === 'side') ? 'side' : 'combined';

            const btnCombined = document.getElementById('viewToggleCombined');
            const btnSide     = document.getElementById('viewToggleSide');

            const activeClasses   = ['bg-emerald-600', 'text-white', 'shadow-sm'];
            const inactiveClasses = ['text-emerald-700', 'hover:bg-emerald-50'];

            if (currentViewMode === 'combined') {
                btnCombined.classList.add(...activeClasses);
                btnCombined.classList.remove(...inactiveClasses);
                btnSide.classList.remove(...activeClasses);
                btnSide.classList.add(...inactiveClasses);
            } else {
                btnSide.classList.add(...activeClasses);
                btnSide.classList.remove(...inactiveClasses);
                btnCombined.classList.remove(...activeClasses);
                btnCombined.classList.add(...inactiveClasses);
            }

            if (lastBreakdownData) {
                const bodyEl = document.getElementById('breakdownModalBody');
                bodyEl.innerHTML = buildBreakdownHTML(lastBreakdownData, lastBreakdownAccountName);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('viewToggleCombined').addEventListener('click', () => setViewMode('combined'));
            document.getElementById('viewToggleSide').addEventListener('click',     () => setViewMode('side'));
        });

        document.addEventListener('DOMContentLoaded', function () {
            const breakdownUrl = "{{ route('report.trial_balance.breakdown') }}";
            const mainTable = document.querySelector('#trialBalanceTable > table');
            const mainTbody = mainTable.querySelector('tbody');
            const modalBody = document.getElementById('breakdownModalBody');
            const modalCount = document.getElementById('breakdownCount');

            mainTbody.addEventListener('click', function (e) {
                const row = e.target.closest('tr.account-row');
                if (!row) return;

                const accountId   = row.dataset.accountId;
                const accountName = row.dataset.accountName;
                const month       = row.dataset.month;
                const year        = row.dataset.year;
                const branch      = row.dataset.branch;

                lastBreakdownAccountName = accountName;
                openBreakdownModal(accountName);

                if (breakdownFetchController) {
                    breakdownFetchController.abort();
                }
                breakdownFetchController = new AbortController();

                fetch(`${breakdownUrl}?account_id=${encodeURIComponent(accountId)}&month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}&branch=${encodeURIComponent(branch)}`, {
                    signal: breakdownFetchController.signal,
                    headers: { 'Accept': 'application/json' }
                })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        lastBreakdownData = data;

                        const total = ((data.beginning && data.beginning.rows) ? data.beginning.rows.length : 0)
                                    + ((data.period && data.period.rows) ? data.period.rows.length : 0);

                        modalBody.innerHTML = buildBreakdownHTML(data, accountName);

                        if (total > 0) {
                            modalCount.textContent = `${total} transaction${total !== 1 ? 's' : ''}`;
                            modalCount.classList.remove('hidden');
                        }
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') return;
                        modalBody.innerHTML = `
                            <div class="flex items-center justify-center text-xs text-red-500" style="flex:1;">
                                Failed to load breakdown.
                            </div>
                        `;
                    });
            });
        });

        // ===================================================================
        //  HELPERS
        // ===================================================================
        function formatNumber(num) {
            return (Number(num) || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function fmtBalance(v) {
            const n = Number(v) || 0;
            if (n === 0) return '<span class="text-slate-400">0.00</span>';

            const formatted = formatNumber(n);
            if (n < 0) {
                return `<span class="text-rose-600 font-semibold">${formatted}</span>`;
            }
            return `<span class="text-slate-800 font-semibold">${formatted}</span>`;
        }

        function fmtMoney(v) {
            return '₱ ' + formatNumber(Math.abs(Number(v) || 0));
        }

        function fmtSide(v) {
            const n = Number(v) || 0;
            return n > 0 ? fmtMoney(n) : '<span class="text-slate-400">0.00</span>';
        }

        function computeRunningRows(rows, openingBalance, debitMinusCredit) {
            return (rows || []).map(function (r) {
                const net = debitMinusCredit
                    ? (Number(r.debit) || 0) - (Number(r.credit) || 0)
                    : (Number(r.credit) || 0) - (Number(r.debit) || 0);
                return Object.assign({}, r, { balance: net });
            });
        }

        function buildItemRow(r, rowBalance) {
            const rawDate = String(r.date || '');
            const rawTime = String(r.time || '');
            const lines = Array.isArray(r.lines) ? r.lines : [];

            let dateLine = rawDate;
            const m = rawDate.match(/^([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/);
            if (m) {
                const monthMap = {
                    'jan':'Jan.', 'feb':'Feb.', 'mar':'Mar.', 'apr':'Apr.',
                    'may':'May',  'jun':'Jun.', 'jul':'Jul.', 'aug':'Aug.',
                    'sep':'Sept.','oct':'Oct.', 'nov':'Nov.', 'dec':'Dec.'
                };
                const key = m[1].slice(0, 3).toLowerCase();
                const monthLabel = monthMap[key] || m[1];
                dateLine = `${monthLabel} ${parseInt(m[2], 10)} ${m[3]}`;
            }

            const timeLine = rawTime.replace(/\b(AM|PM)\b/gi, s => s.toLowerCase());
            const remarks = escapeHtml(r.remarks);
            const detailHtml = lines.length
                ? `
                    <details class="mt-1 text-[10px] text-slate-500">
                        <summary class="cursor-pointer select-none text-emerald-700 hover:text-emerald-900">
                            ${lines.length} item detail${lines.length === 1 ? '' : 's'}
                        </summary>
                        <div class="mt-1 space-y-0.5 border-l-2 border-emerald-200 pl-2">
                            ${lines.map(line => {
                                const label = line.item || line.label || 'Unnamed Item';
                                const amount = Number(line.amount) || 0;
                                return `
                                    <div class="flex items-center justify-between gap-3">
                                        <span>${escapeHtml(label)}</span>
                                        <span class="${amount < 0 ? 'text-rose-600' : 'text-slate-700'} whitespace-nowrap">
                                            ${amount < 0 ? '-' : ''}${fmtMoney(amount)}
                                        </span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </details>
                `
                : '';

            return `
                <tr class="border-b border-slate-100 hover:bg-slate-50/70">
                    <td class="px-2 py-2 text-slate-700 text-[11px] leading-tight">
                        <div class="font-medium">${escapeHtml(dateLine)}</div>
                        <div class="text-slate-400 text-[10px]">${escapeHtml(timeLine)}</div>
                    </td>
                    <td class="px-3 py-2 text-slate-700 font-mono text-[11px]">${escapeHtml(r.reference_no)}</td>
                    <td class="px-3 py-2 text-slate-700">${remarks}${detailHtml}</td>
                    <td class="px-3 py-2 text-right text-rose-600">${fmtSide(r.debit)}</td>
                    <td class="px-3 py-2 text-right text-emerald-700">${fmtSide(r.credit)}</td>
                    <td class="px-3 py-2 text-right">${fmtBalance(rowBalance)}</td>
                </tr>
            `;
        }

        function buildEmptyRow(msg) {
            return `
                <tr class="border-b border-slate-100">
                    <td colspan="6" class="px-3 py-3 text-center text-slate-400 text-[11px]">${msg}</td>
                </tr>
            `;
        }

        function buildCombinedView(data, accountName) {
            const beginning = data.beginning || { rows: [], total_debit: 0, total_credit: 0, label: '' };
            const period    = data.period    || { rows: [], total_debit: 0, total_credit: 0, label: '' };
            const debitMinusCredit = data.balance_mode === 'debit_minus_credit';

            const begRows = computeRunningRows(beginning.rows || [], 0, debitMinusCredit);
            const perRows = computeRunningRows(period.rows || [], 0, debitMinusCredit);

            const begBalEnd = debitMinusCredit ? 0 : begRows.reduce(
                (acc, r) => acc + (Number(r.balance) || 0), 0
            );
            const endBal = begBalEnd + perRows.reduce(
                (acc, r) => acc + (Number(r.balance) || 0), 0
            );

            if (begRows.length === 0 && perRows.length === 0) {
                return `
                    <div class="flex items-center justify-center text-xs text-slate-500" style="flex:1;">
                        No transactions found for <strong class="ml-1">${escapeHtml(accountName)}</strong> in this period.
                    </div>
                `;
            }

            const sectionHeader = (title, sub, cls) => `
                <tr>
                    <td colspan="6" class="sticky top-0 z-20 px-3 py-2 ${cls}">
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-2 w-2 rounded-full bg-current opacity-70"></span>
                            <span class="text-[10px] font-bold uppercase tracking-[0.14em]">${escapeHtml(title)}</span>
                            <span class="text-[10px] font-medium normal-case tracking-normal opacity-70">${escapeHtml(sub || '')}</span>
                        </div>
                    </td>
                </tr>
            `;

            let html = sectionHeader('Beginning Balance', beginning.label, 'bg-slate-100 text-slate-700 border-b border-slate-200');
            html += begRows.length ? begRows.map(r => buildItemRow(r, r.balance)).join('') : buildEmptyRow('No transactions in the beginning balance.');

            html += `
                <tr>
                    <td colspan="6" class="px-0 py-0">
                        <div class="mx-3 my-2 flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                            <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-amber-800">
                                ↓ Carried forward to current period
                            </span>
                            <span class="text-xs font-bold">${fmtBalance(begBalEnd)}</span>
                        </div>
                    </td>
                </tr>
            `;

            html += sectionHeader('Current Period', period.label, 'bg-emerald-100 text-emerald-800 border-b border-emerald-200');
            html += perRows.length ? perRows.map(r => buildItemRow(r, r.balance)).join('') : buildEmptyRow('No transactions in the current period.');

            html += `
                <tr class="bg-slate-100 font-semibold text-slate-800 border-t-2 border-slate-300">
                    <td colspan="3" class="px-3 py-2 text-right border-r border-white text-[11px] uppercase tracking-wider">
                        Current Period Movements
                    </td>
                    <td class="px-3 py-2 text-right text-rose-700">${fmtMoney(period.total_debit)}</td>
                    <td class="px-3 py-2 text-right text-emerald-700">${fmtMoney(period.total_credit)}</td>
                    <td class="px-3 py-2 text-right">${fmtBalance(endBal)}</td>
                </tr>
            `;

            return `
                <div style="display:flex; flex-direction:column; height:100%; min-height:0; overflow:hidden;">
                    <div class="rounded-xl border border-slate-200 bg-white shadow-sm"
                         style="flex:1 1 auto; min-height:0; overflow:hidden; display:flex; flex-direction:column;">
                        <div style="flex:1 1 auto; min-height:0; overflow-y:auto;">
                            <table class="min-w-full text-xs text-slate-700">
                                <thead class="bg-slate-50 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500"
                                       style="position: sticky; top: 0; z-index: 30; box-shadow: 0 1px 0 0 rgb(226 232 240);">
                                    <tr>
                                        <th class="px-3 py-2">Date &amp; Time</th>
                                        <th class="px-3 py-2">Reference</th>
                                        <th class="px-3 py-2">Remarks</th>
                                        <th class="px-3 py-2 text-right w-24">Debit</th>
                                        <th class="px-3 py-2 text-right w-24">Credit</th>
                                        <th class="px-3 py-2 text-right w-32">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">${html}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        function buildSideBySideView(data, accountName) {
            const beginning = data.beginning || { rows: [], total_debit: 0, total_credit: 0, label: '' };
            const period    = data.period    || { rows: [], total_debit: 0, total_credit: 0, label: '' };
            const debitMinusCredit = data.balance_mode === 'debit_minus_credit';

            const begRows = computeRunningRows(beginning.rows || [], 0, debitMinusCredit);
            const perRows = computeRunningRows(period.rows || [], 0, debitMinusCredit);

            const begBalEnd = debitMinusCredit ? 0 : begRows.reduce(
                (acc, r) => acc + (Number(r.balance) || 0), 0
            );
            const endBal = begBalEnd + perRows.reduce(
                (acc, r) => acc + (Number(r.balance) || 0), 0
            );

            if (begRows.length === 0 && perRows.length === 0) {
                return `
                    <div class="flex items-center justify-center text-xs text-slate-500" style="flex:1;">
                        No transactions found for <strong class="ml-1">${escapeHtml(accountName)}</strong> in this period.
                    </div>
                `;
            }

            const colgroup = `
                <colgroup>
                    <col style="width:95px;">
                    <col style="width:110px;">
                    <col style="width:280px;">
                    <col style="width:90px;">
                    <col style="width:90px;">
                    <col style="width:130px;">
                </colgroup>
            `;

            const headerRow = `
                <tr>
                    <th class="px-2 py-1.5">Date</th>
                    <th class="px-3 py-1.5">Ref</th>
                    <th class="px-3 py-1.5">Remarks</th>
                    <th class="px-3 py-1.5 text-right">Debit</th>
                    <th class="px-3 py-1.5 text-right">Credit</th>
                    <th class="px-3 py-1.5 text-right">Balance</th>
                </tr>
            `;

            const totalRow = (opts) => `
                <tr class="${opts.rowClass || ''}">
                    <td colspan="3" class="px-3 py-2 text-right ${opts.labelClass || ''}">${opts.label}</td>
                    <td class="px-3 py-2 text-right">${opts.debit ?? ''}</td>
                    <td class="px-3 py-2 text-right">${opts.credit ?? ''}</td>
                    <td class="px-3 py-2 text-right">${opts.balance ?? ''}</td>
                </tr>
            `;

            const begFooter = totalRow({
                label: 'BEGINNING TOTAL',
                rowClass: 'bg-slate-100 font-bold text-slate-800',
                labelClass: 'uppercase tracking-wider text-[10px]',
                debit:  `<span class="text-rose-700">${fmtMoney(beginning.total_debit)}</span>`,
                credit: `<span class="text-emerald-700">${fmtMoney(beginning.total_credit)}</span>`,
                balance: fmtBalance(begBalEnd),
            });

            const perFooter = totalRow({
                label: 'CURRENT PERIOD TOTAL',
                rowClass: 'bg-emerald-50 font-bold text-emerald-900',
                labelClass: 'uppercase tracking-wider text-[10px]',
                debit:  `<span class="text-rose-700">${fmtMoney(period.total_debit)}</span>`,
                credit: `<span class="text-emerald-700">${fmtMoney(period.total_credit)}</span>`,
                balance: fmtBalance(endBal),
            });

            const renderPane = (opts) => {
                const headerCls  = opts.headerCls;
                const accentDot  = opts.accentDot;
                const rows       = opts.rows || [];
                const title      = opts.title;
                const subtitle   = opts.subtitle || '';
                const footerRows = opts.footerRows;
                const emptyMsg   = opts.emptyMsg || 'No transactions.';
                const footerTint = opts.footerTint || 'border-t-2 border-slate-300';

                return `
                    <div class="flex-1 min-w-0 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                        <div class="px-3 py-2 border-b ${headerCls} flex-shrink-0">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full ${accentDot}"></span>
                                <span class="text-[10px] font-bold uppercase tracking-[0.14em]">${escapeHtml(title)}</span>
                                <span class="text-[10px] font-medium normal-case tracking-normal opacity-70">${escapeHtml(subtitle)}</span>
                                <span class="ml-auto text-[10px] font-semibold opacity-70">${rows.length} row${rows.length === 1 ? '' : 's'}</span>
                            </div>
                        </div>

                        <div class="flex-1 min-h-0 overflow-y-auto">
                            <table class="min-w-full text-xs text-slate-700 table-fixed">
                                ${colgroup}
                                <thead class="bg-slate-50 text-left text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-500 sticky top-0 z-10">
                                    ${headerRow}
                                </thead>
                                <tbody class="bg-white">
                                    ${rows.length ? rows.map(r => buildItemRow(r, r.balance)).join('') : buildEmptyRow(emptyMsg)}
                                </tbody>
                            </table>
                        </div>

                        <div class="flex-shrink-0 ${footerTint}">
                            <table class="min-w-full text-xs text-slate-700 table-fixed">
                                ${colgroup}
                                <tbody>
                                    ${footerRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            };

            return `
                <div style="display:flex; flex-direction:column; height:100%; min-height:0; overflow:hidden;">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3"
                         style="flex:1 1 auto; min-height:0;">
                        ${renderPane({
                            title: 'Beginning Balance',
                            subtitle: beginning.label,
                            headerCls: 'bg-slate-100 text-slate-700 border-slate-200',
                            accentDot: 'bg-slate-400',
                            rows: begRows,
                            emptyMsg: 'No transactions in the beginning balance.',
                            footerRows: begFooter,
                            footerTint: 'border-t-2 border-slate-300',
                        })}
                        ${renderPane({
                            title: 'Current Period',
                            subtitle: period.label,
                            headerCls: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            accentDot: 'bg-emerald-500',
                            rows: perRows,
                            emptyMsg: 'No transactions in the current period.',
                            footerRows: perFooter,
                            footerTint: 'border-t-2 border-emerald-300',
                        })}
                    </div>
                </div>
            `;
        }

        function buildBreakdownHTML(data, accountName) {
            if (currentViewMode === 'side') {
                return buildSideBySideView(data, accountName);
            }
            return buildCombinedView(data, accountName);
        }
    </script>
</x-app-layout>