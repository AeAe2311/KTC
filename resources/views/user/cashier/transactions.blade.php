<x-app-layout>
    <style>
        /* Custom styles */
        .amount-edit-mode {
            border-color: #10b981 !important;
            background-color: #ecfdf5 !important;
        }
        .amount-edit-mode:focus {
            ring: 2px solid #10b981;
            border-color: #10b981;
        }
        .payment-edit-mode {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
        }
        
        /* COMPLETELY FIXED MODAL STYLES */
        #transactionDetailModal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 9999 !important;
            display: none !important;
            background: #1118244f !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 20px !important;
            box-sizing: border-box !important;
        }

        #transactionDetailModal.show {
            display: flex !important;
        }

        #transactionDetailModal.hidden {
            display: none !important;
        }

        #transactionDetailModal .modal-container {
            position: relative !important;
            width: 100% !important;
            max-width: 640px !important;
            max-height: 90vh !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
        }

        #transactionDetailModal .modal-content {
            background: white !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            max-height: 85vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            width: 100% !important;
            position: relative !important;
        }

        #transactionDetailModal .modal-header {
            flex-shrink: 0 !important;
            padding: 16px 20px !important;
            border-bottom: 1px solid #e5e7eb !important;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            background: linear-gradient(to right, #1e40af, #1d4ed8) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
        }

        #transactionDetailModal .modal-header h3 {
            font-size: 1.1rem !important;
        }

        #transactionDetailModal .modal-body {
            flex: 1 !important;
            overflow-y: auto !important;
            padding: 20px 24px !important;
            max-height: calc(85vh - 140px) !important;
            font-size: 15px !important;
        }

        #transactionDetailModal .modal-footer {
            flex-shrink: 0 !important;
            padding: 12px 20px !important;
            border-top: 1px solid #e5e7eb !important;
            background: white !important;
            border-radius: 0 0 0.75rem 0.75rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 12px !important;
            position: sticky !important;
            bottom: 0 !important;
            z-index: 10 !important;
        }

        .closeDetailModal {
            cursor: pointer !important;
            z-index: 20 !important;
            position: relative !important;
            pointer-events: auto !important;
            background: transparent !important;
            border: none !important;
            padding: 8px !important;
            border-radius: 8px !important;
            transition: background 0.2s !important;
        }

        .closeDetailModal:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }

        #closeDetailModalBtn, #cancelDetailBtn {
            cursor: pointer !important;
            z-index: 20 !important;
            position: relative !important;
            pointer-events: auto !important;
        }

        #cancelDetailBtn {
            padding: 8px 20px !important;
        }

        .payment-row {
            transition: all 0.2s ease;
            padding: 8px 4px !important;
        }
        .payment-row:hover {
            background-color: #f8fafc;
        }
        .payment-value-input {
            width: 120px !important;
            text-align: right;
            font-weight: 600;
            color: #059669;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.35rem 0.6rem !important;
            font-size: 0.95rem !important;
        }
        .payment-value-input:focus {
            border-color: #10b981;
            ring: 2px solid #10b981;
            outline: none;
        }
        .payment-value-input:disabled {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
        }
        .payment-value-input.edit-mode {
            border-color: #10b981;
            background-color: #ecfdf5;
        }
        .payment-value-input.edit-mode:focus {
            ring: 2px solid #10b981;
            border-color: #10b981;
        }
        .payment-select {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.35rem 0.6rem !important;
            font-size: 0.95rem !important;
            background-color: #f9fafb;
            min-width: 140px !important;
        }
        .payment-select:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }
        .payment-select:not(:disabled) {
            background-color: #eff6ff;
            border-color: #3b82f6;
            cursor: pointer;
        }
        .payment-edit-btn {
            color: #2563eb;
            padding: 0.35rem !important;
            border-radius: 9999px;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1rem !important;
        }
        .payment-edit-btn:hover {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .payment-edit-btn.active {
            color: #059669;
        }
        .payment-edit-btn.active:hover {
            background-color: #ecfdf5;
            color: #047857;
        }
        .payments-container {
            max-height: 250px !important;
            overflow-y: auto;
        }
        .payments-container::-webkit-scrollbar {
            width: 4px;
        }
        .payments-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .payments-container::-webkit-scrollbar-thumb {
            background: #c1c7cd;
            border-radius: 10px;
        }
        .payments-container::-webkit-scrollbar-thumb:hover {
            background: #a0a7ae;
        }

        #transactionDetailModal .modal-body .text-sm {
            font-size: 0.95rem !important;
        }

        #transactionDetailModal .modal-body .font-bold {
            font-size: 1rem !important;
        }

        #transactionDetailModal .modal-body .text-lg {
            font-size: 1.1rem !important;
        }

        #transactionDetailModal .modal-body .flex.items-center.justify-between {
            padding: 8px 0 !important;
        }

        .modal-loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            gap: 10px;
        }
        .modal-loading-spinner i {
            font-size: 24px;
            color: #1e40af;
        }
        .modal-loading-spinner span {
            color: #6b7280;
            font-size: 14px;
        }

        .whitespace-pre-line {
            white-space: pre-line !important;
        }

        /* ============================================ */
        /* TOOLTIP STYLES */
        /* ============================================ */
        
        #inventoryTable {
            overflow: visible !important;
        }

        #inventoryTable tbody tr {
            position: relative;
        }

        #inventoryTable td {
            overflow: visible !important;
            position: static !important;
        }

        #inventoryTable td:first-child {
            overflow: visible !important;
        }

        .result-row-disabled {
            opacity: 0.85 !important;
            background-color: #fef2f2 !important;
            border-left: 4px solid #ef4444 !important;
            position: relative;
            transition: background-color 0.2s ease;
        }

        .result-row-disabled:hover {
            background-color: #fee2e2 !important;
        }

        .result-row-disabled .row-tooltip-trigger {
            position: relative;
            display: block;
            width: 100%;
            height: 100%;
            min-height: 100%;
        }

        .result-row-disabled .row-disabled-tooltip {
            position: fixed !important;
            z-index: 99999999 !important;
            background-color: #1f2937;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 500;
            white-space: normal;
            max-width: 350px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
            cursor: default;
            user-select: none;
            transform: translateY(-8px);
            line-height: 1.5;
        }

        .result-row-disabled:hover .row-disabled-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .row-disabled-tooltip::after {
            content: "";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent;
        }

        .row-disabled-tooltip[data-arrow="down"]::after {
            top: 100%;
            border-color: #1f2937 transparent transparent transparent;
        }

        .row-disabled-tooltip[data-arrow="up"]::after {
            bottom: 100%;
            border-color: transparent transparent #1f2937 transparent;
        }

        .result-row-disabled .checkbox-tooltip-wrapper {
            pointer-events: auto !important;
        }

        .result-row-disabled .checkbox-tooltip-wrapper input[type="checkbox"] {
            pointer-events: none !important;
        }

        .result-row-disabled .openDetailBtn {
            cursor: not-allowed !important;
            opacity: 0.5;
            pointer-events: none !important;
        }

        .result-row-disabled .edit-icon-disabled-wrapper {
            pointer-events: auto !important;
        }

        .result-row-disabled .edit-icon-disabled-wrapper span {
            pointer-events: auto !important;
            cursor: default !important;
        }

        .checkbox-tooltip-wrapper {
            position: relative !important;
            display: inline-block;
            z-index: 1;
        }

        .checkbox-tooltip-wrapper .tooltip-text {
            position: fixed !important;
            z-index: 99999999 !important;
            background-color: #1f2937;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
            cursor: default;
            user-select: none;
            transform: translateY(-5px);
        }

        .checkbox-tooltip-wrapper .tooltip-text.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .checkbox-tooltip-wrapper .tooltip-text::after {
            content: "";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent;
        }

        .checkbox-tooltip-wrapper .tooltip-text[data-arrow="down"]::after {
            top: 100%;
            border-color: #1f2937 transparent transparent transparent;
        }

        .checkbox-tooltip-wrapper .tooltip-text[data-arrow="up"]::after {
            bottom: 100%;
            border-color: transparent transparent #1f2937 transparent;
        }

        .edit-icon-disabled-wrapper {
            position: relative !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .edit-icon-disabled-wrapper .edit-icon-tooltip {
            position: fixed !important;
            z-index: 99999999 !important;
            background-color: #1f2937;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            white-space: normal;
            max-width: 300px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
            cursor: default;
            user-select: none;
            transform: translateY(-5px);
            word-wrap: break-word;
        }

        .edit-icon-disabled-wrapper .edit-icon-tooltip.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .edit-icon-disabled-wrapper .edit-icon-tooltip::after {
            content: "";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent;
        }

        .edit-icon-disabled-wrapper .edit-icon-tooltip[data-arrow="down"]::after {
            top: 100%;
            border-color: #1f2937 transparent transparent transparent;
        }

        .edit-icon-disabled-wrapper .edit-icon-tooltip[data-arrow="up"]::after {
            bottom: 100%;
            border-color: transparent transparent #1f2937 transparent;
        }

        .openDetailBtn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .result-row-disabled td,
        .result-row-disabled th {
            color: #383d46 !important;
        }

        .result-row-disabled .font-bold {
            color: #383d46 !important;
        }

        .disabled-row-icon {
            display: inline-block;
            margin-right: 4px;
            font-size: 14px;
        }

        /* ============================================ */
        /* APPROVAL STATUS FILTER STYLES */
        /* ============================================ */
        .filter-select {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.375rem 0.5rem; /* Smaller padding */
            font-size: 0.75rem; /* Smaller text */
            color: #111827;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 140px; /* Smaller min-width */
        }
        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        .filter-select:hover {
            background-color: #f3f4f6;
        }

        .resultRow.filtered-out {
            display: none !important;
        }

        #noFilterResults {
            display: none;
            padding: 24px;
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
        }
        #noFilterResults.show {
            display: table-row;
        }

        /* ============================================ */
        /* FULL SCREEN LOADING OVERLAY - REAL PROCESS BASED */
        /* ============================================ */
        #globalLoadingOverlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 999999 !important;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
            pointer-events: all !important;
        }
        #globalLoadingOverlay.show {
            display: flex !important;
        }
        #globalLoadingOverlay .loading-card {
            background: white;
            border-radius: 1rem;
            padding: 32px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            min-width: 260px;
            text-align: center;
        }
        #globalLoadingOverlay .loading-spinner {
            width: 56px;
            height: 56px;
            border: 4px solid #e5e7eb;
            border-top-color: #059669;
            border-radius: 50%;
            animation: spinLoader 0.8s linear infinite;
        }
        @keyframes spinLoader {
            to { transform: rotate(360deg); }
        }
        #globalLoadingOverlay .loading-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
        }
        #globalLoadingOverlay .loading-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: -8px;
        }
        #globalLoadingOverlay .loading-dots {
            display: inline-block;
            width: 20px;
            text-align: left;
        }
        #globalLoadingOverlay .loading-dots::after {
            content: '';
            animation: dots 1.4s steps(4, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }

        /* Blur the page behind the overlay while approving */
        body.approval-processing #contentDiv,
        body.approval-processing #approvalModal {
            filter: blur(2px);
            pointer-events: none !important;
            user-select: none !important;
        }

        /* ============================================ */
        /* STICKY PAGINATION & SCROLLABLE TABLE */
        /* ============================================ */
        .pagination-sticky {
            position: sticky;
            bottom: 0;
            background-color: white;
            z-index: 20;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            padding-bottom: 4px;
            margin-top: 0;
        }

        .table-scroll-container {
            overflow-y: auto;
            max-height: calc(100vh - 220px); /* Adjusted for smaller header */
            min-height: 500px;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
        }

        .table-scroll-container table {
            width: 100%;
            margin-bottom: 0;
        }

        .table-scroll-container thead {
            position: sticky;
            top: 0;
            z-index: 15;
            background-color: #e5e7eb;
        }

        .table-scroll-container thead th {
            background-color: #e5e7eb;
            box-shadow: 0 1px 0 0 #d1d5db;
        }

        .table-scroll-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .page-input {
            width: 60px;
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 0.35rem;
            font-size: 0.8rem;
            background: white;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .page-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
        }
        .page-input::-webkit-outer-spin-button,
        .page-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .page-input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    @section('page_title', '')

    {{-- GLOBAL LOADING OVERLAY --}}
    <div id="globalLoadingOverlay" aria-hidden="true">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-title" id="globalLoadingTitle">Processing approval<span class="loading-dots"></span></div>
            <div class="loading-subtitle" id="globalLoadingSubtitle">Please wait, do not close this window.</div>
        </div>
    </div>

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
    @if (session('message'))
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                window.showTopNotification && window.showTopNotification(@json(session('message')), 'success');
            });
        </script>
    @endif
    {{-- NOTIFICATION END --}}

    {{-- RETURN MODAL --}}
    <button data-modal-target="itemReturnModal" data-modal-toggle="itemReturnModal" id="openreturnModal" class="hidden" type="button"></button>
    
    <div id="itemReturnModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-orange-600"></i>
                        <span class="text-orange-600 text-base md:text-lg lg:text-xl">Return Item</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemReturnModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500 text-center">
                        Are you sure you want to return this item to table?
                    </p>
                    <p class="text-xs md:text-base leading-relaxed text-red-500 font-bold text-center border-t-2 py-1 border-dark">
                        NOTE: This action will return the transaction to the table.
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a href="javascript:void(0)" id="returnAccept" data-slug="" data-modal-hide="itemReturnModal" class="w-24 text-white bg-gradient-to-br from-blue-800 to-blue-500 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                    <button data-modal-hide="itemReturnModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- RETURN MODAL END --}}

    {{-- APPROVAL MODAL --}}
    <div id="approvalModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-hidden bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30">
        <div class="flex min-h-full items-center justify-center">
            <div class="relative w-full max-w-5xl max-h-[90vh] bg-white rounded-lg shadow flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 text-lg">Approve Selected Transactions</h3>
                    <div class="flex items-center gap-2">
                        <button id="removeAllApprovalBtn" type="button" class="text-sm text-red-600 hover:text-red-800 rounded-lg border border-red-200 bg-red-50 px-3 py-1 font-semibold">
                            Remove All
                        </button>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center" data-modal-hide="approvalModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 space-y-4 overflow-hidden flex-1 min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                        <div class="bg-slate-50 border rounded-lg p-3">
                            <p class="text-gray-500">Count of selected transaction</p>
                            <p id="modalSelectedCount" class="font-bold text-lg">0</p>
                        </div>
                        <div class="bg-slate-50 border rounded-lg p-3">
                            <p class="text-gray-500">Total Amount</p>
                            <p id="modalTotalAmount" class="font-bold text-lg">₱ 0.00</p>
                        </div>
                        <div class="bg-slate-50 border rounded-lg p-3">
                            <p class="text-gray-500">Payments</p>
                            <p id="modalPaymentsSummary" class="font-bold text-sm whitespace-pre-line">-</p>
                        </div>
                        <div class="bg-slate-50 border rounded-lg p-3">
                            <p class="text-gray-500">Total AR</p>
                            <p id="modalArAmount" class="font-bold text-lg">₱ 0.00</p>
                        </div>
                    </div>
                    <div class="border rounded-lg overflow-hidden flex-1 min-h-0">
                        <div class="max-h-[40vh] overflow-y-auto overscroll-contain">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="bg-slate-100 text-slate-700 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Transaction #</th>
                                        <th class="px-3 py-2">Amount</th>
                                        <th class="px-3 py-2">Mode of Payment</th>
                                        <th class="px-3 py-2">AR</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="approvalModalBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end px-6 py-3 border-t rounded-b gap-2">
                    <button type="button" data-modal-hide="approvalModal" class="text-gray-500 bg-white hover:bg-gray-100 rounded-lg border border-gray-200 px-5 py-2.5 text-sm">Cancel</button>
                    <button id="confirmApprovalBtn" type="button" class="text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg px-5 py-2.5 text-sm font-semibold">Approve</button>
                </div>
            </div>
        </div>
    </div>
    {{-- APPROVAL MODAL END --}}

    {{-- VIEW MODAL (Original) --}}
    <div id="viewModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full pl-2 pr-2  overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-start justify-between pl-2 pr-2 border-b rounded-t">
                    <h3 id="viewTranNumber" class="text-xl font-semibold text-gray-900">
                        Transaction Numbers
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="viewModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        Edit Amount
                        <div id="viewOrders" class="font-bold text-xl">
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between p-6 border-t border-gray-200 rounded-b">
                    <div class="flex items-center space-x-2">
                        <a id="printUrl" target="_blank" class="text-white bg-blue-700 hover:bg-blue-800 rounded-lg text-sm px-5 py-2.5">
                            Print Receipt
                        </a>
                        <button data-modal-hide="viewModal" class="text-gray-500 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg text-sm px-5 py-2.5">
                            Close
                        </button>
                        @if (auth()->user()->role == '1')
                        <a id="printOnlyUrl" target="_blank" class="text-white bg-red-700 hover:bg-red-800 rounded-lg text-sm px-5 py-2.5">
                            Print Only
                        </a>
                        @endif
                    </div>
                    <a id="saveBtn" class="text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg text-sm px-5 py-2.5">
                        Save
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION DETAIL MODAL --}}
    <div id="transactionDetailModal" tabindex="-1" aria-hidden="true" class="hidden">
        <div class="modal-container">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="font-semibold text-white flex items-center">
                        <i class="uil uil-receipt mr-2 text-xl"></i>
                        <span class="text-base md:text-lg">Transaction Details</span>
                    </h3>
                    <button type="button" class="text-gray-800 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="transactionDetailModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="detailSlug" value="">
                    <input type="hidden" id="detailOriginalAmount" value="0">
                    
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <i class="uil uil-receipt text-blue-500 text-lg"></i>
                            <span class="text-sm font-medium text-gray-600">Transaction #</span>
                        </div>
                        <span id="detailTranNumber" class="text-sm font-bold text-gray-900">-</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <i class="uil uil-calendar-alt text-blue-500 text-lg"></i>
                            <span class="text-sm font-medium text-gray-600">Date</span>
                        </div>
                        <span id="detailDate" class="text-sm font-medium text-gray-700">-</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <i class="uil uil-user text-blue-500 text-lg"></i>
                            <span class="text-sm font-medium text-gray-600">Client</span>
                        </div>
                        <span id="detailClient" class="text-sm font-medium text-gray-700">-</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <i class="uil uil-money-bill text-blue-500 text-lg"></i>
                            <span class="text-sm font-medium text-gray-600">Total Amount</span>
                        </div>
                        <span id="detailTotalAmount" class="text-sm font-bold text-emerald-600">₱ 0.00</span>
                    </div>

                    <div class="border-b border-gray-100 pb-3 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="uil uil-credit-card text-blue-500 text-lg"></i>
                                <span class="text-sm font-medium text-gray-600">Payments</span>
                            </div>
                            <button type="button" 
                                    id="editAllPaymentsToggle" 
                                    class="text-blue-600 hover:text-blue-800 transition p-1 rounded-full hover:bg-blue-50 text-sm font-medium">
                                <i class="uil uil-edit mr-1"></i> Edit All
                            </button>
                        </div>
                        <div id="paymentsContainer" class="payments-container space-y-2">
                        </div>
                        <div id="noPaymentsMessage" class="text-center text-sm text-gray-500 py-2">
                            No payments found for this transaction.
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2">
                            <i class="uil uil-bill text-blue-500 text-lg"></i>
                            <span class="text-sm font-medium text-gray-600">AR Balance</span>
                        </div>
                        <span id="detailAr" class="text-sm font-bold text-orange-600">-</span>
                    </div>
                </div>

                <div class="modal-footer flex justify-end items-center gap-3">
                    <button
                        type="button"
                        class="closeDetailModal inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-200 transition"
                        data-modal-hide="transactionDetailModal">
                        Cancel
                    </button>

                    <button
                        id="saveTransactionBtn"
                        type="button"
                        disabled
                        class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-300 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="uil uil-save mr-2"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="p-3 lg:ml-64">
        <div id="contentDiv" class="w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg pl-2 pr-2">
                <div class="mb-2"> <!-- Reduced margin -->
                    <div class="flex justify-between">
                        <div class="w-32">
                        </div>
                    </div>
                </div>

                <div>
                    <!-- START: COMPACT FILTER SECTION -->
                    <div class="my-2 flex flex-wrap items-center justify-between gap-2"> 
                        <h1 class="text-2xl font-bold text-gray-800">Transactions</h1> <!-- Reduced font size from 3xl -->
                       
                        <form method="POST" action="{{ route('transactions.generate') }}" class="flex flex-wrap items-center gap-1.5"> <!-- Reduced gap -->
                            @csrf

                            <div class="flex items-center">
                                <select id="branch" name="branch"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-1.5 w-36"> <!-- Smaller text and padding -->
                                    <option value="all" {{ $selectedBranch == 'all' ? 'selected' : '' }}>All Branches</option>
                                    @foreach ($branches as $branch)
                                        @if ($branch->id != 1)
                                            <option value="{{ $branch->branch_code }}" {{ $selectedBranch == $branch->branch_code ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center">
                                <select id="approvalFilter" name="approvalFilter" class="filter-select text-xs p-1.5 w-40"> <!-- Compact filter select -->
                                    <option value="all">All Transactions</option>
                                    <option value="approved">APPROVED</option>
                                    <option value="not_approved">PENDING</option>
                                </select>
                            </div>
                            {{-- STATUS FILTER --}}
                            <div class="flex items-center">
                                <select id="status" name="status"
                                    class="filter-select text-xs p-1.5 w-40">
                                    <option value="all" {{ $selectedStatus == 'all' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="CANCELLED" {{ $selectedStatus == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="PAID" {{ $selectedStatus == 'PAID' ? 'selected' : '' }}>Paid</option>
                                    <option value="UNPAID" {{ $selectedStatus == 'UNPAID' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>
                            <div date-rangepicker class="flex items-center gap-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <input name="startDate" id="startDate" value="{{ date('m/d/Y', strtotime($startDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-28 pl-8 p-1.5" placeholder="Start Date"> <!-- Narrower width -->
                                </div>
                                <span class="text-gray-500 text-xs">to</span>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <input name="endDate" id="endDate" value="{{ date('m/d/Y', strtotime($endDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-28 pl-8 p-1.5" placeholder="End Date"> <!-- Narrower width -->
                                </div>
                            </div>
                            <button class="bg-blue-600 px-3 py-1.5 rounded-lg text-white text-xs font-bold hover:bg-blue-700 transition">Generate</button> <!-- Smaller button -->
                        </form>
                    </div>
                    <!-- END: COMPACT FILTER SECTION -->
                    
                    {{-- TOTALS --}}
                    <div class="grid grid-cols-6 gap-2 mb-2"> <!-- Reduced margin -->
                        <!-- START: SMALLER SELECTED TOTAL CARD -->
                        <div id="totalSumDisplay" class="min-w-0 flex items-center justify-between px-1.5 py-1 bg-indigo-50 border border-indigo-200 rounded-md">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 truncate">Selected</p>
                                <p id="totalSumAmount" class="text-sm font-bold text-indigo-700 truncate">₱ 0.00</p>
                            </div>
                            <i class="uil uil-check-square text-sm text-indigo-500 ml-1 flex-shrink-0"></i>
                        </div>
                        <!-- END: SMALLER SELECTED TOTAL CARD -->

                        <div class="min-w-0 flex items-center justify-between px-1.5 py-1 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 truncate">Transactions</p>
                                <p class="text-sm font-bold text-blue-700 truncate">₱ {{ $checkingTotal }}</p>
                            </div>
                            <i class="uil uil-wallet text-sm text-blue-500 ml-1 flex-shrink-0"></i>
                        </div>

                        <div class="min-w-0 flex items-center justify-between px-1.5 py-1 bg-green-50 border border-green-200 rounded-md">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 truncate">Paid</p>
                                <p class="text-sm font-bold text-green-700 truncate">₱ {{ $audited }}</p>
                            </div>
                            <i class="uil uil-check-circle text-sm text-green-500 ml-1 flex-shrink-0"></i>
                        </div>

                        <div class="min-w-0 flex items-center justify-between px-1.5 py-1 bg-red-50 border border-red-200 rounded-md">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 truncate">Unpaid</p>
                                <p class="text-sm font-bold text-red-700 truncate">₱ {{ $nonAudited }}</p>
                            </div>
                            <i class="uil uil-times-circle text-sm text-red-500 ml-1 flex-shrink-0"></i>
                        </div>

                        <div class="min-w-0 flex items-center justify-between px-1.5 py-1 bg-gray-50 border border-gray-300 rounded-md">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 truncate">Cancelled/Void</p>
                                <p class="text-sm font-bold text-gray-700 truncate">₱ {{ $cancelled }}</p>
                            </div>
                            <i class="uil uil-ban text-sm text-gray-500 ml-1 flex-shrink-0"></i>
                        </div>
                        
                        @if (auth()->user()->role == '5' || auth()->user()->role == '1')
                        <button id="approveSelectedBtn"
                            class="flex items-center justify-center gap-1 px-1.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-semibold text-sm transition">
                            <i class="uil uil-check-circle text-xs"></i>
                            <span class="text-sm">Approve</span>
                            <span id="selectedCount" class="bg-emerald-800 rounded-full px-1 py-0.5 text-xs">0</span>
                        </button>
                        @endif
                    </div>

                    {{-- TABLE with scroll container --}}
                    <div class="table-scroll-container" id="tableScrollContainer">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="bg-slate-100 border-b-2 border-slate-300">
                                <tr class="text-slate-700 text-sm font-semibold bg-emerald-200">
                                    @if (auth()->user()->role == '5' || auth()->user()->role == '1')
                                        <th class="px-4 py-3 text-center w-12">
                                            <input type="checkbox" id="selectAll"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        </th>
                                    @endif    
                                    <th class="px-3 py-2 text-center"><i class="uil uil-calendar-alt mr-1"></i>Date</th>
                                    <th class="px-3 py-2 text-center"><i class="uil uil-receipt mr-1"></i>Transaction #</th>
                                    <th class="px-3 py-2 text-center"><i class="uil uil-money-bill mr-1"></i>Sales</th>
                                    <th class="px-3 py-2 text-left"><i class="uil uil-users-alt mr-1"></i>Client</th>
                                    <th class="px-3 py-2 text-left"><i class="uil uil-credit-card mr-1"></i>Payment</th>
                                    <th class="px-3 py-2 text-left"><i class="uil uil-bill mr-1"></i>AR</th>
                                    <th class="px-3 py-2 text-center"><i class="uil uil-check-circle mr-1"></i>Status</th>
                                </tr>
                            </thead>
                            <tbody id="transactionDiv">
                                @foreach ($results as $result)
                                    @if (Auth::user()->role == 2)
                                        @if ($result->ostatus == 'PREPARING' || $result->ostatus == 'COMPLETED') 
                                        <tr class="resultRow border-b cursor-pointer hover:bg-gray-200 even:bg-gray-100">
                                            @if (auth()->user()->role == '5' || auth()->user()->role == '1')
                                                <td class="px-6 py-1 text-center whitespace-nowrap">
                                                    @if(isset($result->tstatus) && strtoupper($result->tstatus) === 'UNPAID' || isset($result->ostatus) && strtoupper($result->ostatus) === 'CANCELLED')
                                                        <div class="checkbox-tooltip-wrapper">
                                                            <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 opacity-50 cursor-not-allowed" disabled>
                                                            <span class="tooltip-text">It was already approved.</span>
                                                        </div>
                                                    @else
                                                        <input type="checkbox" class="selectTran h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $result->slug }}" data-amount="{{ $result->amount }}" data-payment="{{ html_entity_decode(e($result->payment_display)) }}" data-ar="{{ $result->ar_amount }}" data-date="{{ date('Y-m-d h:i:s A', strtotime($result->date)) }}" data-number="{{ $result->nn }}" data-status="{{ $result->tstatus }}" data-checked="{{ $result->is_in_approval_cart ? '1' : '0' }}" {{ $result->is_in_approval_cart ? 'checked' : '' }}>
                                                    @endif
                                                </td>
                                            @endif
                                            <th 
                                                class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                                <span data-id="{{ $result->local_id }}" 
                                                    data-branch="{{ isset($result->branch_code) ? $result->branch_code : 'default' }}">
                                                    {{ date('Y-m-d h:i:s A', strtotime($result->date)) }}
                                                </span>
                                            </th>
                                            <td class="px-6 py-1 text-center whitespace-nowrap">
                                                {{ $result->nn }}
                                            </td>
                                            <td class="px-6 py-1 text-center whitespace-nowrap">
                                                ₱ {{ number_format($result->amount, 2, '.', ',') }}
                                            </td>
                                            <td class="px-6 py-1 text-center whitespace-nowrap">
                                                {{ $result->table_name }}
                                            </td>
                                            <td class="px-6 py-1 text-center whitespace-nowrap">
                                                <span class="whitespace-pre-line">{{ $result->payment_display }}</span>
                                            </td>
                                            <td class="px-6 py-1 text-center whitespace-nowrap">
                                                ₱ {{ number_format($result->ar_amount, 2, '.', ',') }}
                                            </td>                                                    
                                            <td class="px-6 py-4 text-center whitespace-nowrap {{ ($result->tstatus == 'PAID') ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $result->tstatus }}
                                            </td>
                                        </tr>
                                        @endif
                                    @else
                                    @php
                                        $isDisabled = (isset($result->ostatus) && strtoupper($result->ostatus) === 'CANCELLED') || (isset($result->is_approved) && $result->is_approved == 1);
                                    @endphp
                                    <tr class="resultRow border-b hover:bg-gray-200 even:bg-gray-100 {{ $isDisabled ? 'result-row-disabled' : 'cursor-pointer' }}" 
                                        data-is-disabled="{{ $isDisabled ? 'true' : 'false' }}"
                                        data-approval-status="{{ $isDisabled ? 'approved' : 'not_approved' }}">
                                        @if (auth()->user()->role == '5' || auth()->user()->role == '1')
                                        <td class="px-6 py-1 text-center whitespace-nowrap">
                                            @if($isDisabled)
                                                <div class="checkbox-tooltip-wrapper">
                                                    <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 opacity-50 cursor-not-allowed" disabled>
                                                    <span class="tooltip-text">This transaction has already been approved and cannot be selected.</span>
                                                </div>
                                            @else
                                                <input type="checkbox" class="selectTran h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $result->slug }}" data-amount="{{ $result->amount }}" data-payment="{{ html_entity_decode(e($result->payment_display)) }}" data-ar="{{ $result->ar_amount }}" data-date="{{ date('Y-m-d h:i:s A', strtotime($result->date)) }}" data-number="{{ $result->nn }}" data-status="{{ $result->tstatus }}" data-checked="{{ $result->is_in_approval_cart ? '1' : '0' }}" {{ $result->is_in_approval_cart ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                        @endif
                                        <th 
                                            class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap {{ $isDisabled ? 'opacity-60' : '' }}">
                                            <span data-id="{{ $result->local_id }}" 
                                                data-branch="{{ isset($result->branch_code) ? $result->branch_code : 'default' }}">
                                                {{ date('Y-m-d h:i:s A', strtotime($result->date)) }}
                                            </span>
                                        </th>
                                        <td class="px-6 py-1 text-center whitespace-nowrap {{ $isDisabled ? 'opacity-60' : '' }}">
                                            {{ $result->nn }}
                                        </td>
                                        <td class="px-6 py-1 whitespace-nowrap {{ $isDisabled ? 'opacity-60 select-none' : '' }}">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($isDisabled)
                                                    <div class="edit-icon-disabled-wrapper flex items-center gap-2 cursor-not-allowed">
                                                        <span class="font-bold">
                                                            ₱ {{ number_format($result->amount, 2, '.', ',') }}
                                                        </span>
                                                        <span class="inline-flex items-center justify-center text-gray-400">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="w-4 h-4"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 9.414-9.414z"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                @else
                                                    <span
                                                        data-modal-target="transactionDetailModal"
                                                        data-modal-toggle="transactionDetailModal"
                                                        data-slug="{{ $result->slug }}"
                                                        data-number="{{ $result->nn }}"
                                                        data-date="{{ date('Y-m-d h:i:s A', strtotime($result->date)) }}"
                                                        data-amount="{{ $result->amount }}"
                                                        data-client="{{ $result->table_name }}"
                                                        data-payment="{{ html_entity_decode(e($result->payment_display)) }}"
                                                        data-payments="{{ e(json_encode($result->payments ?? [])) }}"
                                                        data-ar="{{ $result->ar_amount }}"
                                                        data-payment-modes="{{ e(json_encode($paymentModes ?? [])) }}"
                                                        class="openDetailBtn cursor-pointer font-bold"
                                                    >
                                                        ₱ {{ number_format($result->amount, 2, '.', ',') }}
                                                    </span>
                                                    <span
                                                        data-modal-target="transactionDetailModal"
                                                        data-modal-toggle="transactionDetailModal"
                                                        data-slug="{{ $result->slug }}"
                                                        data-number="{{ $result->nn }}"
                                                        data-date="{{ date('Y-m-d h:i:s A', strtotime($result->date)) }}"
                                                        data-amount="{{ $result->amount }}"
                                                        data-client="{{ $result->table_name }}"
                                                        data-payment="{{ html_entity_decode(e($result->payment_display)) }}"
                                                        data-payments="{{ e(json_encode($result->payments ?? [])) }}"
                                                        data-ar="{{ $result->ar_amount }}"
                                                        data-payment-modes="{{ e(json_encode($paymentModes ?? [])) }}"
                                                        class="openDetailBtn cursor-pointer text-blue-600 hover:text-blue-800 transition"
                                                        title="Edit Transaction"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 9.414-9.414z"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-1 text-left whitespace-nowrap {{ $isDisabled ? 'opacity-60' : '' }}">
                                            {{ $result->table_name }}
                                        </td>
                                        <td class="px-6 py-1 text-left whitespace-nowrap {{ $isDisabled ? 'opacity-60' : '' }}">
                                            <span class="whitespace-pre-line">{{ $result->payment_display }}</span>
                                        </td>
                                        <td class="px-6 py-1 text-left whitespace-nowrap {{ $isDisabled ? 'opacity-60' : '' }}">
                                            ₱ {{ number_format($result->ar_amount, 2, '.', ',') }}
                                        </td>                                                
                                        <td class="px-6 py-4 text-center whitespace-nowrap {{ ($result->tstatus == 'PAID') ? 'text-green-600' : 'text-red-600' }} {{ $isDisabled ? 'opacity-60' : '' }}">
                                            {{ $result->tstatus . ' - ('. $result->ostatus . ')' }}
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr id="noFilterResults">
                                    <td colspan="8" class="text-center py-6 text-gray-500" 
                                        style="height: 300px; vertical-align: middle;">
                                        <div class="flex flex-col items-center justify-center h-full">
                                            <i class="uil uil-search text-3xl block mb-2"></i>
                                            <span>No transactions match the selected filter.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION - STICKY --}}
                    <div class="pagination-sticky">
                        <div class="flex items-center justify-between px-2 mt-2 gap-2 overflow-x-auto">
                            @php
                                $prev = $page - 1;
                                $next = $page + 1;
                                $from = (($page - 1) * $perPage) + 1;
                                $to = $page * $perPage;
                                if($to > $menuCount){
                                    $to = $menuCount;
                                }
                                if($menuCount == 0){
                                    $from = 0;
                                }
                                
                                $baseUrl = route('transactions.index', [
                                    'startDate' => date('m/d/Y', strtotime($startDate)),
                                    'endDate' => date('m/d/Y', strtotime($endDate)),
                                    'branch' => $selectedBranch
                                ]);
                                
                                $totalPages = $perPage > 0 ? ceil($menuCount / $perPage) : 1;
                                if ($totalPages < 1) $totalPages = 1;
                            @endphp
                            
                            {{-- Showing items info --}}
                            <div class="flex-shrink-0">
                                <span class="text-xs text-gray-700 whitespace-nowrap">
                                    Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $menuCount }}</span> Items
                                </span>
                            </div>
                            
                            {{-- Pagination with manual page input --}}
                            <div class="flex-shrink-0 flex items-center gap-2">
                                <nav aria-label="Page navigation" class="shadow-xl">
                                    <ul class="inline-flex items-center -space-x-px">
                                        <li>
                                            <a href="{{ $baseUrl . '&page=' . $prev }}" 
                                            class="{{ ($page == 1) ? 'pointer-events-none opacity-50' : '' }} block w-7 h-7 leading-7 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 text-xs">
                                                <i class="uil uil-angle-left-b"></i>
                                                <span class="sr-only">Previous</span>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="flex items-center justify-center bg-white border-t border-b border-gray-300 h-7 px-1">
                                                <input type="number" 
                                                       id="manualPageInput" 
                                                       class="page-input" 
                                                       value="{{ $page }}" 
                                                       min="1" 
                                                       max="{{ $totalPages }}"
                                                       data-total-pages="{{ $totalPages }}"
                                                       data-base-url="{{ $baseUrl }}"
                                                       title="Enter page number and press Enter">
                                                <span class="text-xs text-gray-500 ml-1">/ {{ $totalPages }}</span>
                                            </div>
                                        </li>
                                        <li>
                                            <a href="{{ $baseUrl . '&page=' . $next }}" 
                                            class="{{ ($to == $menuCount || $next > $totalPages) ? 'pointer-events-none opacity-50' : '' }} block w-7 h-7 leading-7 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 text-xs">
                                                <i class="uil uil-angle-right-b"></i>
                                                <span class="sr-only">Next</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    {{-- PAGINATION END --}}                     
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="auditVal" value="{{ $audited }}">
    <input type="hidden" id="notAuditVal" value="{{ $nonAudited }}">

    {{-- SCRIPTS --}}
    <script>
        window.preloadedPaymentModes = @json($paymentModes ?? []);
        
        // ============================================
        // GLOBAL LOADING OVERLAY - REAL PROCESS BASED
        // ============================================
        function showGlobalLoading(title = 'Processing', subtitle = 'Please wait, do not close this window.') {
            const overlay = document.getElementById('globalLoadingOverlay');
            const titleEl = document.getElementById('globalLoadingTitle');
            const subtitleEl = document.getElementById('globalLoadingSubtitle');
            
            if (!overlay) return;
            
            if (titleEl) titleEl.innerHTML = title + '<span class="loading-dots"></span>';
            if (subtitleEl) subtitleEl.textContent = subtitle;
            
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('approval-processing');
        }
        
        function hideGlobalLoading() {
            const overlay = document.getElementById('globalLoadingOverlay');
            if (!overlay) return;
            
            overlay.classList.remove('show');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('approval-processing');
        }
        
        window.showGlobalLoading = showGlobalLoading;
        window.hideGlobalLoading = hideGlobalLoading;
        
        // ============================================
        // MANUAL PAGE INPUT HANDLER
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const manualPageInput = document.getElementById('manualPageInput');
            if (manualPageInput) {
                manualPageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const totalPages = parseInt(this.dataset.totalPages) || 1;
                        const baseUrl = this.dataset.baseUrl || '';
                        let pageNum = parseInt(this.value) || 1;
                        
                        // Clamp between 1 and totalPages
                        if (pageNum < 1) pageNum = 1;
                        if (pageNum > totalPages) pageNum = totalPages;
                        this.value = pageNum;
                        
                        window.location.href = baseUrl + '&page=' + pageNum;
                    }
                });
                
                manualPageInput.addEventListener('blur', function() {
                    const totalPages = parseInt(this.dataset.totalPages) || 1;
                    let pageNum = parseInt(this.value) || 1;
                    if (pageNum < 1) pageNum = 1;
                    if (pageNum > totalPages) pageNum = totalPages;
                    this.value = pageNum;
                });
            }
        });
        
        $(document).ready(function() {
            var _token = $('input[name="_token"]').val();
            var editRoute = "{{ route('transactions.edit', ['slug' => ':slug']) }}";
            
            $('#serveAmount').text($('#auditVal').val());
            $('#notServeAmount').text($('#notAuditVal').val());
            
            $('.resultRow:not(.result-row-disabled)').click(function(e) {
                if ($(e.target).closest('.openDetailBtn').length) return;
                if ($(e.target).closest('.edit-icon-disabled-wrapper').length) return;
                if ($(e.target).closest('.checkbox-tooltip-wrapper').length) return;
                
                var id = $(this).find('span').data('id');
                
                $.ajax({
                    url: "{{ route('transactions.view') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        id: id,
                        _token: _token
                    },
                    success: function(result) {
                        $('#viewTranNumber').html('Transaction #' + result.number);
                        $('#viewOrders').html(result.ordered);
                        $('#printUrl').attr('href', result.url);
                        $('#printOnlyUrl').attr('href', result.printOnly);
                    }
                });
            });

            // ============================================
            // APPROVAL STATUS FILTER
            // ============================================
            function applyApprovalFilter() {
                const filterValue = $('#approvalFilter').val();
                let visibleCount = 0;
                
                $('#transactionDiv tr.resultRow').each(function() {
                    const $row = $(this);
                    const approvalStatus = $row.data('approval-status') || 'not_approved';
                    
                    let shouldShow = true;
                    
                    if (filterValue === 'approved') {
                        shouldShow = (approvalStatus === 'approved');
                    } else if (filterValue === 'not_approved') {
                        shouldShow = (approvalStatus === 'not_approved');
                    }
                    
                    if (shouldShow) {
                        $row.removeClass('filtered-out').show();
                        visibleCount++;
                    } else {
                        $row.addClass('filtered-out').hide();
                    }
                });
                
                if (visibleCount === 0) {
                    $('#noFilterResults').addClass('show');
                } else {
                    $('#noFilterResults').removeClass('show');
                }
                
                const selectAllCheckbox = document.getElementById('selectAll');
                if (selectAllCheckbox) {
                    const visibleCheckboxes = $('#transactionDiv tr.resultRow:visible .selectTran:not(:disabled)');
                    const checkedVisible = visibleCheckboxes.filter(':checked');
                    selectAllCheckbox.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible.length;
                }
            }
            
            $('#approvalFilter').on('change', function() {
                applyApprovalFilter();
            });
            
            applyApprovalFilter();

            // ============================================
            // TRANSACTION DETAIL MODAL
            // ============================================
            let currentSlug = '';
            let originalPayments = [];
            let isEditingPayments = false;
            let isModalOpen = false;
            let paymentRowCount = 0;
            let availableMOP = [];
            let currentPaymentData = [];

            const modal = document.getElementById('transactionDetailModal');

            if (window.preloadedPaymentModes && window.preloadedPaymentModes.length > 0) {
                availableMOP = window.preloadedPaymentModes.map(m => {
                    if (typeof m === 'string') return m;
                    return m.name || m.mode_of_payment || '';
                }).filter(m => m !== '');
            } else {
                availableMOP = ['CASH', 'CHEQUE', 'BANK TRANSFER', 'GCASH', 'PAYPAL', 'CREDIT CARD'];
            }

            function closeDetailModal() {
                if (!modal) return;
                
                modal.classList.remove('show');
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                
                isModalOpen = false;
                
                if (isEditingPayments) {
                    isEditingPayments = false;
                    const toggleBtn = document.getElementById('editAllPaymentsToggle');
                    if (toggleBtn) {
                        toggleBtn.innerHTML = '<i class="uil uil-edit mr-1"></i> Edit All';
                    }
                    $('#paymentsContainer input, #paymentsContainer select').prop('disabled', true);
                    $('#paymentsContainer input').removeClass('edit-mode');
                    $('#paymentsContainer select').removeClass('payment-edit-mode');
                }
                
                const saveBtn = document.getElementById('saveTransactionBtn');
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    saveBtn.innerHTML = '<i class="uil uil-save mr-1"></i> Save Changes';
                }
                
                currentPaymentData = [];
            }

            function openDetailModal() {
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('show');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                isModalOpen = true;
            }

            function renderPaymentRows(payments, editMode = false) {
                const container = $('#paymentsContainer');
                const noMsg = $('#noPaymentsMessage');
                container.empty();
                
                if (!payments || payments.length === 0) {
                    noMsg.show();
                    return;
                }
                noMsg.hide();
                
                currentPaymentData = payments.map(p => ({
                    method: p.method || p.mode_of_payment || 'CASH',
                    amount: parseFloat(p.amount || p.value || 0)
                }));
                
                const fragment = document.createDocumentFragment();
                const uniqueMOPs = [...new Set(availableMOP)];
                
                payments.forEach((payment, index) => {
                    const paymentMethod = payment.method || payment.mode_of_payment || 'CASH';
                    const paymentValue = parseFloat(payment.amount || payment.value || 0);
                    
                    const row = document.createElement('div');
                    row.className = 'payment-row flex items-center gap-2 py-1.5 px-2 rounded-lg border border-gray-100';
                    row.id = 'payment-row-' + paymentRowCount++;
                    row.dataset.index = index;
                    
                    row.innerHTML = `
                        <div class="flex-1">
                            <select class="payment-select text-sm font-medium text-gray-700 w-full" 
                                    data-payment-index="${index}"
                                    data-original-value="${paymentMethod}"
                                    ${editMode ? '' : 'disabled'}>
                                <option value="">Select MOP</option>
                                ${uniqueMOPs.map(mop => `<option value="${mop}" ${mop === paymentMethod ? 'selected' : ''}>${mop}</option>`).join('')}
                            </select>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-medium text-gray-500">₱</span>
                            <input type="text" 
                                   class="payment-value-input text-sm font-semibold text-emerald-600"
                                   value="${paymentValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}"
                                   data-payment-index="${index}"
                                   data-original-value="${paymentValue}"
                                   ${editMode ? '' : 'disabled'}>
                        </div>
                        <button type="button" 
                                class="payment-edit-btn text-blue-600 hover:text-blue-800 transition p-1 rounded-full hover:bg-blue-50"
                                data-payment-index="${index}"
                                title="Toggle edit for this payment">
                            <i class="uil uil-edit text-sm"></i>
                        </button>
                    `;
                    
                    fragment.appendChild(row);
                });
                
                container[0].appendChild(fragment);
            }

            function openDetailModalWithData(element) {
                if (isModalOpen) {
                    closeDetailModal();
                }
                
                const slug = $(element).data('slug') || '';
                const number = $(element).data('number') || '-';
                const date = $(element).data('date') || '-';
                const amount = parseFloat($(element).data('amount')) || 0;
                const client = $(element).data('client') || 'N/A';
                const arRaw = parseFloat($(element).data('ar')) || 0;
                
                let paymentsData = $(element).data('payments');
                let paymentDisplay = $(element).data('payment') || '';
                
                let payments = [];
                
                if (paymentsData && typeof paymentsData === 'string' && paymentsData !== '') {
                    try {
                        const parsed = JSON.parse(paymentsData);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            payments = parsed;
                        }
                    } catch (e) {}
                }
                
                if (payments.length === 0 && paymentDisplay && paymentDisplay !== '-') {
                    const lines = paymentDisplay.split('\n');
                    for (let i = 0; i < lines.length; i++) {
                        const line = lines[i].trim();
                        if (line) {
                            const parts = line.split(':');
                            if (parts.length >= 2) {
                                const method = parts[0].trim();
                                const value = parseFloat(parts[1].trim().replace(/[₱,]/g, '')) || 0;
                                if (method && value > 0) {
                                    payments.push({ method: method, amount: value });
                                }
                            }
                        }
                    }
                }
                
                if (payments.length === 0 && amount > 0) {
                    payments.push({ method: 'CASH', amount: amount });
                }
                
                currentSlug = slug;
                originalPayments = JSON.parse(JSON.stringify(payments));
                
                $('#detailSlug').val(slug);
                $('#detailTranNumber').text(number);
                $('#detailDate').text(date);
                $('#detailClient').text(client);
                $('#detailTotalAmount').text('₱ ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#detailOriginalAmount').val(amount);
                $('#detailAr').text('₱ ' + arRaw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                isEditingPayments = false;
                renderPaymentRows(payments, false);
                
                openDetailModal();
                
                $('#editAllPaymentsToggle i').removeClass('uil-check-circle').addClass('uil-edit');
                $('#editAllPaymentsToggle').html('<i class="uil uil-edit mr-1"></i> Edit All');
                $('#saveTransactionBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                $('#saveTransactionBtn').html('<i class="uil uil-save mr-1"></i> Save Changes');
            }

            $(document).on('click', '.openDetailBtn:not([style*="pointer-events:none"])', function(e) {
                e.stopPropagation();
                e.preventDefault();
                openDetailModalWithData(this);
            });

            $(document).on('click', '.closeDetailModal', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeDetailModal();
            });

            $('#closeDetailModalBtn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeDetailModal();
            });

            $('#cancelDetailBtn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeDetailModal();
            });

            $(document).on('click', '#transactionDetailModal', function(e) {
                if (e.target === this) {
                    closeDetailModal();
                }
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && isModalOpen) {
                    closeDetailModal();
                }
            });

            $(document).on('click', '#editAllPaymentsToggle', function(e) {
                e.preventDefault();
                if (!currentSlug) {
                    showTopNotification('No transaction selected.', 'error');
                    return;
                }
                
                const container = $('#paymentsContainer');
                const inputs = container.find('.payment-value-input');
                const selects = container.find('.payment-select');
                
                isEditingPayments = !isEditingPayments;
                
                if (isEditingPayments) {
                    inputs.prop('disabled', false).addClass('edit-mode');
                    selects.prop('disabled', false).addClass('payment-edit-mode');
                    $(this).find('i').removeClass('uil-edit').addClass('uil-check-circle');
                    $(this).html('<i class="uil uil-check-circle mr-1"></i> Done Editing');
                    $('#saveTransactionBtn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#saveTransactionBtn').html('<i class="uil uil-save mr-1"></i> Save Changes');
                } else {
                    currentPaymentData.forEach((payment, index) => {
                        const originalMethod = payment.method || 'CASH';
                        const originalValue = parseFloat(payment.amount || 0);
                        
                        const input = container.find(`.payment-value-input[data-payment-index="${index}"]`);
                        const select = container.find(`.payment-select[data-payment-index="${index}"]`);
                        
                        if (input.length) {
                            input.val(originalValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            input.prop('disabled', true).removeClass('edit-mode');
                        }
                        if (select.length) {
                            select.val(originalMethod);
                            select.prop('disabled', true).removeClass('payment-edit-mode');
                        }
                    });
                    
                    $(this).find('i').removeClass('uil-check-circle').addClass('uil-edit');
                    $(this).html('<i class="uil uil-edit mr-1"></i> Edit All');
                    $('#saveTransactionBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                    $('#saveTransactionBtn').html('<i class="uil uil-save mr-1"></i> Save Changes');
                }
            });

            $(document).on('click', '.payment-edit-btn', function() {
                const row = $(this).closest('.payment-row');
                const input = row.find('.payment-value-input');
                const select = row.find('.payment-select');
                const isCurrentlyDisabled = input.prop('disabled');
                const index = parseInt(row.data('index'));
                
                if (isCurrentlyDisabled) {
                    input.prop('disabled', false).addClass('edit-mode');
                    select.prop('disabled', false).addClass('payment-edit-mode');
                    $(this).find('i').removeClass('uil-edit').addClass('uil-check-circle');
                    $(this).removeClass('text-blue-600').addClass('text-emerald-600');
                    
                    $('#saveTransactionBtn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#saveTransactionBtn').html('<i class="uil uil-save mr-1"></i> Save Changes');
                } else {
                    const originalPayment = currentPaymentData && currentPaymentData[index] ? currentPaymentData[index] : { method: 'CASH', amount: 0 };
                    const originalMethod = originalPayment.method || 'CASH';
                    const originalValue = parseFloat(originalPayment.amount || 0);
                    
                    input.prop('disabled', true).removeClass('edit-mode');
                    input.val(originalValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    select.prop('disabled', true).removeClass('payment-edit-mode');
                    select.val(originalMethod);
                    $(this).find('i').removeClass('uil-check-circle').addClass('uil-edit');
                    $(this).removeClass('text-emerald-600').addClass('text-blue-600');
                    
                    const anyEditing = $('.payment-value-input:not(:disabled)').length > 0;
                    if (!anyEditing && !isEditingPayments) {
                        $('#saveTransactionBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                        $('#saveTransactionBtn').html('<i class="uil uil-save mr-1"></i> Save Changes');
                    }
                }
            });

            $(document).on('click', '#saveTransactionBtn', function() {
                if (!currentSlug) {
                    showTopNotification('No transaction selected.', 'error');
                    return;
                }
                
                const paymentRows = $('#paymentsContainer .payment-row');
                const updatedPayments = [];
                let totalAmount = 0;
                let hasChanges = false;
                
                paymentRows.each(function() {
                    const input = $(this).find('.payment-value-input');
                    const select = $(this).find('.payment-select');
                    const rawValue = input.val().replace(/,/g, '');
                    const value = parseFloat(rawValue) || 0;
                    const method = select.val() || '';
                    const index = parseInt($(this).data('index'));
                    
                    const originalPayment = currentPaymentData && currentPaymentData[index] ? currentPaymentData[index] : null;
                    if (originalPayment) {
                        const originalMethod = originalPayment.method || '';
                        const originalValue = parseFloat(originalPayment.amount || 0);
                        if (method !== originalMethod || Math.abs(value - originalValue) > 0.001) {
                            hasChanges = true;
                        }
                    }
                    
                    if (method && value > 0) {
                        updatedPayments.push({ method: method, amount: value });
                        totalAmount += value;
                    }
                });
                
                if (updatedPayments.length === 0) {
                    showTopNotification('Please add at least one valid payment.', 'error');
                    return;
                }
                
                if (!hasChanges) {
                    showTopNotification('No changes detected.', 'info');
                    return;
                }
                
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="uil uil-spinner uil-spin mr-1"></i> Saving...');
                
                $.ajax({
                    url: "{{ route('transactions.updatePayments') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        slug: currentSlug,
                        payments: updatedPayments,
                        total_amount: totalAmount,
                        _token: _token
                    },
                    success: function(response) {
                        if (response.success) {
                            currentPaymentData = updatedPayments;
                            originalPayments = JSON.parse(JSON.stringify(updatedPayments));
                            
                            showTopNotification(response.message || 'Transaction updated successfully!', 'success');
                            closeDetailModal();
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            showTopNotification(response.message || 'Failed to update transaction.', 'error');
                            $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                            $btn.html('<i class="uil uil-save mr-1"></i> Save Changes');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An unexpected error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showTopNotification(errorMessage, 'error');
                        $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                        $btn.html('<i class="uil uil-save mr-1"></i> Save Changes');
                    }
                });
            });

            $(document).on('keydown', '.payment-value-input', function(e) {
                if (e.key === 'Enter' && !$(this).prop('disabled')) {
                    e.preventDefault();
                    const rawValue = $(this).val().replace(/,/g, '');
                    const amount = parseFloat(rawValue);
                    if (!isNaN(amount) && amount >= 0) {
                        $(this).val(amount.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    }
                    $('#saveTransactionBtn').click();
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeDetailModal();
                }
            });

            jQuery(document).on("click", ".auditBtn", function() {
                var slug = $(this).data('slug');
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                var branch = $('#branch').val();
                
                $.ajax({
                    url: "{{ route('transactions.updatestatus') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        slug: slug,
                        _token: _token,
                        startDate: startDate,
                        endDate: endDate,
                        branch: branch
                    },
                    success: function(result) {
                        location.reload();
                    }
                });
            });
            
            $('.returnButton').click(function() {
                var slug = $(this).data('slug');
                $('#returnAccept').data('slug', slug);
                $('#openreturnModal').click();
            });
            
            $('#returnAccept').click(function() {
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                
                $.ajax({
                    url: "{{ route('transactions.returnTable') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        slug: slug,
                        _token: _token
                    },
                    success: function(response) {
                        if (response.message) {
                            alert(response.message);
                            location.reload();
                        } else if (response.error) {
                            alert(response.error);
                        }
                    }
                });
            });
        });

        // ============================================
        // TOOLTIP POSITIONING
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const disabledRows = document.querySelectorAll('.result-row-disabled');
            
            disabledRows.forEach(function(row) {
                const tooltip = document.createElement('div');
                tooltip.className = 'row-disabled-tooltip';
                tooltip.textContent = '⚠️ This transaction has already been approved and can no longer be edited or selected.';
                tooltip.style.display = 'none';
                row.appendChild(tooltip);
                
                row.addEventListener('mouseenter', function(e) {
                    const rect = row.getBoundingClientRect();
                    const tooltipWidth = 350;
                    const tooltipHeight = 40;
                    
                    let leftPos = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                    let topPos;
                    
                    const spaceAbove = rect.top;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    
                    if (spaceAbove >= tooltipHeight + 15) {
                        topPos = rect.top - tooltipHeight - 10;
                        tooltip.setAttribute('data-arrow', 'down');
                    } else if (spaceBelow >= tooltipHeight + 15) {
                        topPos = rect.bottom + 10;
                        tooltip.setAttribute('data-arrow', 'up');
                    } else {
                        topPos = 10;
                        tooltip.setAttribute('data-arrow', 'down');
                    }
                    
                    if (leftPos < 10) {
                        leftPos = 10;
                    } else if (leftPos + tooltipWidth > window.innerWidth - 10) {
                        leftPos = window.innerWidth - tooltipWidth - 10;
                    }
                    
                    tooltip.style.position = 'fixed';
                    tooltip.style.left = leftPos + 'px';
                    tooltip.style.top = topPos + 'px';
                    tooltip.style.bottom = 'auto';
                    tooltip.style.right = 'auto';
                    tooltip.style.transform = 'none';
                    tooltip.style.display = 'block';
                    
                    setTimeout(() => {
                        tooltip.style.opacity = '1';
                        tooltip.style.visibility = 'visible';
                    }, 10);
                });
                
                row.addEventListener('mouseleave', function() {
                    tooltip.style.opacity = '0';
                    tooltip.style.visibility = 'hidden';
                    setTimeout(() => {
                        tooltip.style.display = 'none';
                    }, 300);
                });
            });

            const checkboxWrappers = document.querySelectorAll('.checkbox-tooltip-wrapper');
            
            checkboxWrappers.forEach(function(wrapper) {
                const checkbox = wrapper.querySelector('input[type="checkbox"]');
                const tooltip = wrapper.querySelector('.tooltip-text');
                
                if (!checkbox || !tooltip) return;
                
                function positionTooltip() {
                    const rect = wrapper.getBoundingClientRect();
                    const tooltipWidth = tooltip.offsetWidth || 180;
                    const tooltipHeight = tooltip.offsetHeight || 40;
                    
                    let leftPos = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                    let topPos;
                    
                    const spaceAbove = rect.top;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    
                    if (spaceAbove >= tooltipHeight + 15) {
                        topPos = rect.top - tooltipHeight - 10;
                        tooltip.setAttribute('data-arrow', 'down');
                    } else if (spaceBelow >= tooltipHeight + 15) {
                        topPos = rect.bottom + 10;
                        tooltip.setAttribute('data-arrow', 'up');
                    } else {
                        topPos = 10;
                        tooltip.setAttribute('data-arrow', 'down');
                    }
                    
                    if (leftPos < 10) {
                        leftPos = 10;
                    } else if (leftPos + tooltipWidth > window.innerWidth - 10) {
                        leftPos = window.innerWidth - tooltipWidth - 10;
                    }
                    
                    tooltip.style.position = 'fixed';
                    tooltip.style.left = leftPos + 'px';
                    tooltip.style.top = topPos + 'px';
                    tooltip.style.bottom = 'auto';
                    tooltip.style.right = 'auto';
                    tooltip.style.transform = 'none';
                }
                
                checkbox.addEventListener('mouseenter', function(e) {
                    positionTooltip();
                    tooltip.classList.add('show');
                });
                
                checkbox.addEventListener('mouseleave', function() {
                    tooltip.classList.remove('show');
                });
            });

            const disabledEditWrappers = document.querySelectorAll('.edit-icon-disabled-wrapper');
            
            disabledEditWrappers.forEach(function(wrapper) {
                const icon = wrapper.querySelector('span:not(.edit-icon-tooltip)');
                const tooltip = wrapper.querySelector('.edit-icon-tooltip');
                
                if (!icon || !tooltip) return;
                
                function positionEditTooltip() {
                    const rect = wrapper.getBoundingClientRect();
                    const tooltipWidth = tooltip.offsetWidth || 300;
                    const tooltipHeight = tooltip.offsetHeight || 40;
                    
                    let leftPos = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                    let topPos;
                    
                    const spaceAbove = rect.top;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    
                    if (spaceAbove >= tooltipHeight + 15) {
                        topPos = rect.top - tooltipHeight - 10;
                        tooltip.setAttribute('data-arrow', 'down');
                    } else if (spaceBelow >= tooltipHeight + 15) {
                        topPos = rect.bottom + 10;
                        tooltip.setAttribute('data-arrow', 'up');
                    } else {
                        topPos = 10;
                        tooltip.setAttribute('data-arrow', 'down');
                    }
                    
                    if (leftPos < 10) {
                        leftPos = 10;
                    } else if (leftPos + tooltipWidth > window.innerWidth - 10) {
                        leftPos = window.innerWidth - tooltipWidth - 10;
                    }
                    
                    tooltip.style.position = 'fixed';
                    tooltip.style.left = leftPos + 'px';
                    tooltip.style.top = topPos + 'px';
                    tooltip.style.bottom = 'auto';
                    tooltip.style.right = 'auto';
                    tooltip.style.transform = 'none';
                }
                
                icon.addEventListener('mouseenter', function(e) {
                    positionEditTooltip();
                    tooltip.classList.add('show');
                });
                
                icon.addEventListener('mouseleave', function() {
                    tooltip.classList.remove('show');
                });
            });

            function updateTooltipPositions() {
                document.querySelectorAll('.tooltip-text.show, .edit-icon-tooltip.show').forEach(function(tooltip) {
                    if (tooltip.style.visibility === 'visible' || tooltip.classList.contains('show')) {
                        const wrapper = tooltip.closest('.checkbox-tooltip-wrapper') || tooltip.closest('.edit-icon-disabled-wrapper');
                        if (wrapper) {
                            const rect = wrapper.getBoundingClientRect();
                            const tooltipWidth = tooltip.offsetWidth || 180;
                            const tooltipHeight = tooltip.offsetHeight || 40;
                            
                            let leftPos = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                            let topPos;
                            
                            const spaceAbove = rect.top;
                            const spaceBelow = window.innerHeight - rect.bottom;
                            
                            if (spaceAbove >= tooltipHeight + 15) {
                                topPos = rect.top - tooltipHeight - 10;
                                tooltip.setAttribute('data-arrow', 'down');
                            } else if (spaceBelow >= tooltipHeight + 15) {
                                topPos = rect.bottom + 10;
                                tooltip.setAttribute('data-arrow', 'up');
                            } else {
                                topPos = 10;
                                tooltip.setAttribute('data-arrow', 'down');
                            }
                            
                            if (leftPos < 10) {
                                leftPos = 10;
                            } else if (leftPos + tooltipWidth > window.innerWidth - 10) {
                                leftPos = window.innerWidth - tooltipWidth - 10;
                            }
                            
                            tooltip.style.position = 'fixed';
                            tooltip.style.left = leftPos + 'px';
                            tooltip.style.top = topPos + 'px';
                            tooltip.style.bottom = 'auto';
                            tooltip.style.right = 'auto';
                            tooltip.style.transform = 'none';
                        }
                    }
                });
            }

            window.addEventListener('scroll', updateTooltipPositions, { passive: true });
            window.addEventListener('resize', updateTooltipPositions, { passive: true });

            // Recalculate tooltip positions on table scroll
            const tableScrollContainer = document.getElementById('tableScrollContainer');
            if (tableScrollContainer) {
                tableScrollContainer.addEventListener('scroll', updateTooltipPositions, { passive: true });
            }
        });

        // ============================================
        // TOP NOTIFICATION
        // ============================================
        function showTopNotification(message, type = 'success') {
            const notification = document.getElementById('topNotification');
            const icon = document.getElementById('topNotificationIcon');
            const messageText = document.getElementById('topNotificationMessage');

            if (!notification || !icon || !messageText) return;

            const isError = type === 'error';
            const isInfo = type === 'info';
            
            let bgColor, borderColor, textColor, iconClass;
            
            if (isError) {
                bgColor = 'bg-red-50';
                borderColor = 'border-red-200';
                textColor = 'text-red-800';
                iconClass = 'uil-times-circle';
            } else if (isInfo) {
                bgColor = 'bg-blue-50';
                borderColor = 'border-blue-200';
                textColor = 'text-blue-800';
                iconClass = 'uil-info-circle';
            } else {
                bgColor = 'bg-emerald-50';
                borderColor = 'border-emerald-200';
                textColor = 'text-emerald-800';
                iconClass = 'uil-cloud-check';
            }
            
            notification.className = `fixed left-1/2 top-4 z-[60] w-[min(92vw,24rem)] -translate-x-1/2 rounded-lg border px-4 py-3 shadow-lg ${borderColor} ${bgColor} ${textColor}`;
            icon.className = `uil ${iconClass} text-xl`;
            messageText.textContent = message || (isError ? 'Something went wrong.' : isInfo ? 'Information' : 'Success');
            notification.classList.remove('hidden');

            clearTimeout(window.topNotificationTimer);
            window.topNotificationTimer = setTimeout(() => {
                notification.classList.add('hidden');
            }, 1000);
        }

        window.showTopNotification = showTopNotification;

        // ============================================
        // APPROVAL MODAL LOGIC
        // ============================================
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.selectTran');
            const approveBtn = document.getElementById('approveSelectedBtn');
            const countSpan = document.getElementById('selectedCount');
            const totalSumDisplay = document.getElementById('totalSumDisplay');
            const totalSumAmount = document.getElementById('totalSumAmount');
            const approvalModal = document.getElementById('approvalModal');
            const modalSelectedCount = document.getElementById('modalSelectedCount');
            const modalTotalAmount = document.getElementById('modalTotalAmount');
            const modalPaymentsSummary = document.getElementById('modalPaymentsSummary');
            const modalArAmount = document.getElementById('modalArAmount');
            const approvalModalBody = document.getElementById('approvalModalBody');
            const confirmApprovalBtn = document.getElementById('confirmApprovalBtn');
            const approvalModalCloseButtons = document.querySelectorAll('[data-modal-hide="approvalModal"]');
            const removeAllApprovalBtn = document.getElementById('removeAllApprovalBtn');

            function disableBodyScroll() {
                document.body.style.overflow = 'hidden';
            }

            function enableBodyScroll() {
                document.body.style.overflow = '';
            }

            function updateTotalSum() {
                const checkedCheckboxes = document.querySelectorAll('.selectTran:checked');
                let totalSum = 0;
                
                checkedCheckboxes.forEach(checkbox => {
                    const amount = parseFloat(checkbox.dataset.amount);
                    if (!isNaN(amount)) {
                        totalSum += amount;
                    }
                });
                
                if (checkedCheckboxes.length > 0) {
                    totalSumAmount.textContent = '₱ ' + totalSum.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                } else {
                    totalSumAmount.textContent = '₱ 0.00';
                }
            }

            function toggleApproveButtonVisibility() {
                const checkedCount = document.querySelectorAll('.selectTran:checked').length;
                if (countSpan) {
                    countSpan.textContent = checkedCount > 0 ? checkedCount : 0;
                }
                updateTotalSum();
            }

            toggleApproveButtonVisibility();

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    itemCheckboxes.forEach(checkbox => {
                        if (!checkbox.disabled) {
                            const row = checkbox.closest('tr');
                            if (row && row.style.display !== 'none' && !row.classList.contains('filtered-out')) {
                                checkbox.checked = selectAllCheckbox.checked;
                                persistSelection(checkbox);
                            }
                        }
                    });
                    toggleApproveButtonVisibility();
                });
            }

            function persistSelection(checkbox, selected = checkbox.checked) {
                const slug = checkbox.value;
                
                fetch('{{ route('transactions.toggleApprovalCart') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ slug: slug, selected: selected })
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    
                    if (!response.ok) {
                        console.warn('Selection save returned status:', response.status);
                        return { success: false, silent: true };
                    }
                    
                    if (!contentType.includes('application/json')) {
                        console.warn('Selection save returned non-JSON response');
                        return { success: false, silent: true };
                    }
                    
                    try {
                        return await response.json();
                    } catch (parseError) {
                        console.warn('Failed to parse selection save response:', parseError);
                        return { success: false, silent: true };
                    }
                })
                .then(data => {
                    if (data && !data.success && !data.silent) {
                        showTopNotification(data.message || 'Unable to save selection.', 'error');
                    }
                })
                .catch(error => {
                    console.warn('Selection save failed:', error);
                });
            }

            function hideApprovalModal() {
                if (approvalModal) {
                    approvalModal.classList.add('hidden');
                    enableBodyScroll();
                }
            }

            function showApprovalModal() {
                const checkedBoxes = Array.from(document.querySelectorAll('.selectTran:checked'));
                if (checkedBoxes.length === 0) return;

                let totalAmount = 0;
                let arAmount = 0;
                const paymentTotals = {};
                let modalRows = '';

                checkedBoxes.forEach(checkbox => {
                    const amount = parseFloat(checkbox.dataset.amount || 0);
                    const ar = parseFloat(checkbox.dataset.ar || 0);
                    const paymentDisplay = checkbox.dataset.payment || '';
                    
                    totalAmount += isNaN(amount) ? 0 : amount;
                    arAmount += isNaN(ar) ? 0 : ar;

                    let formattedPaymentDisplay = paymentDisplay;
                    let paymentLines = [];

                    try {
                        const parsed = JSON.parse(paymentDisplay);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            paymentLines = parsed.map(p => 
                                `${p.method || p.mode_of_payment || 'CASH'}: ₱ ${(p.amount || p.value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                            );
                            formattedPaymentDisplay = paymentLines.join('\n');
                        }
                    } catch (e) {
                        if (paymentDisplay && paymentDisplay !== '-') {
                            const lines = paymentDisplay.split('\n').filter(line => line.trim());
                            if (lines.length > 0) {
                                paymentLines = lines;
                                formattedPaymentDisplay = lines.join('\n');
                            } else {
                                const parts = paymentDisplay.split(':');
                                if (parts.length >= 2) {
                                    const method = parts[0].trim();
                                    const value = parseFloat(parts[1].trim().replace(/[₱,]/g, '')) || 0;
                                    if (method && value > 0) {
                                        paymentLines = [`${method}: ₱ ${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`];
                                        formattedPaymentDisplay = paymentLines.join('\n');
                                    }
                                }
                            }
                        }
                    }

                    paymentLines.forEach(line => {
                        const parts = line.split(':');
                        if (parts.length >= 2) {
                            const label = parts[0].trim().toUpperCase();
                            const value = parseFloat(parts[1].trim().replace(/[₱,]/g, '')) || 0;
                            if (!isNaN(value) && value > 0) {
                                paymentTotals[label] = (paymentTotals[label] || 0) + value;
                            }
                        }
                    });

                    modalRows += `
                        <tr class="border-b" data-slug="${checkbox.value}">
                            <td class="px-3 py-2">${checkbox.dataset.date || ''}</td>
                            <td class="px-3 py-2">${checkbox.dataset.number || ''}</td>
                            <td class="px-3 py-2">₱ ${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="px-3 py-2 whitespace-pre-line" style="white-space: pre-line;">${formattedPaymentDisplay}</td>
                            <td class="px-3 py-2">₱ ${ar.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="px-3 py-2">${checkbox.dataset.status || ''}</td>
                            <td class="px-3 py-2">
                                <button type="button" class="removeApprovalRow text-sm text-red-600 hover:text-red-800 font-semibold">Remove</button>
                            </td>
                        </tr>`;
                });

                const paymentSummaryLines = Object.entries(paymentTotals).map(([label, value]) => `${label}: ₱ ${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);

                modalSelectedCount.textContent = checkedBoxes.length;
                modalTotalAmount.textContent = '₱ ' + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                modalPaymentsSummary.textContent = paymentSummaryLines.length ? paymentSummaryLines.join('\n') : '-';
                modalArAmount.textContent = '₱ ' + arAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                approvalModalBody.innerHTML = modalRows;
                bindApprovalModalRemoveHandlers();
                if (approvalModal) {
                    approvalModal.classList.remove('hidden');
                    disableBodyScroll();
                }
            }

            function bindApprovalModalRemoveHandlers() {
                const removeButtons = document.querySelectorAll('.removeApprovalRow');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const row = this.closest('tr');
                        const slug = row?.dataset?.slug;
                        if (!slug) return;

                        const checkbox = document.querySelector(`.selectTran[value="${slug}"]`);
                        if (checkbox) {
                            checkbox.checked = false;
                            persistSelection(checkbox, false);
                            toggleApproveButtonVisibility();
                            if (selectAllCheckbox) {
                                const totalCheckboxes = document.querySelectorAll('.selectTran:not(:disabled)').length;
                                const checkedCheckboxes = document.querySelectorAll('.selectTran:checked').length;
                                selectAllCheckbox.checked = (totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes);
                            }
                        }

                        row.remove();
                        updateApprovalModalTotals();
                    });
                });
            }

            function updateApprovalModalTotals() {
                const remainingRows = approvalModalBody.querySelectorAll('tr');
                let totalAmount = 0;
                let arAmount = 0;
                const paymentTotals = {};

                remainingRows.forEach(row => {
                    const amountText = row.children[2]?.textContent.replace(/[₱,\s]/g, '') || '0';
                    const arText = row.children[4]?.textContent.replace(/[₱,\s]/g, '') || '0';
                    const paymentDisplay = row.children[3]?.textContent || '';
                    const amount = parseFloat(amountText) || 0;
                    const ar = parseFloat(arText) || 0;

                    totalAmount += amount;
                    arAmount += ar;

                    if (paymentDisplay) {
                        const lines = paymentDisplay.split('\n').filter(line => line.trim());
                        lines.forEach(line => {
                            const parts = line.split(':');
                            if (parts.length >= 2) {
                                const label = parts[0].trim().toUpperCase();
                                const value = parseFloat(parts[1].trim().replace(/[₱,]/g, '')) || 0;
                                if (!isNaN(value) && value > 0) {
                                    paymentTotals[label] = (paymentTotals[label] || 0) + value;
                                }
                            }
                        });
                    }
                });

                const paymentSummaryLines = Object.entries(paymentTotals).map(([label, value]) => `${label}: ₱ ${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);

                if (remainingRows.length === 0) {
                    hideApprovalModal();
                    return;
                }

                modalSelectedCount.textContent = remainingRows.length;
                modalTotalAmount.textContent = '₱ ' + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                modalPaymentsSummary.textContent = paymentSummaryLines.length ? paymentSummaryLines.join('\n') : '-';
                modalArAmount.textContent = '₱ ' + arAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    if (this.disabled) return;
                    
                    const totalCheckboxes = document.querySelectorAll('.selectTran:not(:disabled)').length;
                    const checkedCheckboxes = document.querySelectorAll('.selectTran:checked').length;
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = (totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes);
                    }
                    toggleApproveButtonVisibility();
                    persistSelection(this);
                });
            });

            if (approveBtn) {
                approveBtn.addEventListener('click', function () {
                    const selectedSlugs = Array.from(document.querySelectorAll('.selectTran:checked')).map(cb => cb.value);
                    if (selectedSlugs.length === 0) return;
                    showApprovalModal();
                });
            }

            if (approvalModalCloseButtons.length) {
                approvalModalCloseButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        hideApprovalModal();
                    });
                });
            }

            if (approvalModal) {
                approvalModal.addEventListener('click', function (event) {
                    if (event.target === approvalModal) {
                        hideApprovalModal();
                    }
                });
            }

            if (removeAllApprovalBtn) {
                removeAllApprovalBtn.addEventListener('click', function () {
                    const selectedCheckboxes = Array.from(document.querySelectorAll('.selectTran:checked'));
                    if (!selectedCheckboxes.length) return;

                    selectedCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                        persistSelection(checkbox, false);
                    });

                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }

                    approvalModalBody.innerHTML = '';
                    toggleApproveButtonVisibility();
                    hideApprovalModal();
                });
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && approvalModal && !approvalModal.classList.contains('hidden')) {
                    hideApprovalModal();
                }
            });

            // ============================================
            // CONFIRM APPROVAL - WITH REAL PROCESS-BASED LOADING SCREEN
            // ============================================
            if (confirmApprovalBtn) {
                confirmApprovalBtn.addEventListener('click', function () {
                    const selectedSlugs = Array.from(document.querySelectorAll('.selectTran:checked')).map(cb => cb.value);
                    if (!selectedSlugs.length) return;

                    // Disable the button to prevent double-clicks
                    confirmApprovalBtn.disabled = true;
                    confirmApprovalBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    confirmApprovalBtn.innerHTML = '<i class="uil uil-spinner uil-spin mr-1"></i> Processing...';

                    // SHOW LOADING OVERLAY - tied to actual network request, not a timer
                    showGlobalLoading('Approving transactions', 'Please wait, do not close this window.');

                    fetch('{{ route('sales-journal.bulkApprove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ slugs: selectedSlugs })
                    })
                    .then(async response => {
                        // Always try to get a JSON response; handle non-JSON gracefully
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            return await response.json();
                        }
                        return { success: false, message: 'Server returned an unexpected response.' };
                    })
                    .then(data => {
                        if (data && data.success) {
                            // Update loading text to show success state briefly
                            const titleEl = document.getElementById('globalLoadingTitle');
                            const subtitleEl = document.getElementById('globalLoadingSubtitle');
                            if (titleEl) titleEl.innerHTML = '✓ Approval complete';
                            if (subtitleEl) subtitleEl.textContent = 'Reloading, please wait...';

                            // Reload the page — the overlay stays visible during the reload
                            window.location.reload();
                        } else {
                            // Hide overlay only after we know it failed
                            hideGlobalLoading();
                            confirmApprovalBtn.disabled = false;
                            confirmApprovalBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            confirmApprovalBtn.innerHTML = 'Approve';
                            showTopNotification((data && data.message) || 'Something went wrong during approval.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Approval error:', error);
                        hideGlobalLoading();
                        confirmApprovalBtn.disabled = false;
                        confirmApprovalBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        confirmApprovalBtn.innerHTML = 'Approve';
                        showTopNotification('An unexpected error occurred during approval.', 'error');
                    });
                });
            }

            const singleApproveButtons = document.querySelectorAll('.approveSingleBtn');
            singleApproveButtons.forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const slug = this.dataset.slug;
                    if (!slug) return;

                    if (confirm('Are you sure you want to approve this transaction?')) {
                        showGlobalLoading('Approving transaction', 'Please wait, do not close this window.');

                        fetch('{{ route('sales-journal.approve') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ slug: slug })
                        })
                        .then(async response => {
                            const contentType = response.headers.get('content-type') || '';
                            if (contentType.includes('application/json')) {
                                return await response.json();
                            }
                            return { success: false, message: 'Server returned an unexpected response.' };
                        })
                        .then(data => {
                            if (data && data.success) {
                                window.location.reload();
                            } else {
                                hideGlobalLoading();
                                showTopNotification((data && data.message) || 'Something went wrong during approval.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            hideGlobalLoading();
                            showTopNotification('An unexpected error occurred.', 'error');
                        });
                    }
                });
            });
        });
    </script>
</x-app-layout>