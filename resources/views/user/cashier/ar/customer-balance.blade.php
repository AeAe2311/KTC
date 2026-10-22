<x-app-layout>
    <style>
        /* Select2 Custom Styles */
        .select2-container .select2-selection--single {
            height: 42px !important;
            padding: 5px 0;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            padding-left: 12px !important;
            color: #111827 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }
        .select2-dropdown {
            border-color: #d1d5db !important;
            border-radius: 8px !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            padding: 8px !important;
            outline: none !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: #dbeafe !important;
            color: #1e40af !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 12px !important;
        }
        
        /* Loading spinner */
        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 20px !important;
        }

        /* ===== SEARCHABLE DROPDOWN STYLES - FIXED ===== */
        .searchable-dropdown-wrapper {
            position: relative;
            min-width: 200px;
            z-index: 3000;
            overflow: visible !important;
        }

        .searchable-dropdown-wrapper input[type="text"] {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: #f9fafb;
            transition: all 0.2s;
            position: relative;
            z-index: 1001;
        }

        .searchable-dropdown-wrapper input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        .searchable-dropdown-wrapper .dropdown-list {
            position: absolute;
            z-index: 99999 !important;
            width: 100%;
            margin-top: 4px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2), 0 4px 10px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            display: none;
            animation: dropdownFadeIn 0.2s ease-in-out;
            top: 100%;
            left: 0;
        }

        .searchable-dropdown-wrapper .dropdown-list.visible {
            display: block;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item.active {
            background-color: #dbeafe;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item .item-icon {
            flex-shrink: 0;
            margin-right: 0.5rem;
            color: #9ca3af;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item .item-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.875rem;
        }

        .searchable-dropdown-wrapper .dropdown-list .all-items-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #d1d5db;
            z-index: 10;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .searchable-dropdown-wrapper .dropdown-list .all-items-header:hover {
            background-color: #f3f4f6;
        }

        .searchable-dropdown-wrapper .dropdown-list .no-results {
            padding: 1rem;
            text-align: center;
            color: #6b7280;
        }

        .searchable-dropdown-wrapper .dropdown-list .no-results svg {
            width: 2rem;
            height: 2rem;
            margin: 0 auto 0.5rem;
            color: #d1d5db;
        }

        .searchable-dropdown-wrapper .dropdown-list .no-results .sub-text {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        /* Dropdown scrollbar styling */
        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar {
            width: 6px;
        }

        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Print button styles */
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Client card print button - now positioned in table */
        .client-print-btn {
            z-index: 10;
        }

        .client-card {
            position: relative;
            z-index: 1;
        }

        /* Hide print header and grand total on screen */
        .print-header {
            display: none !important;
        }
        .print-grand-total {
            display: none !important;
        }

        /* Hide the report-header-row (Customer Balances / date / fiscal year block)
        on screen — it should only appear in the printed output */
        .report-header-row {
            display: none;
        }

        /* For single client print */
        .client-card.hidden-client {
            display: none !important;
        }
        .client-card.printing-client {
            display: block !important;
        }

        /* ============================================ */
        /* SCROLLABLE PAGE WRAPPER WITH STICKY FILTERS */
        /* ============================================ */
        .filter-page-wrapper {
            height: 100vh;
            overflow-y: auto;
            scroll-behavior: smooth;
            position: relative;
            overflow-x: visible;
        }

        /* FILTER SECTION - STICKY AT TOP */
        .filter-sticky-wrapper {
            position: sticky !important;
            top: 0 !important;
            z-index: 2000 !important;
            background: #f9fafb !important;
            padding: 8px 0 !important;
            margin-bottom: 10px !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
            border-bottom: 2px solid #e5e7eb !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
            overflow: visible !important;
        }

        .filter-container {
            display: flex;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 8px;
            padding: 4px 0;
            overflow-x: auto;
            overflow-y: visible !important;
            overflow: visible !important;
            min-height: 70px;
            width: 100%;
            position: relative;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 2px;
            flex: 1 1 0;
            min-width: 80px;
            max-width: 100%;
            position: relative;
            overflow: visible;
        }

        .filter-group label {
            font-size: 0.65rem;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-group input,
        .filter-group select,
        .filter-group .select2-container {
            height: 32px;
            font-size: 0.7rem;
            width: 100%;
        }

        .filter-group input,
        .filter-group select {
            padding: 2px 6px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: #f9fafb;
            outline: none;
            transition: border-color 0.15s ease;
            width: 100%;
            min-width: 0;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .filter-group .select2-container {
            min-width: 0;
            width: 100% !important;
        }

        .filter-group .select2-container .select2-selection--single {
            height: 32px !important;
            padding: 1px 0;
            border-radius: 4px !important;
            width: 100%;
        }

        .filter-group .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 8px !important;
            font-size: 0.7rem;
        }

        .filter-group .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
        }

        /* Date range picker with labels on top */
        .date-range-wrapper {
            display: flex;
            flex-direction: column;
            gap: 2px;
            width: 100%;
        }

        .date-range-wrapper .date-range-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .date-range-wrapper .date-inputs-row {
            display: flex;
            align-items: center;
            gap: 3px;
            flex-wrap: nowrap;
            width: 100%;
        }

        .date-range-wrapper .date-input-wrapper {
            position: relative;
            flex: 1 1 0;
            min-width: 0;
        }

        .date-range-wrapper .date-input-wrapper .date-icon {
            position: absolute;
            left: 4px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6b7280;
            width: 12px;
            height: 12px;
        }

        .date-range-wrapper .date-input-wrapper input {
            padding-left: 18px;
            height: 32px;
            font-size: 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: #f9fafb;
            outline: none;
            transition: border-color 0.15s ease;
            width: 100%;
            min-width: 0;
            cursor: pointer;
        }

        .date-range-wrapper .date-input-wrapper input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .date-range-wrapper .date-separator {
            font-size: 0.65rem;
            color: #6b7280;
            padding: 0 2px;
            font-weight: 600;
            flex: 0 0 auto;
        }

        /* Status dropdown */
        .filter-group.status-group {
            flex: 0 0 auto !important;
            min-width: 100px !important;
            max-width: 130px !important;
            width: auto !important;
        }

        .filter-group.status-group select#statusFilter {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 130px !important;
            padding-right: 20px !important;
        }

        /* Buttons */
        .filter-buttons {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 0 0 auto;
            justify-content: flex-end;
        }

        .filter-buttons .btn-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            visibility: hidden;
            height: 0;
        }

        .filter-buttons .btn-row {
            display: flex;
            gap: 3px;
            flex-wrap: nowrap;
        }

        .filter-buttons .btn {
            height: 32px;
            padding: 0 12px;
            border: none;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 3px;
            transition: background-color 0.15s ease;
            white-space: nowrap;
        }

        .btn-filter {
            background-color: #2563eb;
        }
        .btn-filter:hover {
            background-color: #1d4ed8;
        }

        .btn-reset {
            background-color: #6b7280;
        }
        .btn-reset:hover {
            background-color: #4b5563;
        }

        .btn-print-all {
            background-color: #7c3aed;
        }
        .btn-print-all:hover {
            background-color: #6d28d9;
        }

        .filter-buttons .btn i {
            font-size: 0.85rem;
        }

        /* STICKY GRAND TOTAL SECTION */
        .grand-total-wrapper {
            position: sticky;
            bottom: 0;
            z-index: 999;
            padding: 8px 0 4px 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(255,255,255,0.8) 20%, rgba(255,255,255,1) 100%);
        }

        .grand-total-wrapper .grand-total-content {
            background: #ffffff;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 6px 12px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.05);
        }

        .grand-total-wrapper .grand-total-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
        }

        .grand-total-wrapper .grand-total-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4px 8px;
            border-right: 1px solid #e5e7eb;
        }

        .grand-total-wrapper .grand-total-item:last-child {
            border-right: none;
        }

        .grand-total-wrapper .grand-total-label {
            font-size: 0.6rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .grand-total-wrapper .grand-total-value {
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .grand-total-wrapper .grand-total-value.text-red-600 {
            color: #dc2626;
        }
        .grand-total-wrapper .grand-total-value.text-gray-800 {
            color: #1f2937;
        }
        .grand-total-wrapper .grand-total-value.text-blue-600 {
            color: #2563eb;
        }
        .grand-total-wrapper .grand-total-value.text-green-600 {
            color: #16a34a;
        }

        /* Content wrapper */
        .content-wrapper {
            position: relative;
            padding-bottom: 10px;
        }

        /* ===== STAR PIN BUTTON - CLEAN & VISIBLE ===== */
        .pin-btn {
            position: absolute;
            top: -11px;
            right: -8px;
            z-index: 99999 !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer;
            padding: 2px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pin-btn svg {
            width: 18px;
            height: 18px;
            color: #9ca3af;
            transition: all 0.2s ease;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
        }

        .pin-btn:hover svg {
            color: #f59e0b;
            transform: scale(1.15);
        }

        .pin-btn.pinned svg {
            color: #f59e0b;
            fill: #f59e0b;
        }

        .pin-btn.pinned:hover svg {
            transform: scale(1.15);
        }

        /* Ensure client card has position relative for absolute positioning */
        .client-card {
            position: relative !important;
            overflow: visible !important;
        }

        /* RESPONSIVE */
        @media (max-width: 1200px) {
            .filter-container {
                flex-wrap: wrap;
                gap: 6px;
                align-items: flex-start;
            }
            
            .filter-group {
                flex: 1 1 calc(33.33% - 6px);
                min-width: 140px;
            }

            .filter-group:last-child {
                flex: 0 0 auto;
            }

            .filter-group.status-group {
                flex: 0 0 auto !important;
                min-width: 100px !important;
                max-width: 120px !important;
            }
        }

        @media (max-width: 992px) {
            .filter-group {
                flex: 1 1 calc(50% - 6px);
                min-width: 120px;
            }

            .filter-group.status-group {
                flex: 0 0 auto !important;
                min-width: 100px !important;
                max-width: 120px !important;
            }

            .filter-group select#statusFilter {
                width: 100% !important;
                max-width: 120px !important;
            }

            .grand-total-wrapper .grand-total-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 4px;
            }

            .grand-total-wrapper .grand-total-item {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                padding: 4px 6px;
            }

            .grand-total-wrapper .grand-total-item:nth-child(3) {
                border-bottom: none;
            }
            .grand-total-wrapper .grand-total-item:nth-child(4) {
                border-bottom: none;
            }
            .grand-total-wrapper .grand-total-item:nth-child(5) {
                border-bottom: none;
            }
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-wrap: wrap;
                gap: 8px;
                align-items: flex-start;
            }
            
            .filter-group {
                flex: 1 1 100%;
                min-width: 100%;
                max-width: 100%;
            }
            
            .filter-group input,
            .filter-group select,
            .filter-group .select2-container {
                width: 100%;
                min-width: 0;
            }

            .filter-group .select2-container {
                min-width: 0;
                width: 100% !important;
            }

            .filter-group.status-group {
                flex: 1 1 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
            }

            .filter-group.status-group select#statusFilter {
                width: 100% !important;
                max-width: 100% !important;
            }

            .date-range-wrapper {
                width: 100%;
            }
            
            .date-range-wrapper .date-inputs-row {
                flex-wrap: nowrap;
            }
            
            .date-range-wrapper .date-input-wrapper {
                flex: 1;
                min-width: 0;
            }
            
            .date-range-wrapper .date-input-wrapper input {
                width: 100%;
                min-width: 0;
            }

            .filter-buttons {
                flex: 1 1 100%;
                flex-direction: column;
            }

            .filter-buttons .btn-row {
                flex-wrap: wrap;
                width: 100%;
            }

            .filter-buttons .btn {
                flex: 1;
                justify-content: center;
                padding: 0 6px;
                font-size: 0.65rem;
                min-width: 60px;
            }

            .grand-total-wrapper .grand-total-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2px;
            }

            .grand-total-wrapper .grand-total-item {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                padding: 3px 4px;
            }

            .grand-total-wrapper .grand-total-item:nth-child(odd) {
                border-right: 1px solid #e5e7eb;
            }

            .grand-total-wrapper .grand-total-item:nth-child(4) {
                border-bottom: none;
            }
            .grand-total-wrapper .grand-total-item:nth-child(5) {
                border-bottom: none;
            }

            .grand-total-wrapper .grand-total-value {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .filter-buttons .btn span {
                display: none;
            }
            
            .filter-buttons .btn i {
                font-size: 1rem;
            }

            .filter-group {
                min-width: 100%;
                max-width: 100%;
            }

            .filter-group.status-group {
                min-width: 100% !important;
                max-width: 100% !important;
            }

            .filter-group.status-group select#statusFilter {
                width: 100% !important;
                max-width: 100% !important;
                font-size: 0.65rem;
            }

            .grand-total-wrapper .grand-total-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .grand-total-wrapper .grand-total-content {
                padding: 6px 8px;
            }
            
            .grand-total-wrapper .grand-total-value {
                font-size: 0.75rem;
            }

            .grand-total-wrapper .grand-total-label {
                font-size: 0.5rem;
            }
        }
        .print-only-tables {
            display: none;
        }
        /* ============================================ */
        /* PRINT STYLES - CLEAN & PROFESSIONAL - FIXED  */
        /* ============================================ */
        @media print {
            /* Reset all */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            @page {
                margin: 0.8cm 0.5cm 1.2cm 0.5cm !important;
                size: auto !important;
            }
            
            html, body {
                background: white !important;
                height: auto !important;
                overflow: visible !important;
                font-family: 'Arial', 'Helvetica', sans-serif !important;
                font-size: 8pt !important;
                line-height: 1.3 !important;
                color: #000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .screen-only-table {
                display: none !important;
            }
            .print-only-tables {
                display: block !important;
            }            
            /* Hide everything by default */
            body * {
                visibility: hidden !important;
            }
            
            /* Show only print container */
            #printContainer, 
            #printContainer * {
                visibility: visible !important;
            }
            
            #printContainer {
                position: relative !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                overflow: visible !important;
            }
            
            /* ===== HEADER ===== */
            .print-header {
                display: none !important;
            }

            .print-header table {
                width: 100% !important;
                border: none !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
            }
            
            .print-header table td {
                border: none !important;
                padding: 1px 4px !important;
                font-size: 6pt !important;
                vertical-align: top !important;
                background: transparent !important;
                line-height: 1.2 !important;
                color: #000 !important;
            }
            
            .print-header table td:last-child {
                text-align: right !important;
            }
            
            .print-header .page-number {
                font-size: 6pt !important;
                font-weight: normal !important;
            }
            
            /* Page number display - NO COUNTERS, just display styles */
            .page-number {
                font-size: 6pt !important;
                font-weight: normal !important;
            }
            
            .page-counter {
                display: inline-block !important;
                min-width: 4em;
                font-weight: bold;
            }
            
            /* Hide screen elements */
            .no-print {
                display: none !important;
            }
            
            /* Hide pin button in print */
            .pin-btn {
                display: none !important;
            }
            
            /* Page breaks */
            .print-page-break {
                page-break-after: always !important;
                page-break-inside: avoid !important;
            }
            
            .print-client-container {
                margin: 0 !important;
                padding: 0 !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            
            .print-client-container:last-child {
            }
            
            /* Hide print buttons */
            .client-print-btn {
                display: none !important;
            }
            
            /* ===== CLIENT CARD ===== */
            .client-card {
                border: none !important;
                border-radius: 0 !important;
                margin: 0 0 20px 0 !important;
                padding: 0 !important;
                background: white !important;
                width: 100% !important;
                page-break-after: auto !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            .client-card .overflow-x-auto {
                overflow: visible !important;
                width: 100% !important;
            }
            
            /* ===== TABLE - CLEAN & MINIMAL ===== */
            table {
                border-collapse: collapse !important;
                width: 100% !important;
                font-size: 7pt !important;
                border: none !important;
                margin: 0 !important;
                table-layout: auto !important;
            }
            
            th, td {
                border: none !important;
                padding: 2px 4px !important;
                text-align: left !important;
                vertical-align: middle !important;
                background: white !important;
                font-size: 7pt !important;
                line-height: 1.2 !important;
            }
            
            /* ===== HEADER ROW - LIGHT GRAY ===== */
            thead th {
                background-color: #f0f0f0 !important;
                color: #000 !important;
                font-weight: 700 !important;
                padding: 2px 4px !important;
                text-transform: uppercase !important;
                font-size: 6pt !important;
                letter-spacing: 0.3px !important;
                border-bottom: 2px solid #000 !important;
            }
            
            /* ===== FOOTER ===== */
            tfoot td {
                font-weight: 700 !important;
                padding: 2px 4px !important;
                border-top: 2px solid #000 !important;
            }
            
            /* ===== OPENING BALANCE - LIGHT YELLOW ===== */
            .bg-yellow-50 td {
                background-color: #fff9e6 !important;
                font-weight: 600 !important;
                padding: 2px 4px !important;
                border-bottom: 1px solid #ccc !important;
            }
            
            /* ===== ALTERNATING ROWS ===== */
            tbody tr:nth-child(even) {
                background-color: #f9f9f9 !important;
            }
            
            tbody tr:nth-child(even) td {
                background-color: #f9f9f9 !important;
            }
            
            /* ===== TOTAL ROW ===== */
            .bg-gray-100 td {
                background-color: #f0f0f0 !important;
                font-weight: 700 !important;
                padding: 2px 4px !important;
            }
            
            /* ===== GRAND TOTAL ROW ===== */
            tfoot tr:last-child td {
                background-color: #e8f5e9 !important;
                font-weight: 700 !important;
                border-top: 2px solid #000 !important;
                padding: 2px 4px !important;
            }
            
            /* ===== TEXT COLORS ===== */
            .text-red-600, .text-green-600, .text-blue-600, .text-gray-600, .text-gray-700, .text-gray-800 {
                color: #000 !important;
            }
            
            /* ===== ALIGNMENT ===== */
            td.text-right, th.text-right {
                text-align: right !important;
            }
            
            td.text-center, th.text-center {
                text-align: center !important;
            }
            
            /* ===== HIDE ELEMENTS ===== */
            .grand-total-wrapper {
                display: none !important;
            }
            
            .filter-sticky-wrapper {
                display: none !important;
            }
            
            .filter-page-wrapper {
                height: auto !important;
                overflow: visible !important;
                display: block !important;
            }
            
            .content-wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .flex.justify-between.items-center.mt-6 {
                display: none !important;
            }
            
            /* Remove shadows */
            .shadow-md, .shadow-sm, .shadow {
                box-shadow: none !important;
            }
            
            .rounded-lg, .rounded-md, .rounded {
                border-radius: 0 !important;
            }
            
            /* ===== FONT SIZES ===== */
            .text-lg {
                font-size: 9pt !important;
            }
            
            .text-sm {
                font-size: 7pt !important;
            }
            
            .text-xs {
                font-size: 6.5pt !important;
            }
            
            /* ===== TRUNCATE ===== */
            .truncate {
                overflow: visible !important;
                text-overflow: clip !important;
                white-space: normal !important;
                max-width: none !important;
            }
            
            .bg-yellow-50 .truncate {
                white-space: normal !important;
                overflow: visible !important;
                text-overflow: clip !important;
            }
            
            /* ===== REMOVE EXTRA SPACING ===== */
            .client-card .mb-8 {
                margin-bottom: 0 !important;
            }
            
            .client-card .border {
                border: none !important;
            }
            
            .client-card .rounded-lg {
                border-radius: 0 !important;
            }
            
            .client-card .overflow-hidden {
                overflow: visible !important;
            }
            
            .overflow-x-auto {
                overflow: visible !important;
                width: 100% !important;
            }
            
            #printContainer > *:first-child {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* ===== PAIRED ROW INDENT ===== */
            .pl-6 {
                padding-left: 12px !important;
            }
            
            /* ===== COLUMN WIDTHS ===== */
            th:first-child, td:first-child {
                padding-left: 2px !important;
            }
            
            th:last-child, td:last-child {
                padding-right: 2px !important;
            }
            
            /* ===== FIX: Don't let table break across pages ===== */
            .client-card table {
                page-break-inside: auto !important;
            }
            
            .client-card tbody tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            
            .client-card thead {
                display: table-header-group !important;
            }

            .client-card .report-header-row {
                display: table-row !important;
                page-break-inside: avoid !important;
            }

            .client-card .report-header-row th {
                padding: 4px 6px !important;
                border-bottom: 2px solid #333 !important;
                background: white !important;
                color: #000 !important;
                text-align: left !important;
                text-transform: none !important;
                font-size: 6pt !important;
                font-weight: normal !important;
            }

            .client-card .report-header-content {
                display: flex !important;
                justify-content: space-between !important;
                gap: 12px !important;
            }

            .client-card .report-header-right {
                text-align: right !important;
            }

            .client-card .physical-page-number {
                display: inline-block !important;
                min-width: 1em;
            }
            
            .client-card tfoot {
                display: table-row-group !important;
            }
        }

        /* ===== KEYFRAMES ===== */
        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    @section('page_title', 'Customer Balance Report - ' . date('Y-m-d'))

    {{-- NOTIFICATION --}}
    @if (session('message'))
        <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 lg:ml-32 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex flex-row justify-between px-2 no-print">
            <div class="flex">
                <i class="self-center uil uil-cloud-check text-emerald-800 text-2xl"></i>
                <h1 class="self-center text-emerald-800 font-semibold ml-1">{{ session('message') }}</h1>
            </div>
            <button class="notifButton self-center">
                <i class="uil uil-times text-2xl text-emerald-800"></i>
            </button>
        </div>
    @endif
    {{-- NOTIFICATION END --}}

    {{-- SCROLLABLE PAGE WRAPPER --}}
    <div class="filter-page-wrapper">
        <div class="lg:ml-64">
            {{-- FILTER CONTROLS - STICKY WRAPPER --}}
            <div class="filter-sticky-wrapper no-print">
                <form method="GET"
                    action="{{ route('ar.customer-balance') }}"
                    id="filterForm"
                    class="filter-container">

                    {{-- CLIENT SEARCH - FIXED DROPDOWN --}}
                    <div class="filter-group" style="position: relative; overflow: visible; z-index: 1000; min-width: 200px;">
                        <label for="client_search">Client</label>
                        <div class="searchable-dropdown-wrapper">
                            <input 
                                type="text" 
                                id="client_search" 
                                class="w-full px-2 py-1.5 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-xs" 
                                placeholder="Search client..."
                                autocomplete="off"
                                oninput="filterClients()"
                                onfocus="showClientDropdown()"
                                onkeydown="handleClientSearchKeydown(event)"
                                value="{{ $selectedClient != 'all' && $selectedClient ? $clients->firstWhere('id', (int)$selectedClient)->name ?? '' : '' }}"
                            >
                            <input type="hidden" name="client_filter" id="selected_client_id" value="{{ $selectedClient ?? 'all' }}">
                            <div id="clientDropdown" class="dropdown-list">
                                <div class="all-items-header" onclick="selectClient('all', 'All Clients')">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                    <span>All Clients</span>
                                </div>
                                <div class="dropdown-items-container">
                                    @foreach ($clients as $client)
                                        <div class="dropdown-item client-option" 
                                            data-value="{{ $client->id }}" 
                                            data-text="{{ htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8') }}"
                                            onclick="selectClient('{{ $client->id }}', '{{ htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8') }}')">
                                            <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="item-text">{{ $client->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="clientNoResults" class="no-results" style="display: none;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p>No clients found</p>
                                    <p class="sub-text">Try different keywords</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEARCH --}}
                    <div class="filter-group">
                        <label for="client_name">Search</label>
                        <input type="text"
                            name="client_name"
                            id="client_name"
                            value="{{ request('client_name', '') }}"
                            placeholder="Trans # or client..."
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-md
                                    focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- DATE RANGE --}}
                    <div class="filter-group">
                        <div class="date-range-wrapper">
                            <span class="date-range-label">Date Range</span>
                            <div class="date-inputs-row">
                                <div class="date-input-wrapper">
                                    <svg class="date-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd">
                                        </path>
                                    </svg>
                                    <input name="startDate"
                                        id="startDate"
                                        value="{{ date('m/d/Y', strtotime($startDate)) }}"
                                        type="text"
                                        class="datepicker-input bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-md
                                                focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="From Date"
                                        readonly>
                                </div>

                                <span class="date-separator">to</span>

                                <div class="date-input-wrapper">
                                    <svg class="date-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd">
                                        </path>
                                    </svg>
                                    <input name="endDate"
                                        id="endDate"
                                        value="{{ date('m/d/Y', strtotime($endDate)) }}"
                                        type="text"
                                        class="datepicker-input bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-md
                                                focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="To Date"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="filter-group status-group">
                        <label for="statusFilter">Status</label>
                        <select name="status"
                                id="statusFilter"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-md
                                    focus:ring-blue-500 focus:border-blue-500">
                            <option value="all"
                                {{ $selectedStatus == 'all' ? 'selected' : '' }}>
                                All
                            </option>
                            <option value="PAID"
                                {{ $selectedStatus == 'PAID' ? 'selected' : '' }}>
                                Paid
                            </option>
                            <option value="UNPAID"
                                {{ $selectedStatus == 'UNPAID' ? 'selected' : '' }}>
                                Unpaid
                            </option>
                            <option value="PARTIAL"
                                {{ $selectedStatus == 'PARTIAL' ? 'selected' : '' }}>
                                Partial
                            </option>
                        </select>
                    </div>

                    {{-- BUTTONS --}}
                    <div class="filter-buttons">
                        <span class="btn-label">Actions</span>
                        <div class="btn-row">
                            <button type="submit" class="btn btn-filter">
                                <i class="uil uil-search"></i>
                                <span>Filter</span>
                            </button>

                            <a href="{{ route('ar.customer-balance') }}" class="btn btn-reset">
                                <i class="uil uil-refresh"></i>
                                <span>Reset</span>
                            </a>

                            <button type="button"
                                    onclick="printAllClients()"
                                    class="btn btn-print-all">
                                <i class="uil uil-print"></i>
                                <span>Print All</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            {{-- FILTER CONTROLS END --}}        
            
            <div id="contentDiv" class="p-2 w-full">
                <div class="bg-white overflow-hidden shadow-md rounded-lg pl-2 pr-2">
                    {{-- PRINT CONTAINER --}}
                    <div id="printContainer" class="content-wrapper">
                        {{-- CLIENT LIST --}}

                            @php
                                $groupedResults = $results->groupBy(function($item) {
                                    return $item->table . '|' . $item->table_name;
                                });
                                $clientCounter = 0;
                                $totalClients = $groupedResults->count();

                                // Initialize grand totals
                                $grandTotalCharges = 0;
                                $grandTotalCredits = 0;
                                $grandTotalAllocated = 0;
                                $grandTotalOutstanding = 0;
                                
                                // Store client totals for grand total calculation
                                $clientTotals = [];
                            @endphp

                            @forelse ($groupedResults as $groupKey => $clientTransactions)
                                @php
                                    $clientCounter++;
                                    
                                    // Extract table ID and name from the group key
                                    list($tableId, $tableName) = explode('|', $groupKey);
                                    
                                    // Get first transaction for client info
                                    $firstTrans = $clientTransactions->first();
                                    $payorName = $firstTrans->payor_name ?? '';
                                    
                                    // Separate transactions by type
                                    $openBalanceItems = $clientTransactions->filter(function($item) {
                                        return $item->trans_type === 'Open Balance';
                                    });
                                    $salesItems = $clientTransactions->filter(function($item) {
                                        return $item->trans_type === 'Sales Invoice';
                                    });
                                    $paymentItems = $clientTransactions->filter(function($item) {
                                        return $item->trans_type === 'Customer Payment';
                                    });
                                    
                                    // =============================================
                                    // Build transaction groups with PAIRED rows
                                    // =============================================
                                    // Get all unique transaction numbers from sales
                                    $salesByNumber = [];
                                    foreach ($salesItems as $item) {
                                        $key = $item->number ?? $item->id;
                                        $salesByNumber[$key] = $item;
                                    }
                                    
                                    // Get all unique transaction numbers from payments
                                    $paymentsByNumber = [];
                                    foreach ($paymentItems as $item) {
                                        $key = $item->number ?? $item->id;
                                        if (!isset($paymentsByNumber[$key])) {
                                            $paymentsByNumber[$key] = [];
                                        }
                                        $paymentsByNumber[$key][] = $item;
                                    }
                                    
                                    // Merge: For each sales transaction, create a group with its payments
                                    $transactionGroups = [];
                                    
                                    // First, add all sales transactions with their payments
                                    foreach ($salesByNumber as $number => $salesItem) {
                                        $groupKey = $number . '|' . $tableId;
                                        $transactionGroups[$groupKey] = [
                                            'sales' => $salesItem,
                                            'payments' => isset($paymentsByNumber[$number]) ? $paymentsByNumber[$number] : [],
                                            'number' => $number,
                                            'is_sales' => true,
                                            'table_id' => $tableId
                                        ];
                                        unset($paymentsByNumber[$number]);
                                    }
                                    
                                    // Now add any remaining payments that don't have a sales invoice
                                    foreach ($paymentsByNumber as $number => $paymentList) {
                                        $groupKey = 'payment_' . $number . '|' . $tableId;
                                        $transactionGroups[$groupKey] = [
                                            'sales' => null,
                                            'payments' => $paymentList,
                                            'number' => $number,
                                            'is_sales' => false,
                                            'table_id' => $tableId
                                        ];
                                    }
                                    
                                    // Now sort groups by created_at
                                    $sortedGroups = [];
                                    foreach ($transactionGroups as $key => $group) {
                                        $sortDate = $group['sales'] 
                                            ? $group['sales']->created_at 
                                            : ($group['payments'][0]->created_at ?? now());
                                        $sortedGroups[$sortDate . '_' . $key] = $group;
                                    }
                                    ksort($sortedGroups);
                                    
                                    // Rebuild transactionGroups with sorted order
                                    $transactionGroups = array_values($sortedGroups);
                                    
                                    // =============================================
                                    // FIXED: Calculate open balance totals
                                    // =============================================
                                    // For Open Balance: charges = total owed, credits = 0, allocated = 0
                                    // Outstanding = chargaes (full amount is outstanding)
                                    $openCharges = $openBalanceItems->sum('charges');
                                    $openCredits = $openBalanceItems->sum('credits');
                                    $openAllocated = $openBalanceItems->sum('allocated');
                                    $openOutstanding = $openCredits - $openAllocated;
                                    
                                    // Initialize client totals (EXCLUDING open balance)
                                    $clientCharges = 0;
                                    $clientCredits = 0;
                                    $clientAllocated = 0;
                                    $clientOutstanding = 0;

                                    $clientCharges += $openCharges;
                                    $clientCredits += $openCredits;
                                    $clientAllocated += $openAllocated;
                                    $clientOutstanding += $openOutstanding;
                                    // =============================================
                                    // FIXED: Calculate totals from transaction groups
                                    // =============================================
                                    foreach ($transactionGroups as $group) {
                                        $salesItem = $group['sales'];
                                        $payments = $group['payments'];
                                        
                                        if ($salesItem) {
                                            // Sales Invoice: charges = total, credits = 0
                                            $salesCharges = $salesItem->charges ?? 0;
                                            $salesCredits = 0; // FIXED: Sales Invoice has no credits
                                            $salesAllocated = $salesItem->allocated ?? 0;
                                            $salesOutstanding = $openOutstanding - $salesAllocated; // FIXED: outstanding = charges - allocated
                                            
                                            $clientCharges += $salesCharges;
                                            $clientCredits += $salesCredits;
                                            $clientAllocated += $salesAllocated;
                                            $clientOutstanding += $salesOutstanding;
                                        }
                                        
                                        foreach ($payments as $payment) {
                                            // Customer Payment: charges = 0, credits = payment amount
                                            $paymentCharges = 0; // FIXED: Payment has no charges
                                            $paymentCredits = $payment->credits ?? 0;
                                            $paymentAllocated = $payment->allocated ?? 0;
                                            $paymentOutstanding = $paymentCredits - $paymentAllocated; // Remaining unapplied payment
                                            
                                            $clientCharges += $paymentCharges;
                                            $clientCredits += $paymentCredits;
                                            $clientAllocated += $paymentAllocated;
                                            $clientOutstanding += $paymentOutstanding;
                                        }
                                    }
                                    
                                    // =============================================
                                    // FIXED: Calculate client totals (including open balance)
                                    // =============================================
                                    $totalClientCharges = $clientCharges;
                                    $totalClientCredits = $clientCredits;
                                    $totalClientAllocated = $clientAllocated;
                                    $totalClientOutstanding = $clientOutstanding;
                                    
                                    // Store client totals for grand total
                                    $clientTotals[] = [
                                        'charges' => $totalClientCharges,
                                        'credits' => $totalClientCredits,
                                        'allocated' => $totalClientAllocated,
                                        'outstanding' => $totalClientOutstanding
                                    ];
                                    
                                    // Add to grand totals
                                    $grandTotalCharges += $totalClientCharges;
                                    $grandTotalCredits += $totalClientCredits;
                                    $grandTotalAllocated += $totalClientAllocated;
                                    $grandTotalOutstanding += $totalClientOutstanding;
                            @endphp

                            {{-- CLIENT CONTAINER --}}
                            <div class="client-card mt-2 mb-8 border rounded-lg overflow-hidden print-client-container" 
                                data-client-id="{{ $tableId }}"
                                data-client-name="{{ $tableName }}">
                                
                                {{-- STAR PIN BUTTON --}}
                                <button onclick="togglePin('{{ $tableId }}')" 
                                        class="pin-btn no-print" 
                                        data-client-id="{{ $tableId }}"
                                        title="Pin this client"
                                        type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                        width="18" 
                                        height="18" 
                                        viewBox="0 0 24 24" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        stroke-width="2" 
                                        stroke-linecap="round" 
                                        stroke-linejoin="round">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                </button>

                            {{-- ============================================= --}}
                            {{-- BUILD FLAT LIST OF PRINTABLE ROWS FOR THIS CLIENT --}}
                            {{-- ============================================= --}}
                            @php
                                $printRows = collect();

                                // Row 1: Open Balance (always first)
                                $printRows->push([
                                    'type' => 'open_balance',
                                    'charges' => $openCharges,
                                    'credits' => $openCredits,
                                    'allocated' => $openAllocated,
                                    'outstanding' => max($openOutstanding, 0),
                                ]);

                                // Sales + Payment paired rows, in existing sorted order
                                foreach ($transactionGroups as $group) {
                                    $salesItem = $group['sales'];
                                    $payments = $group['payments'];

                                    if ($salesItem) {
                                        $charges = $salesItem->charges ?? 0;
                                        $salesAllocated = $salesItem->allocated ?? 0;
                                        $outstanding = $charges - $salesAllocated;

                                        $printRows->push([
                                            'type' => 'sales',
                                            'number' => $salesItem->number ?? 'N/A',
                                            'date' => $salesItem->created_at ? date('Y-m-d', strtotime($salesItem->created_at)) : '0000-00-00',
                                            'due_date' => $salesItem->updated_at ? date('Y-m-d', strtotime($salesItem->updated_at . ' +7 days')) : '0000-00-00', // added only ng 7days para sa duedate 
                                            'charges' => $charges,
                                            'credits' => 0,
                                            'allocated' => $salesAllocated,
                                            'outstanding' => $outstanding,
                                        ]);
                                    }

                                    foreach ($payments as $payment) {
                                        $paymentCredits = $payment->credits ?? 0;
                                        $paymentAllocated = $payment->allocated ?? 0;
                                        $paymentOutstanding = $salesItem
                                            ? ($paymentAllocated - $paymentCredits)
                                            : ($paymentCredits - $paymentAllocated);

                                        $printRows->push([
                                            'type' => 'payment',
                                            'number' => $payment->number ?? 'N/A',
                                            'date' => $payment->created_at ? date('Y-m-d', strtotime($payment->created_at)) : '0000-00-00',
                                            'due_date' => null,
                                            'charges' => 0,
                                            'credits' => $paymentCredits,
                                            'allocated' => $paymentAllocated,
                                            'outstanding' => $paymentOutstanding,
                                        ]);
                                    }
                                }

                                if ($printRows->count() === 1) {
                                    // Only the open balance row exists — flag "no transactions" state
                                    $printRows->push(['type' => 'empty']);
                                }
                                // ===== RESTORE $totalOutstanding (sum of sales + payment rows, excluding open balance) =====
                                $totalOutstanding = $printRows
                                    ->whereIn('type', ['sales', 'payment'])
                                    ->sum('outstanding');
                                // ===== CHUNK INTO PRINT-SIZED PAGES =====
                                $rowsPerPage = 60; // adjust to your tested capacity
                                $printChunks = $printRows->chunk($rowsPerPage)->values();
                                $totalPrintPages = $printChunks->count();
                            @endphp

                                <div class="overflow-x-auto">

                                    {{-- ============================================= --}}
                                    {{-- SCREEN VERSION: one continuous table, header shown ONCE --}}
                                    {{-- ============================================= --}}
                                    <table class="w-full text-sm text-left text-gray-700 border-collapse screen-only-table">
                                        <thead class="text-xs text-gray-700 uppercase bg-gradient-to-r from-blue-50 to-blue-100">
                                            <tr class="border-b border-gray-200">
                                                <th class="px-4 py-2 text-left" style="min-width: 120px;">TRANS TYPE</th>
                                                <th class="px-4 py-2 text-center">#</th>
                                                <th class="px-4 py-2 text-center">DATE</th>
                                                <th class="px-4 py-2 text-center">DUE DATE</th>
                                                <th class="px-4 py-2 text-right">CHARGES</th>
                                                <th class="px-4 py-2 text-right">CREDITS</th>
                                                <th class="px-4 py-2 text-right">ALLOCATED</th>
                                                <th class="px-4 py-2 text-right">OUTSTANDING</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($printRows as $row)
                                                @if ($row['type'] === 'open_balance')
                                                    <tr class="bg-yellow-50 font-semibold">
                                                        <td colspan="3" class="px-4 py-2 text-left">
                                                            <span class="text-lg font-bold text-gray-800 truncate block max-w-xs" title="{{ $tableName ?: 'Unknown Table' }}">
                                                                {{ $tableName ?: 'Unknown Table' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-left italic">Open Balance</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right
                                                            @if($row['outstanding'] > 0) text-red-600
                                                            @elseif($row['outstanding'] < 0) text-green-600
                                                            @else text-gray-600 @endif">
                                                            ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                                        </td>
                                                    </tr>

                                                @elseif ($row['type'] === 'sales')
                                                    <tr class="border-b hover:bg-gray-50 even:bg-gray-50">
                                                        <td class="px-4 py-2 text-left font-medium text-blue-700">Sales Invoice</td>
                                                        <td class="px-4 py-2 text-center font-mono">{{ $row['number'] }}</td>
                                                        <td class="px-4 py-2 text-center">{{ $row['date'] }}</td>
                                                        <td class="px-4 py-2 text-center">{{ $row['due_date'] }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right font-bold
                                                            @if($row['outstanding'] > 0) text-red-600
                                                            @elseif($row['outstanding'] < 0) text-green-600
                                                            @else text-gray-600 @endif">
                                                            ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                                        </td>
                                                    </tr>

                                                @elseif ($row['type'] === 'payment')
                                                    <tr class="border-b hover:bg-gray-50 even:bg-gray-50">
                                                        <td class="px-4 py-2 text-left text-green-600 pl-6">Customer Payment</td>
                                                        <td class="px-4 py-2 text-center font-mono">{{ $row['number'] }}</td>
                                                        <td class="px-4 py-2 text-center">{{ $row['date'] }}</td>
                                                        <td class="px-4 py-2 text-center">-</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                                        <td class="px-4 py-2 text-right font-bold
                                                            @if($row['outstanding'] > 0) text-red-600
                                                            @elseif($row['outstanding'] < 0) text-green-600
                                                            @else text-gray-600 @endif">
                                                            ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                                        </td>
                                                    </tr>

                                                @elseif ($row['type'] === 'empty')
                                                    <tr class="border-b hover:bg-gray-50 even:bg-gray-50 text-gray-400">
                                                        <td class="px-4 py-2 text-left" colspan="8">No transactions in this period</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>

                                        <tfoot class="font-bold">
                                            <tr class="bg-gray-100 border-t-2 border-gray-300">
                                                <td colspan="4" class="px-4 py-2 text-right text-gray-700">TOTAL</td>
                                                <td class="px-4 py-2 text-right">₱ {{ number_format($clientCharges, 2, '.', ',') }}</td>
                                                <td class="px-4 py-2 text-right">₱ {{ number_format($clientCredits, 2, '.', ',') }}</td>
                                                <td class="px-4 py-2 text-right">₱ {{ number_format($clientAllocated, 2, '.', ',') }}</td>
                                                <td class="px-4 py-2 text-right text-gray-600">
                                                    ₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}
                                                </td>
                                            </tr>

                                            <tr class="border-t border-gray-300 bg-blue-50">
                                                <td class="px-4 py-2 text-left">
                                                    <button onclick="printSingleClient('{{ $tableId }}')"
                                                            class="client-print-btn bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg transition-colors duration-200 print-btn no-print flex items-center shadow-sm text-sm">
                                                        <i class="uil uil-print mr-2"></i>
                                                        <span>Print</span>
                                                    </button>
                                                </td>
                                                <td colspan="3" class="px-4 py-2 text-right text-gray-700">GRAND TOTAL:</td>
                                                <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientCharges, 2, '.', ',') }}</td>
                                                <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientCredits, 2, '.', ',') }}</td>
                                                <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientAllocated, 2, '.', ',') }}</td>
                                                <td class="px-4 py-2 text-right text-gray-600">
                                                    ₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    {{-- ============================================= --}}
                                    {{-- PRINT VERSION: chunked tables, one header PER PRINTED PAGE --}}
                                    {{-- ============================================= --}}
                                    <div class="print-only-tables">
                                        @foreach ($printChunks as $pageIndex => $chunkRows)
                                            <table class="w-full text-sm text-left text-gray-700 border-collapse
                                                        {{ !$loop->last ? 'print-page-break' : '' }}">
                                                <thead class="text-xs text-gray-700 uppercase bg-gradient-to-r from-blue-50 to-blue-100">
                                                    <tr class="report-header-row">
                                                        <th colspan="8">
                                                            <div class="report-header-content">
                                                                <div>
                                                                    <strong class="text-2xl font-bold">Customer Balances</strong><br>
                                                                    Print Out Date: {{ date('Y-m-d H:i') }}<br>
                                                                    Fiscal Year: {{ date('Y') }}-01-01 - {{ date('Y') }}-12-31 (Active)<br>
                                                                    Period: {{ date('Y-m-d', strtotime($startDate)) }} - {{ date('Y-m-d', strtotime($endDate)) }}<br>
                                                                    Customer: {{ $tableName }}<br>
                                                                    Suppress Zeros: No
                                                                </div>
                                                                <div class="report-header-right">
                                                                    <strong>{{ $settings->name ?? 'KUMPADRES / LIMEATLESS TRADING CORP' }}</strong><br>
                                                                    {{ $settings->email ?? 'tindahan9181.bitpos.pro' }}<br>
                                                                    {{ auth()->user()->lastname.', '.auth()->user()->firstname ?? 'N/A' }}<br>
                                                                    <br>
                                                                    <span class="page-number">Page {{ $pageIndex + 1 }} of {{ $totalPrintPages }}</span>
                                                                </div>
                                                            </div>
                                                        </th>
                                                    </tr>
                                                    <tr class="border-b border-gray-200">
                                                        <th class="px-4 py-2 text-left" style="min-width: 120px;">TRANS TYPE</th>
                                                        <th class="px-4 py-2 text-center">#</th>
                                                        <th class="px-4 py-2 text-center">DATE</th>
                                                        <th class="px-4 py-2 text-center">DUE DATE</th>
                                                        <th class="px-4 py-2 text-right">CHARGES</th>
                                                        <th class="px-4 py-2 text-right">CREDITS</th>
                                                        <th class="px-4 py-2 text-right">ALLOCATED</th>
                                                        <th class="px-4 py-2 text-right">OUTSTANDING</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($chunkRows as $row)
                                                        @if ($row['type'] === 'open_balance')
                                                            <tr class="bg-yellow-50 font-semibold">
                                                                <td colspan="3" class="px-4 py-2 text-left">
                                                                    <span class="text-lg font-bold text-gray-800 truncate block max-w-xs" title="{{ $tableName ?: 'Unknown Table' }}">
                                                                        {{ $tableName ?: 'Unknown Table' }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-2 text-left italic">Open Balance</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right
                                                                    @if($row['outstanding'] > 0) text-red-600
                                                                    @elseif($row['outstanding'] < 0) text-green-600
                                                                    @else text-gray-600 @endif">
                                                                    ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                                                </td>
                                                            </tr>

                                                        @elseif ($row['type'] === 'sales')
                                                            <tr class="border-b hover:bg-gray-50 even:bg-gray-50">
                                                                <td class="px-4 py-2 text-left font-medium text-blue-700">Sales Invoice</td>
                                                                <td class="px-4 py-2 text-center font-mono">{{ $row['number'] }}</td>
                                                                <td class="px-4 py-2 text-center">{{ $row['date'] }}</td>
                                                                <td class="px-4 py-2 text-center">{{ $row['due_date'] }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right font-bold
                                                                    @if($row['outstanding'] > 0) text-red-600
                                                                    @elseif($row['outstanding'] < 0) text-green-600
                                                                    @else text-gray-600 @endif">
                                                                    ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                                                </td>
                                                            </tr>

                                                        @elseif ($row['type'] === 'payment')
                                                            <tr class="border-b hover:bg-gray-50 even:bg-gray-50">
                                                                <td class="px-4 py-2 text-left text-green-600 pl-6">Customer Payment</td>
                                                                <td class="px-4 py-2 text-center font-mono">{{ $row['number'] }}</td>
                                                                <td class="px-4 py-2 text-center">{{ $row['date'] }}</td>
                                                                <td class="px-4 py-2 text-center">-</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                                                <td class="px-4 py-2 text-right font-bold
                                                                    @if($row['outstanding'] > 0) text-red-600
                                                                    @elseif($row['outstanding'] < 0) text-green-600
                                                                    @else text-gray-600 @endif">
                                                                    ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                                                </td>
                                                            </tr>

                                                        @elseif ($row['type'] === 'empty')
                                                            <tr class="border-b hover:bg-gray-50 even:bg-gray-50 text-gray-400">
                                                                <td class="px-4 py-2 text-left" colspan="8">No transactions in this period</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>

                                                @if ($loop->last)
                                                    <tfoot class="font-bold">
                                                        <tr class="bg-gray-100 border-t-2 border-gray-300">
                                                            <td colspan="4" class="px-4 py-2 text-right text-gray-700">TOTAL</td>
                                                            <td class="px-4 py-2 text-right">₱ {{ number_format($clientCharges, 2, '.', ',') }}</td>
                                                            <td class="px-4 py-2 text-right">₱ {{ number_format($clientCredits, 2, '.', ',') }}</td>
                                                            <td class="px-4 py-2 text-right">₱ {{ number_format($clientAllocated, 2, '.', ',') }}</td>
                                                            <td class="px-4 py-2 text-right text-gray-600">
                                                                ₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}
                                                            </td>
                                                        </tr>

                                                        <tr class="border-t border-gray-300 bg-blue-50">
                                                            <td class="px-4 py-2 text-left"></td>
                                                            <td colspan="3" class="px-4 py-2 text-right text-gray-700">GRAND TOTAL:</td>
                                                            <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientCharges, 2, '.', ',') }}</td>
                                                            <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientCredits, 2, '.', ',') }}</td>
                                                            <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientAllocated, 2, '.', ',') }}</td>
                                                            <td class="px-4 py-2 text-right text-gray-600">
                                                                ₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                @endif
                                            </table>
                                        @endforeach
                                    </div>

                                </div>

                            </div>
                        @empty
                            <div class="text-center py-12 bg-gray-50 rounded-lg">
                                <i class="uil uil-chair text-6xl text-gray-400 mb-4"></i>
                                <h3 class="text-xl font-medium text-gray-600">No transactions found</h3>
                                <p class="text-gray-500 mt-2">Try adjusting your search filters or date range.</p>
                            </div>
                        @endforelse
                    </div>
                    {{-- PRINT CONTAINER END --}}
                </div>

                {{-- GRAND TOTAL - STICKY --}}
                @if($results->isNotEmpty())
                    <div class="grand-total-wrapper no-print">
                        {{-- PAGINATION --}}
                        <div class="flex justify-between items-center mt-6 no-print">
                            <div class="text-sm text-gray-600">
                                Showing {{ $results->firstItem() ?? 0 }} to {{ $results->lastItem() ?? 0 }} of {{ $results->total() }} results
                            </div>
                            <div>
                                {{ $results->appends(request()->query())->links() }}
                            </div>
                        </div>
                        {{-- PAGINATION END --}}                        
                        <div class="grand-total-content">
                            <div class="grand-total-grid">
                                <div class="grand-total-item">
                                    <span class="grand-total-label">Total Clients</span>
                                    <span class="grand-total-value text-blue-600">{{ $groupedResults->count() }}</span>
                                </div>
                                <div class="grand-total-item">
                                    <span class="grand-total-label">Total Trans</span>
                                    <span class="grand-total-value text-gray-800">{{ $results->count() }}</span>
                                </div>
                                <div class="grand-total-item">
                                    <span class="grand-total-label">Total Charges</span>
                                    <span class="grand-total-value text-gray-800">₱ {{ number_format($grandTotalCharges, 2, '.', ',') }}</span>
                                </div>
                                <div class="grand-total-item">
                                    <span class="grand-total-label">Total Credits</span>
                                    <span class="grand-total-value text-green-600">₱ {{ number_format($grandTotalCredits, 2, '.', ',') }}</span>
                                </div>
                                <div class="grand-total-item">
                                    <span class="grand-total-label">Total Allocations</span>
                                    <span class="grand-total-value text-purple-600">₱ {{ number_format($grandTotalAllocated, 2, '.', ',') }}</span>
                                </div>                                
                                <div class="grand-total-item">
                                    <span class="grand-total-label">Total Outstanding</span>
                                    <span class="grand-total-value text-red-600">₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- GRAND TOTAL END --}}            
            </div>
        </div>
    </div>
    {{-- SCROLLABLE PAGE WRAPPER END --}}

    {{-- Include Select2 CSS and JS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    {{-- jQuery UI for Datepicker --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    window.PagedConfig = {
        auto: false // don't repaginate on normal page load, only when we call it
    };

    $(document).ready(function() {
        // Initialize Select2 for client dropdown
        $('#clientFilter').select2({
            placeholder: 'Search for a client...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: '{{ route("clients.search") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            templateResult: formatClient,
            templateSelection: formatClientSelection
        });

        function formatClient(client) {
            if (client.loading) {
                return client.text;
            }
            var $container = $(
                '<div class="flex items-center">' +
                '<span>' + client.text + '</span>' +
                '</div>'
            );
            return $container;
        }

        function formatClientSelection(client) {
            return client.text || client.id;
        }

        $('#clientFilter').on('change', function() {
            var form = $(this).closest('form');
            var searchValue = $('#client_name').val();
            if (searchValue) {
                $('#client_name').val(searchValue);
            }
            form.submit();
        });

        $('#client_name').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                var form = $(this).closest('form');
                form.submit();
            }
        });

        $('#filterForm').on('submit', function(e) {
            var searchValue = $('#client_name').val();
            if (searchValue) {
                $('#client_name').val(searchValue);
            }
            return true;
        });

        // ============================================
        // DATE PICKER
        // ============================================
        function initDatepickers() {
            try {
                $('#startDate, #endDate').datepicker('destroy');
            } catch(e) {}
            
            var datepickerOptions = {
                dateFormat: 'mm/dd/yy',
                changeMonth: true,
                changeYear: true,
                showAnim: 'slideDown',
                showButtonPanel: true,
                yearRange: '-5:+5',
                onClose: function(selectedDate) {
                    var target = $(this).attr('id') === 'startDate' ? '#endDate' : '#startDate';
                    var targetDate = $(target).datepicker('getDate');
                    var currentDate = $(this).datepicker('getDate');
                    
                    if (target === '#endDate' && currentDate && targetDate && currentDate > targetDate) {
                        $(target).datepicker('setDate', currentDate);
                    } else if (target === '#startDate' && currentDate && targetDate && currentDate < targetDate) {
                        $(target).datepicker('setDate', currentDate);
                    }
                }
            };

            $('#startDate').datepicker({
                ...datepickerOptions,
                defaultDate: new Date('{{ date('m/d/Y', strtotime($startDate)) }}'),
                onSelect: function(selectedDate) {
                    var endDate = $('#endDate').datepicker('getDate');
                    var startDate = $(this).datepicker('getDate');
                    
                    if (startDate && endDate && startDate > endDate) {
                        $('#endDate').datepicker('setDate', startDate);
                    }
                }
            });

            $('#endDate').datepicker({
                ...datepickerOptions,
                defaultDate: new Date('{{ date('m/d/Y', strtotime($endDate)) }}'),
                onSelect: function(selectedDate) {
                    var startDate = $('#startDate').datepicker('getDate');
                    var endDate = $(this).datepicker('getDate');
                    
                    if (startDate && endDate && endDate < startDate) {
                        $('#startDate').datepicker('setDate', endDate);
                    }
                }
            });

            $('#startDate').datepicker('setDate', '{{ date('m/d/Y', strtotime($startDate)) }}');
            $('#endDate').datepicker('setDate', '{{ date('m/d/Y', strtotime($endDate)) }}');
        }

        initDatepickers();

        $(document).ajaxComplete(function() {
            initDatepickers();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.ui-datepicker, .datepicker-input, .date-input-wrapper').length) {
                $('.ui-datepicker').hide();
            }
        });

        // ============================================
        // PINNING CLIENTS - STAR VERSION
        // ============================================
        function getPinnedClients() {
            try {
                return JSON.parse(localStorage.getItem('pinned_clients')) || [];
            } catch (e) {
                return [];
            }
        }

        function setPinnedClients(pinned) {
            localStorage.setItem('pinned_clients', JSON.stringify(pinned));
        }

        window.togglePin = function(clientId) {
            let pinned = getPinnedClients();
            const index = pinned.indexOf(clientId);
            if (index > -1) {
                pinned.splice(index, 1);
            } else {
                pinned.push(clientId);
            }
            setPinnedClients(pinned);
            updatePinIcons();
            reorderClients();
        };

        function updatePinIcons() {
            const pinned = getPinnedClients();
            document.querySelectorAll('.pin-btn').forEach(btn => {
                const id = btn.dataset.clientId;
                if (pinned.includes(id)) {
                    btn.classList.add('pinned');
                } else {
                    btn.classList.remove('pinned');
                }
            });
        }

        function reorderClients() {
            const container = document.querySelector('#printContainer');
            if (!container) return;
            const cards = Array.from(container.querySelectorAll('.client-card'));
            const pinned = getPinnedClients();

            // Sort: pinned first, then others (preserve original order for non-pinned)
            const pinnedCards = [];
            const unpinnedCards = [];

            cards.forEach(card => {
                const id = card.dataset.clientId;
                if (pinned.includes(id)) {
                    pinnedCards.push(card);
                } else {
                    unpinnedCards.push(card);
                }
            });

            // Remove all cards and re-append in order
            cards.forEach(card => card.remove());
            pinnedCards.forEach(card => container.appendChild(card));
            unpinnedCards.forEach(card => container.appendChild(card));

        }

        // Initialize pin icons and reorder on page load
        updatePinIcons();
        reorderClients();
    });

    // ===== SEARCHABLE CLIENT DROPDOWN FUNCTIONS =====
    window.filterClients = function() {
        const input = document.getElementById('client_search');
        const filter = input.value.toLowerCase().trim();
        const dropdown = document.getElementById('clientDropdown');
        const items = dropdown.querySelectorAll('.client-option');
        const noResults = document.getElementById('clientNoResults');
        
        let hasVisible = false;
        
        items.forEach(item => {
            const text = item.getAttribute('data-text').toLowerCase();
            const value = item.getAttribute('data-value').toLowerCase();
            
            if (text.includes(filter) || value.includes(filter)) {
                item.style.display = 'flex';
                hasVisible = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        if (noResults) {
            if (!hasVisible && filter !== '') {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
        
        if (filter !== '' || hasVisible) {
            dropdown.classList.add('visible');
        }
    };
    
    window.showClientDropdown = function() {
        const dropdown = document.getElementById('clientDropdown');
        dropdown.classList.add('visible');
        filterClients();
    };
    
    window.selectClient = function(value, text) {
        document.getElementById('client_search').value = text;
        document.getElementById('selected_client_id').value = value;
        document.getElementById('clientDropdown').classList.remove('visible');
    };
    
    window.handleClientSearchKeydown = function(event) {
        const dropdown = document.getElementById('clientDropdown');
        const visibleItems = Array.from(dropdown.querySelectorAll('.client-option:not([style*="display: none"])'));
        const allItemsHeader = dropdown.querySelector('.all-items-header');
        
        let allItems = [];
        if (allItemsHeader && allItemsHeader.style.display !== 'none') {
            allItems = [allItemsHeader];
        }
        const allVisible = [...allItems, ...visibleItems];
        
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            
            const currentFocus = dropdown.querySelector('.active');
            let index = allVisible.indexOf(currentFocus);
            
            if (event.key === 'ArrowDown') {
                index = (index + 1) % allVisible.length;
            } else {
                index = (index - 1 + allVisible.length) % allVisible.length;
            }
            
            dropdown.querySelectorAll('.active').forEach(item => {
                item.classList.remove('active');
            });
            
            if (allVisible[index]) {
                allVisible[index].classList.add('active');
                allVisible[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        } else if (event.key === 'Enter') {
            event.preventDefault();
            
            const highlighted = dropdown.querySelector('.active');
            if (highlighted) {
                highlighted.click();
            } else if (allVisible.length === 1) {
                allVisible[0].click();
            }
        } else if (event.key === 'Escape') {
            dropdown.classList.remove('visible');
        }
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.searchable-dropdown-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            const dropdown = document.getElementById('clientDropdown');
            if (dropdown) {
                dropdown.classList.remove('visible');
            }
        }
    });

    // ============================================
    // PAGE NUMBERING FALLBACK
    // ============================================
    function preparePageNumbers() {
        var printContent = document.getElementById('printContainer');
        if (!printContent) return;

        var pages = printContent.querySelectorAll('.client-card.printing-client, .client-card:not(.hidden-client)');
        if (pages.length === 0) {
            pages = printContent.querySelectorAll('.client-card');
        }

        pages.forEach(function(page, index) {
            page.querySelectorAll('.page-counter').forEach(function(counter) {
                counter.textContent = (index + 1) + ' of ' + pages.length;
            });
        });
    }

    // ============================================
    // PRINT FUNCTIONS - OPEN IN NEW TAB
    // ============================================

    function getCustomerBalancePrintUrl() {
        var form = document.getElementById('filterForm');
        var params = new URLSearchParams(new FormData(form));

        // The screen query is paginated; print-all deliberately is not.
        params.delete('page');

        return '{{ route("ar.customer-balance.print-all") }}?' + params.toString();
    }

    // Browser print would otherwise print only the rows rendered on this page.
    document.addEventListener('keydown', function(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'p') {
            event.preventDefault();
            window.open(getCustomerBalancePrintUrl(), '_blank');
        }
    });

    /**
     * Print single client - opens new tab with all data for that client
     */
    window.printSingleClient = function(clientId) {
        // Show loading indicator
       /*  var loadingOverlay = $('<div id="printLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">' +
            '<div class="bg-white p-4 rounded-lg shadow-lg">Loading client data for printing...</div>' +
            '</div>');
        $('body').append(loadingOverlay); */
        
        // Get current filter values from the form
        var form = document.getElementById('filterForm');
        var formData = new FormData(form);
        var params = new URLSearchParams(formData);
        params.delete('page');
        
        // Build URL with current filters and client ID
        var printUrl = '{{ route("ar.customer-balance.print-single", "") }}/' + clientId + '?' + params.toString();
        
        // Open in NEW TAB
        var printTab = window.open(printUrl, '_blank');
        
        if (printTab) {
            printTab.onload = function() {
                // The print view will auto-print
            };
            printTab.onerror = function() {
                $('#printLoading').remove();
                alert('Error loading print data. Please try again.');
            };
        } else {
            $('#printLoading').remove();
            alert('Please allow pop-ups to print. Alternatively, you can use the "Print All" button.');
        }
        
        // Remove loading after a few seconds
        setTimeout(function() {
            $('#printLoading').remove();
        }, 5000);
    };

    /**
     * Print all clients - opens new tab with all data
     */
    window.printAllClients = function() {
        // Show loading indicator
        /* var loadingOverlay = $('<div id="printLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">' +
            '<div class="bg-white p-4 rounded-lg shadow-lg">Loading all client data for printing...</div>' +
            '</div>');
        $('body').append(loadingOverlay); */
        
        // Get current filter values from the form
        var form = document.getElementById('filterForm');
        var printUrl = getCustomerBalancePrintUrl();
        
        // Open in NEW TAB
        var printTab = window.open(printUrl, '_blank');
        
        if (printTab) {
            printTab.onload = function() {
                // The print view will auto-print
            };
            printTab.onerror = function() {
                $('#printLoading').remove();
                alert('Error loading print data. Please try again.');
            };
        } else {
            $('#printLoading').remove();
            alert('Please allow pop-ups to print. You can also try the browser\'s print function (Ctrl+P).');
        }
        
        // Remove loading after a few seconds
        setTimeout(function() {
            $('#printLoading').remove();
        }, 5000);
    };
</script>
</x-app-layout>