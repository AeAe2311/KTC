<x-app-layout>
    <style>
        /* Summary table styles */
        .summary-table th {
            background-color: #1e293b !important;
            color: white !important;
            padding: 10px 8px !important;
            font-size: 0.7rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .summary-table td {
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-table tr:hover td {
            background-color: #f8fafc !important;
        }

        .summary-table .clickable-row {
            cursor: pointer;
        }

        .summary-table .clickable-row:hover {
            background-color: #fef3c7 !important;
        }

        .view-details-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.65rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .view-details-btn:hover {
            background: #2563eb;
        }

        .cash-breakdown-btn {
            background: #059669;
            color: white;
            border: none;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.65rem;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .cash-breakdown-btn:hover {
            background: #047857;
        }

        /* ===== TRANSACTION DETAILS MODAL STYLES ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            display: none;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        /* Transaction Details Modal - Wide */
        .modal-container-transaction {
            background: white;
            border-radius: 12px;
            width: 95%;
            max-width: 1400px;
            max-height: 90vh;
            height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
        }

        /* Cash Breakdown Modal - Compact */
        .modal-container-cash {
            background: white;
            border-radius: 12px;
            width: 95%;
            max-width: 650px;
            max-height: 90vh;
            height: auto;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 2px solid #e5e7eb;
            flex-shrink: 0;
        }

        .modal-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: #6b7280;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .modal-body {
            flex: 1;
            overflow: hidden;
            padding: 0 20px 20px 20px;
            display: flex;
            flex-direction: column;
            min-height: 0;
            position: relative;
        }

        /* ===== SCROLLABLE TABLE BODY ===== */
        #modalTableScrollBody {
            flex: 1;
            overflow-y: auto !important;
            overflow-x: auto !important;
            min-height: 0;
            position: relative;
            height: 100%;
        }

        #modalTableScrollBody table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        #modalTableScrollBody thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        #modalTableScrollBody thead th {
            background-color: #8aecb0 !important;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
            border-bottom: 2px solid #000 !important;
        }

        /* Cash Modal Scroll Body */
        #cashModalScrollBody {
            flex: 1;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            min-height: 0;
            position: relative;
            height: 100%;
            padding: 10px 0;
        }

        /* Make sure the modal content takes full height */
        #printableModalContent {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        #printableCashContent {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        /* Modal content container */
        #modalContent {
            width: 100%;
            height: 100%;
            display: block;
        }

        #modalContent table {
            width: 100%;
            border-collapse: collapse;
        }

        #cashModalContent {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* ===== LOADING SPINNER ===== */
        #modalLoading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.92);
            z-index: 5;
            min-height: 300px;
        }

        #modalLoading .spinner-container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        #modalLoading .animate-spin {
            animation: spin 0.8s linear infinite;
            font-size: 3.5rem;
            color: #3b82f6;
        }

        #cashModalLoading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.92);
            z-index: 5;
            min-height: 200px;
        }

        #cashModalLoading .spinner-container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        #cashModalLoading .animate-spin {
            animation: spin 0.8s linear infinite;
            font-size: 2.5rem;
            color: #3b82f6;
        }

        #cashModalLoading .loading-text {
            color: #6b7280;
            font-size: 0.85rem;
            font-weight: 500;
        }

        #cashModalLoading .loading-subtext {
            color: #9ca3af;
            font-size: 0.7rem;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== TRANSACTION GROUP STYLES INSIDE MODAL ===== */
        #modalContent .transaction-group {
            display: table-row-group !important;
            box-shadow: 0 0 0 2px #830505, 0 2px 4px rgba(0,0,0,0.1) !important;
            border-radius: 6px !important;
            margin-bottom: 12px !important;
            background: white !important;
        }
        
        #modalContent .transaction-group .resultRow td {
            border-bottom: 1px solid #ffffff !important;
        }
        
        #modalContent .transaction-group .resultRow:last-child td {
            border-bottom: none !important;
        }
        
        #modalContent .transaction-group .total-row td {
            border-top: 2px dashed #8f0707 !important;
        }
        
        #modalContent .row-colored td {
            background-color: #ffffff !important;
        }
        
        #modalContent .row-white td {
            background-color: #ffffff !important;
        }
        
        #modalContent .resultRow:hover td {
            background-color: #ffffff !important;
        }
        
        #modalContent .total-row:hover td {
            background-color: #ffffff !important;
        }
        
        #modalContent .total-row td {
            background-color: #ffffff !important;
            font-weight: bold !important;
        }

        /* ===== CASH BREAKDOWN STYLES ===== */
        .cash-breakdown-modal {
            padding: 5px 0;
        }

        .cash-breakdown-modal .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }

        .cash-breakdown-modal .report-number {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1f2937;
        }

        .cash-breakdown-modal .report-date {
            font-size: 0.7rem;
            color: #6b7280;
        }

        .cash-breakdown-modal .section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cash-breakdown-modal .section-title i {
            color: #059669;
        }

        /* Compact Denomination Table */
        .denomination-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.7rem;
            margin-bottom: 10px;
        }

        .denomination-table th {
            background-color: #1e293b !important;
            color: white !important;
            padding: 5px 8px !important;
            font-size: 0.6rem !important;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.3px;
        }

        .denomination-table td {
            padding: 5px 8px !important;
            font-size: 0.7rem !important;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .denomination-table tr:hover td {
            background-color: #f8fafc !important;
        }

        .denomination-table .total-row td {
            background-color: #f0fdf4 !important;
            border-top: 2px solid #059669 !important;
            font-weight: bold !important;
            padding: 6px 8px !important;
        }

        .denomination-table .amount-col {
            text-align: right;
            font-weight: 500;
        }

        .denomination-table .count-col {
            text-align: center;
        }

        .denomination-table .label-col {
            text-align: left;
            font-weight: 500;
        }

        /* Variance Badge */
        .variance-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .variance-over {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .variance-short {
            background: #f0fdf4;
            color: #059669;
            border: 1px solid #bbf7d0;
        }

        .variance-zero {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }

        /* Summary Cards - Compact */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 6px;
            margin-top: 10px;
        }

        .summary-card {
            background: #f8fafc;
            padding: 8px 10px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .summary-card .label {
            font-size: 0.55rem;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        .summary-card .value {
            font-size: 0.9rem;
            font-weight: 700;
            margin-top: 2px;
        }

        .summary-card .value.positive {
            color: #059669;
        }
        .summary-card .value.negative {
            color: #dc2626;
        }
        .summary-card .value.neutral {
            color: #1f2937;
        }
        .summary-card .value.blue {
            color: #2563eb;
        }

        /* Print buttons in modal */
        .print-btn-modal {
            background: #059669;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .print-btn-modal:hover {
            background: #047857;
        }

        .print-btn-transaction {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .print-btn-transaction:hover {
            background: #2563eb;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Controls and layout */
        .controls-sticky {
            flex-shrink: 0;
            position: sticky;
            top: 0;
            z-index: 30;
            background: white;
            padding-bottom: 0px;
        }

        .pagination-sticky {
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
            z-index: 20;
            background: white;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .content-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 90px);
            max-height: calc(100vh - 90px);
            overflow: hidden;
        }

        .table-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        .table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        .table-scroll-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            min-height: 0;
            position: relative;
        }

        .bg-white.overflow-hidden {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .p-3.lg\:ml-64 {
            height: 100vh;
            overflow: hidden;
        }

        #contentDiv {
            height: 100%;
            overflow: hidden;
        }

        .bg-white.overflow-hidden.shadow-md.rounded-lg.p-4 {
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .p-4 {
            padding: 0.75rem !important;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            body * {
                visibility: hidden;
            }
            #printableModalContent, #printableModalContent * {
                visibility: visible;
            }
            #printableModalContent {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: white;
                padding: 20px;
                margin: 0;
                z-index: 99999;
                overflow-y: auto;
            }
            #printableModalContent .modal-print-header {
                display: block !important;
            }
            .modal-print-header {
                display: none;
            }
            .no-print {
                display: none !important;
            }
            #modalTableScrollBody {
                overflow: visible !important;
                height: auto !important;
            }
            
            #modalTableScrollBody thead th {
                border-bottom: 2px solid #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #8aecb0 !important;
            }
            
            #modalTableScrollBody thead tr {
                border-bottom: 2px solid #000 !important;
            }
            
            #modalTableScrollBody tfoot tr {
                border-top: 2px solid #000 !important;
            }
            
            #modalTableScrollBody thead th {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Cash Print Styles */
        @media print {
            #printableCashContent, #printableCashContent * {
                visibility: visible;
            }
            #printableCashContent {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: white;
                padding: 20px;
                margin: 0;
                z-index: 99999;
                overflow-y: auto;
            }
            #printableCashContent .modal-print-header {
                display: block !important;
            }
            .denomination-table th {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #1e293b !important;
            }
            .denomination-table .total-row td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .summary-card {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .modal-container-cash {
                max-width: 95%;
                width: 95%;
            }
            .modal-container-transaction {
                max-width: 95%;
                width: 95%;
            }
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            .cash-breakdown-modal .report-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .summary-table th,
            .summary-table td {
                font-size: 0.6rem !important;
                padding: 4px !important;
            }
        }
    </style>

    @section('page_title', 'Sales Report')

    {{-- NOTIFICATION --}}
    <div id="topNotification" class="fixed left-1/2 top-4 z-[60] hidden w-[min(92vw,24rem)] -translate-x-1/2 rounded-lg border px-4 py-3 shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i id="topNotificationIcon" class="uil text-xl"></i>
                <h1 id="topNotificationMessage" class="font-semibold"></h1>
            </div>
            <button type="button" class="topNotificationClose self-center" aria-label="Close notification">
                <i class="uil uil-times text-xl"></i>
            </button>
        </div>
    </div>

    <div class="lg:ml-64">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg pl-2 pr-2">
                <div class="content-wrapper">
                    {{-- CONTROLS SECTION --}}
                    <div class="controls-sticky">
                        <div class="my-2 flex justify-between items-end flex-wrap gap-2">
                            <!-- Title -->
                            <h1 class="text-2xl font-bold">
                                Sales Report
                            </h1>

                            <!-- Filters -->
                            <form method="POST" action="{{ route('sales-report.generate') }}" class="flex items-end gap-2" id="filterForm">
                                @csrf

                                <!-- Date Range -->
                                <div date-rangepicker class="flex items-end gap-2">
                                    <!-- From -->
                                    <div class="flex flex-col">
                                        <label for="startDate" class="text-gray-500 text-[11px] font-medium leading-none mb-1">
                                            From Date
                                        </label>

                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                                <svg aria-hidden="true" class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd">
                                                    </path>
                                                </svg>
                                            </div>

                                            <input name="startDate" id="startDate" value="{{ date('m/d/Y', strtotime($startDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 w-[110px] pl-7 p-1.5" placeholder="Start">
                                        </div>
                                    </div>

                                    <!-- To -->
                                    <div class="flex flex-col">
                                        <label for="endDate" class="text-gray-500 text-[11px] font-medium leading-none mb-1">
                                            To Date
                                        </label>

                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                                <svg aria-hidden="true" class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd">
                                                    </path>
                                                </svg>
                                            </div>

                                            <input name="endDate" id="endDate" value="{{ date('m/d/Y', strtotime($endDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 w-[110px] pl-7 p-1.5" placeholder="End">
                                        </div>
                                    </div>
                                </div>

                                <!-- Generate -->
                                <button type="submit" class="bg-blue-600 px-3 py-1.5 rounded-lg text-white font-bold text-xs hover:bg-blue-700 transition h-[30px]">
                                    Generate
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- SUMMARY TABLE --}}
                    <div class="table-container">
                        <div class="flex-1 flex flex-col min-h-0">
                            <div class="table-wrapper">
                                <div class="table-scroll-body">
                                    <table class="summary-table w-full text-xs text-left text-gray-500">
                                        <thead>
                                            <tr>
                                                <th class="text-center">REPORT #</th>
                                                <th class="text-center">COMPLETED</th>
                                                <th class="text-center">AR</th>
                                                <th class="text-center">CANCELLED</th>
                                                <th class="text-center">PAID</th>
                                                <th class="text-center">AR UNPAID</th>
                                                <th class="text-center">2307</th>
                                                <th class="text-center">TOTAL SALES</th>
                                                <th class="text-center">AR TOTALS</th>
                                                <th class="text-center">AR 2307</th>
                                                <th class="text-center">START DATE</th>
                                                <th class="text-center">END DATE</th>
                                                <th class="text-center">CASH BREAKDOWN</th>
                                                <th class="text-center">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($salesReports as $report)
                                                <tr class="clickable-row" onclick="openTransactionModal('{{ $report->id }}')">
                                                    <td class="text-center font-medium text-gray-900">{{ $report->trans_number }}</td>
                                                    <td class="text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $report->completed ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ number_format((int) $report->completed) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $report->ar ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ number_format((int) $report->ar) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $report->cancelled ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ number_format((int) $report->cancelled) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center text-green-600">₱ {{ number_format($report->paid_sales, 2) }}</td>
                                                    <td class="text-center text-red-600">₱ {{ number_format($report->ar_unpaid, 2) }}</td>
                                                    <td class="text-center text-blue-600">₱ {{ number_format($report->sales_report_form, 2) }}</td>
                                                    <td class="text-center text-indigo-600 font-bold">₱ {{ number_format($report->total_sales, 2) }}</td>
                                                    <td class="text-center text-purple-600">₱ {{ number_format($report->ar_collection_total, 2) }}</td>
                                                    <td class="text-center text-teal-600">₱ {{ number_format($report->ar_collection_Form, 2) }}</td>
                                                    <td class="text-center text-gray-500">{{ $report->start_date ? date('Y-m-d', strtotime($report->start_date)) : 'N/A' }}</td>
                                                    <td class="text-center text-gray-500">{{ $report->end_date ? date('Y-m-d', strtotime($report->end_date)) : 'N/A' }}</td>
                                                    <td class="text-center">
                                                        <button onclick="event.stopPropagation(); openCashBreakdownModal('{{ $report->id }}')" class="cash-breakdown-btn">
                                                            <i class="uil uil-money-bill-stack"></i> Cash
                                                        </button>
                                                    </td>                                                    
                                                    <td class="text-center">
                                                        <button onclick="event.stopPropagation(); openTransactionModal('{{ $report->id }}')" class="view-details-btn">
                                                            {{-- <i class="uil uil-eye"></i>  --}}View
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="14" class="text-center py-8 text-gray-400">
                                                        <i class="uil uil-file-search-alt text-3xl block mb-2"></i>
                                                        No sales reports found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- PAGINATION --}}
                <div class="pagination-sticky">
                    <div class="flex items-center justify-between px-2 mt-2 gap-2 overflow-x-auto">
                        @php
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = ($page - 1) * $perPage + 1;
                            $to = $page * $perPage;
                            if ($to > $menuCount) {
                                $to = $menuCount;
                            }
                            if ($menuCount == 0) {
                                $from = 0;
                            }

                            $baseUrl = route('sales-report.index', [
                                'startDate' => date('m/d/Y', strtotime($startDate)),
                                'endDate' => date('m/d/Y', strtotime($endDate)),
                                'branch' => $selectedBranch,
                            ]);
                        @endphp

                        <div class="flex-shrink-0">
                            <span class="text-xs text-gray-700 whitespace-nowrap">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $menuCount }}</span> Reports
                            </span>
                        </div>

                        <div class="flex-shrink-0">
                            <nav aria-label="Page navigation example" class="shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ $baseUrl . '&page=' . $prev }}" class="{{ $page == 1 ? 'pointer-events-none opacity-50' : '' }} block w-7 h-7 leading-7 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 text-xs">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="z-10 block font-semibold leading-7 text-center text-gray-500 bg-white border border-gray-300 w-7 h-7 text-xs">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ $baseUrl . '&page=' . $next }}" class="{{ $to == $menuCount ? 'pointer-events-none opacity-50' : '' }} block w-7 h-7 leading-7 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 text-xs">
                                            <i class="uil uil-angle-right-b"></i>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TRANSACTION DETAILS MODAL (EXISTING - KEPT INTACT) ===== --}}
    <div id="transactionModal" class="modal-overlay">
        <div class="modal-container-transaction">
            <div class="modal-header">
                <h2>
                    <i class="uil uil-receipt text-blue-600"></i>
                    Transaction Details
                    <span id="modalReportNumber" class="text-sm font-normal text-gray-500 ml-2"></span>
                </h2>
                <div class="flex items-center gap-2">
                    <button onclick="printModalContent()" class="print-btn-transaction">
                        <i class="uil uil-print"></i> Print
                    </button>
                    <button class="modal-close-btn" onclick="closeTransactionModal()">
                        <i class="uil uil-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="modalBody">
                <div id="printableModalContent" style="width:100%; height:100%; display:flex; flex-direction:column;">
                    <div class="table-scroll-body" id="modalTableScrollBody" style="flex:1; overflow-y:auto !important; overflow-x:auto !important; min-height:0; position:relative; height:100%;">
                        <!-- Loading Spinner -->
                        <div id="modalLoading">
                            <div class="spinner-container">
                                <i class="uil uil-spinner-alt animate-spin"></i>
                                <span class="loading-text">Loading transactions...</span>
                                <span class="loading-subtext">Please wait</span>
                            </div>
                        </div>
                        <!-- Content -->
                        <div id="modalContent" style="display: none; width:100%; height:100%;">
                            <!-- Transaction table will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== LINE 1014: CASH BREAKDOWN MODAL (NEW) added by ken 9-10-2026 ===== --}}
    <div id="cashBreakdownModal" class="modal-overlay">
        <div class="modal-container-cash">
            <div class="modal-header">
                <h2>
                    <i class="uil uil-money-bill-stack text-green-600"></i>
                    Cash Breakdown Details
                    <span id="cashModalReportNumber" class="text-sm font-normal text-gray-500 ml-2"></span>
                </h2>
                <div class="flex items-center gap-2">
                    <button onclick="printCashBreakdown()" class="print-btn-modal">
                        <i class="uil uil-print"></i> Print
                    </button>
                    <button class="modal-close-btn" onclick="closeCashBreakdownModal()">
                        <i class="uil uil-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="cashModalBody">
                <div id="printableCashContent" style="width:100%; height:100%; display:flex; flex-direction:column;">
                    <div id="cashModalScrollBody" style="flex:1; overflow-y:auto !important; overflow-x:hidden !important; min-height:0; position:relative; height:100%; padding:10px 0;">
                        <!-- Loading Spinner -->
                        <div id="cashModalLoading">
                            <div class="spinner-container">
                                <i class="uil uil-spinner-alt animate-spin"></i>
                                <span class="loading-text">Loading cash breakdown...</span>
                                <span class="loading-subtext">Please wait</span>
                            </div>
                        </div>
                        <!-- Content -->
                        <div id="cashModalContent" style="display: none; width:100%; height:100%;">
                            <!-- Cash breakdown will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var _token = $('input[name="_token"]').val();

            // ===== OPEN TRANSACTION MODAL (EXISTING - KEPT INTACT) =====
            window.openTransactionModal = function(reportId) {
                const modal = document.getElementById('transactionModal');
                const loading = document.getElementById('modalLoading');
                const content = document.getElementById('modalContent');
                const reportNumber = document.getElementById('modalReportNumber');
                const scrollBody = document.getElementById('modalTableScrollBody');
                
                // Show modal and loading state
                modal.classList.add('active');
                loading.style.display = 'flex';
                content.style.display = 'none';
                content.innerHTML = '';
                reportNumber.textContent = '';
                
                // Reset scroll position
                if (scrollBody) {
                    scrollBody.scrollTop = 0;
                }
                
                // Fetch transaction data
                $.ajax({
                    url: "{{ route('sales-report.transactions') }}",
                    method: "POST",
                    dataType: 'json',
                    timeout: 30000,
                    data: {
                        report_id: reportId,
                        _token: _token
                    },
                    success: function(response) {
                        loading.style.display = 'none';
                        content.style.display = 'block';
                        
                        if (response.success && response.html) {
                            content.innerHTML = response.html;
                            reportNumber.textContent = '(Report #' + (response.report_number || reportId) + ')';
                            
                            // Update print header info
                            document.getElementById('printHeaderInfo').textContent = 
                                'Report #: ' + (response.report_number || reportId) + 
                                ' | Generated: ' + new Date().toLocaleString();
                        } else {
                            content.innerHTML = `
                                <div class="flex justify-center items-center h-64 text-gray-400">
                                    <div class="text-center">
                                        <i class="uil uil-file-times text-4xl block"></i>
                                        <span class="text-sm mt-2 block">No transactions found</span>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    error: function(xhr) {
                        loading.style.display = 'none';
                        content.style.display = 'block';
                        
                        let errorMessage = 'Failed to load transactions';
                        if (xhr.status === 0) {
                            errorMessage = 'Connection timeout or network error';
                        } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        content.innerHTML = `
                            <div class="flex justify-center items-center h-64 text-red-400">
                                <div class="text-center">
                                    <i class="uil uil-exclamation-triangle text-4xl block"></i>
                                    <span class="text-sm mt-2 block">${errorMessage}</span>
                                    <span class="text-xs text-gray-400">Please try again</span>
                                </div>
                            </div>
                        `;
                    }
                });
            };
            
            // ===== CLOSE TRANSACTION MODAL (EXISTING) =====
            window.closeTransactionModal = function() {
                document.getElementById('transactionModal').classList.remove('active');
                document.getElementById('modalContent').innerHTML = '';
            };
            
            // ===== PRINT TRANSACTION MODAL CONTENT (EXISTING) =====
            window.printModalContent = function() {
                // Get the printable content
                const printableContent = document.getElementById('printableModalContent');
                const reportNumber = document.getElementById('modalReportNumber').textContent || 'Report';
                
                // Show the print header temporarily for capturing
                const printHeader = document.querySelector('.modal-print-header');
                if (printHeader) {
                    printHeader.style.display = 'block';
                }
                
                // Clone the content to avoid affecting the original
                const contentClone = printableContent.cloneNode(true);
                
                // Hide the print header in the clone after a small delay
                if (printHeader) {
                    printHeader.style.display = 'none';
                }
                
                // Create a new tab for printing
                const printTab = window.open('', '_blank');
                if (!printTab) {
                    alert('Please allow pop-ups for this site to print.');
                    return;
                }
                
                // Get current date/time for header
                const now = new Date();
                const startDate = '{{ $startDate ?? 'Start' }}';
                const endDate = '{{ $endDate ?? 'End' }}';
                const headerInfo = document.getElementById('printHeaderInfo')?.textContent || '';
                
                // Write the HTML content
                printTab.document.write('<!DOCTYPE html>');
                printTab.document.write('<html>');
                printTab.document.write('<head>');
                printTab.document.write('<title>Sales Report ' + reportNumber + '</title>');
                printTab.document.write('<style>');
                printTab.document.write(`
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 0;
                        padding: 15px;
                        background: white;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .print-container {
                        max-width: 1200px;
                        margin: 0 auto;
                        padding: 10px;
                    }
                    .report-header {
                        margin-bottom: 15px;
                    }
                    .report-title {
                        font-size: 18px;
                        font-weight: bold;
                        margin: 0;
                        text-align: left;
                        color: #000;
                    }
                    .report-subtitle {
                        font-size: 11px;
                        text-align: left;
                        margin: 2px 0 0 0;
                        color: #333;
                    }
                    .header-details {
                        display: flex;
                        justify-content: space-between;
                        font-size: 10px;
                        margin-top: 8px;
                        padding-top: 8px;
                        border-top: 1px solid #ccc;
                    }
                    .header-details-stack {
                        display: flex;
                        flex-direction: column;
                        gap: 2px;
                        text-align: left;
                    }
                    .header-details-right {
                        text-align: right;
                        min-width: 100px;
                    }
                    .header-details strong {
                        font-weight: 600;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10px;
                        margin-top: 8px;
                    }
                    th {
                        background-color: #8aecb0 !important;
                        border-bottom: 2px solid #000 !important;
                        padding: 5px 4px !important;
                        text-align: left;
                        font-weight: bold;
                        font-size: 9px;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    thead {
                        display: table-header-group !important;
                    }
                    tfoot {
                        display: table-footer-group !important;
                    }
                    .print-header-row td {
                        border: none !important;
                        padding: 0 !important;
                    }                        
                    td {
                        padding: 4px 4px !important;
                        border-bottom: 1px solid #e5e7eb;
                        text-align: center;
                        font-size: 9px;
                    }
                    .transaction-group {
                        display: table-row-group !important;
                        box-shadow: 0 0 0 1px #830505, 0 2px 4px rgba(0,0,0,0.1) !important;
                        border-radius: 4px !important;
                        margin-bottom: 6px !important;
                        background: white !important;
                    }
                    .transaction-group .resultRow td {
                        border-bottom: 1px solid #ffffff !important;
                    }
                    .transaction-group .resultRow:last-child td {
                        border-bottom: none !important;
                    }
                    .transaction-group .total-row td {
                        border-top: 2px dashed #8f0707 !important;
                        background-color: #ffffff !important;
                        font-weight: bold !important;
                    }
                    .row-colored td {
                        background-color: #ffffff !important;
                    }
                    .row-white td {
                        background-color: #ffffff !important;
                    }
                    .total-row td {
                        background-color: #ffffff !important;
                        font-weight: bold !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                    .grand-total-row td {
                        font-weight: bold !important;
                        font-size: 10px !important;
                        background-color: #ffffff !important;
                        border-top: 2px solid #000 !important;
                    }
                    .text-left { text-align: left !important; }
                    .text-right { text-align: right !important; }
                    .text-center { text-align: center !important; }
                    .font-bold { font-weight: bold !important; }
                    
                    .page-number-container {
                        text-align: right;
                        font-size: 10px;
                        margin-top: 15px;
                        padding-top: 5px;
                        border-top: 1px solid #eee;
                    }
                    .page-number {
                        font-weight: normal;
                    }
                    
                    @page {
                        size: landscape;
                        margin: 10mm 12mm;
                    }
                    
                    @media print {
                        body { margin: 0; padding: 0; }
                        .no-print { display: none !important; }
                        .print-container { padding: 5px; }
                        th { 
                            background-color: #8aecb0 !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                    }
                `);
                printTab.document.write('</style>');
                printTab.document.write('</head>');
                printTab.document.write('<body>');
                printTab.document.write('<div class="print-container">');
                
                // Build the header
                const headerHtml = `
                    <div class="report-header">
                        <div class="report-title">SALES DETAIL REPORT </div>
                        <div class="report-subtitle">${headerInfo}</div>
                        <div class="header-details">
                            <div class="header-details-stack">
                                <div><strong>Print Out Date:</strong> ${now.toLocaleString()}</div>
                                <div><strong>Fiscal Year:</strong> 2025-01-01 - 2026-12-31 (Active)</div>
                                <div><strong>Period:</strong> ${startDate} - ${endDate}</div>
                                <div><strong>Printed By:</strong> {{ $printedBy ?? 'N/A' }}</div>                                 
                            </div>
                            <div class="header-details-right">
                                <div><br></div>
                                <div><br></div>
                                <div><br></div>                            
                                <div><strong>Page: <span class="page-number" id="pageNumberDisplay">1 of 1</span></strong></div>   
                            </div>                     
                        </div>
                    </div>
                `;
                
                // Get the HTML content from the clone
                let htmlContent = contentClone.innerHTML;

                // Remove any existing header from the content
                htmlContent = htmlContent.replace(/<div class="modal-print-header"[^>]*>.*?<\/div>/s, '');

                // Insert the header as a repeating <thead> row so it prints on every page
                const theadMatch = htmlContent.match(/<thead[^>]*>/i);
                if (theadMatch) {
                    const theadOpenIdx = htmlContent.indexOf(theadMatch[0]) + theadMatch[0].length;
                    const headerRow = `<tr class="print-header-row"><td colspan="11" style="padding:0;border:none;">${headerHtml}</td></tr>`;
                    htmlContent = htmlContent.slice(0, theadOpenIdx) + headerRow + htmlContent.slice(theadOpenIdx);
                } else {
                    htmlContent = headerHtml + htmlContent;
                }
                
                // Write the content
                printTab.document.write(htmlContent);
                
                // Close the container div
                printTab.document.write('</div>');
                
                // Add JavaScript to handle page numbers
                printTab.document.write(`
                    <script>
                        function updatePageNumbers() {
                            var pageNumberElements = document.querySelectorAll('.page-number');
                            var totalPages = 1;
                            var container = document.querySelector('.print-container');
                            var table = container ? container.querySelector('table') : null;
                            
                            if (table) {
                                var tableHeight = table.offsetHeight;
                                var pageHeight = 650;
                                totalPages = Math.max(1, Math.ceil(tableHeight / pageHeight));
                            }
                            
                            var groups = document.querySelectorAll('.transaction-group');
                            if (groups.length > 0) {
                                var extraPages = 0;
                                groups.forEach(function(group) {
                                    var rows = group.querySelectorAll('tr');
                                    if (rows.length > 15) {
                                        extraPages += Math.ceil(rows.length / 20);
                                    }
                                });
                                totalPages = Math.max(totalPages, extraPages + 1);
                            }
                            
                            var currentPage = 1;
                            pageNumberElements.forEach(function(el, index) {
                                if (index > 0) {
                                    currentPage = index + 1;
                                }
                                el.textContent = currentPage + ' of ' + totalPages;
                            });
                        }
                        
                        if (document.readyState === 'complete') {
                            setTimeout(updatePageNumbers, 100);
                        } else {
                            window.addEventListener('load', function() {
                                setTimeout(updatePageNumbers, 200);
                            });
                        }
                        
                        window.addEventListener('beforeprint', function() {
                            setTimeout(updatePageNumbers, 300);
                        });
                        
                        window.addEventListener('afterprint', function() {
                            setTimeout(updatePageNumbers, 100);
                        });
                    <\/script>
                `);
                
                printTab.document.write('</body>');
                printTab.document.write('</html>');
                printTab.document.close();
                
                // Wait for content to load then print
                printTab.onload = function() {
                    printTab.focus();
                    setTimeout(function() {
                        printTab.print();
                    }, 800);
                };
            };

            // ===== OPEN CASH BREAKDOWN MODAL (NEW) =====
            window.openCashBreakdownModal = function(reportId) {
                const modal = document.getElementById('cashBreakdownModal');
                const loading = document.getElementById('cashModalLoading');
                const content = document.getElementById('cashModalContent');
                const reportNumber = document.getElementById('cashModalReportNumber');
                const scrollBody = document.getElementById('cashModalScrollBody');

                // Show modal and loading state
                modal.classList.add('active');
                loading.style.display = 'flex';
                content.style.display = 'none';
                content.innerHTML = '';
                reportNumber.textContent = '';

                // Reset scroll position
                if (scrollBody) {
                    scrollBody.scrollTop = 0;
                }

                // Fetch cash breakdown data
                $.ajax({
                    url: "{{ route('sales-report.cash-breakdown') }}",
                    method: "POST",
                    dataType: 'json',
                    timeout: 30000,
                    data: {
                        report_id: reportId,
                        _token: _token
                    },
                    success: function(response) {
                        loading.style.display = 'none';
                        content.style.display = 'block';

                        if (response.success && response.html) {
                            content.innerHTML = response.html;
                            reportNumber.textContent = '(Report #' + (response.report_number ||
                                reportId) + ')';

                            // Update print header info
                            document.getElementById('cashPrintHeaderInfo').textContent =
                                'Report #: ' + (response.report_number || reportId) +
                                ' | Generated: ' + new Date().toLocaleString();
                        } else {
                            content.innerHTML = `
                                    <div class="flex justify-center items-center h-64 text-gray-400">
                                        <div class="text-center">
                                            <i class="uil uil-file-times text-4xl block"></i>
                                            <span class="text-sm mt-2 block">No cash breakdown found</span>
                                        </div>
                                    </div>
                                `;
                        }
                    },
                    error: function(xhr) {
                        loading.style.display = 'none';
                        content.style.display = 'block';

                        let errorMessage = 'Failed to load cash breakdown';
                        if (xhr.status === 0) {
                            errorMessage = 'Connection timeout or network error';
                        } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        content.innerHTML = `
                                <div class="flex justify-center items-center h-64 text-red-400">
                                    <div class="text-center">
                                        <i class="uil uil-exclamation-triangle text-4xl block"></i>
                                        <span class="text-sm mt-2 block">${errorMessage}</span>
                                        <span class="text-xs text-gray-400">Please try again</span>
                                    </div>
                                </div>
                            `;
                    }
                });
            };

            // ===== CLOSE CASH BREAKDOWN MODAL (NEW) =====
            window.closeCashBreakdownModal = function() {
                document.getElementById('cashBreakdownModal').classList.remove('active');
                document.getElementById('cashModalContent').innerHTML = '';
            };

            // ===== PRINT CASH BREAKDOWN (NEW) =====
            window.printCashBreakdown = function() {
                const printableContent = document.getElementById('printableCashContent');
                const reportNumber = document.getElementById('cashModalReportNumber').textContent || 'Report';

                const printHeader = document.querySelector('.modal-print-header');
                if (printHeader) {
                    printHeader.style.display = 'block';
                }

                const contentClone = printableContent.cloneNode(true);

                if (printHeader) {
                    printHeader.style.display = 'none';
                }

                const printTab = window.open('', '_blank');
                if (!printTab) {
                    alert('Please allow pop-ups for this site to print.');
                    return;
                }

                const now = new Date();
                const startDate = '{{ $startDate ?? 'Start' }}';
                const endDate = '{{ $endDate ?? 'End' }}';
                const headerInfo = document.getElementById('cashPrintHeaderInfo')?.textContent || '';

                printTab.document.write('<!DOCTYPE html>');
                printTab.document.write('<html>');
                printTab.document.write('<head>');
                printTab.document.write('<title>Cash Breakdown Report ' + reportNumber + '</title>');
                printTab.document.write('<style>');
                printTab.document.write(`
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 0;
                            padding: 15px;
                            background: white;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        * {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .print-container {
                            max-width: 700px;
                            margin: 0 auto;
                            padding: 10px;
                        }
                        .report-header {
                            margin-bottom: 15px;
                        }
                        .report-title {
                            font-size: 16px;
                            font-weight: bold;
                            margin: 0;
                            text-align: left;
                            color: #000;
                        }
                        .report-subtitle {
                            font-size: 10px;
                            text-align: left;
                            margin: 2px 0 0 0;
                            color: #333;
                        }
                        .header-details {
                            display: flex;
                            justify-content: space-between;
                            font-size: 9px;
                            margin-top: 8px;
                            padding-top: 8px;
                            border-top: 1px solid #ccc;
                        }
                        .header-details-stack {
                            display: flex;
                            flex-direction: column;
                            gap: 2px;
                            text-align: left;
                        }
                        .header-details-right {
                            text-align: right;
                            min-width: 100px;
                        }
                        .header-details strong {
                            font-weight: 600;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 9px;
                            margin-top: 8px;
                        }
                        th {
                            background-color: #1e293b !important;
                            color: white !important;
                            border-bottom: 2px solid #000 !important;
                            padding: 4px 8px !important;
                            text-align: center;
                            font-weight: bold;
                            font-size: 8px;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        td {
                            padding: 4px 8px !important;
                            border-bottom: 1px solid #e5e7eb;
                            text-align: center;
                            font-size: 8px;
                        }
                        .total-row td {
                            background-color: #f0fdf4 !important;
                            border-top: 2px solid #059669 !important;
                            font-weight: bold !important;
                        }
                        .no-print {
                            display: none !important;
                        }
                        .text-left { text-align: left !important; }
                        .text-right { text-align: right !important; }
                        .text-center { text-align: center !important; }
                        .font-bold { font-weight: bold !important; }
                        
                        .page-number-container {
                            text-align: right;
                            font-size: 9px;
                            margin-top: 15px;
                            padding-top: 5px;
                            border-top: 1px solid #eee;
                        }
                        .page-number {
                            font-weight: normal;
                        }
                        
                        .summary-cards {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 6px;
                            margin-top: 10px;
                        }
                        .summary-card {
                            background: #f8fafc;
                            padding: 6px 8px;
                            border-radius: 4px;
                            text-align: center;
                            border: 1px solid #e5e7eb;
                        }
                        .summary-card .label {
                            font-size: 7px;
                            text-transform: uppercase;
                            color: #6b7280;
                            font-weight: 600;
                        }
                        .summary-card .value {
                            font-size: 10px;
                            font-weight: 700;
                            margin-top: 2px;
                        }
                        .variance-badge {
                            padding: 2px 8px;
                            border-radius: 10px;
                            font-size: 8px;
                            font-weight: 600;
                            display: inline-block;
                        }
                        
                        @page {
                            size: portrait;
                            margin: 10mm 12mm;
                        }
                        
                        @media print {
                            body { margin: 0; padding: 0; }
                            .no-print { display: none !important; }
                            .print-container { padding: 5px; }
                            th { 
                                background-color: #1e293b !important;
                                color: white !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }
                        }
                    `);
                printTab.document.write('</style>');
                printTab.document.write('</head>');
                printTab.document.write('<body>');
                printTab.document.write('<div class="print-container">');

                const headerHtml = `
                        <div class="report-header">
                            <div class="report-title">CASH BREAKDOWN REPORT</div>
                            <div class="report-subtitle">${headerInfo}</div>
                            <div class="header-details">
                                <div class="header-details-stack">
                                    <div><strong>Print Out Date:</strong> ${now.toLocaleString()}</div>
                                    <div><strong>Period:</strong> ${startDate} - ${endDate}</div>
                                    <div><strong>Printed By:</strong> {{ $printedBy ?? 'N/A' }}</div>                                 
                                </div>
                                <div class="header-details-right">
                                    <div><strong>Page: <span class="page-number">1 of 1</span></strong></div>   
                                </div>                     
                            </div>
                        </div>
                    `;

                let htmlContent = contentClone.innerHTML;
                htmlContent = htmlContent.replace(/<div class="modal-print-header"[^>]*>.*?<\/div>/s, '');

                const theadMatch = htmlContent.match(/<thead[^>]*>/i);
                if (theadMatch) {
                    const theadOpenIdx = htmlContent.indexOf(theadMatch[0]) + theadMatch[0].length;
                    const headerRow =
                        `<tr class="print-header-row"><td colspan="13" style="padding:0;border:none;">${headerHtml}</td></tr>`;
                    htmlContent = htmlContent.slice(0, theadOpenIdx) + headerRow + htmlContent.slice(theadOpenIdx);
                } else {
                    htmlContent = headerHtml + htmlContent;
                }

                printTab.document.write(htmlContent);
                printTab.document.write('</div>');
                printTab.document.write('</body>');
                printTab.document.write('</html>');
                printTab.document.close();

                printTab.onload = function() {
                    printTab.focus();
                    setTimeout(function() {
                        printTab.print();
                    }, 500);
                };
            };

            // Close transaction modal on overlay click
            document.getElementById('transactionModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeTransactionModal();
                }
            });

            // Close cash breakdown modal on overlay click
            document.getElementById('cashBreakdownModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeCashBreakdownModal();
                }
            });

            // Close modals on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTransactionModal();
                    closeCashBreakdownModal();
                }
            });

            // ===== SHOW TOP NOTIFICATION =====
            window.showTopNotification = function(message, type = 'success') {
                const notification = document.getElementById('topNotification');
                const icon = document.getElementById('topNotificationIcon');
                const messageText = document.getElementById('topNotificationMessage');

                if (!notification || !icon || !messageText) return;

                const isError = type === 'error';
                notification.className =
                    `fixed left-1/2 top-4 z-[60] w-[min(92vw,24rem)] -translate-x-1/2 rounded-lg border px-4 py-3 shadow-lg ${isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'}`;
                icon.className = `uil ${isError ? 'uil-times-circle' : 'uil-cloud-check'} text-xl`;
                messageText.textContent = message || (isError ? 'Something went wrong.' : 'Success');
                notification.classList.remove('hidden');

                clearTimeout(window.topNotificationTimer);
                window.topNotificationTimer = setTimeout(() => {
                    notification.classList.add('hidden');
                }, 4000);
            };
        });
    </script>
</x-app-layout>