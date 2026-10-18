<x-app-layout>
    @section('page_title', 'SAWDUST')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="w-full p-2">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 overflow-hidden rounded-lg bg-white p-4 shadow-md">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Sawdust Items</h1>
                        <p class="text-sm text-gray-600">Current quantity for sawdust menu items in your store.</p>
                    </div>
                    <a href="{{ route('sawdust.add') }}"
                        class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                        ADD QUANTITY
                    </a>
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
                                    <td class="px-4 py-3">{{ $menu->unit }}</td>
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
                                    <td class="px-4 py-3">{{ number_format((float) $log->quantity_after, 3) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
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
</x-app-layout>
