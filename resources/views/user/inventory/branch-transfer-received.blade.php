<x-app-layout>
    @section('page_title', 'LOCATION TRANSFER RECEIVED')

    @php
        $currentUserName = trim(collect([
            auth()->user()->firstname ?? null,
            auth()->user()->middlename ?? null,
            auth()->user()->lastname ?? null,
        ])->filter()->implode(' ')) ?: (auth()->user()->username ?? auth()->user()->email ?? 'Unknown');
    @endphp

    <style>
        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(4.5rem, 1fr));
            gap: 0.5rem;
        }

        .breakdown-chip {
            min-height: 2.25rem;
        }

        .breakdown-edit-input::-webkit-outer-spin-button,
        .breakdown-edit-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .breakdown-edit-input {
            -moz-appearance: textfield;
            appearance: textfield;
        }
    </style>

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
                <div class="mb-6 border-b pb-4">
                    <div class="flex justify-between items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Received Location Transfers</h1>
                            <p class="text-gray-600 mt-1">Check and receive approved location transfers</p>
                        </div>
                        <a href="{{ route('branch-transfer.pending') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 shadow-sm transition-all">
                            Back to Pending
                        </a>
                    </div>
                </div>

                @if(session('message'))
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg">
                        {{ session('message') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @if(!empty($isMainLocationTransferBranch))
                    <form method="GET" action="{{ route('branch-transfer.received') }}" class="mb-4 flex flex-wrap items-end gap-3">
                        <div>
                            <label for="branch_scope" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Branch</label>
                            <select id="branch_scope" name="branch_scope" class="min-w-[260px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Branches</option>
                                @foreach($locationTransferBranches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $selectedBranch === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold">Apply</button>
                        @if(!empty($selectedBranch))
                            <a href="{{ route('branch-transfer.received') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-semibold">Clear</a>
                        @endif
                    </form>
                @endif

                <div class="overflow-x-auto overflow-y-auto h-[500px] shadow-sm border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 border-collapse" id="receivedTable">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Transaction #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Deliver From</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Deliver To</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Prepared By</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Checked By</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Received By</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Date Delivered</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200 relative">
                                    <div class="flex items-center gap-2">
                                        <span>Status</span>
                                        <div class="relative inline-block">
                                            <button onclick="toggleReceivedFilterDropdown(event)"
                                                class="inline-flex items-center px-2 py-1 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-xs">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                                </svg>
                                                <span id="receivedFilterLabel">Filter</span>
                                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div id="receivedFilterDropdown" class="hidden absolute top-full left-0 mt-1 w-56 bg-white rounded-md shadow-lg border border-gray-200 z-50 py-1">
                                                <div class="px-3 py-2 border-b border-gray-100">
                                                    <span class="text-xs font-semibold text-gray-500">Filter by Status</span>
                                                </div>
                                                <div class="px-2 py-1" onclick="event.stopPropagation();">
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="receivedStatusFilter" value="All" onchange="filterReceivedByStatus('All')" checked class="mr-2">
                                                        <span class="text-sm text-gray-700">All</span>
                                                        <span class="ml-auto text-xs text-gray-400" id="receivedCountAll">0</span>
                                                    </label>
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="receivedStatusFilter" value="not_received" onchange="filterReceivedByStatus('not_received')" class="mr-2">
                                                        <span class="text-sm text-gray-700"><span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-1.5"></span>Not Received</span>
                                                        <span class="ml-auto text-xs text-gray-400" id="receivedCountNotReceived">0</span>
                                                    </label>
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="receivedStatusFilter" value="checking" onchange="filterReceivedByStatus('checking')" class="mr-2">
                                                        <span class="text-sm text-gray-700"><span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-1.5"></span>Checking</span>
                                                        <span class="ml-auto text-xs text-gray-400" id="receivedCountChecking">0</span>
                                                    </label>
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="receivedStatusFilter" value="received" onchange="filterReceivedByStatus('received')" class="mr-2">
                                                        <span class="text-sm text-gray-700"><span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>Received</span>
                                                        <span class="ml-auto text-xs text-gray-400" id="receivedCountReceived">0</span>
                                                    </label>
                                                </div>
                                                <div class="px-3 py-2 border-t border-gray-100 flex justify-between items-center">
                                                    <button onclick="clearReceivedFilter(event)" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                                        Clear Filter
                                                    </button>
                                                    <span class="text-xs text-gray-400" id="receivedFilterResult"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                            @if($receivedOrders->isEmpty())
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">No approved transfers found</p>
                                            <p class="text-sm">Approved location transfers will show here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @foreach($receivedOrders as $order)
                                <tr class="hover:bg-blue-50 transition-colors duration-150 order-row"
                                    data-order-id="{{ $order->id }}"
                                    data-status="{{ $order->receive_status }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->sales_invoice ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->sender_branch_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->branch_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->prepared_by ?? 'Unknown' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->checked_by ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->received_by ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $order->date_delivered ? date('M d, Y h:i A', strtotime($order->date_delivered)) : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm status-cell">
                                        @if($order->receive_status === 'received')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                Received
                                            </span>
                                        @elseif($order->receive_status === 'checking')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                Checking
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                Not Received
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex justify-center gap-1.5">
                                            <button onclick="openViewModal({{ $order->id }})"
                                                class="px-3 py-1 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 text-xs font-medium transition-colors">
                                                View
                                            </button>
                                            <button onclick="openReceiveModal({{ $order->id }})"
                                                class="px-3 py-1 rounded-md text-xs font-medium transition-colors {{ $order->receive_status === 'received' || !$order->can_receive ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-green-50 text-green-700 hover:bg-green-100' }}"
                                                title="{{ !$order->can_receive ? 'Only the selected receiving store can receive this transfer.' : '' }}"
                                                {{ $order->receive_status === 'received' || !$order->can_receive ? 'disabled' : '' }}>
                                                Receive
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex justify-between items-center text-sm text-gray-500">
                    <span>Showing <span id="receivedVisibleCount">{{ $receivedOrders->count() }}</span> of <span id="receivedTotalCount">{{ $receivedOrders->count() }}</span> location transfers</span>
                    <div class="mt-4">
                        {{ $receivedOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Location Transfer Details</h3>
                <button onclick="closeViewModal()" class="text-white hover:text-gray-200 transition-colors p-1 hover:bg-white/10 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="viewContent" class="p-6"></div>
            </div>
        </div>
    </div>

    <div id="receiveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h3 class="text-xl font-bold text-white" id="receiveHeader">Location Transfer Transaction #</h3>
            </div>
            <div class="overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="receiveContent" class="p-6"></div>
            </div>
        </div>
    </div>

<script>
    const RECEIVED_BASE_URL = '{{ url("/inventory/location-transfer/received") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const CURRENT_USER_NAME = @json($currentUserName);
    let currentReceiveId = null;
    let receiveConfirmed = false;
    let receiveItemsState = [];
    let receiveBreakdownEditMode = false;
    let receivedFilterOpen = false;
    let currentReceivedFilter = 'All';

    function toggleReceivedFilterDropdown(event) {
        if (event) {
            event.stopPropagation();
        }

        const dropdown = document.getElementById('receivedFilterDropdown');
        receivedFilterOpen = !receivedFilterOpen;
        dropdown.classList.toggle('hidden', !receivedFilterOpen);

        if (receivedFilterOpen) {
            updateReceivedFilterCounts();
        }
    }

    function updateReceivedFilterCounts() {
        const rows = document.querySelectorAll('.order-row');
        let total = 0;
        let notReceived = 0;
        let checking = 0;
        let received = 0;

        rows.forEach(row => {
            total++;
            if (row.dataset.status === 'not_received') notReceived++;
            if (row.dataset.status === 'checking') checking++;
            if (row.dataset.status === 'received') received++;
        });

        document.getElementById('receivedCountAll').textContent = total;
        document.getElementById('receivedCountNotReceived').textContent = notReceived;
        document.getElementById('receivedCountChecking').textContent = checking;
        document.getElementById('receivedCountReceived').textContent = received;
    }

    function filterReceivedByStatus(status) {
        currentReceivedFilter = status;
        const rows = document.querySelectorAll('.order-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const visible = status === 'All' || row.dataset.status === status;
            row.style.display = visible ? '' : 'none';
            if (visible) {
                visibleCount++;
            }
        });

        const labels = {
            All: 'Filter',
            not_received: 'Not Received',
            checking: 'Checking',
            received: 'Received'
        };

        document.getElementById('receivedFilterLabel').textContent = labels[status] || 'Filter';
        document.getElementById('receivedFilterResult').textContent = visibleCount > 0 ? `${visibleCount} transfers` : 'No transfers found';
        document.getElementById('receivedVisibleCount').textContent = visibleCount;

        setTimeout(() => {
            document.querySelectorAll('input[name="receivedStatusFilter"]').forEach(input => {
                input.checked = input.value === status;
            });
        }, 10);
    }

    function clearReceivedFilter(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }

        filterReceivedByStatus('All');
        document.getElementById('receivedFilterResult').textContent = '';
        const dropdown = document.getElementById('receivedFilterDropdown');
        dropdown.classList.add('hidden');
        receivedFilterOpen = false;
    }

    function loadingHtml(label) {
        return `
            <div class="flex justify-center items-center py-12">
                <div class="flex flex-col items-center gap-3">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="text-gray-500 text-sm">${label}</p>
                </div>
            </div>
        `;
    }

    function money(value) {
        const amount = parseFloat(value || 0);
        return `&#8369;${amount.toFixed(2)}`;
    }

    function formatQuantity(value) {
        return (parseFloat(value || 0)).toFixed(3);
    }

    function normalizeBreakdown(item) {
        if (item.quantity_breakdown && item.quantity_breakdown.length) {
            return item.quantity_breakdown.map(value => parseFloat(value || 0)).filter(value => value > 0);
        }

        const quantity = parseFloat(item.quantity || 0);
        return quantity > 0 ? [quantity] : [];
    }

    function normalizeStatuses(item, count) {
        const statuses = item.breakdown_statuses && item.breakdown_statuses.length ? item.breakdown_statuses : [];
        return Array.from({ length: count }, (_, index) => {
            const status = statuses[index] || 'unchecked';
            return ['unchecked', 'received', 'rejected'].includes(status) ? status : 'unchecked';
        });
    }

    function breakdownChipClasses(status, interactive = false) {
        const base = 'breakdown-chip px-2 py-1 border rounded-md text-sm font-semibold text-center transition-colors';
        const cursor = interactive ? ' cursor-pointer' : '';
        const palette = {
            received: ' bg-green-100 border-green-300 text-green-800',
            rejected: ' bg-red-100 border-red-300 text-red-800',
            unchecked: ' bg-white border-gray-300 text-gray-800'
        };

        return base + cursor + (palette[status] || palette.unchecked);
    }

    function breakdownTotal(breakdown) {
        return breakdown.reduce((sum, quantity) => sum + (parseFloat(quantity) || 0), 0);
    }

    function maxReceivableQuantity(item) {
        return (parseFloat(item.approved_quantity || item.quantity || 0) || 0) +
            (parseFloat(item.sender_current_quantity || 0) || 0);
    }

    function renderBreakdownGrid(item, itemIndex = null, interactive = false) {
        const breakdown = normalizeBreakdown(item);
        const statuses = normalizeStatuses(item, breakdown.length);

        if (!breakdown.length) {
            return '<span class="text-gray-400 text-sm">N/A</span>';
        }

        return `
            <div class="breakdown-grid">
                ${breakdown.map((quantity, breakdownIndex) => {
                    const status = statuses[breakdownIndex] || 'unchecked';
                    if (!interactive) {
                        return `<div class="${breakdownChipClasses(status)}">${formatQuantity(quantity)}</div>`;
                    }

                    if (receiveBreakdownEditMode) {
                        return `
                            <input type="number"
                                class="breakdown-edit-input px-2 py-1 border border-green-400 rounded-md text-sm font-semibold focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                min="0.001"
                                step="0.001"
                                value="${formatQuantity(quantity)}"
                                data-item-index="${itemIndex}"
                                data-breakdown-index="${breakdownIndex}"
                                onclick="event.stopPropagation()">
                        `;
                    }

                    return `
                        <div class="flex items-center gap-1 breakdown-chip-wrap" data-breakdown-index="${breakdownIndex}">
                            <button type="button"
                                class="${breakdownChipClasses(status, true)} flex-1"
                                data-item-index="${itemIndex}"
                                data-breakdown-index="${breakdownIndex}"
                                onclick="event.stopPropagation(); cycleReceiveBreakdownStatus(${itemIndex}, ${breakdownIndex})">
                                ${formatQuantity(quantity)}
                            </button>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function refreshReceiveRow(itemIndex) {
        const row = document.querySelector(`.receive-row[data-item-index="${itemIndex}"]`);
        if (!row) {
            return;
        }

        const item = receiveItemsState[itemIndex];
        const total = breakdownTotal(item.quantity_breakdown);
        row.querySelector('.receive-breakdown-cell').innerHTML = renderBreakdownGrid(item, itemIndex, true);
        row.querySelector('.receive-qty-total').textContent = formatQuantity(total);
    }

    function toggleInlineBreakdown(row) {
        const panel = row.querySelector('.breakdown-grid-panel');
        if (panel) {
            panel.classList.toggle('hidden');
        }
    }

    function refreshAllReceiveRows() {
        receiveItemsState.forEach((_, index) => refreshReceiveRow(index));
        const button = document.getElementById('toggleReceiveBreakdownEditBtn');
        if (button) {
            button.textContent = receiveBreakdownEditMode ? 'Save' : 'Edit';
            button.className = receiveBreakdownEditMode
                ? 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-semibold'
                : 'px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-semibold';
        }
    }

    function fetchJson(url, options = {}) {
        return fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                ...(options.headers || {})
            },
            ...options
        }).then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                throw new Error(data.message || `HTTP error ${response.status}`);
            }
            return data;
        });
    }

    function openViewModal(orderId) {
        const modal = document.getElementById('viewModal');
        const content = document.getElementById('viewContent');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        content.innerHTML = loadingHtml('Loading transfer details...');

        fetchJson(`${RECEIVED_BASE_URL}/view/${orderId}/json`)
            .then(data => renderView(data.order, data.items))
            .catch(error => {
                content.innerHTML = `<div class="text-center py-8 text-red-600 font-semibold">${error.message}</div>`;
            });
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function renderView(order, items) {
        const content = document.getElementById('viewContent');
        const itemsTotal = (items || []).reduce((sum, item) => sum + parseFloat(item.line_total || 0), 0);
        const rows = (items || []).map((item, index) => `
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="toggleInlineBreakdown(this)">
                <td class="px-4 py-3 text-sm text-gray-500">${index + 1}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.brand_name || 'N/A'}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.menu_name || 'N/A'}</td>
                <td class="px-4 py-3 text-sm text-gray-900">
                    <div class="mb-2 font-bold text-right">${formatQuantity(item.quantity || 0)}</div>
                    <div class="breakdown-grid-panel hidden mt-2">
                        ${renderBreakdownGrid(item, null, false)}
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.unit || 'N/A'}</td>
                <td class="px-4 py-3 text-sm text-gray-900 text-right">${money(item.price)}</td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">${money(item.line_total)}</td>
            </tr>
        `).join('');

        content.innerHTML = `
            <div class="mb-6 border-b pb-4">
                <div class="flex justify-between items-start flex-wrap gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">${order.sales_invoice || order.reference_number || 'N/A'}</h2>
                        <p class="text-sm text-gray-500 mt-1">Prepared by ${order.user_name || 'Unknown'}</p>
                        <p class="text-sm text-gray-500 mt-1">Deliver from ${order.sender_branch_name || 'N/A'} to ${order.branch_name || 'N/A'}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Amount</p>
                        <p class="text-2xl font-bold text-gray-900">${money(itemsTotal || order.total_amount)}</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Brand</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Item Description</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${rows || '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No items found.</td></tr>'}
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeViewModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Back</button>
            </div>
        `;
    }

    function openReceiveModal(orderId) {
        currentReceiveId = orderId;
        receiveConfirmed = false;
        receiveBreakdownEditMode = false;
        const modal = document.getElementById('receiveModal');
        const content = document.getElementById('receiveContent');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        content.innerHTML = loadingHtml('Preparing receive form...');

        fetchJson(`${RECEIVED_BASE_URL}/checking/${orderId}/json`, { method: 'POST', body: JSON.stringify({}) })
            .then(() => {
                updateReceivedRowStatus(orderId, 'checking');
                return fetchJson(`${RECEIVED_BASE_URL}/view/${orderId}/json`);
            })
            .then(data => renderReceive(data.order, data.items))
            .catch(error => {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-600 text-lg font-semibold">${error.message}</p>
                        <button onclick="closeReceiveModal(true)" class="mt-4 px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Back</button>
                    </div>
                `;
            });
    }

    function renderReceive(order, items) {
        document.getElementById('receiveHeader').textContent = `Location Transfer Transaction # ${order.sales_invoice || order.reference_number || 'N/A'}`;
        const content = document.getElementById('receiveContent');
        receiveItemsState = (items || []).map(item => {
            const quantityBreakdown = normalizeBreakdown(item);
            return {
                ...item,
                quantity_breakdown: quantityBreakdown,
                breakdown_statuses: normalizeStatuses(item, quantityBreakdown.length)
            };
        });

        const rows = receiveItemsState.map((item, index) => `
            <tr class="receive-row hover:bg-gray-50 cursor-pointer" data-request-id="${item.id}" data-item-index="${index}" onclick="toggleInlineBreakdown(this)">
                <td class="px-4 py-3 text-sm text-gray-500">${index + 1}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.brand_name || 'N/A'}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.menu_name || 'N/A'}</td>
                <td class="px-4 py-3 text-sm min-w-[520px]">
                    <div class="mb-2 inline-flex items-center gap-2 rounded-md bg-gray-50 px-3 py-1 text-sm font-bold text-gray-900">
                        <span class="receive-qty-total">${formatQuantity(breakdownTotal(item.quantity_breakdown))}</span>
                        <span class="text-xs font-medium text-gray-500">${item.unit || ''}</span>
                    </div>
                    <div class="receive-breakdown-cell breakdown-grid-panel hidden mt-2">
                        ${renderBreakdownGrid(item, index, true)}
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">${item.unit || 'N/A'}</td>
            </tr>
        `).join('');

        content.innerHTML = `
            <div class="mb-6">
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Deliver From</p>
                        <p class="text-base font-bold text-gray-900">${order.sender_branch_name || 'N/A'}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Deliver To</p>
                        <p class="text-base font-bold text-gray-900">${order.branch_name || 'N/A'}</p>
                    </div>
                </div>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h4 class="text-lg font-bold text-gray-900">Transfer Items</h4>
                    <button type="button" id="toggleReceiveBreakdownEditBtn"
                        onclick="event.stopPropagation(); toggleReceiveBreakdownEditMode()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-semibold">
                        Edit
                    </button>
                </div>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Brand</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Item Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Unit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${rows || '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No items found.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Prepared By</label>
                    <input id="preparedBy" type="text" value="${order.user_name || 'Unknown'}" readonly
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Checked By</label>
                    <input id="checkedBy" type="text" value="${CURRENT_USER_NAME}" readonly
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Received By</label>
                    <input id="receivedBy" type="text" value="${CURRENT_USER_NAME}" readonly
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
                <textarea id="receiveRemarks" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button onclick="closeReceiveModal(true)" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Back</button>
                <button onclick="confirmReceived()" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Confirm Received</button>
            </div>
        `;
    }

    function updateReceivedRowStatus(orderId, status) {
        const row = document.querySelector(`.order-row[data-order-id="${orderId}"]`);
        if (!row) {
            return;
        }

        const statusCell = row.querySelector('.status-cell');
        if (!statusCell) {
            return;
        }

        row.dataset.status = status;
        const badges = {
            not_received: `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                    Not Received
                </span>
            `,
            checking: `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                    Checking
                </span>
            `,
            received: `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                    Received
                </span>
            `
        };

        statusCell.innerHTML = badges[status] || badges.not_received;
        updateReceivedFilterCounts();
        filterReceivedByStatus(currentReceivedFilter);
    }

    function closeReceiveModal(cancelChecking) {
        const orderId = currentReceiveId;
        const closeNow = () => {
            document.getElementById('receiveModal').classList.add('hidden');
            document.body.style.overflow = '';
            currentReceiveId = null;
        };

        if (cancelChecking && orderId && !receiveConfirmed) {
            fetchJson(`${RECEIVED_BASE_URL}/cancel-checking/${orderId}/json`, { method: 'POST', body: JSON.stringify({}) })
                .finally(() => {
                    updateReceivedRowStatus(orderId, 'not_received');
                    closeNow();
                });
            return;
        }

        closeNow();
    }

    function cycleReceiveBreakdownStatus(itemIndex, breakdownIndex) {
        if (receiveBreakdownEditMode) {
            return;
        }

        const item = receiveItemsState[itemIndex];
        if (!item) {
            return;
        }

        const current = item.breakdown_statuses[breakdownIndex] || 'unchecked';
        const next = current === 'unchecked' ? 'received' : (current === 'received' ? 'rejected' : 'unchecked');
        item.breakdown_statuses[breakdownIndex] = next;
        refreshReceiveRow(itemIndex);
    }

    function toggleReceiveBreakdownEditMode() {
        if (!receiveBreakdownEditMode) {
            receiveBreakdownEditMode = true;
            refreshAllReceiveRows();
            return;
        }

        saveAllReceiveBreakdownQuantities();
    }

    function saveAllReceiveBreakdownQuantities() {
        const draftItems = receiveItemsState.map(item => ({
            ...item,
            quantity_breakdown: [...item.quantity_breakdown],
            breakdown_statuses: [...item.breakdown_statuses]
        }));

        let invalidInput = null;
        document.querySelectorAll('.breakdown-edit-input').forEach(input => {
            if (invalidInput) {
                return;
            }

            const itemIndex = parseInt(input.dataset.itemIndex);
            const breakdownIndex = parseInt(input.dataset.breakdownIndex);
            const quantity = parseFloat(input.value);

            if (Number.isNaN(quantity) || quantity <= 0) {
                invalidInput = input;
                alert('Breakdown quantity must be greater than 0.');
                return;
            }

            const draftItem = draftItems[itemIndex];
            if (!draftItem) {
                return;
            }

            const oldQuantity = draftItem.quantity_breakdown[breakdownIndex];
            draftItem.quantity_breakdown[breakdownIndex] = quantity;
            if (Math.abs((parseFloat(oldQuantity) || 0) - quantity) > 0.0001) {
                draftItem.breakdown_statuses[breakdownIndex] = 'unchecked';
            }
        });

        if (invalidInput) {
            invalidInput.focus();
            return;
        }

        for (const item of draftItems) {
            const total = breakdownTotal(item.quantity_breakdown);
            if (total > maxReceivableQuantity(item) + 0.0001) {
                alert(`Insufficient stock quantity for ${item.menu_name || 'this item'}. Maximum receivable quantity is ${formatQuantity(maxReceivableQuantity(item))}.`);
                return;
            }
        }

        receiveItemsState = draftItems;
        receiveBreakdownEditMode = false;
        refreshAllReceiveRows();
    }

    function confirmReceived() {
        if (!currentReceiveId) {
            alert('No transfer selected.');
            return;
        }

        const receivedBy = document.getElementById('receivedBy').value.trim();

        const items = [];
        let invalid = false;
        if (receiveBreakdownEditMode) {
            saveAllReceiveBreakdownQuantities();
            if (receiveBreakdownEditMode) {
                return;
            }
        }

        receiveItemsState.forEach((item, itemIndex) => {
            const quantity = breakdownTotal(item.quantity_breakdown);
            if (Number.isNaN(quantity) || quantity < 0.1) {
                alert('Quantity must be at least 0.1.');
                invalid = true;
                return;
            }

            if (quantity > maxReceivableQuantity(item) + 0.0001) {
                alert(`Insufficient stock quantity for ${item.menu_name || 'this item'}. Maximum receivable quantity is ${formatQuantity(maxReceivableQuantity(item))}.`);
                invalid = true;
                return;
            }

            items.push({
                id: parseInt(item.id),
                quantity: quantity,
                quantity_breakdown: item.quantity_breakdown,
                breakdown_statuses: item.breakdown_statuses
            });
        });

        if (invalid) {
            return;
        }

        if (!items.length) {
            alert('No items to receive.');
            return;
        }

        fetchJson(`${RECEIVED_BASE_URL}/confirm/${currentReceiveId}/json`, {
            method: 'POST',
            body: JSON.stringify({
                prepared_by: document.getElementById('preparedBy').value,
                checked_by: document.getElementById('checkedBy').value,
                received_by: receivedBy,
                remarks: document.getElementById('receiveRemarks').value,
                items: items
            })
        }).then(data => {
            receiveConfirmed = true;
            alert(data.message || 'Location transfer received successfully.');
            updateReceivedRowStatus(currentReceiveId, 'received');
            closeReceiveModal(false);
            location.reload();
        }).catch(error => {
            alert(error.message);
        });
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('receivedFilterDropdown');
        if (!dropdown) {
            return;
        }

        const filterButton = event.target.closest('button');
        if (!dropdown.contains(event.target) && !filterButton?.querySelector('#receivedFilterLabel')) {
            dropdown.classList.add('hidden');
            receivedFilterOpen = false;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateReceivedFilterCounts();
        filterReceivedByStatus('All');
        document.getElementById('receivedFilterResult').textContent = '';
    });
</script>
</x-app-layout>