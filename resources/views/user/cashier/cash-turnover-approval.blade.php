<x-app-layout>
    <style>
        /* ===== CASH BREAKDOWN MODAL STYLES ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            display: none;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        body.modal-open {
            overflow: hidden !important;
            position: fixed;
            width: 100%;
            touch-action: none;
        }

        .modal-container-cash {
            background: white;
            border-radius: 12px;
            width: 95%;
            max-width: 650px;
            max-height: 90vh;
            height: auto;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
            pointer-events: auto;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 2px solid #e5e7eb;
            flex-shrink: 0;
        }

        .modal-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: #6b7280;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .modal-body {
            flex: 1;
            overflow: hidden;
            padding: 0 20px 20px 20px;
            display: flex;
            flex-direction: column;
            min-height: 0;
            position: relative;
        }

        /* Printable content wrapper — flex column layout */
        #printableBreakdownContent {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            position: relative;
        }

        /* The content injected via AJAX becomes the flex column */
        #breakdownModalContent {
            width: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        /* Loading overlay */
        #breakdownModalLoading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.92);
            z-index: 5;
            min-height: 200px;
        }

        #breakdownModalLoading .spinner-container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        #breakdownModalLoading .animate-spin {
            animation: spin 0.8s linear infinite;
            font-size: 2.5rem;
            color: #3b82f6;
        }

        #breakdownModalLoading .loading-text {
            color: #6b7280;
            font-size: 0.85rem;
            font-weight: 500;
        }

        #breakdownModalLoading .loading-subtext {
            color: #9ca3af;
            font-size: 0.7rem;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ===== CASH BREAKDOWN STYLES ===== */
        .cash-breakdown-modal {
            padding: 5px 0;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        /* Wrap the denomination table so ONLY it scrolls */
        .denomination-table-wrapper {
            flex: 1;
            min-height: 0;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Everything else in the modal stays fixed */
        .cash-breakdown-modal > *:not(.denomination-table-wrapper) {
            flex-shrink: 0;
        }

        .denomination-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.7rem;
            margin-bottom: 10px;
        }

        .denomination-table th {
            background-color: #1e293b !important;
            color: white !important;
            padding: 5px 8px !important;
            font-size: 0.6rem !important;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.3px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .denomination-table td {
            padding: 5px 8px !important;
            font-size: 0.7rem !important;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .denomination-table tr:hover td {
            background-color: #f8fafc !important;
        }

        .denomination-table .total-row td {
            background-color: #f0fdf4 !important;
            border-top: 2px solid #059669 !important;
            font-weight: bold !important;
            padding: 6px 8px !important;
        }

        .denomination-table .amount-col {
            text-align: right;
            font-weight: 500;
        }

        .denomination-table .count-col {
            text-align: center;
        }

        .denomination-table .label-col {
            text-align: left;
            font-weight: 500;
        }

        .variance-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .variance-over {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .variance-short {
            background: #f0fdf4;
            color: #059669;
            border: 1px solid #bbf7d0;
        }

        .variance-zero {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }

        @media (max-width: 640px) {
            .modal-container-cash {
                max-width: 95%;
                width: 95%;
            }
        }

        @media print {
            body * { visibility: hidden; }
            #printableBreakdownContent,
            #printableBreakdownContent * { visibility: visible; }
            #printableBreakdownContent {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                background: white;
                padding: 20px;
                margin: 0;
                z-index: 99999;
                overflow: visible;
            }
            #breakdownModalLoading { display: none !important; }
            #breakdownModalContent {
                display: block !important;
                overflow: visible !important;
            }
            .cash-breakdown-modal {
                display: block !important;
                overflow: visible !important;
            }
            .denomination-table-wrapper {
                overflow: visible !important;
                max-height: none !important;
            }
            #printableBreakdownContent .modal-print-header {
                display: block !important;
            }
            .modal-print-header { display: none; }
            .no-print { display: none !important; }
            .denomination-table th {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #1e293b !important;
                color: white !important;
                position: static !important;
            }
            .denomination-table .total-row td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            /* Make count inputs look like plain text when printing */
            .denomination-count-input {
                border: none !important;
                background: transparent !important;
                width: auto !important;
                -webkit-appearance: none;
                -moz-appearance: textfield;
            }
            .denomination-count-input::-webkit-outer-spin-button,
            .denomination-count-input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
        }
    </style>

    @section('page_title', 'CASH TURN OVER APPROVAL')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div class="w-full p-2">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Filters --}}
            <div class="mb-4 rounded-lg bg-white p-4 shadow-md">
                <form method="GET" action="{{ route('cash-turnover-approval.index') }}"
                    class="flex flex-col gap-3 md:flex-row md:items-end md:gap-2 lg:gap-3">

                    <div class="flex-1 min-w-0">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Start Date</label>
                        <input type="date" name="startDate" value="{{ $startDate }}"
                            class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex-1 min-w-0">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">End Date</label>
                        <input type="date" name="endDate" value="{{ $endDate }}"
                            class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex-1 min-w-0">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Branch</label>
                        <select name="branch"
                                class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($branches as $branch)
                                @php
                                    $branchValue = $branch->store_code
                                        ?? $branch->branch_code
                                        ?? $branch->id;
                                @endphp
                                <option value="{{ $branchValue }}"
                                    {{ (string) $selectedBranch === (string) $branchValue ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-0">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Status</label>
                        <select name="status"
                                class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All</option>
                            <option value="PENDING" {{ $statusFilter === 'PENDING' ? 'selected' : '' }}>Pending</option>
                            <option value="APPROVED" {{ $statusFilter === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ $statusFilter === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="flex-shrink-0">
                        <label class="mb-1 block text-xs font-semibold text-transparent select-none">Action</label>
                        <button type="submit"
                                class="w-full rounded bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 md:w-auto whitespace-nowrap">
                            Generate
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="rounded-lg bg-white p-4 shadow-md">
                <h2 class="mb-4 text-xl font-semibold text-gray-900">Cash Turnover Approvals</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Report #</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th class="px-4 py-3">Cash Amount</th>
                                <th class="px-4 py-3">Remarks</th>
                                <th class="px-4 py-3">Cash Variance</th>
                                <th class="px-4 py-3">Submitted By</th>
                                <th class="px-4 py-3">Approved By</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="turnoverTableBody">
                            @forelse ($reports as $report)
                                <tr class="js-turnover-row cursor-pointer border-b transition hover:bg-blue-50"
                                    data-report-id="{{ $report->id }}">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{ $report->trans_number ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{ $report->start_date ? date('F j, Y', strtotime($report->start_date)) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-green-700">
                                        ₱ {{ number_format($report->total_cash, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        {{ $report->approval_remarks ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold {{ $report->cash_variance < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                        ₱ {{ number_format($report->cash_variance, 2) }}
                                    </td>
                                    <td class="px-4 py-3">{{ $report->submitted_by_name }}</td>
                                    <td class="px-4 py-3">{{ $report->approved_by_name }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColor = match($report->approval_status) {
                                                'APPROVED' => 'bg-green-100 text-green-700',
                                                'REJECTED' => 'bg-red-100 text-red-700',
                                                default    => 'bg-yellow-100 text-yellow-700',
                                            };
                                        @endphp
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $statusColor }}">
                                            {{ $report->approval_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button"
                                                class="js-open-modal rounded bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700"
                                                data-report-id="{{ $report->id }}">
                                            {{ $report->approval_status === 'PENDING' ? 'Review' : 'View' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        No cash turnover records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($totalCount > $perPage)
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xs text-gray-600">
                            Showing {{ (($page - 1) * $perPage) + 1 }}–{{ min($page * $perPage, $totalCount) }}
                            of {{ $totalCount }}
                        </span>
                        <div class="flex gap-2">
                            @if ($page > 1)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
                                   class="rounded bg-gray-200 px-3 py-1 text-xs hover:bg-gray-300">Prev</a>
                            @endif
                            @if ($page * $perPage < $totalCount)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
                                   class="rounded bg-gray-200 px-3 py-1 text-xs hover:bg-gray-300">Next</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== CASH BREAKDOWN MODAL ===== --}}
    <div id="breakdownModal" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="modal-container-cash" tabindex="-1">
            <div class="modal-header">
                <h2>
                    <i class="uil uil-money-bill-stack text-green-600"></i>
                    Cash Breakdown Details
                </h2>
                <div class="flex items-center gap-2">
                    <button class="modal-close-btn no-print" onclick="printBreakdownModal()" title="Print" style="color:#2563eb;">
                        <i class="uil uil-print"></i>
                    </button>
                    <button class="modal-close-btn no-print" onclick="closeBreakdownModal()">
                        <i class="uil uil-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <div id="printableBreakdownContent">
                    <div id="breakdownModalLoading">
                        <div class="spinner-container">
                            <i class="uil uil-spinner-alt animate-spin"></i>
                            <span class="loading-text">Loading cash breakdown...</span>
                            <span class="loading-subtext">Please wait</span>
                        </div>
                    </div>
                    <div id="breakdownModalContent" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.currentReportId = null;
        window._prevBodyOverflow = '';

        const FOCUSABLE = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

        function lockBackground() {
            window._prevBodyOverflow = document.body.style.overflow;
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        }

        function unlockBackground() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = window._prevBodyOverflow || '';
        }

        function trapFocus(event) {
            const modal = document.getElementById('breakdownModal');
            if (!modal || !modal.classList.contains('active')) return;
            if (event.key !== 'Tab') return;

            const focusable = modal.querySelectorAll(FOCUSABLE);
            if (!focusable.length) return;

            const first = focusable[0];
            const last  = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        window.closeBreakdownModal = function () {
            const modal = document.getElementById('breakdownModal');
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.getElementById('breakdownModalContent').innerHTML = '';
            unlockBackground();
            window.currentReportId = null;
        };

        // ===== PRINT BREAKDOWN MODAL =====
        window.printBreakdownModal = function () {
            const content = document.getElementById('breakdownModalContent');
            const loading = document.getElementById('breakdownModalLoading');

            if (loading && loading.style.display !== 'none') {
                alert('Please wait for the breakdown to finish loading.');
                return;
            }
            if (!content || content.style.display === 'none' || !content.innerHTML.trim()) {
                alert('Nothing to print yet. Please wait for the breakdown to load.');
                return;
            }

            window.print();
        };

        // ===== DENOMINATION LIVE RECALCULATION =====
        const DENOM_VALUES = {
            'count_1000': 1000, 'count_500': 500, 'count_200': 200,
            'count_100': 100, 'count_50': 50, 'count_20': 20,
            'count_10': 10, 'count_5': 5, 'count_1': 1,
            'count_50cents': 0.50, 'count_10cents': 0.10, 'count_5cents': 0.05
        };

        window.recalcModalTotals = function () {
            let total = 0;
            document.querySelectorAll('.denomination-count-input').forEach(input => {
                const key = input.dataset.key;
                const value = DENOM_VALUES[key] || 0;
                const count = parseFloat(input.value) || 0;
                const amount = count * value;
                total += amount;

                const amountCell = document.querySelector(`.denomination-amount[data-key="${key}"]`);
                if (amountCell) {
                    amountCell.textContent = '₱ ' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            });

            const totalEl = document.getElementById('modalTotalCash');
            if (totalEl) {
                totalEl.textContent = '₱ ' + total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        };

        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('denomination-count-input')) {
                window.recalcModalTotals();
            }
        });

        // ===== SAVE EDITED COUNTS =====
        window.saveEditedCounts = function () {
            if (!window.currentReportId) {
                alert('No report selected.');
                return;
            }

            const counts = {};
            document.querySelectorAll('.denomination-count-input').forEach(input => {
                counts[input.dataset.key] = parseFloat(input.value) || 0;
            });

            const remarks = document.getElementById('approvalRemarks')?.value || '';

            if (!confirm('Save the edited denomination counts? This will update the pending record.')) return;

            const formData = new FormData();
            formData.append('report_id', window.currentReportId);
            formData.append('remarks', remarks);
            formData.append('_token', '{{ csrf_token() }}');

            Object.keys(counts).forEach(key => {
                formData.append(`counts[${key}]`, counts[key]);
            });

            fetch('{{ route('cash-turnover-approval.save-counts') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Counts saved successfully.');
                    if (data.total_cash !== undefined) {
                        const totalEl = document.getElementById('modalTotalCash');
                        if (totalEl) {
                            totalEl.textContent = '₱ ' + parseFloat(data.total_cash).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                    if (data.variance !== undefined) {
                        const v = parseFloat(data.variance);
                        const container = document.getElementById('modalVarianceContainer');
                        if (container) {
                            if (v === 0) {
                                container.innerHTML = '';
                            } else {
                                const isOver = v > 0;
                                const bgColor = isOver ? '#f0fdf4' : '#fef2f2';
                                const borderColor = isOver ? '#bbf7d0' : '#fecaca';
                                const textColor = isOver ? '#059669' : '#dc2626';
                                const statusText = isOver ? 'Over' : 'Short';
                                container.innerHTML = `
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:8px; padding:8px 12px; border-radius:6px; background:${bgColor}; border:1px solid ${borderColor};">
                                        <span style="font-size:0.7rem; color:#6b7280; font-weight:600;">VARIANCE:</span>
                                        <span style="font-weight:bold; color:${textColor}; font-size:0.85rem;">
                                            ${isOver ? '+' : ''}${v.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                        </span>
                                        <span style="font-size:0.7rem; color:#6b7280;">
                                            (${statusText} by ₱ ${Math.abs(v).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})})
                                        </span>
                                    </div>`;
                            }
                        }
                    }
                } else {
                    alert(data.message || 'Failed to save counts.');
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
                console.error(error);
            });
        };

        window.confirmApprove = function () {
            const remarks = document.getElementById('approvalRemarks')?.value || '';
            if (!confirm('Are you sure you want to APPROVE this cash turnover? This action cannot be undone.')) return;
            window.submitApproval('{{ route('cash-turnover-approval.approve') }}', remarks);
        };

        window.confirmReject = function () {
            const remarks = document.getElementById('approvalRemarks')?.value || '';
            if (!remarks.trim()) { alert('Please provide a remark before rejecting.'); return; }
            if (!confirm('Are you sure you want to REJECT this cash turnover?')) return;
            window.submitApproval('{{ route('cash-turnover-approval.reject') }}', remarks);
        };

        window.submitApproval = function (url, remarks) {
            const formData = new FormData();
            formData.append('report_id', window.currentReportId);
            formData.append('remarks', remarks);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.closeBreakdownModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Operation failed.');
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
                console.error(error);
            });
        };

        window.openBreakdownModal = function (reportId) {
            window.currentReportId = reportId;
            const modal   = document.getElementById('breakdownModal');
            const content = document.getElementById('breakdownModalContent');
            const loading = document.getElementById('breakdownModalLoading');

            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            lockBackground();
            loading.style.display = 'flex';
            content.style.display = 'none';
            content.innerHTML = '';

            setTimeout(() => {
                const firstFocusable = modal.querySelector(FOCUSABLE);
                if (firstFocusable) firstFocusable.focus();
            }, 50);

            fetch(`{{ route('cash-turnover-approval.breakdown') }}?report_id=${encodeURIComponent(reportId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                content.style.display = 'flex';
                content.innerHTML = data.success
                    ? data.html
                    : `<div class="py-10 text-center text-red-500">${data.message}</div>`;

                // Initialize editable recalc after content loads
                if (window.recalcModalTotals) window.recalcModalTotals();
            })
            .catch(error => {
                loading.style.display = 'none';
                content.style.display = 'flex';
                content.innerHTML = '<div class="py-10 text-center text-red-500">Failed to load breakdown.</div>';
                console.error(error);
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('breakdownModal');

            // Close when clicking the backdrop (overlay itself, not its children)
            modal?.addEventListener('click', function (event) {
                if (event.target === modal) {
                    window.closeBreakdownModal();
                }
            });

            // Global click handler — open modal only from table rows/buttons
            document.addEventListener('click', function (event) {
                // If modal is active, ignore all background clicks
                // (prevents accidental reopen/close on fresh load)
                if (modal && modal.classList.contains('active')) {
                    return;
                }

                const row = event.target.closest('.js-turnover-row');
                if (!row) return;

                const reportId = row.dataset.reportId;
                if (!reportId) return;

                // Prevent the same click from bubbling and causing issues
                event.preventDefault();
                event.stopPropagation();

                window.openBreakdownModal(reportId);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    window.closeBreakdownModal();
                }
                trapFocus(event);
            });

            // Prevent background scroll — only the denomination table wrapper scrolls
            document.getElementById('breakdownModal')?.addEventListener('wheel', function (e) {
                const wrapper = document.querySelector('.denomination-table-wrapper');
                if (wrapper && wrapper.contains(e.target)) return;
                e.preventDefault();
            }, { passive: false });

            document.getElementById('breakdownModal')?.addEventListener('touchmove', function (e) {
                const wrapper = document.querySelector('.denomination-table-wrapper');
                if (wrapper && wrapper.contains(e.target)) return;
                e.preventDefault();
            }, { passive: false });
        });
    </script>
</x-app-layout>