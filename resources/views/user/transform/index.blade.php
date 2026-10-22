<x-app-layout>
    @section('page_title', 'TRANSFORMS')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                @if (session('message'))
                    <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex items-center justify-between px-3">
                        <div class="flex items-center gap-2 text-emerald-800 font-semibold">
                            <i class="uil uil-cloud-check text-2xl"></i>
                            {{ session('message') }}
                        </div>
                        <button class="notifButton"><i class="uil uil-times text-2xl text-emerald-800"></i></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-96 h-12 bg-red-200 rounded-lg z-50 shadow-md shadow-red-800 flex items-center justify-between px-3">
                        <div class="flex items-center gap-2 text-red-800 font-semibold">
                            <i class="uil uil-exclamation-octagon text-2xl"></i>
                            {{ session('error') }}
                        </div>
                        <button class="notifButton"><i class="uil uil-times text-2xl text-red-800"></i></button>
                    </div>
                @endif

                <div class="flex flex-col md:flex-row md:justify-between gap-3 mb-4">
                    <h1 class="text-xl font-semibold">Transform Records</h1>
                    <a href="{{ route('transform.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                        <i class="uil uil-exchange-alt mr-2"></i>
                        New Transform
                    </a>
                </div>

                <form method="GET" action="{{ route('transform.index') }}" class="grid gap-3 md:grid-cols-4 mb-4">
                    <div>
                        <label for="search" class="block mb-1 text-xs font-semibold text-gray-600">Search</label>
                        <input id="search" name="search" type="text" value="{{ request('search') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Transform no, source, status, user" />
                    </div>
                    <div>
                        <label for="date_from" class="block mb-1 text-xs font-semibold text-gray-600">Date from</label>
                        <input id="date_from" name="date_from" type="datetime-local" value="{{ request('date_from', \Carbon\Carbon::now()->startOfDay()->format('Y-m-d\\TH:i')) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label for="date_to" class="block mb-1 text-xs font-semibold text-gray-600">Date to</label>
                        <input id="date_to" name="date_to" type="datetime-local" value="{{ request('date_to', \Carbon\Carbon::now()->endOfDay()->format('Y-m-d\\TH:i')) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">Filter</button>
                        <a href="{{ route('transform.index') }}" class="w-full px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 text-center">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto shadow rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">Transform No.</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Source Item</th>
                                <th class="px-4 py-3">Source Qty</th>
                                <th class="px-4 py-3">Outputs</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Submitted By</th>
                                <th class="px-4 py-3">Approved/Rejected By</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($transforms as $transform)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $transform->transform_no }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($transform->date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ optional($transform->source)->name ?? 'Deleted item' }}{{ optional($transform->source)->brand ? ' - ' . $transform->source->brand->name : '' }}</td>
                                    <td class="px-4 py-3">{{ number_format($transform->source_quantity, 2) }} {{ $transform->source_unit }}</td>
                                    <td class="px-4 py-3">
                                        @if($transform->items->count() > 0)
                                            <ul class="list-disc list-inside">
                                                @foreach($transform->items as $item)
                                                    <li>
                                                        {{ optional($item->menu)->name ?? 'Deleted Item' }}
                                                        - {{ number_format($item->quantity, 2) }} {{ $item->unit }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-400">No items</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($transform->status === 'PENDING')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                PENDING
                                            </span>
                                        @elseif($transform->status === 'COMPLETED')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                COMPLETED
                                            </span>
                                        @elseif($transform->status === 'REJECTED')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                REJECTED
                                            </span>
                                        @else
                                            {{ $transform->status }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $transform->createdBy->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ optional($transform->approvedBy)->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-600">
                                        @if ($transform->status === 'PENDING')
                                            <button type="button" onclick="openApproveModal('{{ $transform->slug }}', '{{ $transform->transform_no }}', '{{ addslashes(optional($transform->source)->name ?? 'Deleted item') }}', {{ $transform->items->count() }})" class="text-emerald-600 hover:underline font-medium">
                                                Approve
                                            </button>
                                            <span class="px-1">|</span>
                                        @endif
                                        <a href="{{ route('transform.show', $transform->slug) }}" class="text-blue-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">No transform records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-gray-600">
                        Page {{ $transforms->currentPage() }} of {{ $transforms->lastPage() }}
                        @if ($transforms->total() > 0)
                            · {{ $transforms->total() }} record{{ $transforms->total() === 1 ? '' : 's' }}
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($transforms->previousPageUrl())
                            <a href="{{ $transforms->previousPageUrl() }}" class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Prev</a>
                        @else
                            <span class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">Prev</span>
                        @endif
                        <span class="px-3 py-2 text-sm font-semibold text-gray-900 bg-gray-100 border border-gray-200 rounded-lg">{{ $transforms->currentPage() }}</span>
                        @if ($transforms->nextPageUrl())
                            <a href="{{ $transforms->nextPageUrl() }}" class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Next</a>
                        @else
                            <span class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Confirmation Modal - Centered with rgba(0, 0, 0, 0.45) background -->
    <div id="approveModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Background overlay with rgba(0, 0, 0, 0.45) -->
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.45);" onclick="closeApproveModal()"></div>

            <!-- Modal panel - centered -->
            <div class="relative z-10 w-full max-w-md overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex items-start">
                        <!-- Warning Icon -->
                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                                Confirm Approval
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to approve this transform?
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Note -->
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Important Note</h3>
                                <div class="mt-1 text-sm text-yellow-700">
                                    <p>This action <strong>cannot be undone</strong>. Once approved:</p>
                                    <ul class="pl-5 mt-1 list-disc">
                                        <li>Output quantities will be added to inventory</li>
                                        <li>Waste will be recorded</li>
                                        <li>Source items will be deducted</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transform Details -->
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700">Transform Details:</p>
                        <div class="mt-1 text-sm text-gray-600" id="transformDetails">
                            <p><span class="font-semibold">Transform No:</span> <span id="modalTransformNo">-</span></p>
                            <p><span class="font-semibold">Source Item:</span> <span id="modalSourceItem">-</span></p>
                            <p><span class="font-semibold">Output Items:</span> <span id="modalOutputCount">-</span></p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end px-6 py-5 border-t border-gray-200 bg-white rounded-b-xl">

                    <div class="flex items-center gap-3">

                        <button
                            type="button"
                            onclick="closeApproveModal()"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Cancel
                        </button>

                        <form id="approveForm" action="{{ route('transform.approve') }}" method="POST">
                            @csrf
                            <input type="hidden" name="slug" id="approveSlug">

                            <button
                                type="submit"
                                class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-md hover:shadow-lg transition-all duration-200">
                                ✓ Approve
                            </button>
                        </form>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Define functions immediately when the page loads
        (function() {
            // Function to open the approval modal with specific transform data
            window.openApproveModal = function(slug, transformNo, sourceItem, outputCount) {
                // Update modal with transform details
                document.getElementById('modalTransformNo').textContent = transformNo || '-';
                document.getElementById('modalSourceItem').textContent = sourceItem || '-';
                document.getElementById('modalOutputCount').textContent = outputCount || '0';
                document.getElementById('approveSlug').value = slug || '';

                // Show the modal
                const modal = document.getElementById('approveModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    console.error('Modal element not found');
                }
            };

            // Function to close the approval modal
            window.closeApproveModal = function() {
                const modal = document.getElementById('approveModal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            };

            // Close modal on ESC key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modal = document.getElementById('approveModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        window.closeApproveModal();
                    }
                }
            });

            // Close modal when clicking on the overlay background
            document.addEventListener('click', function(event) {
                const modal = document.getElementById('approveModal');
                if (modal && !modal.classList.contains('hidden')) {
                    // Check if the click is on the background overlay
                    const overlay = modal.querySelector('.fixed.inset-0');
                    if (event.target === overlay) {
                        window.closeApproveModal();
                    }
                }
            });

            // Auto-hide alert messages after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const alerts = document.querySelectorAll('.notif');
                    alerts.forEach(function(alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    });
                }, 5000);

                // Notification close button functionality
                document.querySelectorAll('.notifButton').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const notif = this.closest('.notif');
                        if (notif) {
                            notif.style.transition = 'opacity 0.5s';
                            notif.style.opacity = '0';
                            setTimeout(function() {
                                notif.remove();
                            }, 500);
                        }
                    });
                });
            });

            console.log('Modal functions initialized successfully');
        })();
    </script>
</x-app-layout>