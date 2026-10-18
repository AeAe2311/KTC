<x-app-layout>
    @section('page_title', 'SAWDUST - ADD QUANTITY')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                <h1 class="mb-4 text-xl font-semibold text-gray-900">Add Sawdust Quantity</h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($menus->isEmpty())
                    <p class="mb-4 rounded-lg bg-yellow-100 p-3 text-yellow-800">
                        No active sawdust menu items are available for your store.
                    </p>
                @else
                    <form action="{{ route('sawdust.store') }}" method="POST" class="max-w-2xl">
                        @csrf

                        <div class="mb-4">
                            <label for="menu_id" class="mb-1 block text-sm font-medium text-gray-900">
                                Sawdust Item <span class="text-red-500">*</span>
                            </label>
                            <select id="menu_id" name="menu_id" required
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900">
                                @foreach ($menus as $menu)
                                    <option value="{{ $menu->id }}" @selected(old('menu_id') == $menu->id)>
                                        {{ $menu->name }} — Current: {{ number_format((float) $menu->current_quantity, 3) }} {{ $menu->unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="quantity" class="mb-1 block text-sm font-medium text-gray-900">
                                Quantity to Add <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}"
                                min="0.00000000000000000001" step="any" required
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900">
                        </div>

                        <div class="mt-6 flex justify-between">
                            <a href="{{ route('sawdust.index') }}"
                                class="mr-2 rounded-lg bg-gray-600 px-5 py-2.5 text-center text-sm font-semibold tracking-wider text-white hover:bg-gray-700">
                                BACK
                            </a>
                            <button type="submit"
                                class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold tracking-wider text-white hover:bg-blue-800">
                                ADD QUANTITY
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
