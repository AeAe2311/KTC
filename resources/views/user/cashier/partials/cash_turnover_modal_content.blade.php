<!-- Cash Breakdown Modal Content -->
<div class="cash-breakdown-modal">
    <!-- Print Header (visible only in print) -->
    <div class="modal-print-header" style="display:none;">
        <div id="cashPrintHeaderInfo" style="text-align:center; margin-bottom:12px;">
            <h2 style="font-size:1.1rem; font-weight:700; color:#1f2937; margin:0 0 4px 0;">
                CASH BREAKDOWN REPORT
            </h2>
            <div style="font-size:0.75rem; color:#6b7280;">
                Report #: {{ $report->trans_number ?? '—' }}
                &nbsp;|&nbsp;
                Date: {{ $report->start_date ? date('F j, Y', strtotime($report->start_date)) : '—' }}
                &nbsp;|&nbsp;
                Status: {{ $approval->status ?? 'PENDING' }}
            </div>
            @if(isset($report->cashier_name) && !empty($report->cashier_name))
                <div style="font-size:0.7rem; color:#6b7280; margin-top:2px;">
                    Submitted by: {{ $report->cashier_name }}
                </div>
            @endif
        </div>
    </div>

    @php
        $isProcessed = isset($approval) && $approval !== null
                       && in_array($approval->status ?? '', ['APPROVED', 'REJECTED']);
    @endphp

    {{-- ===== SCROLLABLE TABLE WRAPPER ===== --}}
    <div class="denomination-table-wrapper">
        <table class="denomination-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:40%;">Denomination</th>
                    <th style="text-align:center; width:25%;">Count</th>
                    <th style="text-align:right; width:35%;">Amount</th>
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
                    $denominationValuesMap = [
                        'count_1000' => 1000, 'count_500' => 500, 'count_200' => 200,
                        'count_100' => 100, 'count_50' => 50, 'count_20' => 20,
                        'count_10' => 10, 'count_5' => 5, 'count_1' => 1,
                        'count_50cents' => 0.50, 'count_10cents' => 0.10, 'count_5cents' => 0.05,
                    ];
                @endphp

                @foreach($denominationTotals as $key => $data)
                    <tr>
                        <td class="label-col">{{ $denominationLabels[$key] ?? $key }}</td>
                        <td class="count-col">
                            <input type="number"
                                   class="denomination-count-input"
                                   data-key="{{ $key }}"
                                   data-value="{{ $denominationValuesMap[$key] }}"
                                   value="{{ $data['count'] }}"
                                   min="0"
                                   step="1"
                                   @if($isProcessed) disabled @endif
                                   style="width:80px; text-align:center; border:1px solid #d1d5db; border-radius:4px; padding:3px 6px; font-size:0.75rem; @if($isProcessed) background:#f3f4f6; color:#6b7280; @endif">
                        </td>
                        <td class="amount-col denomination-amount" data-key="{{ $key }}">
                            ₱ {{ number_format($data['total'], 2) }}
                        </td>
                    </tr>
                @endforeach

                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="2" class="label-col" style="font-weight:bold;">TOTAL CASH</td>
                    <td class="amount-col" id="modalTotalCash" style="font-weight:bold; color:#059669;">
                        ₱ {{ number_format($totalCash, 2) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="label-col" style="font-weight:bold;">CASH SALE</td>
                    <td class="amount-col" style="font-weight:bold; color:#059669;">
                        ₱ {{ number_format($cashSales->amount ?? 0, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    {{-- ===== END SCROLLABLE TABLE WRAPPER ===== --}}

    <!-- Variance (dynamic) -->
    <div id="modalVarianceContainer">
        @php
            $variance = $cashBreakdown['cash_variance'] ?? 0;
            $isOver = $variance > 0;
            $isShort = $variance < 0;
            $bgColor = $isOver ? '#f0fdf4' : ($isShort ? '#fef2f2' : '#f3f4f6');
            $borderColor = $isOver ? '#bbf7d0' : ($isShort ? '#fecaca' : '#e5e7eb');
            $textColor = $isOver ? '#059669' : ($isShort ? '#dc2626' : '#6b7280');
            $statusText = $isOver ? 'Over' : ($isShort ? 'Short' : 'Balanced');
        @endphp
        @if($variance != 0)
            <div style="display:flex; align-items:center; gap:8px; margin-top:8px; padding:8px 12px; border-radius:6px; background:{{ $bgColor }}; border:1px solid {{ $borderColor }};">
                <span style="font-size:0.7rem; color:#6b7280; font-weight:600;">VARIANCE:</span>
                <span style="font-weight:bold; color:{{ $textColor }}; font-size:0.85rem;">
                    {{ $isOver ? '+' : '' }}{{ number_format($variance, 2) }}
                </span>
                <span style="font-size:0.7rem; color:#6b7280;">
                    ({{ $statusText }} by ₱ {{ number_format(abs($variance), 2) }})
                </span>
            </div>
        @endif
    </div>

    <!-- ===== REMARKS SECTION ===== -->
    <div style="margin-top: 15px; padding-top: 12px; border-top: 2px solid #e5e7eb;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <i class="uil uil-notes" style="color: #6b7280; font-size: 1rem;"></i>
            <span style="font-size: 0.75rem; font-weight: 600; color: #1f2937;">REMARKS / NOTES</span>
        </div>

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

        @if(isset($report->created_at))
            <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                <span style="font-size: 0.6rem; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 10px; border: 1px solid #bfdbfe;">
                    📅 {{ date('Y-m-d H:i', strtotime($report->created_at)) }}
                </span>
                @if(isset($report->cashier_name) && !empty($report->cashier_name))
                    <span style="font-size: 0.6rem; background: #f3e8ff; color: #7c3aed; padding: 2px 8px; border-radius: 10px; border: 1px solid #ddd6fe;">
                        👤 {{ $report->cashier_name }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- ===== APPROVAL ACTIONS SECTION ===== -->
    @if($isProcessed)
        <div style="margin-top: 15px; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <i class="uil uil-check-circle" style="color: #6b7280; font-size: 1rem;"></i>
                <span style="font-size: 0.75rem; font-weight: 600; color: #1f2937;">APPROVAL INFORMATION</span>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; font-size:0.8rem;">

                {{-- STATUS --}}
                <div>
                    <span style="color:#9ca3af; font-size:0.7rem; font-weight:600;">
                        STATUS:
                    </span>
                    <span style="margin-left:6px; font-weight:700; color:{{ ($approval->status ?? '') === 'APPROVED' ? '#059669' : '#dc2626' }};">
                        {{ $approval->status ?? '—' }}
                    </span>
                </div>

                {{-- APPROVED BY --}}
                <div>
                    <span style="color:#9ca3af; font-size:0.7rem; font-weight:600;">
                        APPROVED BY:
                    </span>
                    <span style="margin-left:6px; color:#374151;">
                        {{ $approval->approved_by_name ?? '—' }}
                    </span>
                </div>

                {{-- REMARKS --}}
                <div>
                    <span style="color:#9ca3af; font-size:0.7rem; font-weight:600;">
                        REMARKS:
                    </span>
                    <span style="margin-left:6px; color:#374151;">
                        {{ $approval->remarks ?? '—' }}
                    </span>
                </div>
                {{-- APPROVED AT --}}
                <div>
                    <span style="color:#9ca3af; font-size:0.7rem; font-weight:600;">
                        APPROVED AT:
                    </span>
                    <span style="margin-left:6px; color:#374151;">
                        {{ !empty($approval->approve_at) ? date('m/d/Y h:i A', strtotime($approval->approve_at)) : '—' }}
                    </span>
                </div>
            </div>
        </div>
    @else
        {{-- Approver remarks input --}}
        <div class="no-print" style="margin-top: 15px; padding-top: 12px; border-top: 2px solid #e5e7eb;">
            <label for="approvalRemarks" style="display:block; font-size:0.75rem; font-weight:600; color:#374151; margin-bottom:6px;">
                APPROVER REMARKS <span style="font-weight:400; color:#9ca3af;">(required for rejection)</span>
            </label>
            <textarea id="approvalRemarks" rows="2" placeholder="Enter remarks here..."
                      style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:0.8rem; resize:vertical; box-sizing:border-box;"></textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="no-print" style="display:flex; justify-content:flex-end; gap:8px; margin-top:12px; flex-wrap:wrap;">
            <button type="button" onclick="closeBreakdownModal()"
                    style="background:#e5e7eb; color:#374151; font-weight:600; font-size:0.8rem; padding:8px 18px; border-radius:6px; border:none; cursor:pointer;">
                Close
            </button>
            <button type="button" onclick="printBreakdownModal()"
                    style="background:#7c3aed; color:#fff; font-weight:600; font-size:0.8rem; padding:8px 18px; border-radius:6px; border:none; cursor:pointer; display:flex; align-items:center; gap:5px;">
                <i class="uil uil-print"></i> Print
            </button>
            <button type="button" onclick="saveEditedCounts()"
                    style="background:#2563eb; color:#fff; font-weight:600; font-size:0.8rem; padding:8px 18px; border-radius:6px; border:none; cursor:pointer; display:flex; align-items:center; gap:5px;">
                <i class="uil uil-save"></i> Save Counts
            </button>
            <button type="button" onclick="confirmReject()"
                    style="background:#dc2626; color:#fff; font-weight:600; font-size:0.8rem; padding:8px 18px; border-radius:6px; border:none; cursor:pointer;">
                Reject
            </button>
            <button type="button" onclick="confirmApprove()"
                    style="background:#059669; color:#fff; font-weight:600; font-size:0.8rem; padding:8px 18px; border-radius:6px; border:none; cursor:pointer;">
                Approve
            </button>
        </div>
    @endif
</div>