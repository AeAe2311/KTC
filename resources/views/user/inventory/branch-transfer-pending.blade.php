<x-app-layout>
    @section('page_title', 'LOCATION TRANSFER PENDING')

    <style>
        .pending-breakdown-input::-webkit-outer-spin-button,
        .pending-breakdown-input::-webkit-inner-spin-button,
        .qty-input::-webkit-outer-spin-button,
        .qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .pending-breakdown-input,
        .qty-input {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        #pendingQuantityBreakdownPanel {
            --pending-breakdown-panel-height: 24rem;
            height: min(var(--pending-breakdown-panel-height), calc(100vh - 16px));
            max-height: calc(100vh - 16px);
            overflow: hidden;
            z-index: 9999;
        }

        #pendingQuantityBreakdownRows {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
    </style>

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
                <!-- Header sections-->
                <div class="mb-6 border-b pb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Pending Location Transfers</h1>
                            <p class="text-gray-600 mt-1">Review, edit, and approve location transfer requests</p>
                        </div>
                        <a href="{{ route('branch-transfer.add') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm transition-all hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Request
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
                    <form method="GET" action="{{ route('branch-transfer.pending') }}" class="mb-4 flex flex-wrap items-end gap-3">
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
                            <a href="{{ route('branch-transfer.pending') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-semibold">Clear</a>
                        @endif
                    </form>
                @endif

                <!-- Table with Filter in Header - Excel Style -->
                <div class="overflow-x-auto overflow-y-auto h-[500px] shadow-sm border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 border-collapse" id="ordersTable"> 
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Transaction #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Deliver From</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Deliver To</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Request Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Submitted By</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Total Amount</th>
                                <!-- Status Column with Excel-like Filter Dropdown -->
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200 relative">
                                    <div class="flex items-center gap-2">
                                        <span>Status</span>
                                        <div class="relative inline-block">
                                            <button onclick="toggleFilterDropdown(event)" 
                                                class="inline-flex items-center px-2 py-1 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-xs">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                                </svg>
                                                <span id="filterLabel">Filter</span>
                                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <!-- Dropdown Menu -->
                                            <div id="filterDropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50 py-1">
                                                <div class="px-3 py-2 border-b border-gray-100">
                                                    <span class="text-xs font-semibold text-gray-500">Filter by Status</span>
                                                </div>
                                                <div class="px-2 py-1" id="filterOptions" onclick="event.stopPropagation();">
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="statusFilter" value="All" onchange="filterByStatus('All')" checked class="mr-2">
                                                        <span class="text-sm text-gray-700">All</span>
                                                        <span class="ml-auto text-xs text-gray-400" id="countAll">0</span>
                                                    </label>
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="statusFilter" value="pending" onchange="filterByStatus('pending')" class="mr-2">
                                                        <span class="text-sm text-gray-700">
                                                            <span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-1.5"></span>
                                                            Pending
                                                        </span>
                                                        <span class="ml-auto text-xs text-gray-400" id="countPending">0</span>
                                                    </label>
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="statusFilter" value="approved" onchange="filterByStatus('approved')" class="mr-2">
                                                        <span class="text-sm text-gray-700">
                                                            <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>
                                                            Approved
                                                        </span>
                                                        <span class="ml-auto text-xs text-gray-400" id="countApproved">0</span>
                                                    </label>
                                                    <label class="flex items-center px-2 py-1.5 rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                                        <input type="radio" name="statusFilter" value="rejected" onchange="filterByStatus('rejected')" class="mr-2">
                                                        <span class="text-sm text-gray-700">
                                                            <span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-1.5"></span>
                                                            Rejected
                                                        </span>
                                                        <span class="ml-auto text-xs text-gray-400" id="countRejected">0</span>
                                                    </label>
                                                </div>
                                                <div class="px-3 py-2 border-t border-gray-100 flex justify-between items-center">
                                                    <button onclick="clearFilter(event)" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                                        Clear Filter
                                                    </button>
                                                    <span class="text-xs text-gray-400" id="filterResult"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                            <!-- Empty State Row - Hidden by default -->
                            <tr id="emptyStateRow" class="hidden">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-700">No results found</p>
                                        <p class="text-sm text-gray-500 mt-1">No orders match the selected filter</p>
                                        <button onclick="clearFilter(event)" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Clear Filter
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Initial Empty State for No Orders -->
                            @if($pendingOrders->isEmpty())
                            <tr id="noOrdersRow">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No location transfers found</p>
                                        <p class="text-sm">All location transfers have been processed</p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            
                            @forelse($pendingOrders as $order)
                                <tr class="hover:bg-blue-50 transition-colors duration-150 order-row" 
                                    data-status="{{ $order->status }}"
                                    data-order-id="{{ $order->id }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->sales_invoice ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->sender_branch_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->branch_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $order->order_date ? date('M d, Y', strtotime($order->order_date)) : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->user_name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                        ₱{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($order->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                Pending
                                            </span>
                                        @elseif($order->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex justify-center gap-1.5">
                                            <button onclick="openOrderPopup({{ $order->id }})" 
                                                class="view-btn px-3 py-1 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 text-xs font-medium transition-colors">
                                                View
                                            </button>
                                            @if($order->status === 'pending' && ($order->can_manage ?? false))
                                                <button onclick="approveOrder({{ $order->id }})" 
                                                    class="approve-btn px-3 py-1 bg-green-50 text-green-700 rounded-md hover:bg-green-100 text-xs font-medium transition-colors">
                                                    Approve
                                                </button>
                                                <button onclick="showRejectModal({{ $order->id }})" 
                                                    class="reject-btn px-3 py-1 bg-red-50 text-red-700 rounded-md hover:bg-red-100 text-xs font-medium transition-colors">
                                                    Reject
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <!-- No orders row is already added above -->
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer with Count -->
                <div class="mt-3 flex justify-between items-center text-sm text-gray-500">
                    <span>Showing <span id="visibleCount">{{ $pendingOrders->count() }}</span> of <span id="totalCount">{{ $pendingOrders->count() }}</span> orders</span>
                    {{-- KEN ADDED PAGINATION LINKS 08-27-2026 --}}
                    <div class="mt-4">
                        {{ $pendingOrders->links() }}
                    </div>      
                    {{-- END OF KEN ADDED PAGINATION LINKS 08-27-2026  --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Popup Modal -->
    <div id="orderPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden">
            <!-- Popup Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Location Transfer Details
                </h3>
                <button onclick="closeOrderPopup()" class="text-white hover:text-gray-200 transition-colors p-1 hover:bg-white/10 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Popup Content -->
            <div class="overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="popupContent" class="p-6">
                    <!-- Dynamic content will be loaded here -->
                    <div class="flex justify-center items-center py-12">
                        <div class="flex flex-col items-center gap-3">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                            <p class="text-gray-500 text-sm">Loading order details...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="pendingQuantityBreakdownPanel" class="hidden fixed w-40 bg-white shadow-2xl border border-gray-300 flex flex-col z-[70]">
        <div class="px-4 py-3 border-b border-gray-300 bg-white shrink-0">
            <h4 class="text-base font-bold text-gray-900">Qty. Breakdown</h4>
        </div>
        <div id="pendingQuantityBreakdownRows" class="flex-1 overflow-y-auto px-4 py-3 space-y-2 bg-white"></div>
        <div class="px-3 py-3 border-t-2 border-gray-900 bg-white shrink-0">
            <div class="grid grid-cols-[1fr_auto] gap-3">
                <div class="border-2 border-gray-200 px-3 py-2 text-sm font-bold text-blue-600 bg-white flex items-center justify-center">
                    <span id="pendingQuantityBreakdownTotal">0.000</span>
                </div>
                <button type="button" id="applyPendingQuantityBreakdownBtn" class="px-4 py-2 bg-blue-600 text-white font-bold text-sm hover:bg-blue-700">OK</button>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Reject Location Transfer
                </h3>
                <p class="text-red-100 text-sm mt-1">Please provide a reason for rejection</p>
            </div>
            <div class="px-6 py-6">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            Reason for Rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                            placeholder="Please specify why this location transfer is being rejected..."></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    const BASE_URL = '{{ url("/inventory/location-transfer/pending") }}';
    let currentFilter = 'All';
    let isDropdownOpen = false;
    let editMode = false;
    let editingOrderId = null;
    let currentOrderData = null; // Store order data for saving
    const MIN_PENDING_QUANTITY = 0.1;
    let activePendingBreakdownRow = null;
    
    // Toggle Filter Dropdown
    function toggleFilterDropdown(event) {
        if (event) {
            event.stopPropagation();
        }
        const dropdown = document.getElementById('filterDropdown');
        isDropdownOpen = !isDropdownOpen;
        
        if (isDropdownOpen) {
            dropdown.classList.remove('hidden');
            updateFilterCounts();
        } else {
            dropdown.classList.add('hidden');
        }
    }
    
    // Update filter counts
    function updateFilterCounts() {
        const rows = document.querySelectorAll('.order-row');
        let total = 0, pending = 0, approved = 0, rejected = 0;
        
        rows.forEach(row => {
            const status = row.dataset.status;
            total++;
            if (status === 'pending') pending++;
            else if (status === 'approved') approved++;
            else if (status === 'rejected') rejected++;
        });
        
        document.getElementById('countAll').textContent = total;
        document.getElementById('countPending').textContent = pending;
        document.getElementById('countApproved').textContent = approved;
        document.getElementById('countRejected').textContent = rejected;
    }
    
    // Filter by Status
    function filterByStatus(status) {
        if (window.event) {
            window.event.stopPropagation();
        }
        
        currentFilter = status;
        const rows = document.querySelectorAll('.order-row');
        const emptyStateRow = document.getElementById('emptyStateRow');
        const noOrdersRow = document.getElementById('noOrdersRow');
        let visibleCount = 0;
        
        if (noOrdersRow) {
            noOrdersRow.style.display = 'none';
        }
        
        if (emptyStateRow) {
            emptyStateRow.classList.add('hidden');
            emptyStateRow.style.display = 'none';
        }
        
        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            if (status === 'All' || rowStatus === status) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        if (visibleCount === 0 && emptyStateRow) {
            emptyStateRow.classList.remove('hidden');
            emptyStateRow.style.display = 'table-row';
        }
        
        const filterLabel = document.getElementById('filterLabel');
        const statusNames = {
            'All': 'Filter',
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected'
        };
        filterLabel.textContent = statusNames[status] || 'Filter';
        
        const resultText = visibleCount > 0 ? `${visibleCount} orders` : 'No orders found';
        document.getElementById('filterResult').textContent = resultText;
        
        document.getElementById('visibleCount').textContent = visibleCount;
        
        setTimeout(() => {
            document.querySelectorAll('input[name="statusFilter"]').forEach(input => {
                input.checked = (input.value === status);
            });
        }, 10);
    }
    
    // Clear Filter
    function clearFilter(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const allRadio = document.querySelector('input[name="statusFilter"][value="All"]');
        if (allRadio) {
            allRadio.checked = true;
        }
        filterByStatus('All');
        document.getElementById('filterResult').textContent = '';
        
        const dropdown = document.getElementById('filterDropdown');
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            isDropdownOpen = false;
        }
    }
    
    // Approve Order function
    function approveOrder(orderId) {
        if (confirm('Are you sure you want to approve this location transfer? This will move this request to approval.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/inventory/location-transfer/pending/approve') }}/${orderId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Open Order Popup
    function openOrderPopup(orderId) {
        const popup = document.getElementById('orderPopup');
        const popupContent = document.getElementById('popupContent');
        
        // Reset edit mode
        editMode = false;
        editingOrderId = orderId;
        currentOrderData = null;
        
        popup.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        popupContent.innerHTML = `
            <div class="flex justify-center items-center py-12">
                <div class="flex flex-col items-center gap-3">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="text-gray-500 text-sm">Loading order details...</p>
                </div>
            </div>
        `;
        
        const url = `${BASE_URL}/view/${orderId}/json`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    currentOrderData = data;
                    renderPopupContent(data.order, data.items);
                } else {
                    popupContent.innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-red-500 text-6xl mb-4">⚠️</div>
                            <p class="text-red-600 text-lg font-semibold">Error loading order details</p>
                            <p class="text-gray-600">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                popupContent.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-500 text-6xl mb-4">⚠️</div>
                        <p class="text-red-600 text-lg font-semibold">Error loading order details</p>
                        <p class="text-gray-600">Please try again later.</p>
                    </div>
                `;
            });
    }
    
    // Close Order Popup
    function closeOrderPopup() {
        document.getElementById('orderPopup').classList.add('hidden');
        document.body.style.overflow = '';
        closePendingQuantityBreakdown();
        editMode = false;
        editingOrderId = null;
        currentOrderData = null;
    }
    
    // Toggle Edit Mode - UPDATED to use a single button
    function toggleEditMode() {
        editMode = !editMode;
        const actionBtn = document.getElementById('actionBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        
        if (editMode) {
            // Change to Save mode
            actionBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save
            `;
            actionBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 transition-colors shadow-sm';
            actionBtn.onclick = saveChanges;
            
            // Show Cancel button
            if (cancelBtn) {
                cancelBtn.classList.remove('hidden');
            }
            
            // Make fields editable
            makeFieldsEditable(true);
        } else {
            // Change back to Edit mode
            actionBtn.innerHTML = `
                <span id="editIcon">✏️</span>
                Edit Order
            `;
            actionBtn.className = 'px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition-colors';
            actionBtn.onclick = toggleEditMode;
            
            // Hide Cancel button
            if (cancelBtn) {
                cancelBtn.classList.add('hidden');
            }
            
            // Make fields readonly
            makeFieldsEditable(false);
            // Reset to original values
            closePendingQuantityBreakdown();
            resetFields();
        }
    }
    
    // Make fields editable or readonly
    function makeFieldsEditable(editable) {
        const quantityInputs = document.querySelectorAll('.qty-input');
        const priceInputs = document.querySelectorAll('.price-input');
        
        quantityInputs.forEach(input => {
            input.readOnly = true;
            input.classList.toggle('bg-white', editable);
            input.classList.toggle('bg-gray-50', !editable);
            input.classList.toggle('border-blue-300', editable);
            input.classList.toggle('border-gray-200', !editable);
            input.classList.toggle('cursor-pointer', editable);
            input.classList.toggle('cursor-not-allowed', !editable);
        });
        
        priceInputs.forEach(input => {
            input.readOnly = !editable;
            input.classList.toggle('bg-white', editable);
            input.classList.toggle('bg-gray-50', !editable);
            input.classList.toggle('border-blue-300', editable);
            input.classList.toggle('border-gray-200', !editable);
        });
        
        // Transaction number and request date stay server-controlled for location transfers.
        const salesInvoiceInput = document.getElementById('editSalesInvoice');
        const orderDateInput = document.getElementById('editOrderDate');
        
        if (salesInvoiceInput) {
            salesInvoiceInput.readOnly = true;
            salesInvoiceInput.classList.add('bg-gray-50', 'border-gray-200');
            salesInvoiceInput.classList.remove('bg-white', 'border-blue-300');
        }
        
        if (orderDateInput) {
            orderDateInput.readOnly = true;
            orderDateInput.classList.add('bg-gray-50', 'border-gray-200');
            orderDateInput.classList.remove('bg-white', 'border-blue-300');
        }
    }
    
    // Reset fields to original values
    function resetFields() {
        const rows = document.querySelectorAll('.editable-row');
        rows.forEach(row => {
            const originalQty = row.dataset.originalQty;
            const originalPrice = row.dataset.originalPrice;
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const lineTotal = row.querySelector('.line-total');
            
            if (qtyInput && originalQty) {
                qtyInput.value = parseFloat(originalQty).toFixed(3);
            }
            if (row.dataset.originalQuantityBreakdown) {
                row.dataset.quantityBreakdown = row.dataset.originalQuantityBreakdown;
            }
            if (priceInput && originalPrice) {
                priceInput.value = parseFloat(originalPrice).toFixed(2);
            }
            updateLineTotal(row);
        });
        
        // Reset sales invoice and order date
        const salesInvoiceInput = document.getElementById('editSalesInvoice');
        const orderDateInput = document.getElementById('editOrderDate');
        
        if (salesInvoiceInput && currentOrderData) {
            salesInvoiceInput.value = currentOrderData.order.sales_invoice || '';
        }
        if (orderDateInput && currentOrderData && currentOrderData.order.order_date) {
            // Format the date for the input
            const dateObj = new Date(currentOrderData.order.order_date);
            if (!isNaN(dateObj.getTime())) {
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                orderDateInput.value = `${year}-${month}-${day}`;
            }
        }
    }
    
    // Update line total for a row
    function updateLineTotal(row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const lineTotal = row.querySelector('.line-total');
        const totalAmountElement = document.getElementById('totalAmountDisplay');
        
        if (qtyInput && priceInput && lineTotal) {
            const qty = parseFloat(qtyInput.value) || 0;
            const currentStock = parseFloat(row.dataset.currentStock) || 0;
            const addedPrice = parseFloat(row.dataset.addedPrice) || 0;

            if (qty < MIN_PENDING_QUANTITY) {
                alert(`Quantity must be at least ${MIN_PENDING_QUANTITY.toFixed(2)}.`);
                qtyInput.value = parseFloat(row.dataset.originalQty || MIN_PENDING_QUANTITY).toFixed(3);
            }

            if (qty > currentStock + 0.0001) {
                alert(`Quantity cannot exceed current stock (${currentStock.toFixed(3)}).`);
                const fallbackQty = Math.min(parseFloat(row.dataset.originalQty || 0), currentStock);
                qtyInput.value = fallbackQty.toFixed(3);
            }

            const validQty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = (validQty * price) + addedPrice;
            lineTotal.textContent = `₱${total.toFixed(2)}`;
            lineTotal.dataset.value = total;
        }
        
        // Update total amount
        updateTotalAmount();
    }
    
    // Update total amount
    function updateTotalAmount() {
        const lineTotals = document.querySelectorAll('.line-total');
        let total = 0;
        lineTotals.forEach(el => {
            const value = parseFloat(el.dataset.value) || 0;
            total += value;
        });
        const totalAmountElement = document.getElementById('totalAmountDisplay');
        if (totalAmountElement) {
            totalAmountElement.textContent = `₱${total.toFixed(2)}`;
        }
    }
    
    function validateQuantityInput(input) {
        const row = input.closest('tr');
        const quantity = parseFloat(input.value);

        if (input.value === '' || Number.isNaN(quantity)) {
            return;
        }

        if (quantity < MIN_PENDING_QUANTITY) {
            alert(`Quantity must be at least ${MIN_PENDING_QUANTITY.toFixed(2)}.`);
            input.value = parseFloat(row.dataset.originalQty || MIN_PENDING_QUANTITY).toFixed(3);
        }

        updateLineTotal(row);
    }

    function hasPendingBreakdownValue(input) {
        return input.value.trim() !== '' && !Number.isNaN(parseFloat(input.value));
    }

    function makePendingBreakdownRow(value = '') {
        return `
            <div class="flex items-center gap-2 pending-breakdown-row">
                <input type="number" min="${MIN_PENDING_QUANTITY}" step="0.001" class="pending-breakdown-input flex-1 min-w-0 px-3 py-2 border-2 border-gray-300 rounded-none font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.000" value="${value}" data-had-value="${value !== '' ? 'true' : 'false'}">
                <button type="button" class="add-pending-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50" aria-label="Add breakdown line">+</button>
            </div>
        `;
    }

    function refreshPendingBreakdownControls() {
        const rows = Array.from(document.querySelectorAll('#pendingQuantityBreakdownRows .pending-breakdown-row'));

        rows.forEach((row, index) => {
            const button = row.querySelector('button');
            const isLastRow = index === rows.length - 1;
            button.className = isLastRow
                ? 'add-pending-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50'
                : 'remove-pending-breakdown-row w-8 h-8 border-2 border-red-500 text-red-600 rounded-full text-xl leading-none font-bold hover:bg-red-50';
            button.textContent = isLastRow ? '+' : 'x';
            button.setAttribute('aria-label', isLastRow ? 'Add breakdown line' : 'Remove breakdown line');
        });
    }

    function updatePendingBreakdownTotal() {
        const total = Array.from(document.querySelectorAll('#pendingQuantityBreakdownRows .pending-breakdown-input')).reduce((sum, input) => {
            return sum + (parseFloat(input.value) || 0);
        }, 0);
        document.getElementById('pendingQuantityBreakdownTotal').textContent = total.toFixed(3);
        return total;
    }

    function addPendingBreakdownRow(value = '', focusNewRow = true) {
        const rowsContainer = document.getElementById('pendingQuantityBreakdownRows');
        rowsContainer.insertAdjacentHTML('beforeend', makePendingBreakdownRow(value));
        refreshPendingBreakdownControls();
        updatePendingBreakdownTotal();

        if (focusNewRow) {
            rowsContainer.lastElementChild.querySelector('.pending-breakdown-input').focus();
        }
    }

    function getPendingBreakdownValues() {
        return Array.from(document.querySelectorAll('#pendingQuantityBreakdownRows .pending-breakdown-input'))
            .map(input => parseFloat(input.value))
            .filter(value => !Number.isNaN(value) && value > 0);
    }

    function openPendingQuantityBreakdown(row, anchor) {
        if (!editMode) {
            return;
        }

        activePendingBreakdownRow = row;
        const panel = document.getElementById('pendingQuantityBreakdownPanel');
        const rowsContainer = document.getElementById('pendingQuantityBreakdownRows');
        const values = row.dataset.quantityBreakdown
            ? JSON.parse(row.dataset.quantityBreakdown)
            : [parseFloat(row.querySelector('.qty-input').value) || ''];
        rowsContainer.innerHTML = '';
        values.forEach(value => addPendingBreakdownRow(value, false));
        panel.classList.remove('hidden');
        panel.style.visibility = 'hidden';

        const anchorRect = anchor.getBoundingClientRect();
        const panelWidth = panel.offsetWidth || 160;
        const panelHeight = panel.offsetHeight;
        const gap = 12;
        const viewportPadding = 8;
        const verticalOffset = 200;
        const maxTop = window.innerHeight - panelHeight - viewportPadding;
        const left = anchorRect.left - panelWidth - gap;
        const top = Math.min(anchorRect.top - verticalOffset, maxTop);

        panel.style.left = `${Math.max(viewportPadding, left)}px`;
        panel.style.top = `${Math.max(viewportPadding, top)}px`;
        panel.style.visibility = '';
        refreshPendingBreakdownControls();
        updatePendingBreakdownTotal();

        const firstInput = panel.querySelector('.pending-breakdown-input');
        if (firstInput) {
            firstInput.focus();
        }
    }

    function closePendingQuantityBreakdown() {
        document.getElementById('pendingQuantityBreakdownPanel').classList.add('hidden');
        activePendingBreakdownRow = null;
    }

    function bindPendingQuantityBreakdownTriggers() {
        document.querySelectorAll('#popupContent .qty-input').forEach(input => {
            input.addEventListener('click', function(e) {
                e.preventDefault();
                openPendingQuantityBreakdown(this.closest('tr'), this);
            });
        });
    }

    document.getElementById('pendingQuantityBreakdownRows').addEventListener('click', function(e) {
        if (e.target.closest('.add-pending-breakdown-row')) {
            const currentInput = e.target.closest('.pending-breakdown-row')?.querySelector('.pending-breakdown-input');
            if (currentInput && !hasPendingBreakdownValue(currentInput)) {
                return;
            }

            addPendingBreakdownRow();
            return;
        }

        const removeButton = e.target.closest('.remove-pending-breakdown-row');
        if (removeButton) {
            removeButton.closest('.pending-breakdown-row').remove();
            refreshPendingBreakdownControls();
            updatePendingBreakdownTotal();
        }
    });

    document.getElementById('pendingQuantityBreakdownRows').addEventListener('input', function(e) {
        if (e.target.classList.contains('pending-breakdown-input')) {
            if (hasPendingBreakdownValue(e.target)) {
                e.target.dataset.hadValue = 'true';
            }
            updatePendingBreakdownTotal();
        }
    });

    document.getElementById('pendingQuantityBreakdownRows').addEventListener('focusout', function(e) {
        if (!e.target.classList.contains('pending-breakdown-input')) {
            return;
        }

        if (e.target.dataset.hadValue !== 'true' || e.target.value.trim() !== '') {
            return;
        }

        e.target.closest('.pending-breakdown-row').remove();
        if (!document.querySelector('#pendingQuantityBreakdownRows .pending-breakdown-row')) {
            addPendingBreakdownRow('', false);
        }
        refreshPendingBreakdownControls();
        updatePendingBreakdownTotal();
    });

    document.getElementById('pendingQuantityBreakdownRows').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('pending-breakdown-input')) {
            e.preventDefault();

            if (!e.target.closest('.pending-breakdown-row')?.querySelector('.add-pending-breakdown-row')) {
                return;
            }

            if (!hasPendingBreakdownValue(e.target)) {
                return;
            }

            addPendingBreakdownRow();
        }
    });

    document.getElementById('applyPendingQuantityBreakdownBtn').addEventListener('click', function() {
        if (!activePendingBreakdownRow) {
            closePendingQuantityBreakdown();
            return;
        }

        const total = updatePendingBreakdownTotal();
        const currentStock = parseFloat(activePendingBreakdownRow.dataset.currentStock) || 0;

        if (total < MIN_PENDING_QUANTITY) {
            alert(`Quantity must be at least ${MIN_PENDING_QUANTITY.toFixed(2)}.`);
            return;
        }

        if (total > currentStock + 0.0001) {
            alert(`Quantity cannot exceed current stock (${currentStock.toFixed(3)}).`);
            return;
        }

        activePendingBreakdownRow.dataset.quantityBreakdown = JSON.stringify(getPendingBreakdownValues());
        activePendingBreakdownRow.querySelector('.qty-input').value = total.toFixed(3);
        updateLineTotal(activePendingBreakdownRow);
        closePendingQuantityBreakdown();
    });

    // Save changes - UPDATED to close popup and reload
    function saveChanges() {
        if (!editingOrderId || !currentOrderData) {
            alert('No order data available to save.');
            return;
        }
        
        const rows = document.querySelectorAll('.editable-row');
        const items = [];
        let stockValidationFailed = false;
        
        rows.forEach(row => {
            const menuId = row.dataset.menuId;
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const unitSpan = row.querySelector('.unit-display');
            
            if (menuId && qtyInput && priceInput) {
                const quantity = parseFloat(qtyInput.value) || 0;
                const currentStock = parseFloat(row.dataset.currentStock) || 0;
                const addedPrice = parseFloat(row.dataset.addedPrice) || 0;

                if (quantity > currentStock + 0.0001) {
                    alert(`Quantity cannot exceed current stock (${currentStock.toFixed(3)}).`);
                    qtyInput.focus();
                    stockValidationFailed = true;
                    return;
                }

                if (quantity < MIN_PENDING_QUANTITY) {
                    alert(`Quantity must be at least ${MIN_PENDING_QUANTITY.toFixed(2)}.`);
                    qtyInput.focus();
                    stockValidationFailed = true;
                    return;
                }

                items.push({
                    id: parseInt(row.dataset.requestId),
                    menu_id: parseInt(menuId),
                    quantity: quantity,
                    quantity_breakdown: row.dataset.quantityBreakdown
                        ? JSON.parse(row.dataset.quantityBreakdown)
                        : [quantity],
                    price: parseFloat(priceInput.value) || 0,
                    added_price: addedPrice,
                    unit: unitSpan ? unitSpan.textContent.replace(/['"]/g, '').trim() : ''
                });
            }
        });

        if (stockValidationFailed) {
            return;
        }
        
        if (items.length === 0) {
            alert('No items to save.');
            return;
        }
        
        // Get values from the inputs
        const salesInvoiceInput = document.getElementById('editSalesInvoice');
        const orderDateInput = document.getElementById('editOrderDate');
        
        // Build data object - use existing order date if not changed
        const orderDate = orderDateInput ? orderDateInput.value : currentOrderData.order.order_date;
        const salesInvoice = salesInvoiceInput ? salesInvoiceInput.value : currentOrderData.order.sales_invoice;
        
        const data = {
            sales_invoice: salesInvoice || '',
            order_date: orderDate || '',
            items: items
        };
        
        // Validate required fields
        if (!data.sales_invoice || data.sales_invoice.trim() === '') {
            alert('Please enter transaction number.');
            return;
        }
        if (!data.order_date) {
            alert('Please select order date.');
            return;
        }
        
        // Show loading state on the button
        const actionBtn = document.getElementById('actionBtn');
        const originalText = actionBtn ? actionBtn.innerHTML : '';
        if (actionBtn) {
            actionBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Saving...';
            actionBtn.disabled = true;
        }
        
        // Use the correct route with JSON endpoint
        const url = `${BASE_URL}/update/${editingOrderId}/json`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            }
            // If not JSON, return text and try to parse
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            if (data.success) {
                alert('Changes saved successfully!');
                
                // Close the popup first
                closeOrderPopup();
                
                // Then reload the page to update the table
                location.reload();
            } else {
                alert('Error saving changes: ' + (data.message || 'Unknown error'));
                if (actionBtn) {
                    actionBtn.innerHTML = originalText;
                    actionBtn.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving changes: ' + error.message);
            if (actionBtn) {
                actionBtn.innerHTML = originalText;
                actionBtn.disabled = false;
            }
        });
    }
    
    // Cancel edit
    function cancelEdit() {
        if (confirm('Are you sure you want to cancel? Your changes will be lost.')) {
            // Reset edit mode
            editMode = true; // Set to true so toggleEditMode will switch back
            toggleEditMode();
        }
    }
    
    // Render Popup Content
    function renderPopupContent(order, items) {
        const popupContent = document.getElementById('popupContent');
        
        let statusBadge = '';
        if (order.status === 'pending') {
            statusBadge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800"><span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>Pending Approval</span>';
        } else if (order.status === 'approved') {
            statusBadge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800"><span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>Approved</span>';
        } else {
            statusBadge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800"><span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>Rejected</span>';
        }
        
        let actionButtons = '';
        if (order.status === 'pending' && order.can_manage) {
            actionButtons = `
                <div class="flex flex-wrap gap-2">
                    <button onclick="approveOrder(${order.id})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve
                    </button>
                    <button onclick="showRejectModal(${order.id})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject
                    </button>
                </div>
            `;
        }
        
        let itemsHtml = '';
        if (items && items.length > 0) {
            items.forEach((item, index) => {
                const originalQty = parseFloat(item.quantity).toFixed(3);
                const originalPrice = parseFloat(item.price).toFixed(2);
                const addedPrice = parseFloat(item.added_price || 0);
                const lineTotal = parseFloat(item.line_total ?? ((parseFloat(item.quantity) * parseFloat(item.price)) + addedPrice));
                const currentStock = parseFloat(item.current_quantity || 0);
                const quantityBreakdown = item.quantity_breakdown && item.quantity_breakdown.length
                    ? item.quantity_breakdown
                    : [originalQty];
                const quantityBreakdownJson = JSON.stringify(quantityBreakdown);
                
                // Clean up the unit value to remove quotes
                const cleanUnit = item.unit ? item.unit.replace(/['"]/g, '').trim() : '';
                
                itemsHtml += `
                    <tr class="editable-row hover:bg-gray-50 transition-colors" 
                        data-request-id="${item.id}" 
                        data-menu-id="${item.menu_id}" 
                        data-original-qty="${originalQty}" 
                        data-original-price="${originalPrice}"
                        data-added-price="${addedPrice}"
                        data-current-stock="${currentStock}"
                        data-original-quantity-breakdown='${quantityBreakdownJson}'
                        data-quantity-breakdown='${quantityBreakdownJson}'>
                        <td class="px-4 py-3 text-sm text-gray-500">${index + 1}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.brand_name || 'N/A'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.menu_name}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="number" 
                                        class="qty-input w-24 px-2 py-1 text-sm border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 cursor-pointer" 
                                        value="${originalQty}" 
                                        step="0.001" 
                                        min="${MIN_PENDING_QUANTITY}" 
                                        readonly>
                                    ${order.status === 'pending' && order.can_manage ? `
                                        <span class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </span>
                                    ` : ''}
                                </div>
                                <span class="text-xs text-gray-400">${cleanUnit}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="unit-display">${cleanUnit || 'N/A'}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-1">
                                <span class="text-gray-500 text-xs">₱</span>
                                <div class="relative">
                                    <input type="number" 
                                        class="price-input w-24 px-2 py-1 text-sm border border-gray-200 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" 
                                        value="${originalPrice}" 
                                        step="0.01" 
                                        min="0" 
                                        readonly 
                                        onchange="updateLineTotal(this.closest('tr'))">
                                    ${order.status === 'pending' && order.can_manage ? `
                                        <span class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </span>
                                    ` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">${parseFloat(item.current_quantity || 0).toFixed(3)}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right line-total" data-value="${lineTotal}">₱${lineTotal.toFixed(2)}</td>
                    </tr>
                `;
            });
        } else {
            itemsHtml = `
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">No items found in this order.</td>
                </tr>
            `;
        }
        
        // Determine if we should show edit controls
        const canEdit = order.status === 'pending' && !!order.can_manage;
        
        // Format the date for the input field (YYYY-MM-DD)
        let dateForInput = '';
        let orderDateDisplay = 'N/A';
        const itemsTotal = items && items.length > 0
            ? items.reduce((sum, item) => sum + parseFloat(item.line_total || 0), 0)
            : parseFloat(order.total_amount || 0);
        
        if (order.order_date) {
            try {
                // Parse the date - handle different formats
                let dateObj;
                if (typeof order.order_date === 'string') {
                    // If it's already a string, try to create a Date object
                    dateObj = new Date(order.order_date);
                } else {
                    dateObj = new Date(order.order_date);
                }
                
                // Check if date is valid
                if (!isNaN(dateObj.getTime())) {
                    // Format for input (YYYY-MM-DD)
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    dateForInput = `${year}-${month}-${day}`;
                    
                    // Format for display (Month DD, YYYY)
                    orderDateDisplay = dateObj.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                } else {
                    console.warn('Invalid date:', order.order_date);
                    // Try to extract date from string if possible
                    if (typeof order.order_date === 'string' && order.order_date.includes('-')) {
                        const parts = order.order_date.split('-');
                        if (parts.length === 3) {
                            dateForInput = order.order_date;
                            orderDateDisplay = new Date(order.order_date + 'T00:00:00').toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            });
                        }
                    }
                }
            } catch (e) {
                console.error('Error parsing date:', e);
            }
        }
        
        popupContent.innerHTML = `
            <div class="mb-6 border-b pb-4">
                <div class="flex justify-between items-start flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-2xl font-bold text-gray-900">Location Transfer ${order.sales_invoice || ''}</h2>
                            ${statusBadge}
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${canEdit ? `
                            <button id="actionBtn" onclick="toggleEditMode()" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition-colors">
                                <span id="editIcon">✏️</span>
                                Edit
                            </button>
                            <button id="cancelBtn" onclick="cancelEdit()" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition-colors hidden">
                                <svg class="w-4 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </button>
                        ` : ''}
                        ${actionButtons}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Deliver From</p>
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-gray-900">${order.sender_branch_name || 'N/A'}</p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Deliver To</p>
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-gray-900">${order.branch_name || 'N/A'}</p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Transaction #</p>
                    <input id="editSalesInvoice" type="text" value="${order.sales_invoice || ''}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" 
                        readonly>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Request Date</p>
                    <div class="relative">
                        <input id="editOrderDate" type="date" value="${dateForInput}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" 
                            readonly>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">${orderDateDisplay}</span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Submitted By</p>
                    <p class="font-semibold text-gray-900">${order.user_name || 'Unknown'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Submitted At</p>
                    <p class="font-semibold text-gray-900">${new Date(order.created_at).toLocaleString()}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Amount</p>
                    <p id="totalAmountDisplay" class="font-semibold text-2xl text-green-600">₱${itemsTotal.toFixed(2)}</p>
                </div>
                ${order.status === 'approved' ? `
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Approved By</p>
                        <p class="font-semibold text-gray-900">${order.approved_by_name || 'Unknown'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Approved At</p>
                        <p class="font-semibold text-gray-900">${order.approved_at ? new Date(order.approved_at).toLocaleString() : 'N/A'}</p>
                    </div>
                ` : ''}
                ${order.status === 'rejected' ? `
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Rejected By</p>
                        <p class="font-semibold text-gray-900">${order.rejected_by_name || 'Unknown'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Rejected At</p>
                        <p class="font-semibold text-gray-900">${order.rejected_at ? new Date(order.rejected_at).toLocaleString() : 'N/A'}</p>
                    </div>
                ` : ''}
                ${order.updated_by_name ? `
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Last Updated By</p>
                        <p class="font-semibold text-gray-900">${order.updated_by_name}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Last Updated At</p>
                        <p class="font-semibold text-gray-900">${order.last_updated_at ? new Date(order.last_updated_at).toLocaleString() : 'N/A'}</p>
                    </div>
                ` : ''}
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Transfer Items
                    ${canEdit ? `
                        <span class="text-sm text-gray-400 font-normal ml-2">(Click Edit to modify quantities and prices)</span>
                    ` : ''}
                </h3>
                <div class="overflow-x-auto shadow-sm border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Brand</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Current Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">${itemsHtml}</tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-right font-bold text-gray-900">Total Amount</td>
                                <td class="px-4 py-3 text-right font-bold text-green-600">₱${parseFloat(order.total_amount).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            ${order.remarks ? `
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm font-medium text-gray-500">Remarks</p>
                    <p class="text-gray-700 mt-1">${order.remarks}</p>
                </div>
            ` : ''}
        `;
        
        // Enable/disable edit mode controls based on status
        if (canEdit) {
            // Set initial state for edit controls
            const actionBtn = document.getElementById('actionBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            
            if (actionBtn) {
                actionBtn.classList.add('bg-gray-100', 'text-gray-700');
            }
            if (cancelBtn) {
                cancelBtn.classList.add('hidden');
            }
        }

        bindPendingQuantityBreakdownTriggers();
    }
    
    // Show Reject Modal
    function showRejectModal(id) {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectForm').action = `{{ url('/inventory/location-transfer/pending/reject') }}/${id}`;
        document.body.style.overflow = 'hidden';
        document.getElementById('rejection_reason').value = '';
    }
    
    // Close Reject Modal
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('filterDropdown');
        const filterContainer = event.target.closest('.relative');
        
        if (!filterContainer && !dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            isDropdownOpen = false;
        }
    });
    
    // Close popup on click outside
    document.getElementById('orderPopup').addEventListener('click', function(e) {
        if (e.target === this) {
            closeOrderPopup();
        }
    });
    
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
    
    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOrderPopup();
            closeRejectModal();
            const dropdown = document.getElementById('filterDropdown');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
                isDropdownOpen = false;
            }
        }
    });
    
    // Initialize filter on page load
    document.addEventListener('DOMContentLoaded', function() {
        filterByStatus('All');
        updateFilterCounts();
        
        const filterOptions = document.getElementById('filterOptions');
        if (filterOptions) {
            filterOptions.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
</script>
    <style>
        .order-row {
            transition: all 0.15s ease;
        }
        .order-row:hover {
            background-color: #eff6ff !important;
        }
        #orderPopup, #rejectModal {
            animation: fadeIn 0.2s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        /* Custom scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        /* Dropdown animation */
        #filterDropdown {
            animation: slideDown 0.15s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Radio button styling */
        input[type="radio"] {
            accent-color: #3b82f6;
            cursor: pointer;
        }
        #ordersTable thead th {
            background: #f9fafb;
        }
        /* Button hover effects */
        .transition-colors {
            transition: all 0.2s ease;
        }
        /* Disable text selection on filter options */
        #filterOptions label {
            user-select: none;
            cursor: pointer;
        }
        /* Make radio buttons more clickable */
        #filterOptions label input[type="radio"] {
            cursor: pointer;
            flex-shrink: 0;
            pointer-events: auto;
        }
        #filterOptions label span {
            cursor: pointer;
            pointer-events: none;
        }
        /* Ensure dropdown stays interactive */
        #filterDropdown {
            pointer-events: auto;
        }
        #filterDropdown * {
            pointer-events: auto;
        }
        /* Ensure buttons are clickable */
        .view-btn, .approve-btn, .reject-btn {
            position: relative;
            z-index: 2;
            cursor: pointer;
            pointer-events: auto;
        }
        /* Prevent button clicks from triggering row events */
        .order-row td .flex {
            position: relative;
            z-index: 2;
        }
        /* Make sure buttons are always on top */
        .order-row td:last-child {
            position: relative;
            z-index: 2;
        }
        /* Empty state animation */
        #emptyStateRow {
            animation: fadeIn 0.3s ease;
        }
        #emptyStateRow .flex {
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Ensure empty state row is properly displayed */
        #emptyStateRow.hidden {
            display: none !important;
        }
        #emptyStateRow:not(.hidden) {
            display: table-row !important;
        }
    </style>
</x-app-layout>
