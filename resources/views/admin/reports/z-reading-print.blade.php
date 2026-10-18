<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Z Reading · Murang Karne (Full Page)</title>
  <style>
    /* RESET & BODY – no padding, full page */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Courier New', 'Consolas', monospace;
    }

    body {
      background: white;
      margin: 0;
      padding: 0;
      width: 100%;
      min-height: 100vh;
      display: block;
    }

    /* main wrapper – no max-width, no padding, uses full viewport */
    .receipt {
      width: 100%;
      max-width: 100%;
      background: white;
      padding: 0.3in 0.2in;   /* small margin for readability, but fills page */
      border: none;
      box-shadow: none;
      border-radius: 0;
      margin: 0 auto;
      box-sizing: border-box;
    }

    /* all other styles remain consistent, but we remove fixed widths */
    .header-title {
      text-align: center;
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .header-line {
      font-size: 12px;
      letter-spacing: 0.2px;
      text-align: center;
    }

    .company {
      font-weight: 700;
      font-size: 14px;
      text-align: left;
      letter-spacing: 0.3px;
      margin-top: 4px;
    }

    .sub-line {
      text-align: center;
      font-size: 12px;
    }


    .divider {
      border: none;
      border-top: 2px dashed #444;
      margin: 12px 0 6px;
      height: 0;
    }

    .divider-light {
      border: none;
      border-top: 1px dashed #777;
      margin: 6px 0;
      height: 0;
    }

    /* SALES SUMMARY - exactly as before */
    .sales-row {
      display: flex;
      justify-content: space-between;
      padding: 1px 0;
      font-size: 13px;
      letter-spacing: 0.3px;
    }

    .sales-row .label {
      font-weight: 400;
    }

    .sales-row .value {
      font-weight: 400;
      text-align: right;
    }

    .grand-total {
      display: flex;
      justify-content: space-between;
      font-weight: 700;
      font-size: 15px;
      border-top: 2px double #222;
      border-bottom: 2px double #222;
      padding: 6px 0;
      margin: 6px 0;
    }

    .grand-total .label {
      font-weight: 700;
    }

    .grand-total .value {
      font-weight: 700;
    }

    .section-title {
      font-weight: 700;
      background: #f2f2f2;
      padding: 2px 6px;
      letter-spacing: 0.3px;
      margin-top: 8px;
      border-bottom: 1px solid #aaa;
    }

    .item-list {
      padding-left: 6px;
    }

    .item {
      display: flex;
      justify-content: space-between;
      padding: 1px 0;
      font-size: 12px;
    }

    .indent {
      padding-left: 16px;
    }

    .cashier-block {
      margin-top: 6px;
      padding: 4px 8px;
      background: #f6f8fc;
    }

    .text-small {
      font-size: 11px;
    }

    .flex-between {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .sub-company {
      text-align: left;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 6px;
    }

    /* PRINT STYLES: remove any border/box, use full page */
    @media print {
      body {
        background: white;
        margin: 0;
        padding: 0;
        width: 100%;
      }
      .receipt {
        width: 100%;
        max-width: 100%;
        padding: 0.15in 0.1in;   /* minimal padding to avoid cut-off, but fills page */
        box-shadow: none;
        border: none;
        border-radius: 0;
        margin: 0;
        background: white;
      }
      /* ensure no extra spacing */
      .divider, .divider-light {
        margin: 4px 0;
      }
      .section-title {
        margin-top: 4px;
      }
      .cashier-block {
        padding: 2px 6px;
      }
    }

    /* optional: small screens still use full width */
    @media screen and (max-width: 600px) {
      .receipt {
        padding: 12px 8px;
      }
    }
  </style>
</head>
<body>
@php
  $zread = $zreadingSummary ?? [];
  $money = fn ($value) => number_format((float) ($value ?? 0), 2, '.', ',');
  $reportDate = date('Y-m-d', strtotime($startDate));
  $printedAt = date('Y-m-d h:i A');
  $endDate = date('Y-m-d', strtotime($endDate));
  $branchTin = $branchSettings->tin ?? '';
  $branchName = $branchSettings->name ?? 'Selected Branch';
  $branchAddress = $branchSettings->address ?? '';
  $branchStoreCode = $branchSettings->store_code ?? ($selectedBranch ?? '');
@endphp

  <div class="receipt">
    <!-- HEADER -->
    <div class="header-title"># Sales Z Reading</div>
    
    <div class="header-line">Print Out Date: {{ $printedAt }}</div>
    <div class="header-line">Fiscal Year: 2026-01-01 - 2026-12-31 (Active)</div>
    <div class="header-line">Period: {{ $reportDate }} - {{ $endDate }}</div>
    <div class="header-line">Terminal: {{ $branchName }}</div>

    <hr class="divider">

    <div class="company">{{ strtoupper($branchName) }}</div>
    <div class="sub-company">{{ strtoupper($branchAddress) }}</div>

    <div style="font-size:12px; margin: 6px 0 4px;">
      <span>RETURN POLICY:</span><br>
      *24 hours of purchase<br>
      *once signed, confirms items received in good condition<br>
      *full details in-store or contact us via our Facebook Page ({{ $branchName }}) {{-- LINE 234: ken update to zreading print dynamic name 9-7-2026 --}}
    </div>
    <div class="flex-between text-small">
      <span>VAT REG TIN: {{ $branchTin }}</span>
      <span>Date {{ $reportDate }}</span>
    </div>
    <div class="flex-between text-small">
      <span>Terminal: {{ $branchName }}</span>
      <span>Summary: 000000000</span>
    </div>

    <hr class="divider">

    <!-- SALES SUMMARY -->
    <div class="sales-row">
      <span class="label">GROSS SALES</span>
      <span class="value">{{ $money($zread['gross_sales'] ?? 0) }}</span>
    </div>
    <div class="sales-row">
      <span class="label">LESS RETURNS</span>
      <span class="value">{{ $money($zread['less_returns'] ?? 0) }}</span>
    </div>
    <div class="sales-row" style="margin-top:2px;">
      <span class="label">NET SALES AFTER SC</span>
      <span class="value">{{ $money($zread['net_sales_after_sc'] ?? 0) }}</span>
    </div>
    <div class="sales-row" style="margin-top:2px;">
      <span class="label">SALES CHARGE</span>
      <span class="value">{{ $money($zread['sales_charge'] ?? 0) }}</span>
    </div>
    <div class="sales-row" style="margin-top:2px;">
      <span class="label">VAT 12%</span>
      <span class="value">{{ $money($zread['vat_12'] ?? 0) }}</span>
    </div>
    <div class="sales-row">
      <span class="label">VATABLE SALES</span>
      <span class="value">{{ $money($zread['vatable_sales'] ?? 0) }}</span>
    </div>
    <div class="sales-row">
      <span class="label">VAT EXEMPT SALES</span>
      <span class="value">{{ $money($zread['vat_exempt_sales'] ?? 0) }}</span>
    </div>
    <div class="sales-row" style="margin-top:2px;">
      <span class="label">SERVICE CHARGE</span>
      <span class="value">{{ $money($zread['serviceCharge'] ?? 0) }}</span>
    </div>
    <div class="sales-row" style="margin-top:2px;">
      <span class="label">DISCOUNTS</span>
      <span class="value">{{ $money($zread['discounts'] ?? 0) }}</span>
    </div>
    <div class="sales-row">
      <span class="label">LESS VAT SENIOR/PWD</span>
      <span class="value">{{ $money($zread['less_vat_senior_pwd'] ?? 0) }}</span>
    </div>
    <div class="sales-row">
      <span class="label">DISCOUNT SENIOR/PWD</span>
      <span class="value">{{ $money($zread['discount_senior_pwd'] ?? 0) }}</span>
    </div>

    <div class="grand-total">
      <span class="label">GRAND TOTAL</span>
      <span class="value">{{ $money($zread['grand_total'] ?? 0) }}</span>
    </div>

    <hr class="divider-light">
    <!-- CASH BREAKDOWN SECTION -->
    <div class="sales-row" style="margin-top:12px;">CASH BREAKDOWN</div>
    <div class="grand-totalk">
      <div style="font-weight:700; margin-bottom:4px;">DENOMINATIONS</div>
      
      @php
        $denomLabels = [
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

      @foreach($denominationTotals ?? [] as $key => $denom)
        @if($denom['count'] > 0)
          <div class="item">
            <span>{{ $denomLabels[$key] ?? $key }} x {{ $denom['count'] }}</span>
            <span>{{ number_format($denom['total'], 2, '.', ',') }}</span>
          </div>
        @endif
      @endforeach

      <div class="item" style="font-weight:700; border-top: 1px dashed #999; margin-top:4px; padding-top:4px;">
        <span>TOTAL CASH COUNTED</span>
        <span>{{ number_format($totalCash ?? 0, 2, '.', ',') }}</span>
      </div>

      <div class="item" style="margin-top:4px;">
        <span>CASH SALES</span>
        <span>{{ number_format($cashSales->amount ?? 0, 2, '.', ',') }}</span>
      </div>

      <div class="item" style="font-weight:700; border-top: 1px dashed #999; margin-top:4px; padding-top:4px;">
        <span>CASH VARIANCE</span>
        <span>
          @if(($variance ?? 0) > 0)
            +{{ number_format($variance, 2, '.', ',') }}
          @elseif(($variance ?? 0) < 0)
            -{{ number_format(abs($variance), 2, '.', ',') }}
          @else
            {{ number_format($variance, 2, '.', ',') }}
          @endif
        </span>
      </div>

      <div style="font-size:11px; margin-top:2px;">
        @if(($variance ?? 0) > 0)
          OVER by {{ number_format($variance, 2, '.', ',') }}
        @elseif(($variance ?? 0) < 0)
          SHORT by {{ number_format(abs($variance), 2, '.', ',') }}
        @else
          EXACT CASH - NO VARIANCE
        @endif
      </div>
    </div>

    <hr class="divider-light">
      <div class="sales-row">
        <span class="label">RUNNING TOTAL</span>
        <span class="value">{{ $money($zread['runningTotal'] ?? 0) }}</span>
    </div>
    <div class="sales-row">        
        <span class="label">BEGINNING TOTAL</span>
        <span class="value">{{ $money($zread['beginningTotal'] ?? 0) }}</span>   
    </div>
    <div class="sales-row">        
        <span class="label">ENDING TOTAL</span>
        <span class="value">{{ $money($zread['endingTotal'] ?? 0) }}</span>             
    </div>
    {{-- <div style="text-align:center; font-weight:600;">Z READING END OF DAY REPORT</div>
    <hr class="divider-light">

    <div class="section-title">HOURLY SALES/RETURNS</div>
    <div class="item-list">
      @foreach($hourlySummary ?? [] as $hour)
        <div class="item"><span>{{ date('h:00 A', strtotime($hour->date . ' ' . $hour->hour . ':00:00')) }} ({{ $hour->hourly_transactions }})</span><span>{{ $money($hour->hourly_sales) }}</span></div>
      @endforeach
    </div>

    <div class="sales-row" style="margin-top:8px;">
      <span>Total Transaction {{ $hourlyGrandTotal['transactions'] ?? 0 }}</span>
      <span>{{ $money($hourlyGrandTotal['sales'] ?? 0) }}</span>
    </div>
    <hr class="divider-light">

    <div class="section-title">8 TOP SELLING ITEMS</div>
    <div class="item-list">
      @forelse($topSellingItems ?? [] as $item)
        <div class="item"><span>{{ strtoupper($item->menu_name) }} - {{ strtoupper($item->brand_name ?? 'N/A') }}</span><span>{{ $money($item->total_amount) }}</span></div>
      @empty
        <div class="item"><span>NO SOLD ITEMS FOUND</span><span>0.00</span></div>
      @endforelse
    </div>

    <div class="section-title" style="margin-top:12px;">8 TOP FREQUENTLY SOLD ITEMS</div>
    <div class="item-list">
      @forelse($topItems ?? [] as $item)
        <div class="item"><span>{{ strtoupper($item->menu_name) }} - {{ strtoupper($item->brand_name ?? 'N/A') }}</span><span>{{ $item->transaction_count }}/{{ number_format((float) $item->total_quantity, 0, '.', '') }}</span></div>
      @empty
        <div class="item"><span>NO SOLD ITEMS FOUND</span><span>0/0</span></div>
      @endforelse
    </div> 

    <hr class="divider-light"> --}}
    {{-- <div class="section-title">CATEGORY SALES SUMMARY</div>
    @foreach($categorySummary ?? [] as $category)
      <div class="item"><span>{{ strtoupper($category->category_name) }} ({{ number_format((float) $category->total_quantity, 0) }})</span><span>{{ $money($category->total_amount) }}</span></div>
      @foreach($category->menus as $menu)
        <div class="item indent"><span>{{ strtoupper($menu->menu_name) }}</span><span>{{ $money($menu->menu_amount) }}</span></div>
      @endforeach
    @endforeach
    <div class="sales-row"><span>GRAND TOTAL</span><span>{{ $money($categoryGrandTotal ?? 0) }}</span></div> --}}

    {{-- <div class="section-title" style="margin-top:12px;">MODE OF PAYMENT SUMMARY</div>
    @foreach($paymentSummary ?? [] as $payment)
      <div class="item"><span>{{ $payment->mop }} ({{ $payment->total_count }})</span><span>{{ $money($payment->total_amount) }}</span></div>
    @endforeach
    <div class="sales-row"><span>GRAND TOTAL</span><span>{{ $money($paymentGrandTotal ?? 0) }}</span></div> --}}

    {{-- <div class="section-title">TERMINAL CASH POSITION</div>
    @foreach($paymentSummary ?? [] as $payment)
      <div class="item"><span>{{ $payment->mop }} ({{ $payment->total_count }})</span><span>{{ $money($payment->total_amount) }}</span></div>
    @endforeach --}}

    <!-- CASHIERS CASH POSITION -->
   {{--  <div class="section-title" style="margin-top:12px;">CASHIERS CASH POSITION</div>
    @if(isset($cashierData) && count($cashierData) > 0)
      @foreach($cashierData as $cashierName => $data)
        <div class="cashier-block">
          <div style="font-weight:700;">=== {{ strtoupper($cashierName) }} ===</div>
          @foreach($data['payments'] as $payment)
            <div class="item"><span>({{ $payment['count'] }}){{ $payment['mop'] }}</span><span>{{ $money($payment['amount']) }}</span></div>
          @endforeach
          <div class="item" style="font-weight:700; border-top: 1px dashed #999; margin-top:2px; padding-top:2px;">
            <span>TOTAL</span>
            <span>{{ $money($data['total']) }}</span>
          </div>
        </div>
      @endforeach
    @else
      <div class="cashier-block">
        <div style="font-weight:700;">NO CASHIER DATA AVAILABLE</div>
      </div>
    @endif

    <div style="margin-top:8px;">
      <div class="item"><span>Sales X Reading</span><span>Print Out Date: {{ $printedAt }}</span></div>
      <div class="item"><span>Period: {{ $reportDate }} - {{ $endDate }}</span><span>Transactions: {{ $hourlyGrandTotal['transactions'] ?? 0 }}</span></div>
      <div class="item"><span>Store Code: {{ $branchStoreCode }}</span><span>{{ strtoupper($branchName) }}</span></div>
      <div class="item"><span>{{ $branchAddress }}</span><span>STORE CODE: {{ $branchStoreCode }}</span></div>
      <div class="item"><span>Page 3</span></div>
    </div>

    <hr class="divider-light"> --}}
    {{-- <div style="font-size:12px; background: #f4f4f4; padding: 4px 6px;">
      Accredited POS Supplier Trade Name<br>
      Live Help 4 Us Computer Services<br>
      Bantayan Dumaguete City<br>
      TIN 935546208<br>
      ACCREG NO.<br>
      ACCREG DATE ISSUED May 24, 2012<br>
      PERMIT NO.<br>
      MIN NO.<br>
      MSN
    </div>

    <div style="margin-top:10px;">
      <div class="item"><span>RESET COUNTER</span><span></span></div>
      <div class="item"><span>RUNNING TOTAL</span><span>87,741,444.69</span></div>
      <div class="item"><span>BEGINNING TOTAL</span><span>87,570,263.18</span></div>
      <div class="item"><span>ENDING TOTAL</span><span>87,741,444.69</span></div>
    </div> --}}

    <div style="margin-top:12px; border-top: 1px solid #aaa; padding-top: 8px; font-size:12px;">
      <span>Date Printed {{ date('Y-m-d h:i:s A') }}</span>
    </div>

    <div style="margin-top:8px; font-size:11px; color:#2a2a2a; border-top: 1px dashed #999; padding-top: 6px;">
      <div>Sales X Reading {{ strtoupper($branchName) }}</div>
      <div>Print Out Date: {{ $printedAt }} Store Code: {{ $branchStoreCode }}</div>
      <div>Period: {{ $reportDate }} - {{ $endDate }}</div>
      {{-- <div>Total Transaction {{ $hourlyGrandTotal['transactions'] ?? 0 }}</div> --}}
    </div>
  </div>

  <script>
    window.onload = function() {
      window.print();
    };
  </script>

</body>
</html>