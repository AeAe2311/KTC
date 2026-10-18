<x-app-layout>
    @section('page_title', 'TRANSFORM DETAILS')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-start">
                    <div>
                        <h1 class="text-xl font-semibold">{{ $transform->transform_no }}</h1>
                        <p class="text-sm text-gray-500">{{ $transform->date }}</p>
                        @if($transform->status === 'PENDING')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                Pending Approval
                            </span>
                        @elseif($transform->status === 'COMPLETED')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                Completed
                            </span>
                        @elseif($transform->status === 'REJECTED')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                Rejected
                            </span>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @if($transform->status === 'PENDING')
                            <!-- Approve Button - triggers modal -->
                            <button type="button" onclick="openApproveModal()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve
                            </button>
                            <form action="{{ route('transform.reject') }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this transform?')">
                                @csrf
                                <input type="hidden" name="slug" value="{{ $transform->slug }}">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Reject
                                </button>
                            </form>
                            {{-- <a href="{{ route('transform.edit', $transform->slug) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Update
                            </a> --}}
                        @endif
                        @if($transform->status === 'REJECTED')
                            {{-- <a href="{{ route('transform.edit', $transform->slug) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Update
                            </a> --}}
                        @endif
                        <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </button>
                        <a href="{{ route('transform.index') }}" class="inline-flex items-center px-4 py-2 text-gray-900 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            Back
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('message'))
                    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                    </div>
                @endif

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                        <h2 class="font-semibold mb-3">Source</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <div><span class="font-semibold">Item:</span> {{ optional($transform->source)->name ?? 'Deleted item' }}{{ optional($transform->source)->brand ? ' - ' . $transform->source->brand->name : '' }}</div>
                            <div><span class="font-semibold">Quantity:</span> {{ number_format($transform->source_quantity, 2) }} {{ $transform->source_unit }}</div>
                            <div><span class="font-semibold">Status:</span> 
                                <span class="font-medium 
                                    @if($transform->status === 'COMPLETED') text-green-600 
                                    @elseif($transform->status === 'PENDING') text-yellow-600 
                                    @elseif($transform->status === 'REJECTED') text-red-600 
                                    @endif">
                                    {{ $transform->status }}
                                </span>
                            </div>
                            <div><span class="font-semibold">Submitted By:</span> {{ $transform->createdBy->firstname ?? '' }} {{ $transform->createdBy->lastname ?? $transform->createdBy->username ?? 'N/A' }}</div>
                            <div><span class="font-semibold">Approved By:</span> {{ optional($transform->approvedBy)->firstname ?? '' }} {{ optional($transform->approvedBy)->lastname ?? optional($transform->approvedBy)->username ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                        <h2 class="font-semibold mb-3">Waste / Scrap</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <div><span class="font-semibold">Quantity:</span> {{ $transform->waste_quantity ? number_format($transform->waste_quantity, 2) : '-' }} {{ $transform->waste_unit ?? '' }}</div>
                            <div><span class="font-semibold">Description:</span> {{ $transform->waste_description ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="font-semibold mb-3">Transformed Output Items</h2>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Item</th>
                                    <th class="px-4 py-3">Quantity</th>
                                    <th class="px-4 py-3">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transform->items as $item)
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->menu->name ?? $item->inventory->name ?? 'Deleted item' }}{{ $item->menu ? ' - ' . optional($item->menu->brand)->name : '' }}</td>
                                        <td class="px-4 py-3">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-4 py-3">{{ $item->unit }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-center text-gray-500">No items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <h2 class="font-semibold mb-3">Notes</h2>
                    <p class="text-sm text-gray-700">{{ $transform->remarks ?? 'No remarks provided.' }}</p>
                </div>

                @if($transform->status === 'REJECTED' && $transform->rejection_reason)
                <div class="mt-6 rounded-lg border border-red-200 p-4 bg-red-50">
                    <h2 class="font-semibold mb-3 text-red-800">Rejection Reason</h2>
                    <p class="text-sm text-red-700">{{ $transform->rejection_reason }}</p>
                </div>
                @endif
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
                        <div class="mt-1 text-sm text-gray-600">
                            <p><span class="font-semibold">Transform No:</span> {{ $transform->transform_no }}</p>
                            <p><span class="font-semibold">Source Item:</span> {{ optional($transform->source)->name ?? 'Deleted item' }}</p>
                            <p><span class="font-semibold">Output Items:</span> {{ $transform->items->count() }}</p>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex justify-end px-6 py-5 border-t border-gray-200 bg-white rounded-b-xl">

                    <div class="flex items-center gap-3">

                        <button
                            type="button"
                            onclick="closeApproveModal()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition-all duration-200">

                            <!-- X Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>

                            Cancel
                        </button>

                        <form action="{{ route('transform.approve') }}" method="POST">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $transform->slug }}">

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
            // Function to open the approval modal
            window.openApproveModal = function() {
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
                    const alerts = document.querySelectorAll('[role="alert"]');
                    alerts.forEach(function(alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    });
                }, 5000);
            });

            console.log('Modal functions initialized successfully');
        })();
    </script>
</x-app-layout>