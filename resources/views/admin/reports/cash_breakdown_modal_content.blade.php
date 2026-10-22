<!-- Cash Breakdown Modal Content -->
<div class="cash-breakdown-modal">
    <!-- Print Header (visible only in print) -->
    <div class="modal-print-header" style="display:none;">
        <div id="cashPrintHeaderInfo"></div>
    </div>
    <table class="denomination-table">
        <thead>
            <tr>
                <th style="text-align:left; width:45%;">Denomination</th>
                <th style="text-align:center; width:25%;">Count</th>
                <th style="text-align:right; width:30%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $denominationLabels = [
                    'count_1000' => '₱1,000',
                    'count_500' => '₱500',
                    'count_200' => '₱200',
                    'count_100' => '₱100',
                    'count_50' => '₱50',
                    'count_20' => '₱20',
                    'count_10' => '₱10',
                    'count_5' => '₱5',
                    'count_1' => '₱1',
                    'count_50cents' => '₱0.50',
                    'count_10cents' => '₱0.10',
                    'count_5cents' => '₱0.05',
                ];
            @endphp

            @foreach($denominationTotals as $key => $data)
                @if($data['count'] > 0 || $data['total'] > 0)
                    <tr>
                        <td class="label-col">{{ $denominationLabels[$key] ?? $key }}</td>
                        <td class="count-col">{{ number_format($data['count']) }}</td>
                        <td class="amount-col">₱ {{ number_format($data['total'], 2) }}</td>
                    </tr>
                @endif
            @endforeach

            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="2" class="label-col" style="font-weight:bold;">TOTAL CASH</td>
                <td class="amount-col" style="font-weight:bold; color:#059669;">₱ {{ number_format($totalCash, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="label-col" style="font-weight:bold;">CASH SALE</td>
                <td class="amount-col" style="font-weight:bold; color:#059669;">₱ {{ number_format($cashSales->amount ?? 0, 2) }}</td>
            </tr>            
        </tbody>
    </table>

    <!-- Variance -->
    @if(isset($cashBreakdown['cash_variance']) && $cashBreakdown['cash_variance'] != 0)
        @php
            $variance = $cashBreakdown['cash_variance'];
            $isOver = $variance > 0;
            $isShort = $variance < 0;
            $bgColor = $isOver ? '#f0fdf4' : '#fef2f2';  // Green for over, red for short
            $borderColor = $isOver ? '#bbf7d0' : '#fecaca';
            $textColor = $isOver ? '#059669' : '#dc2626';  // Green for over, red for short
            $badgeClass = $isOver ? 'variance-over' : 'variance-short';
            $statusText = $isOver ? 'Over' : 'Short';
        @endphp
        <div style="display:flex; align-items:center; gap:8px; margin-top:8px; padding:8px 12px; border-radius:6px; background:{{ $bgColor }}; border:1px solid {{ $borderColor }};">
            <span style="font-size:0.7rem; color:#6b7280; font-weight:600;">VARIANCE:</span>
            <span class="variance-badge {{ $badgeClass }}" style="font-weight:bold; color:{{ $textColor }}; font-size:0.85rem;">
                {{ $isOver ? '+' : '' }}{{ number_format($variance, 2) }}
            </span>
            <span style="font-size:0.7rem; color:#6b7280;">
                ({{ $statusText }} by ₱ {{ number_format(abs($variance), 2) }})
            </span>
        </div>
    @endif

    <!-- ===== REMARKS SECTION ===== -->
    <div style="margin-top: 15px; padding-top: 12px; border-top: 2px solid #e5e7eb;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <i class="uil uil-notes" style="color: #6b7280; font-size: 1rem;"></i>
            <span style="font-size: 0.75rem; font-weight: 600; color: #1f2937;">REMARKS / NOTES</span>
        </div>
        
        <!-- Remarks Content -->
        <div style="background: #f8fafc; border-radius: 6px; padding: 10px 12px; border: 1px solid #e5e7eb; min-height: 60px;">
            @if(isset($report->remarks) && !empty($report->remarks))
                <div style="font-size: 0.8rem; color: #1f2937; line-height: 1.5; white-space: pre-wrap;">
                    {{ $report->remarks }}
                </div>
            @else
                <div style="font-size: 0.75rem; color: #9ca3af; font-style: italic; text-align: center;">
                    No remarks added for this report
                </div>
            @endif
        </div>

        <!-- Optional: Quick remark indicators -->
        @if(isset($cashBreakdown['cash_variance']) && $cashBreakdown['cash_variance'] != 0)
            @php
                $variance = $cashBreakdown['cash_variance'];
                $isOver = $variance > 0;
                $isShort = $variance < 0;
            @endphp
            <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                @if($isOver)
                    <span style="font-size: 0.6rem; background: #f0fdf4; color: #059669; padding: 2px 8px; border-radius: 10px; border: 1px solid #bbf7d0; font-weight:600;">
                        ✅ Over by ₱ {{ number_format(abs($variance), 2) }}
                    </span>
                @elseif($isShort)
                    <span style="font-size: 0.6rem; background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 10px; border: 1px solid #fecaca; font-weight:600;">
                        ❌ Short by ₱ {{ number_format(abs($variance), 2) }}
                    </span>
                @endif
                
                @if(isset($report->created_at))
                    <span style="font-size: 0.6rem; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 10px; border: 1px solid #bfdbfe;">
                        📅 {{ date('Y-m-d H:i', strtotime($report->created_at)) }}
                    </span>
                @endif
                
                @if(isset($report->cashier_name) && !empty($report->cashier_name))
                    <span style="font-size: 0.6rem; background: #f3e8ff; color: #7c3aed; padding: 2px 8px; border-radius: 10px; border: 1px solid #ddd6fe;">
                        👤 {{ $report->cashier_name }}
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>