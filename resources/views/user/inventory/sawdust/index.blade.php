<x-app-layout>
    @section('page_title', 'SAWDUST')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="w-full p-2">
            @if (session('success'))
                <script>
                    window.__serverToast = window.__serverToast || [];
                    window.__serverToast.push({
                        type: 'success',
                        message: {!! json_encode(session('success')) !!}
                    });
                </script>
            @endif

            @if ($errors->any())
                <script>
                    window.__serverToast = window.__serverToast || [];
                    @foreach ($errors->all() as $error)
                        window.__serverToast.push({
                            type: 'error',
                            message: {!! json_encode($error) !!}
                        });
                    @endforeach
                </script>
            @endif

            <div class="mb-6 overflow-hidden rounded-lg bg-white p-4 shadow-md">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Sawdust Items</h1>
                        <p class="text-sm text-gray-600">Current quantity for sawdust menu items in your store.</p>
                    </div>
                    <button type="button" id="openAddModalBtn"
                        class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                        ADD QUANTITY
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                            <tr>
                                <th scope="col" class="px-4 py-3">Item</th>
                                <th scope="col" class="px-4 py-3">Current Quantity</th>
                                <th scope="col" class="px-4 py-3">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($menus as $menu)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $menu->name }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $menu->current_quantity, 3) }}</td>
                                    <td class="px-4 py-3">{{ $menu->unit ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                                        No active sawdust menu items were found for your store.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white p-4 shadow-md">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Quantity Addition Logs</h2>
                    <p class="text-sm text-gray-600">When stock was added, who added it, and how much.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                            <tr>
                                <th scope="col" class="px-4 py-3">Date and Time</th>
                                <th scope="col" class="px-4 py-3">Item</th>
                                <th scope="col" class="px-4 py-3">Added By</th>
                                <th scope="col" class="px-4 py-3">Quantity Added</th>
                                <th scope="col" class="px-4 py-3">Unit</th>
                                <th scope="col" class="px-4 py-3">Quantity After</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr class="border-b">
                                    <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at }}</td>
                                    <td class="px-4 py-3">{{ $log->item_name }}</td>
                                    <td class="px-4 py-3">{{ $log->user_name }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $log->quantity_added, 3) }}</td>
                                    <td class="px-4 py-3">{{ $log->unit ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $log->quantity_after, 3) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        No sawdust quantity additions have been logged yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Compact Add Quantity Modal --}}
    <div
        id="addQuantityModal"
        class="fixed inset-0 z-[9999] hidden overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true">

        {{-- Dark Blurred Backdrop --}}
        <div
            id="addQuantityBackdrop"
            style="
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.62);
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
            ">
        </div>

        {{-- Modal container --}}
        <div class="relative flex min-h-full items-center justify-center p-3">
            <div class="relative my-4 w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <div>
                        <h3 id="modal-title" class="text-base font-bold text-gray-900">
                            Add Sawdust Quantity
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Record additional inventory stock.
                        </p>
                    </div>

                    <button type="button" id="closeAddModalBtn"
                        aria-label="Close modal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('sawdust.store') }}" method="POST"
                    id="addQuantityForm">
                    @csrf

                    <div class="space-y-3 p-4">

                        @if ($menus->isEmpty())
                            <div class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">
                                No active sawdust items are available for your store.
                            </div>
                        @else
                            {{-- Item dropdown: choose which sawdust item --}}
                            <div>
                                <label for="menu_id"
                                    class="mb-1 block text-xs font-semibold text-gray-600">
                                    INVENTORY ITEM <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="menu_id"
                                    name="menu_id"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Select sawdust item</option>
                                    @foreach ($menus as $menu)
                                        <option
                                            value="{{ $menu->id }}"
                                            data-stock="{{ number_format((float) $menu->current_quantity, 3) }}"
                                            data-unit="{{ $menu->unit }}"
                                            @selected(old('menu_id') == $menu->id)>
                                            {{ $menu->name }}
                                            (stock: {{ number_format((float) $menu->current_quantity, 3) }} {{ $menu->unit ?: '' }})
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-1 text-xs text-gray-500" id="currentStockHint"></p>
                            </div>

                            {{-- Quantity --}}
                            <div>
                                <label for="quantity"
                                    class="mb-1 block text-xs font-semibold text-gray-600">
                                    QUANTITY TO ADD <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    value="{{ old('quantity') }}"
                                    min="0.001"
                                    step="0.001"
                                    placeholder="Enter quantity"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>

                            {{-- Unit dropdown: merged static units + existing units from DB --}}
                            <div>
                                <label for="unit"
                                    class="mb-1 block text-xs font-semibold text-gray-600">
                                    UNIT <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="unit"
                                    name="unit"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                                    <option value="">Select unit</option>
                                    @foreach (collect(['kg', 'pcs', 'each', 'box', 'pack'])->merge($units)->unique()->sort()->values() as $unit)
                                        <option value="{{ $unit }}"
                                            @selected(old('unit') === $unit)>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    @if (!$menus->isEmpty())
                        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-4 py-3">
                            <button type="button" id="cancelAddModalBtn"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                CANCEL
                            </button>

                            <button type="submit"
                                class="rounded-lg bg-blue-700 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-800">
                                ENTER QUANTITY
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('addQuantityModal');

            if (!modal) return;

            const openBtn   = document.getElementById('openAddModalBtn');
            const closeBtn  = document.getElementById('closeAddModalBtn');
            const cancelBtn = document.getElementById('cancelAddModalBtn');
            const backdrop  = document.getElementById('addQuantityBackdrop');

            function openAddModal() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeAddModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            openBtn?.addEventListener('click', openAddModal);
            closeBtn?.addEventListener('click', closeAddModal);
            cancelBtn?.addEventListener('click', closeAddModal);
            backdrop?.addEventListener('click', closeAddModal);

            document.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Escape' &&
                    !modal.classList.contains('hidden')
                ) {
                    closeAddModal();
                }
            });

            // ---- Item selection sync (stock hint + unit preselect) ----
            const menuSelect = document.getElementById('menu_id');
            const stockHint  = document.getElementById('currentStockHint');
            const unitSelect = document.getElementById('unit');

            function syncItemHint() {
                if (!menuSelect) return;

                const opt = menuSelect.selectedOptions[0];
                if (!opt || !opt.value) {
                    if (stockHint) stockHint.textContent = '';
                    return;
                }

                const stock = opt.dataset.stock || '0';
                const unit  = opt.dataset.unit  || '';

                if (stockHint) {
                    stockHint.textContent = `Current stock: ${stock} ${unit}`.trim();
                }

                // Preselect the item's existing unit if present in the dropdown
                if (unitSelect && unit) {
                    const match = [...unitSelect.options].find(o => o.value === unit);
                    if (match) {
                        unitSelect.value = unit;
                    }
                }
            }

            menuSelect?.addEventListener('change', syncItemHint);
            syncItemHint(); // handles old('menu_id') after validation error

            // Reopen the modal after validation errors.
            @if ($errors->any() && old('menu_id'))
                openAddModal();
            @endif
        })();
    </script>
</x-app-layout>