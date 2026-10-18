<x-app-layout>
    @section('page_title', 'EDIT TRANSFORM')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-700 bg-red-100 rounded-lg p-3">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 text-sm text-red-700 bg-red-100 rounded-lg p-3">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('transform.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $transform->slug }}">

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Transform No.</label>
                            <input type="text" value="{{ $transform->transform_no }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', $transform->date) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" required>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source Item</label>
                            <input type="text" value="{{ optional($transform->source)->name }}{{ optional($transform->source)->brand ? ' - ' . $transform->source->brand->name : '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source Quantity</label>
                            <input type="text" value="{{ number_format($transform->source_quantity, 2) }} {{ $transform->source_unit }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100" readonly>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h2 class="text-base font-semibold mb-3">Waste / Scrap</h2>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waste Quantity</label>
                                <input type="number" step="0.01" min="0" name="waste_quantity" value="{{ old('waste_quantity', $transform->waste_quantity) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waste Unit</label>
                                <input type="text" name="waste_unit" value="{{ old('waste_unit', $transform->waste_unit) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" placeholder="e.g. kg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waste Description</label>
                                <input type="text" name="waste_description" value="{{ old('waste_description', $transform->waste_description) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" placeholder="e.g. trimming loss">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea name="remarks" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">{{ old('remarks', $transform->remarks) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" required>
                                    <option value="COMPLETED" {{ old('status', $transform->status) === 'COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
                                    <option value="PENDING" {{ old('status', $transform->status) === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                </select>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <h3 class="font-semibold">Transformed Outputs</h3>
                            <div class="mt-3 space-y-2">
                                @foreach ($transform->items as $item)
                                    <div class="rounded-lg border border-gray-200 p-3 bg-white">
                                        <div class="grid gap-4 md:grid-cols-3">
                                            <div>
                                                <div class="text-xs font-semibold text-gray-500">Item</div>
                                                <div class="mt-1 text-sm text-gray-900">{{ $item->menu->name ?? $item->inventory->name ?? 'Deleted item' }}{{ $item->menu ? ' - ' . optional($item->menu->brand)->name : '' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-500">Quantity</div>
                                                <div class="mt-1 text-sm text-gray-900">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-500">Unit</div>
                                                <div class="mt-1 text-sm text-gray-900">{{ $item->unit }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 md:flex-row md:justify-between">
                        <a href="{{ route('transform.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-gray-900 bg-gray-200 rounded-lg hover:bg-gray-300">Back</a>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">Update Transform</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>