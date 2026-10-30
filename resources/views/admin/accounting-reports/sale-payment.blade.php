<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Sale Payment') }}
            </h2>
            <a href="{{ route('admin.accounting-reports.ar-collection') }}"
               class="px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-600 rounded-md hover:bg-indigo-50">
                Go to AR Collection →
            </a>
        </div>
    </x-slot>

    <div class="p-3 lg:ml-64">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- FILTERS --}}
            <div class="p-4 mb-6 bg-white rounded-lg shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Branch</label>
                        <select id="branch" class="w-full border-gray-300 rounded-md shadow-sm">
                            @if(auth()->user()->role == 1)
                                <option value="">— Select Branch —</option>
                                @foreach($branchs as $b)
                                    <option value="{{ $b->store_code }}">{{ $b->name }}</option>
                                @endforeach
                            @else
                                @foreach($branchs as $b)
                                    <option value="{{ $b->store_code }}" selected>{{ $b->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start" class="w-full border-gray-300 rounded-md shadow-sm"
                               value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end" class="w-full border-gray-300 rounded-md shadow-sm"
                               value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="flex items-end">
                        <button id="btnGenerate"
                                class="w-full px-4 py-2 font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                            Generate
                        </button>
                    </div>
                </div>
            </div>

            <div id="loader" class="hidden py-10 text-center text-gray-500">Loading…</div>

            {{-- TABLE --}}
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr id="sale-head">
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Ref No</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2 text-right">Form 2307</th>
                            <th class="px-3 py-2 text-right">AR Balance</th>
                        </tr>
                    </thead>
                    <tbody id="sale-body" class="bg-white divide-y divide-gray-100">
                        <tr>
                            <td colspan="99" class="py-6 text-center text-gray-400">
                                Click <strong>Generate</strong> to load data.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="font-semibold bg-gray-100" id="sale-foot"></tfoot>
                </table>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        const $ = id => document.getElementById(id);
        const money = n => (parseFloat(n || 0)).toLocaleString('en-PH', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });

        $('btnGenerate').addEventListener('click', async () => {
            const branch = $('branch').value;
            const start  = $('start').value;
            const end    = $('end').value;

            if (!branch) { alert('Please select a branch.'); return; }
            if (!start || !end) { alert('Please select start and end dates.'); return; }
            if (start > end) { alert('Start date cannot be after end date.'); return; }

            $('loader').classList.remove('hidden');

            const qs = new URLSearchParams({ branch, start, end }).toString();

            try {
                const res = await fetch(
                    `{{ route('admin.accounting-reports.sale-payment.data') }}?${qs}`,
                    { headers: { 'Accept': 'application/json' } }
                );

                if (!res.ok) throw new Error('Failed to load data.');
                const data = await res.json();
                renderSale(data);

            } catch (err) {
                console.error(err);
                alert(err.message || 'Error loading data.');
            } finally {
                $('loader').classList.add('hidden');
            }
        });

        function renderSale(data) {
            const rows = data.dailySRRows || [];
            const cols = data.dailySRPaymentColumns || [];
            const tot  = data.dailyTotals || { payments: {} };

            $('sale-head').innerHTML = `
                <th class="px-3 py-2 text-left">Date</th>
                <th class="px-3 py-2 text-left">Ref No</th>
                <th class="px-3 py-2 text-left">Customer</th>
                <th class="px-3 py-2 text-right">Amount</th>
                <th class="px-3 py-2 text-right">Form 2307</th>
                <th class="px-3 py-2 text-right">AR Balance</th>
                ${cols.map(c => `<th class="px-3 py-2 text-right">${c}</th>`).join('')}
            `;

            if (!rows.length) {
                $('sale-body').innerHTML = `<tr><td colspan="${6 + cols.length}" class="py-6 text-center text-gray-400">No records found.</td></tr>`;
                $('sale-foot').innerHTML = '';
                return;
            }

            $('sale-body').innerHTML = rows.map(r => `
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 whitespace-nowrap">${r.created_at ?? ''}</td>
                    <td class="px-3 py-2">${r.display_number ?? ''}</td>
                    <td class="px-3 py-2">${r.customer_name ?? ''}</td>
                    <td class="px-3 py-2 text-right">${money(r.row_amount)}</td>
                    <td class="px-3 py-2 text-right">${money(r.service_charge)}</td>
                    <td class="px-3 py-2 text-right ${parseFloat(r.ar_display_amount) > 0 ? 'text-red-600 font-semibold' : ''}">
                        ${money(r.ar_display_amount)}
                    </td>
                    ${cols.map(c => `<td class="px-3 py-2 text-right">${money(r.payments?.[c] ?? 0)}</td>`).join('')}
                </tr>
            `).join('');

            $('sale-foot').innerHTML = `
                <tr>
                    <td colspan="3" class="px-3 py-2 text-right">TOTALS</td>
                    <td class="px-3 py-2 text-right">${money(tot.amount)}</td>
                    <td class="px-3 py-2 text-right">${money(tot.service_charge)}</td>
                    <td class="px-3 py-2 text-right">${money(tot.ar)}</td>
                    ${cols.map(c => `<td class="px-3 py-2 text-right">${money(tot.payments?.[c] ?? 0)}</td>`).join('')}
                </tr>
            `;
        }
    </script>
    @endpush
</x-app-layout>