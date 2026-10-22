<div id="printArea">
    <!-- Print Header -->
    <div class="mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Transform Record</h3>
                <p class="text-lg font-semibold text-blue-600">{{ $transform->transform_no }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-sm font-semibold 
                    @if($transform->status === 'COMPLETED') bg-green-100 text-green-800
                    @elseif($transform->status === 'PENDING') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $transform->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="text-sm text-gray-600">Date</p>
                <p class="font-semibold">{{ \Carbon\Carbon::parse($transform->date)->format('F d, Y h:i A') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Submitted By</p>
                <p class="font-semibold">{{ $transform->createdBy->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Approved By</p>
                <p class="font-semibold">{{ optional($transform->approvedBy)->name ?? 'Pending Approval' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Remarks</p>
                <p class="font-semibold">{{ $transform->remarks ?? 'No remarks' }}</p>
            </div>
        </div>
    </div>

    <!-- Source Item Section -->
    <div class="mb-6 border rounded-lg overflow-hidden">
        <div class="bg-blue-50 px-4 py-3 border-b">
            <h4 class="text-lg font-bold text-blue-900">
                <i class="uil uil-arrow-from-top mr-2"></i>Source Item
            </h4>
        </div>
        <div class="p-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2">Item</th>
                        <th class="text-right px-4 py-2">Quantity Used</th>
                        <th class="text-right px-4 py-2">Unit</th>
                        <th class="text-right px-4 py-2">Before Transform</th>
                        <th class="text-right px-4 py-2">After Transform</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">
                            {{ optional($transform->source)->name ?? 'Deleted Item' }}
                            @if(optional($transform->source)->brand)
                                <br><span class="text-gray-500 text-xs">{{ $transform->source->brand->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-red-600">
                            -{{ number_format($transform->source_quantity, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">{{ $transform->source_unit }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($sourceBefore !== null)
                                {{ number_format($sourceBefore, 2) }}
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($sourceAfter !== null)
                                <span class="{{ $sourceAfter < $sourceBefore ? 'text-red-600' : '' }}">
                                    {{ number_format($sourceAfter, 2) }}
                                </span>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            
            @if($transform->waste_quantity)
            <div class="mt-3 p-3 bg-orange-50 rounded-lg">
                <p class="text-sm font-semibold text-orange-800">
                    <i class="uil uil-trash-alt mr-1"></i>Waste: 
                    {{ number_format($transform->waste_quantity, 2) }} {{ $transform->waste_unit }}
                </p>
                @if($transform->waste_description)
                    <p class="text-xs text-orange-600 mt-1">{{ $transform->waste_description }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Output Items Section -->
    <div class="mb-6 border rounded-lg overflow-hidden">
        <div class="bg-green-50 px-4 py-3 border-b">
            <h4 class="text-lg font-bold text-green-900">
                <i class="uil uil-arrow-to-bottom mr-2"></i>Output Items
            </h4>
        </div>
        <div class="p-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2">#</th>
                        <th class="text-left px-4 py-2">Item</th>
                        <th class="text-right px-4 py-2">Quantity Produced</th>
                        <th class="text-right px-4 py-2">Unit</th>
                        <th class="text-right px-4 py-2">Before Transform</th>
                        <th class="text-right px-4 py-2">After Transform</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outputItems as $index => $outputItem)
                        <tr class="border-t">
                            <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ optional($outputItem['item']->menu)->name ?? 'Deleted Item' }}
                                @if(optional($outputItem['item']->menu)->brand)
                                    <br><span class="text-gray-500 text-xs">{{ $outputItem['item']->menu->brand->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">
                                +{{ number_format($outputItem['item']->quantity, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">{{ $outputItem['item']->unit }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($outputItem['before'] !== null)
                                    {{ number_format($outputItem['before'], 2) }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($outputItem['after'] !== null)
                                    <span class="{{ $outputItem['after'] > $outputItem['before'] ? 'text-green-600' : '' }}">
                                        {{ number_format($outputItem['after'], 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b">
            <h4 class="text-lg font-bold text-gray-800">
                <i class="uil uil-analytics mr-2"></i>Summary
            </h4>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="p-3 bg-red-50 rounded-lg">
                    <p class="text-xs text-red-600 font-semibold">Source Used</p>
                    <p class="text-lg font-bold text-red-700">{{ number_format($transform->source_quantity, 2) }} {{ $transform->source_unit }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-green-600 font-semibold">Total Output</p>
                    <p class="text-lg font-bold text-green-700">
                        @php
                            $totalOutput = $transform->items->sum('quantity');
                        @endphp
                        {{ number_format($totalOutput, 2) }} 
                        {{ $transform->items->first()->unit ?? 'units' }}
                    </p>
                </div>
                @if($transform->waste_quantity)
                <div class="p-3 bg-orange-50 rounded-lg">
                    <p class="text-xs text-orange-600 font-semibold">Waste</p>
                    <p class="text-lg font-bold text-orange-700">{{ number_format($transform->waste_quantity, 2) }} {{ $transform->waste_unit }}</p>
                </div>
                @endif
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-600 font-semibold">Items Produced</p>
                    <p class="text-lg font-bold text-blue-700">{{ $transform->items->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-6 pt-4 border-t text-center text-xs text-gray-500">
        <p>Generated on {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
        <p>Transform No: {{ $transform->transform_no }}</p>
    </div>
</div>