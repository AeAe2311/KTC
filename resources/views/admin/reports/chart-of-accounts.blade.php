<x-app-layout>
    @section('page_title', 'CHART OF ACCOUNTS')

    <style>
        /* Tab styles */
        .browser-tab-container {
            display: inline-flex;
            gap: 0.125rem;
            padding: 0.125rem;
            border: 1px solid #d1d5db;
            border-bottom: none;
            border-radius: 0.5rem 0.5rem 0 0;
            background: #f1f3f5;
        }
        .browser-tab {
            position: relative;
            border: 1px solid transparent;
            border-bottom: 1px solid transparent;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            padding: 0.375rem 0.875rem;
            font-size: 0.8125rem;
            font-weight: 500;
            background: transparent;
            color: #6b7280;
            transition: all 0.2s ease;
            cursor: pointer;
            white-space: nowrap;
            letter-spacing: 0.01em;
        }
        .browser-tab.active {
            background: white;
            color: #1f2937;
            border-color: #d1d5db;
            border-bottom-color: white;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            font-weight: 600;
        }
        .browser-tab:hover:not(.active) {
            color: #374151;
            background: rgba(255, 255, 255, 0.5);
        }
        .browser-tab .tab-icon {
            display: inline-block;
            margin-right: 0.375rem;
            font-size: 0.875rem;
            opacity: 0.7;
        }
        .browser-tab.active .tab-icon {
            opacity: 1;
        }

        /* Inline Editing Styles - UNDERLINE ONLY */
        .editable-cell {
            cursor: text;
            padding: 4px 0;
            border-radius: 0;
            border-bottom: 1px dashed #d1d5db;
            transition: border-color 0.15s ease, background-color 0.15s ease;
            position: relative;
            min-height: 24px;
            display: inline-block;
            min-width: 60px;
        }
        .editable-cell:hover {
            border-bottom-color: #3b82f6;
        }
        .editable-cell.editing {
            background-color: #ffffff;
            border-radius: 0;
            box-shadow: none;
            padding: 4px 0;
        }
        .editable-cell .cell-value {
            display: inline-block;
            min-width: 20px;
        }
        .editable-cell .cell-placeholder {
            color: #9ca3af;
            font-style: italic;
        }
        .editable-cell .edit-indicator {
            display: none;
            font-size: 10px;
            color: #d81414;
            margin-left: 4px;
        }
        .editable-cell:hover .edit-indicator {
            display: inline;
        }
        .editable-cell.editing .edit-indicator {
            display: none;
        }

        /* Inline select dropdown */
        .inline-select-wrapper {
            position: relative;
            display: inline-block;
        }
        .inline-select {
            background: transparent;
            border: none;
            border-bottom: 1px solid #3b82f6;
            border-radius: 0;
            padding: 4px 20px 4px 0;
            font-size: inherit;
            font-family: inherit;
            color: inherit;
            outline: none;
            cursor: pointer;
            min-width: 80px;
            appearance: none;
            -webkit-appearance: none;
        }
        .inline-select-wrapper::after {
            content: '▾';
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10px;
            color: #9ca3af;
            pointer-events: none;
        }
        .inline-select:focus {
            background: #ffffff;
            box-shadow: none;
            border-bottom: 2px solid #3b82f6;
            border-radius: 0;
        }
        .inline-select option {
            padding: 4px 8px;
        }

        /* Custom searchable dropdown - FIXED POSITIONING */
        .inline-edit-dropdown {
            position: absolute;
            left: 0;
            min-width: 220px;
            max-width: 300px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            z-index: 9999;
            padding: 8px;
            max-height: 260px;
            display: flex;
            flex-direction: column;
            cursor: default;
            white-space: normal;
        }
        .inline-edit-dropdown input[type="search"] {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
            margin-bottom: 6px;
            flex-shrink: 0;
            box-sizing: border-box;
        }
        .inline-edit-dropdown input[type="search"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .inline-edit-options {
            overflow-y: auto;
            flex: 1;
            max-height: 180px;
        }
        .inline-edit-option {
            padding: 6px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: #374151;
        }
        .inline-edit-option:hover,
        .inline-edit-option.highlighted {
            background: #eff6ff;
            color: #1a56db;
        }
        .inline-edit-option.selected {
            font-weight: 600;
            background: #dbeafe;
            color: #1a56db;
        }
        .inline-edit-empty {
            padding: 6px 8px;
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
        }

        /* Ensure table cells can contain positioned dropdowns */
        #accountCodeTable tbody tr {
            position: relative;
        }
        #accountCodeTable tbody td {
            position: relative;
            overflow: visible !important;
        }

        /* Loading state */
        .editable-cell.saving {
            opacity: 0.6;
            pointer-events: none;
        }
        .editable-cell .saving-spinner {
            display: none;
            width: 14px;
            height: 14px;
            border: 2px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-left: 4px;
            vertical-align: middle;
        }
        .editable-cell.saving .saving-spinner {
            display: inline-block;
        }
        .editable-cell.saving .cell-value {
            display: none;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            transform: translateY(120px);
            opacity: 0;
            transition: all 0.3s ease;
            max-width: 400px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        .toast.success {
            background: #10b981;
            color: white;
        }
        .toast.error {
            background: #ef4444;
            color: white;
        }
        .toast.info {
            background: #3b82f6;
            color: white;
        }

        /* Table cell positioning */
        .table-cell-content {
            display: flex;
            align-items: center;
            min-height: 32px;
        }

        /* Filter styles */
        .filter-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            user-select: none;
            position: relative;
            z-index: 5;
        }
        .filter-header:hover {
            color: #1a56db;
        }
        .filter-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            transition: all 0.2s;
            opacity: 0.6;
            flex-shrink: 0;
        }
        .filter-header:hover .filter-icon {
            opacity: 1;
            background: #e5e7eb;
        }
        .filter-icon.active {
            opacity: 1;
            background: #dbeafe;
            color: #1a56db;
        }
        .filter-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 250px;
            max-width: 320px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            z-index: 9999 !important;
            padding: 12px;
            margin-top: 4px;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }
        .filter-dropdown.show {
            display: block;
            animation: dropdownSlide 0.15s ease;
        }
        @keyframes dropdownSlide {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .filter-dropdown input[type="search"] {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
        }
        .filter-dropdown input[type="search"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .filter-options {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 8px;
        }
        .filter-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.15s;
            font-size: 13px;
        }
        .filter-option:hover {
            background: #f3f4f6;
        }
        .filter-option input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #3b82f6;
            cursor: pointer;
            flex-shrink: 0;
        }
        .filter-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        .filter-actions button {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }
        .btn-clear-filter {
            color: #6b7280;
            background: transparent;
        }
        .btn-clear-filter:hover {
            color: #374151;
            background: #f3f4f6;
        }
        .btn-apply-filter {
            color: white;
            background: #3b82f6;
        }
        .btn-apply-filter:hover {
            background: #2563eb;
        }
        .filter-chip-header {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #dbeafe;
            color: #1a56db;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 8px 1px 6px;
            border-radius: 12px;
            margin-left: 4px;
        }
        .filter-chip-header .remove-chip {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            transition: background 0.15s;
        }
        .filter-chip-header .remove-chip:hover {
            background: rgba(0,0,0,0.1);
        }
        .table-header-cell {
            position: relative !important;
            z-index: 10 !important;
            overflow: visible !important;
        }
        #accountCodePanel {
            position: relative;
            z-index: 1;
            overflow: visible !important;
        }
        .overflow-x-auto {
            overflow: visible !important;
            overflow-x: auto !important;
        }
        .table-container-wrapper {
            position: relative;
            overflow: visible !important;
            min-height: 300px;
        }
        #accountCodeTable thead th {
            vertical-align: top;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        @media (max-width: 640px) {
            .browser-tab-container {
                flex-wrap: wrap;
                justify-content: center;
            }
            .browser-tab {
                padding: 0.25rem 0.5rem;
                font-size: 0.7rem;
            }
        }
    </style>

    <div class="lg:ml-64">
        <div class="ml-2 mr-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-2">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Chart of Accounts</h2>
                        <p class="text-sm text-gray-500">Switch between Industry, Sub Accounts, Journals, Chart Category, and Chart of Accounts tabs.</p>
                    </div>
                    <div class="browser-tab-container">
                        <button id="tabIndustry" type="button" class="browser-tab active">
                            <span class="tab-icon">🏢</span> Industry
                        </button>
                        <button id="tabCategory" type="button" class="browser-tab">
                            <span class="tab-icon">📂</span> Sub Accounts
                        </button>
                        <button id="tabClassification" type="button" class="browser-tab">
                            <span class="tab-icon">📝</span> Journals
                        </button>
                        <button id="tabChartCategory" type="button" class="browser-tab">
                            <span class="tab-icon">🏷️</span> Chart Category
                        </button>
                        <button id="tabAccountCode" type="button" class="browser-tab">
                            <span class="tab-icon">📊</span> Chart of Accounts
                        </button>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative flex-1 min-w-0">
                            <label for="searchInput" class="sr-only">Search chart of accounts</label>
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"></path></svg>
                            </span>
                            <input id="searchInput" type="text" placeholder="Search name, code, industry, sub account, journal, or chart category"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-10 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                            <button id="clearSearchButton" type="button" onclick="clearSearch()" class="absolute inset-y-0 right-2 hidden items-center justify-center rounded-full bg-gray-100 p-1 text-gray-500 hover:bg-gray-200 hover:text-gray-700 focus:outline-none">
                                <span class="sr-only">Clear search</span>
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <button id="addButton" type="button" onclick="openAddModal()" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Add Industry
                        </button>
                    </div>

                    <!-- Industry Panel -->
                    <div id="industryPanel">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Industry Name</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse($industries as $industry)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $industry->name }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium">
                                                <button type="button" class="mr-2 text-blue-600 hover:text-blue-800 edit-action-button" data-edit-type="industry" data-edit-slug="{{ $industry->slug }}" data-edit-name="{{ $industry->name }}">Edit</button>
                                                <button type="button" class="text-red-600 hover:text-red-800 delete-action-button" data-delete-url="{{ route('report.chart-of-accounts.industry.delete', $industry->slug) }}" data-delete-name="{{ $industry->name }}" data-delete-type="Industry">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">No industries defined yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Category/Sub Accounts Panel -->
                    <div id="categoryPanel" class="hidden">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Sub Account Name</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse($categories as $category)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $category->name }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium">
                                                <button type="button" class="mr-2 text-blue-600 hover:text-blue-800 edit-action-button" data-edit-type="category" data-edit-slug="{{ $category->slug }}" data-edit-name="{{ $category->name }}">Edit</button>
                                                <button type="button" class="text-red-600 hover:text-red-800 delete-action-button" data-delete-url="{{ route('report.chart-of-accounts.subaccount.delete', $category->slug) }}" data-delete-name="{{ $category->name }}" data-delete-type="Sub Account">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">No sub accounts defined yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Classification Panel -->
                    <div id="classificationPanel" class="hidden">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Journal Name</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse($classifications as $classification)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $classification->name }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium">
                                                <button type="button" class="mr-2 text-blue-600 hover:text-blue-800 edit-action-button" data-edit-type="classification" data-edit-slug="{{ $classification->slug }}" data-edit-name="{{ $classification->name }}">Edit</button>
                                                <button type="button" class="text-red-600 hover:text-red-800 delete-action-button" data-delete-url="{{ route('report.chart-of-accounts.classification.delete', $classification->slug) }}" data-delete-name="{{ $classification->name }}" data-delete-type="Journal">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">No journals defined yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Chart Category Panel -->
                    <div id="chartCategoryPanel" class="hidden">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Chart Category Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Formula</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse($chartCategories as $chartCategory)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $chartCategory->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $chartCategory->com_formula ?: 'Not set' }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium">
                                                <button type="button" class="mr-2 text-indigo-600 hover:text-indigo-800 chart-category-formula-button" data-category-id="{{ $chartCategory->id }}" data-category-name="{{ $chartCategory->name }}" data-formula="{{ $chartCategory->com_formula ?? '' }}">{{ $chartCategory->com_formula ? 'Edit Formula' : 'Set Formula' }}</button>
                                                <button type="button" class="mr-2 text-blue-600 hover:text-blue-800 edit-action-button" data-edit-type="chartCategory" data-edit-slug="{{ $chartCategory->id }}" data-edit-name="{{ $chartCategory->name }}">Edit</button>
                                                <button type="button" class="text-red-600 hover:text-red-800 delete-action-button" data-delete-url="{{ route('report.chart-of-accounts.chart-category.delete', $chartCategory->id) }}" data-delete-name="{{ $chartCategory->name }}" data-delete-type="Chart Category">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No chart categories defined yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Account Codes Panel with Inline Editing -->
                    <div id="accountCodePanel" class="hidden">
                        <div class="overflow-x-auto rounded-lg border border-gray-200" style="overflow:visible !important; overflow-x:auto !important;">
                            <div class="table-container-wrapper" style="position:relative; overflow:visible !important; min-height:300px;">
                                <table class="min-w-full divide-y divide-gray-200" id="accountCodeTable" style="overflow:visible !important;">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 table-header-cell" style="position:relative !important; z-index:10 !important; overflow:visible !important;">
                                                <span class="filter-header" onclick="toggleFilterDropdown('industryFilter')">
                                                    Industry
                                                    <span class="filter-icon" id="industryFilterIcon">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                                        </svg>
                                                    </span>
                                                    <span id="industryFilterChip" class="filter-chip-header" style="display:none;">
                                                        <span id="industryFilterCount"></span>
                                                        <span class="remove-chip" onclick="event.stopPropagation(); clearFilter('industry')">×</span>
                                                    </span>
                                                </span>
                                                <div id="industryFilter" class="filter-dropdown" onclick="event.stopPropagation();" style="z-index:9999 !important;">
                                                    <input type="search" id="industrySearch" placeholder="Search industries..." oninput="filterDropdownOptions('industry')">
                                                    <div class="filter-options" id="industryOptions">
                                                        @foreach($industries as $industry)
                                                            <label class="filter-option">
                                                                <input type="checkbox" class="industry-filter-cb" value="{{ $industry->id }}" onchange="applyColumnFilter()">
                                                                <span>{{ $industry->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <div class="filter-actions">
                                                        <button class="btn-clear-filter" onclick="clearFilter('industry')">Clear</button>
                                                        <button class="btn-apply-filter" onclick="applyColumnFilter()">Apply</button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 table-header-cell" style="position:relative !important; z-index:10 !important; overflow:visible !important;">
                                                <span class="filter-header" onclick="toggleFilterDropdown('subAccountFilter')">
                                                    Sub Account
                                                    <span class="filter-icon" id="subAccountFilterIcon">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                                        </svg>
                                                    </span>
                                                    <span id="subAccountFilterChip" class="filter-chip-header" style="display:none;">
                                                        <span id="subAccountFilterCount"></span>
                                                        <span class="remove-chip" onclick="event.stopPropagation(); clearFilter('subAccount')">×</span>
                                                    </span>
                                                </span>
                                                <div id="subAccountFilter" class="filter-dropdown" onclick="event.stopPropagation();" style="z-index:9999 !important;">
                                                    <input type="search" id="subAccountSearch" placeholder="Search sub accounts..." oninput="filterDropdownOptions('subAccount')">
                                                    <div class="filter-options" id="subAccountOptions">
                                                        @foreach($categories as $category)
                                                            <label class="filter-option">
                                                                <input type="checkbox" class="subaccount-filter-cb" value="{{ $category->id }}" onchange="applyColumnFilter()">
                                                                <span>{{ $category->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <div class="filter-actions">
                                                        <button class="btn-clear-filter" onclick="clearFilter('subAccount')">Clear</button>
                                                        <button class="btn-apply-filter" onclick="applyColumnFilter()">Apply</button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Code</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Account Name</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 table-header-cell" style="position:relative !important; z-index:10 !important; overflow:visible !important;">
                                                <span class="filter-header" onclick="toggleFilterDropdown('journalFilter')">
                                                    Journal
                                                    <span class="filter-icon" id="journalFilterIcon">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                                        </svg>
                                                    </span>
                                                    <span id="journalFilterChip" class="filter-chip-header" style="display:none;">
                                                        <span id="journalFilterCount"></span>
                                                        <span class="remove-chip" onclick="event.stopPropagation(); clearFilter('journal')">×</span>
                                                    </span>
                                                </span>
                                                <div id="journalFilter" class="filter-dropdown" onclick="event.stopPropagation();" style="z-index:9999 !important;">
                                                    <input type="search" id="journalSearch" placeholder="Search journals..." oninput="filterDropdownOptions('journal')">
                                                    <div class="filter-options" id="journalOptions">
                                                        @foreach($classifications as $classification)
                                                            <label class="filter-option">
                                                                <input type="checkbox" class="journal-filter-cb" value="{{ $classification->id }}" onchange="applyColumnFilter()">
                                                                <span>{{ $classification->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <div class="filter-actions">
                                                        <button class="btn-clear-filter" onclick="clearFilter('journal')">Clear</button>
                                                        <button class="btn-apply-filter" onclick="applyColumnFilter()">Apply</button>
                                                    </div>
                                                </div>
                                            </th>
                                            <!-- Chart Category Column Header with Filter -->
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 table-header-cell" style="position:relative !important; z-index:10 !important; overflow:visible !important;">
                                                <span class="filter-header" onclick="toggleFilterDropdown('chartCategoryFilter')">
                                                    Chart Category
                                                    <span class="filter-icon" id="chartCategoryFilterIcon">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                                        </svg>
                                                    </span>
                                                    <span id="chartCategoryFilterChip" class="filter-chip-header" style="display:none;">
                                                        <span id="chartCategoryFilterCount"></span>
                                                        <span class="remove-chip" onclick="event.stopPropagation(); clearFilter('chartCategory')">×</span>
                                                    </span>
                                                </span>
                                                <div id="chartCategoryFilter" class="filter-dropdown" onclick="event.stopPropagation();" style="z-index:9999 !important;">
                                                    <input type="search" id="chartCategorySearch" placeholder="Search chart categories..." oninput="filterDropdownOptions('chartCategory')">
                                                    <div class="filter-options" id="chartCategoryOptions">
                                                        @foreach($chartCategories as $chartCategory)
                                                            <label class="filter-option">
                                                                <input type="checkbox" class="chartcategory-filter-cb" value="{{ $chartCategory->id }}" onchange="applyColumnFilter()">
                                                                <span>{{ $chartCategory->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <div class="filter-actions">
                                                        <button class="btn-clear-filter" onclick="clearFilter('chartCategory')">Clear</button>
                                                        <button class="btn-apply-filter" onclick="applyColumnFilter()">Apply</button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white" id="accountCodeTableBody" style="overflow:visible !important;">
                                        @forelse($expenseCategories as $expenseCategory)
                                            <tr data-slug="{{ $expenseCategory->slug }}" data-industry="{{ $expenseCategory->industry_id }}" data-category="{{ $expenseCategory->category_id }}" data-classification="{{ $expenseCategory->classification_id }}" data-chart-category="{{ $expenseCategory->cc_id ?? '' }}">
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <div class="table-cell-content">
                                                        <span class="editable-cell" data-slug="{{ $expenseCategory->slug }}" data-field="industry_id" data-value="{{ $expenseCategory->industry_id }}" data-display="{{ $expenseCategory->industry_name ?? '—' }}" data-type="select" data-options="{{ json_encode($industries->map(fn($i) => ['id' => $i->id, 'name' => $i->name])) }}">
                                                            <span class="cell-value">{{ $expenseCategory->industry_name ?? '—' }}</span>
                                                            <span class="edit-indicator">✎</span>
                                                            <span class="saving-spinner"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <div class="table-cell-content">
                                                        <span class="editable-cell" data-slug="{{ $expenseCategory->slug }}" data-field="category_id" data-value="{{ $expenseCategory->category_id }}" data-display="{{ $expenseCategory->category_name ?? '—' }}" data-type="select" data-options="{{ json_encode($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }}">
                                                            <span class="cell-value">{{ $expenseCategory->category_name ?? '—' }}</span>
                                                            <span class="edit-indicator">✎</span>
                                                            <span class="saving-spinner"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <div class="table-cell-content">
                                                        <span class="editable-cell" data-slug="{{ $expenseCategory->slug }}" data-field="code" data-value="{{ $expenseCategory->code }}" data-display="{{ $expenseCategory->code }}" data-type="text">
                                                            <span class="cell-value">{{ $expenseCategory->code }}</span>
                                                            <span class="edit-indicator">✎</span>
                                                            <span class="saving-spinner"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 font-bold">
                                                    <div class="table-cell-content">
                                                        <span class="editable-cell"
                                                            data-slug="{{ $expenseCategory->slug }}"
                                                            data-field="name"
                                                            data-value="{{ $expenseCategory->name }}"
                                                            data-display="{{ $expenseCategory->name }}"
                                                            data-type="text">
                                                            <span class="cell-value">{{ $expenseCategory->name }}</span>
                                                            <span class="edit-indicator">✎</span>
                                                            <span class="saving-spinner"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <div class="table-cell-content">
                                                        <span class="editable-cell" data-slug="{{ $expenseCategory->slug }}" data-field="classification_id" data-value="{{ $expenseCategory->classification_id }}" data-display="{{ $expenseCategory->classification_name ?? '—' }}" data-type="select" data-options="{{ json_encode($classifications->map(fn($j) => ['id' => $j->id, 'name' => $j->name])) }}">
                                                            <span class="cell-value">{{ $expenseCategory->classification_name ?? '—' }}</span>
                                                            <span class="edit-indicator">✎</span>
                                                            <span class="saving-spinner"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <!-- Chart Category Cell (inline editable select) -->
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <div class="table-cell-content">
                                                        <span class="editable-cell" data-slug="{{ $expenseCategory->slug }}" data-field="cc_id" data-value="{{ $expenseCategory->cc_id ?? '' }}" data-display="{{ $expenseCategory->chart_category_name ?? '—' }}" data-type="select" data-options="{{ json_encode($chartCategories->map(fn($cc) => ['id' => $cc->id, 'name' => $cc->name])) }}">
                                                            <span class="cell-value">{{ $expenseCategory->chart_category_name ?? '—' }}</span>
                                                            <span class="edit-indicator">✎</span>
                                                            <span class="saving-spinner"></span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right text-sm font-medium">
                                                    <button type="button" class="text-red-600 hover:text-red-800 delete-action-button"
                                                        data-delete-url="{{ route('report.chart-of-accounts.account-code.delete', $expenseCategory->slug) }}"
                                                        data-delete-name="{{ $expenseCategory->name }}"
                                                        data-delete-type="Account Code">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">No chart of accounts defined yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl modal-content scale-95 opacity-0" id="deleteModalContent">
            <div class="text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 ring-8 ring-red-50/50">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Delete Confirmation</h3>
                <div class="mt-3">
                    <p class="text-sm text-gray-600">Are you sure you want to delete</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 break-words" id="deleteItemName"></p>
                    <p class="mt-1 text-xs text-gray-400">This action cannot be undone.</p>
                </div>
                <div class="mt-6 flex justify-center gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        Cancel
                    </button>
                    <a id="deleteConfirmLink" href="#" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal (for industry, category, classification, chartCategory) -->
    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 id="editModalTitle" class="text-lg font-semibold text-gray-900">Edit item</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeEditModal()">×</button>
            </div>
            <form method="POST" id="editForm">
                @csrf
                <input type="hidden" name="slug" id="editSlug">
                <input type="hidden" name="tab" id="editTab" value="">
                <div class="space-y-4">
                    <div>
                        <label for="editName" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="editName" name="name" type="text" required autocomplete="off"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Industry Modal -->
    <div id="addIndustryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Industry</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeAddIndustryModal()">×</button>
            </div>
            <form action="{{ route('report.chart-of-accounts.industry.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tab" value="industry">
                <div class="space-y-4">
                    <div>
                        <label for="addIndustryName" class="block text-sm font-medium text-gray-700">Industry Name</label>
                        <input id="addIndustryName" name="name" type="text" required autocomplete="off"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter industry name">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeAddIndustryModal()">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Industry</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Sub Account Modal -->
    <div id="addCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Sub Account</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeAddCategoryModal()">×</button>
            </div>
            <form action="{{ route('report.chart-of-accounts.subaccount.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tab" value="category">
                <div class="space-y-4">
                    <div>
                        <label for="addCategoryName" class="block text-sm font-medium text-gray-700">Sub Account Name</label>
                        <input id="addCategoryName" name="name" type="text" required autocomplete="off"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter sub account name">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeAddCategoryModal()">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Sub Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Journal Modal -->
    <div id="addClassificationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Journal</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeAddClassificationModal()">×</button>
            </div>
            <form action="{{ route('report.chart-of-accounts.classification.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tab" value="classification">
                <div class="space-y-4">
                    <div>
                        <label for="addClassificationName" class="block text-sm font-medium text-gray-700">Journal Name</label>
                        <input id="addClassificationName" name="name" type="text" required autocomplete="off"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter journal name">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeAddClassificationModal()">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Journal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Chart Category Modal -->
    <div id="addChartCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Chart Category</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeAddChartCategoryModal()">×</button>
            </div>
            <form action="{{ route('report.chart-of-accounts.chart-category.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tab" value="chartCategory">
                <div class="space-y-4">
                    <div>
                        <label for="addChartCategoryName" class="block text-sm font-medium text-gray-700">Chart Category Name</label>
                        <input id="addChartCategoryName" name="name" type="text" required autocomplete="off"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter chart category name">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeAddChartCategoryModal()">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Chart Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Category Formula Modal -->
    <div id="chartCategoryFormulaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Chart Category Formula</h3>
                    <p id="chartCategoryFormulaName" class="mt-1 text-xs text-gray-500"></p>
                </div>
                <button type="button" class="text-xl leading-none text-gray-500 hover:text-gray-700" onclick="closeChartCategoryFormulaModal()" aria-label="Close">×</button>
            </div>
            <form action="{{ route('report.chart-of-accounts.chart-category.formula') }}" method="POST" id="chartCategoryFormulaForm">
                @csrf
                <input type="hidden" name="chart_category_id" id="chartCategoryFormulaId">
                <input type="hidden" name="com_formula" id="chartCategoryFormulaValue">
                <div class="space-y-3">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-formula-token="DEBIT" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">DEBIT</button>
                        <button type="button" data-formula-token="CREDIT" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">CREDIT</button>
                        <button type="button" data-formula-token="BEGINNING" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">BEGINNING</button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-formula-operator="+" class="rounded-md border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">+</button>
                        <button type="button" data-formula-operator="-" class="rounded-md border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">-</button>
                        <button type="button" id="removeFormulaPart" class="ml-auto rounded-md border border-gray-300 px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Undo</button>
                        <button type="button" id="clearFormula" class="rounded-md border border-gray-300 px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Clear</button>
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-medium text-gray-500">Computation</p>
                        <div id="chartCategoryFormulaPreview" class="min-h-10 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800" aria-live="polite"></div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeChartCategoryFormulaModal()">Cancel</button>
                        <button type="submit" id="saveChartCategoryFormula" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Save Formula</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Account Code Modal -->
    <div id="addAccountCodeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
        <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Create Chart of Accounts</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeAddAccountCodeModal()">×</button>
            </div>
            <form action="{{ route('report.chart-of-accounts.account-code.store') }}" method="POST" id="addAccountCodeForm">
                @csrf
                <input type="hidden" name="tab" value="accountCode">
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Industry</label>
                            <div class="relative mt-1">
                                <button id="addIndustryDropdownButton" type="button" onclick="toggleDropdown('add', 'industry')" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm text-gray-700 shadow-sm hover:border-gray-400">
                                    <span id="addIndustryDropdownLabel">Select industry</span>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">▾</span>
                                </button>
                                <input type="hidden" id="addIndustryInput" name="industry_id">
                                <div id="addIndustryDropdown" class="absolute inset-x-0 top-full z-50 hidden mt-1 rounded-lg border border-gray-200 bg-white shadow-lg">
                                    <div class="p-2">
                                        <input id="addIndustrySearch" type="text" oninput="filterDropdown('add', 'industry')" placeholder="Search industries"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto" id="addIndustryOptions">
                                        @foreach($industries as $industry)
                                            <button type="button" class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100" onclick="selectDropdown('add', 'industry', '{{ $industry->id }}', '{{ $industry->name }}')">{{ $industry->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sub Account</label>
                            <div class="relative mt-1">
                                <button id="addCategoryDropdownButton" type="button" onclick="toggleDropdown('add', 'category')" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm text-gray-700 shadow-sm hover:border-gray-400">
                                    <span id="addCategoryDropdownLabel">Select sub account</span>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">▾</span>
                                </button>
                                <input type="hidden" id="addCategoryInput" name="category_id">
                                <div id="addCategoryDropdown" class="absolute inset-x-0 top-full z-50 hidden mt-1 rounded-lg border border-gray-200 bg-white shadow-lg">
                                    <div class="p-2">
                                        <input id="addCategorySearch" type="text" oninput="filterDropdown('add', 'category')" placeholder="Search sub accounts"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto" id="addCategoryOptions">
                                        @foreach($categories as $category)
                                            <button type="button" class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100" onclick="selectDropdown('add', 'category', '{{ $category->id }}', '{{ $category->name }}')">{{ $category->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Journal</label>
                            <div class="relative mt-1">
                                <button id="addClassificationDropdownButton" type="button" onclick="toggleDropdown('add', 'classification')" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm text-gray-700 shadow-sm hover:border-gray-400">
                                    <span id="addClassificationDropdownLabel">Select journal</span>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">▾</span>
                                </button>
                                <input type="hidden" id="addClassificationInput" name="classification_id">
                                <div id="addClassificationDropdown" class="absolute inset-x-0 top-full z-50 hidden mt-1 rounded-lg border border-gray-200 bg-white shadow-lg">
                                    <div class="p-2">
                                        <input id="addClassificationSearch" type="text" oninput="filterDropdown('add', 'classification')" placeholder="Search journals"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto" id="addClassificationOptions">
                                        @foreach($classifications as $classification)
                                            <button type="button" class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100" onclick="selectDropdown('add', 'classification', '{{ $classification->id }}', '{{ $classification->name }}')">{{ $classification->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Chart Category dropdown in Add Account Code modal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chart Category</label>
                            <div class="relative mt-1">
                                <button id="addChartCategoryDropdownButton" type="button" onclick="toggleDropdown('add', 'chartCategory')" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm text-gray-700 shadow-sm hover:border-gray-400">
                                    <span id="addChartCategoryDropdownLabel">Select chart category</span>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">▾</span>
                                </button>
                                <input type="hidden" id="addChartCategoryInput" name="cc_id">
                                <div id="addChartCategoryDropdown" class="absolute inset-x-0 top-full z-50 hidden mt-1 rounded-lg border border-gray-200 bg-white shadow-lg">
                                    <div class="p-2">
                                        <input id="addChartCategorySearch" type="text" oninput="filterDropdown('add', 'chartCategory')" placeholder="Search chart categories"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto" id="addChartCategoryOptions">
                                        @foreach($chartCategories as $chartCategory)
                                            <button type="button" class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100" onclick="selectDropdown('add', 'chartCategory', '{{ $chartCategory->id }}', '{{ $chartCategory->name }}')">{{ $chartCategory->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Code of Account</label>
                            <input id="addAccountCodeValue" name="code" type="text" required autocomplete="off"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500" placeholder="Enter code of account">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Name</label>
                        <input id="addAccountName" name="name" type="text" required autocomplete="off"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500" placeholder="Enter account name">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onclick="closeAddAccountCodeModal()">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Account Code</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Store current tab
        let currentTab = 'industry';

        const industryUpdateUrl = "{{ route('report.chart-of-accounts.industry.update') }}";
        const categoryUpdateUrl = "{{ route('report.chart-of-accounts.subaccount.update') }}";
        const classificationUpdateUrl = "{{ route('report.chart-of-accounts.classification.update') }}";
        const chartCategoryUpdateUrl = "{{ route('report.chart-of-accounts.chart-category.update') }}";
        const updateFieldUrl = "{{ route('report.chart-of-accounts.update-field') }}";

        // Tab elements
        const tabIndustry = document.getElementById('tabIndustry');
        const tabCategory = document.getElementById('tabCategory');
        const tabClassification = document.getElementById('tabClassification');
        const tabChartCategory = document.getElementById('tabChartCategory');
        const tabAccountCode = document.getElementById('tabAccountCode');
        const addButton = document.getElementById('addButton');
        const industryPanel = document.getElementById('industryPanel');
        const categoryPanel = document.getElementById('categoryPanel');
        const classificationPanel = document.getElementById('classificationPanel');
        const chartCategoryPanel = document.getElementById('chartCategoryPanel');
        const accountCodePanel = document.getElementById('accountCodePanel');
        const searchInput = document.getElementById('searchInput');

        // Filter state
        let filterState = {
            industry: [],
            subAccount: [],
            journal: [],
            chartCategory: []
        };

        // ===== TAB FUNCTIONS =====
        function activateTab(tab) {
            currentTab = tab;
            addButton.textContent = tab === 'industry' ? 'Add Industry'
                : tab === 'category' ? 'Add Sub Account'
                : tab === 'classification' ? 'Add Journal'
                : tab === 'chartCategory' ? 'Add Chart Category'
                : 'Create Chart of Accounts';

            tabIndustry.classList.toggle('active', tab === 'industry');
            tabCategory.classList.toggle('active', tab === 'category');
            tabClassification.classList.toggle('active', tab === 'classification');
            tabChartCategory.classList.toggle('active', tab === 'chartCategory');
            tabAccountCode.classList.toggle('active', tab === 'accountCode');

            industryPanel.classList.toggle('hidden', tab !== 'industry');
            categoryPanel.classList.toggle('hidden', tab !== 'category');
            classificationPanel.classList.toggle('hidden', tab !== 'classification');
            chartCategoryPanel.classList.toggle('hidden', tab !== 'chartCategory');
            accountCodePanel.classList.toggle('hidden', tab !== 'accountCode');

            filterActiveTable();
            sessionStorage.setItem('activeChartTab', tab);
        }

        // Tab click handlers
        tabIndustry.addEventListener('click', () => activateTab('industry'));
        tabCategory.addEventListener('click', () => activateTab('category'));
        tabClassification.addEventListener('click', () => activateTab('classification'));
        tabChartCategory.addEventListener('click', () => activateTab('chartCategory'));
        tabAccountCode.addEventListener('click', () => activateTab('accountCode'));

        // Search
        searchInput.addEventListener('input', () => {
            toggleClearSearchButton();
            filterActiveTable();
        });

        function filterActiveTable() {
            const query = searchInput.value.trim().toLowerCase();
            let activePanel = document.getElementById('accountCodePanel');
            if (!industryPanel.classList.contains('hidden')) activePanel = industryPanel;
            else if (!categoryPanel.classList.contains('hidden')) activePanel = categoryPanel;
            else if (!classificationPanel.classList.contains('hidden')) activePanel = classificationPanel;
            else if (!chartCategoryPanel.classList.contains('hidden')) activePanel = chartCategoryPanel;

            const rows = activePanel.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return;
                const text = row.textContent.trim().toLowerCase();
                row.style.display = query && !text.includes(query) ? 'none' : '';
            });
        }

        function toggleClearSearchButton() {
            const clearButton = document.getElementById('clearSearchButton');
            clearButton.classList.toggle('hidden', !searchInput.value.trim());
        }

        function clearSearch() {
            searchInput.value = '';
            toggleClearSearchButton();
            filterActiveTable();
        }

        // ===== INLINE EDITING =====
        document.addEventListener('DOMContentLoaded', function() {
            // Set up editable cells
            document.querySelectorAll('.editable-cell').forEach(cell => {
                cell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    startEditing(this);
                });
                // Prevents immediate blur on click
                cell.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                });
            });

            // Close editing on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const editingCell = document.querySelector('.editable-cell.editing');
                    if (editingCell) {
                        cancelEditing(editingCell);
                    }
                }
            });

            // Click outside to save
            document.addEventListener('click', function(e) {
                const editingCell = document.querySelector('.editable-cell.editing');
                if (editingCell && !editingCell.contains(e.target)) {
                    saveEditing(editingCell);
                }
            });

            // Restore tab state
            const storedTab = sessionStorage.getItem('activeChartTab');
            if (storedTab && ['industry', 'category', 'classification', 'chartCategory', 'accountCode'].includes(storedTab)) {
                activateTab(storedTab);
            } else {
                activateTab('industry');
            }
        });

        function startEditing(cell) {
            // If already editing, save first
            const existingEdit = document.querySelector('.editable-cell.editing');
            if (existingEdit && existingEdit !== cell) {
                saveEditing(existingEdit);
            }

            const type = cell.dataset.type;
            const currentValue = cell.dataset.value;
            const displayText = cell.dataset.display;

            cell.classList.add('editing');
            delete cell.dataset.pendingValue;
            delete cell.dataset.pendingDisplay;

            if (type === 'select') {
                const options = JSON.parse(cell.dataset.options || '[]');

                // Get cell position for dropdown positioning
                const rect = cell.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const spaceBelow = viewportHeight - rect.bottom;
                const dropdownHeight = 260; // approximate height

                cell.innerHTML = '';

                const anchor = document.createElement('span');
                anchor.className = 'cell-value';
                anchor.textContent = displayText;
                cell.appendChild(anchor);

                const dropdown = document.createElement('div');
                dropdown.className = 'inline-edit-dropdown';

                // Position dropdown above if not enough space below
                if (spaceBelow < dropdownHeight) {
                    dropdown.style.top = 'auto';
                    dropdown.style.bottom = '100%';
                    dropdown.style.marginTop = '0';
                    dropdown.style.marginBottom = '4px';
                } else {
                    dropdown.style.top = '100%';
                    dropdown.style.bottom = 'auto';
                    dropdown.style.marginTop = '4px';
                    dropdown.style.marginBottom = '0';
                }

                // Ensure dropdown stays within viewport horizontally
                const rectLeft = rect.left;
                const viewportWidth = window.innerWidth;
                const dropdownWidth = Math.min(300, viewportWidth - 20);
                if (rectLeft + dropdownWidth > viewportWidth - 10) {
                    dropdown.style.left = 'auto';
                    dropdown.style.right = '0';
                } else {
                    dropdown.style.left = '0';
                    dropdown.style.right = 'auto';
                }
                dropdown.style.maxWidth = dropdownWidth + 'px';

                dropdown.addEventListener('mousedown', e => e.stopPropagation());
                dropdown.addEventListener('click', e => e.stopPropagation());

                const searchBox = document.createElement('input');
                searchBox.type = 'search';
                searchBox.placeholder = 'Search...';
                dropdown.appendChild(searchBox);

                const optionsList = document.createElement('div');
                optionsList.className = 'inline-edit-options';
                dropdown.appendChild(optionsList);

                function renderOptions(filterText) {
                    optionsList.innerHTML = '';
                    const filter = (filterText || '').toLowerCase();
                    const allOptions = [{ id: ''}, ...options];
                    const filtered = allOptions.filter(opt => (opt.name || '—').toLowerCase().includes(filter));

                    if (filtered.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'inline-edit-empty';
                        empty.textContent = 'No matches';
                        optionsList.appendChild(empty);
                        return;
                    }

                    filtered.forEach(opt => {
                        const optEl = document.createElement('div');
                        optEl.className = 'inline-edit-option';
                        if (String(opt.id) === String(currentValue)) {
                            optEl.classList.add('selected');
                        }
                        optEl.textContent = opt.name;
                        optEl.addEventListener('mousedown', e => e.stopPropagation());
                        optEl.addEventListener('click', function(e) {
                            e.stopPropagation();
                            selectDropdownOption(cell, opt.id, opt.name || '—');
                        });
                        optionsList.appendChild(optEl);
                    });
                }

                renderOptions('');

                searchBox.addEventListener('input', () => renderOptions(searchBox.value));
                searchBox.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelEditing(cell);
                    }
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const firstOption = optionsList.querySelector('.inline-edit-option');
                        if (firstOption) firstOption.click();
                    }
                });

                cell.appendChild(dropdown);
                setTimeout(() => searchBox.focus(), 20);

            } else {
                // Text input
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'inline-select';
                input.value = currentValue || '';
                input.setAttribute('data-original-value', currentValue || '');

                input.style.minWidth = '60px';
                input.style.background = 'transparent';
                input.style.border = 'none';
                input.style.borderBottom = '1px solid #3b82f6';
                input.style.borderRadius = '0';
                input.style.padding = '4px 0';
                input.style.fontSize = 'inherit';
                input.style.fontFamily = 'inherit';
                input.style.color = 'inherit';
                input.style.outline = 'none';
                input.style.width = 'auto';

                cell.innerHTML = '';
                cell.appendChild(input);

                input.focus();
                input.select();

                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        if (document.activeElement !== input) {
                            saveEditing(cell);
                        }
                    }, 100);
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        saveEditing(cell);
                    }
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelEditing(cell);
                    }
                });
            }
        }

        function selectDropdownOption(cell, value, display) {
            cell.dataset.pendingValue = value;
            cell.dataset.pendingDisplay = display;
            saveEditing(cell);
        }

        function cancelEditing(cell) {
            const originalValue = cell.dataset.display;
            const originalText = originalValue || '—';
            cell.classList.remove('editing');
            delete cell.dataset.pendingValue;
            delete cell.dataset.pendingDisplay;
            cell.innerHTML = `
                <span class="cell-value">${originalText}</span>
                <span class="edit-indicator">✎</span>
                <span class="saving-spinner"></span>
            `;
            // Reattach click handler
            cell.addEventListener('click', function(e) {
                e.stopPropagation();
                startEditing(this);
            });
        }

        function saveEditing(cell) {
            if (!cell.classList.contains('editing')) return;

            const type = cell.dataset.type;
            const slug = cell.dataset.slug;
            const field = cell.dataset.field;
            let newValue, newDisplay;

            if (type === 'select') {
                if (cell.dataset.pendingValue === undefined) {
                    cancelEditing(cell);
                    return;
                }
                newValue = cell.dataset.pendingValue;
                newDisplay = cell.dataset.pendingDisplay;
            } else {
                const input = cell.querySelector('input');
                if (!input) return;
                newValue = input.value.trim();
                newDisplay = newValue || '—';
            }

            const oldValue = cell.dataset.value;
            const oldDisplay = cell.dataset.display;

            // For cc_id, allow empty string to clear
            if (field !== 'cc_id' && String(newValue) === String(oldValue)) {
                cancelEditing(cell);
                return;
            }
            if (field === 'cc_id' && String(newValue) === String(oldValue)) {
                cancelEditing(cell);
                return;
            }

            cell.classList.remove('editing');
            cell.classList.add('saving');
            delete cell.dataset.pendingValue;
            delete cell.dataset.pendingDisplay;
            cell.innerHTML = `
                <span class="cell-value">${newDisplay || '—'}</span>
                <span class="saving-spinner"></span>
            `;

            fetch(updateFieldUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    slug: slug,
                    field: field,
                    value: newValue
                })
            })
            .then(response => response.json())
            .then(data => {
                cell.classList.remove('saving');
                if (data.success) {
                    cell.dataset.value = newValue;
                    cell.dataset.display = newDisplay;

                    if (field === 'industry_id') {
                        const row = cell.closest('tr');
                        if (row) row.dataset.industry = newValue;
                    } else if (field === 'category_id') {
                        const row = cell.closest('tr');
                        if (row) row.dataset.category = newValue;
                    } else if (field === 'classification_id') {
                        const row = cell.closest('tr');
                        if (row) row.dataset.classification = newValue;
                    } else if (field === 'cc_id') {
                        const row = cell.closest('tr');
                        if (row) row.dataset.chartCategory = newValue;
                    }

                    cell.innerHTML = `
                        <span class="cell-value">${newDisplay || '—'}</span>
                        <span class="edit-indicator">✎</span>
                        <span class="saving-spinner"></span>
                    `;
                    cell.addEventListener('click', function(e) {
                        e.stopPropagation();
                        startEditing(this);
                    });

                    showToast('Updated successfully', 'success');
                } else {
                    cell.dataset.value = oldValue;
                    cell.innerHTML = `
                        <span class="cell-value">${oldDisplay || '—'}</span>
                        <span class="edit-indicator">✎</span>
                        <span class="saving-spinner"></span>
                    `;
                    cell.addEventListener('click', function(e) {
                        e.stopPropagation();
                        startEditing(this);
                    });
                    showToast(data.message || 'Update failed', 'error');
                }
            })
            .catch(error => {
                cell.classList.remove('saving');
                cell.innerHTML = `
                    <span class="cell-value">${oldDisplay || '—'}</span>
                    <span class="edit-indicator">✎</span>
                    <span class="saving-spinner"></span>
                `;
                cell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    startEditing(this);
                });
                showToast('Network error. Please try again.', 'error');
            });
        }

        // ===== TOAST NOTIFICATION =====
        let toastTimeout;

        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;

            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // ===== DELETE MODAL =====
        function openDeleteModal(deleteUrl, deleteName, deleteType) {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            const nameSpan = document.getElementById('deleteItemName');
            const confirmLink = document.getElementById('deleteConfirmLink');

            nameSpan.textContent = `"${deleteName}" (${deleteType})`;
            confirmLink.href = deleteUrl;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        // ===== EDIT MODAL =====
        function openEditModal(type, slug, name) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            const title = document.getElementById('editModalTitle');
            const editName = document.getElementById('editName');
            const editSlug = document.getElementById('editSlug');
            const editTab = document.getElementById('editTab');

            editName.value = name;
            editSlug.value = slug;
            editTab.value = currentTab;

            const titles = {
                industry: 'Edit Industry',
                category: 'Edit Sub Account',
                classification: 'Edit Journal',
                chartCategory: 'Edit Chart Category'
            };
            title.textContent = titles[type] || 'Edit item';

            const urls = {
                industry: industryUpdateUrl,
                category: categoryUpdateUrl,
                classification: classificationUpdateUrl,
                chartCategory: chartCategoryUpdateUrl
            };
            form.action = urls[type] || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('flex');
            document.getElementById('editModal').classList.add('hidden');
        }

        // ===== ADD MODALS =====
        function openAddModal() {
            if (currentTab === 'industry') openAddIndustryModal();
            else if (currentTab === 'category') openAddCategoryModal();
            else if (currentTab === 'classification') openAddClassificationModal();
            else if (currentTab === 'chartCategory') openAddChartCategoryModal();
            else openAddAccountCodeModal();
        }

        function openAddIndustryModal() {
            document.getElementById('addIndustryModal').classList.remove('hidden');
            document.getElementById('addIndustryModal').classList.add('flex');
        }

        function closeAddIndustryModal() {
            document.getElementById('addIndustryModal').classList.remove('flex');
            document.getElementById('addIndustryModal').classList.add('hidden');
        }

        function openAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.remove('hidden');
            document.getElementById('addCategoryModal').classList.add('flex');
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.remove('flex');
            document.getElementById('addCategoryModal').classList.add('hidden');
        }

        function openAddClassificationModal() {
            document.getElementById('addClassificationModal').classList.remove('hidden');
            document.getElementById('addClassificationModal').classList.add('flex');
        }

        function closeAddClassificationModal() {
            document.getElementById('addClassificationModal').classList.remove('flex');
            document.getElementById('addClassificationModal').classList.add('hidden');
        }

        function openAddChartCategoryModal() {
            document.getElementById('addChartCategoryModal').classList.remove('hidden');
            document.getElementById('addChartCategoryModal').classList.add('flex');
        }

        function closeAddChartCategoryModal() {
            document.getElementById('addChartCategoryModal').classList.remove('flex');
            document.getElementById('addChartCategoryModal').classList.add('hidden');
        }

        let chartCategoryFormulaParts = [];

        function renderChartCategoryFormula() {
            const formula = chartCategoryFormulaParts.join(' ');
            const lastPart = chartCategoryFormulaParts[chartCategoryFormulaParts.length - 1];
            const needsOperand = !chartCategoryFormulaParts.length || lastPart === '+' || lastPart === '-';

            document.getElementById('chartCategoryFormulaPreview').textContent = formula || 'Click a term to begin.';
            document.getElementById('chartCategoryFormulaValue').value = formula;
            document.getElementById('saveChartCategoryFormula').disabled = needsOperand;

            document.querySelectorAll('[data-formula-token]').forEach(button => {
                button.disabled = !needsOperand;
            });
            document.querySelectorAll('[data-formula-operator]').forEach(button => {
                button.disabled = needsOperand;
            });
            document.getElementById('removeFormulaPart').disabled = !chartCategoryFormulaParts.length;
            document.getElementById('clearFormula').disabled = !chartCategoryFormulaParts.length;
        }

        function openChartCategoryFormulaModal(categoryId, categoryName, formula) {
            document.getElementById('chartCategoryFormulaId').value = categoryId;
            document.getElementById('chartCategoryFormulaName').textContent = categoryName;
            chartCategoryFormulaParts = formula ? formula.trim().split(/\s*([+-])\s*/) : [];
            renderChartCategoryFormula();
            document.getElementById('chartCategoryFormulaModal').classList.remove('hidden');
            document.getElementById('chartCategoryFormulaModal').classList.add('flex');
        }

        function closeChartCategoryFormulaModal() {
            document.getElementById('chartCategoryFormulaModal').classList.remove('flex');
            document.getElementById('chartCategoryFormulaModal').classList.add('hidden');
        }

        function openAddAccountCodeModal() {
            document.getElementById('addAccountCodeForm').reset();
            document.getElementById('addAccountCodeModal').classList.remove('hidden');
            document.getElementById('addAccountCodeModal').classList.add('flex');
            resetDropdowns('add');
        }

        function closeAddAccountCodeModal() {
            document.getElementById('addAccountCodeModal').classList.remove('flex');
            document.getElementById('addAccountCodeModal').classList.add('hidden');
        }

        // ===== DROPDOWN FUNCTIONS =====
        function toggleDropdown(prefix, field) {
            const dropdown = document.getElementById(`${prefix}${capitalize(field)}Dropdown`);
            if (!dropdown) return;
            const isHidden = dropdown.classList.contains('hidden');
            ['industry', 'category', 'classification', 'chartCategory'].forEach(f => {
                const d = document.getElementById(`${prefix}${capitalize(f)}Dropdown`);
                if (d) d.classList.add('hidden');
            });
            if (isHidden) {
                dropdown.classList.remove('hidden');
            }
        }

        function closeDropdown(prefix, field) {
            const dropdown = document.getElementById(`${prefix}${capitalize(field)}Dropdown`);
            if (dropdown) dropdown.classList.add('hidden');
        }

        function resetDropdowns(prefix) {
            ['industry', 'category', 'classification', 'chartCategory'].forEach(f => {
                const hidden = document.getElementById(`${prefix}${capitalize(f)}Input`);
                const label = document.getElementById(`${prefix}${capitalize(f)}DropdownLabel`);
                if (hidden) hidden.value = '';
                if (label) {
                    const labels = {
                        industry: 'Select industry',
                        category: 'Select sub account',
                        classification: 'Select journal',
                        chartCategory: 'Select chart category'
                    };
                    label.textContent = labels[f] || 'Select';
                }
                const dropdown = document.getElementById(`${prefix}${capitalize(f)}Dropdown`);
                if (dropdown) dropdown.classList.add('hidden');
            });
        }

        function selectDropdown(prefix, field, value, label) {
            const hiddenInput = document.getElementById(`${prefix}${capitalize(field)}Input`);
            const labelElement = document.getElementById(`${prefix}${capitalize(field)}DropdownLabel`);
            if (hiddenInput) hiddenInput.value = value;
            if (labelElement) labelElement.textContent = label;
            closeDropdown(prefix, field);
        }

        function filterDropdown(prefix, field) {
            const searchInput = document.getElementById(`${prefix}${capitalize(field)}Search`);
            if (!searchInput) return;
            const filter = searchInput.value.toLowerCase();
            const optionsContainer = document.getElementById(`${prefix}${capitalize(field)}Options`);
            if (!optionsContainer) return;
            optionsContainer.querySelectorAll('button').forEach(btn => {
                btn.style.display = btn.textContent.toLowerCase().includes(filter) ? 'block' : 'none';
            });
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // ===== FILTER FUNCTIONS =====
        function toggleFilterDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            const isShowing = dropdown.classList.contains('show');

            document.querySelectorAll('.filter-dropdown.show').forEach(d => {
                if (d.id !== dropdownId) d.classList.remove('show');
            });

            dropdown.classList.toggle('show');
            if (dropdown.classList.contains('show')) {
                const searchInput = dropdown.querySelector('input[type="search"]');
                if (searchInput) setTimeout(() => searchInput.focus(), 100);
            }
        }

        function filterDropdownOptions(type) {
            const searchInput = document.getElementById(`${type}Search`);
            if (!searchInput) return;
            const filter = searchInput.value.toLowerCase();
            const options = document.getElementById(`${type}Options`);
            if (!options) return;
            options.querySelectorAll('.filter-option').forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        }

        function getFilterValues(type) {
            const checkboxes = document.querySelectorAll(`.${type}-filter-cb:checked`);
            return Array.from(checkboxes).map(cb => cb.value);
        }

        function applyColumnFilter() {
            filterState.industry = getFilterValues('industry');
            filterState.subAccount = getFilterValues('subaccount');
            filterState.journal = getFilterValues('journal');
            filterState.chartCategory = getFilterValues('chartcategory');

            updateFilterIndicators();
            filterTableRows();

            document.querySelectorAll('.filter-dropdown.show').forEach(d => d.classList.remove('show'));
        }

        function filterTableRows() {
            const rows = document.querySelectorAll('#accountCodeTableBody tr');
            const hasNoData = rows.length === 0 || (rows.length === 1 && rows[0].querySelector('td[colspan]'));

            if (hasNoData) return;

            let visibleCount = 0;
            const hasIndustryFilter = filterState.industry.length > 0;
            const hasSubAccountFilter = filterState.subAccount.length > 0;
            const hasJournalFilter = filterState.journal.length > 0;
            const hasChartCategoryFilter = filterState.chartCategory.length > 0;

            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return;

                let show = true;

                if (hasIndustryFilter) {
                    const industryId = row.dataset.industry;
                    show = show && filterState.industry.includes(industryId);
                }

                if (hasSubAccountFilter) {
                    const categoryId = row.dataset.category;
                    show = show && filterState.subAccount.includes(categoryId);
                }

                if (hasJournalFilter) {
                    const classificationId = row.dataset.classification;
                    show = show && filterState.journal.includes(classificationId);
                }

                if (hasChartCategoryFilter) {
                    const chartCategoryId = row.dataset.chartCategory;
                    show = show && filterState.chartCategory.includes(chartCategoryId);
                }

                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            let noResultsRow = document.querySelector('#accountCodeTableBody tr.no-results-row');
            if (visibleCount === 0) {
                if (!noResultsRow) {
                    const tbody = document.getElementById('accountCodeTableBody');
                    const tr = document.createElement('tr');
                    tr.className = 'no-results-row';
                    tr.innerHTML = `
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                            No account codes match the selected filters.
                        </td>
                    `;
                    tbody.appendChild(tr);
                }
            } else {
                if (noResultsRow) noResultsRow.remove();
            }
        }

        function updateFilterIndicators() {
            updateFilterIndicator('industry', 'industryFilterIcon', 'industryFilterChip', 'industryFilterCount');
            updateFilterIndicator('subAccount', 'subAccountFilterIcon', 'subAccountFilterChip', 'subAccountFilterCount');
            updateFilterIndicator('journal', 'journalFilterIcon', 'journalFilterChip', 'journalFilterCount');
            updateFilterIndicator('chartCategory', 'chartCategoryFilterIcon', 'chartCategoryFilterChip', 'chartCategoryFilterCount');
        }

        function updateFilterIndicator(type, iconId, chipId, countId) {
            const icon = document.getElementById(iconId);
            const chip = document.getElementById(chipId);
            const count = document.getElementById(countId);
            const values = filterState[type] || [];

            if (icon) icon.classList.toggle('active', values.length > 0);
            if (chip) chip.style.display = values.length > 0 ? 'inline-flex' : 'none';
            if (count) count.textContent = values.length;
        }

        function clearFilter(type) {
            document.querySelectorAll(`.${type}-filter-cb`).forEach(cb => cb.checked = false);
            const searchInput = document.getElementById(`${type}Search`);
            if (searchInput) searchInput.value = '';
            filterState[type] = [];
            updateFilterIndicators();
            filterTableRows();
            const options = document.getElementById(`${type}Options`);
            if (options) {
                options.querySelectorAll('.filter-option').forEach(opt => opt.style.display = '');
            }
        }

        // Delete button handlers
        document.querySelectorAll('.delete-action-button').forEach(button => {
            button.addEventListener('click', function() {
                openDeleteModal(
                    this.dataset.deleteUrl,
                    this.dataset.deleteName,
                    this.dataset.deleteType
                );
            });
        });

        // Edit button handlers
        document.querySelectorAll('.edit-action-button').forEach(button => {
            button.addEventListener('click', function() {
                openEditModal(
                    this.dataset.editType,
                    this.dataset.editSlug,
                    this.dataset.editName
                );
            });
        });

        document.querySelectorAll('.chart-category-formula-button').forEach(button => {
            button.addEventListener('click', function() {
                openChartCategoryFormulaModal(
                    this.dataset.categoryId,
                    this.dataset.categoryName,
                    this.dataset.formula
                );
            });
        });

        document.querySelectorAll('[data-formula-token]').forEach(button => {
            button.addEventListener('click', function() {
                const lastPart = chartCategoryFormulaParts[chartCategoryFormulaParts.length - 1];
                if (!chartCategoryFormulaParts.length || lastPart === '+' || lastPart === '-') {
                    chartCategoryFormulaParts.push(this.dataset.formulaToken);
                    renderChartCategoryFormula();
                }
            });
        });

        document.querySelectorAll('[data-formula-operator]').forEach(button => {
            button.addEventListener('click', function() {
                const lastPart = chartCategoryFormulaParts[chartCategoryFormulaParts.length - 1];
                if (chartCategoryFormulaParts.length && lastPart !== '+' && lastPart !== '-') {
                    chartCategoryFormulaParts.push(this.dataset.formulaOperator);
                    renderChartCategoryFormula();
                }
            });
        });

        document.getElementById('removeFormulaPart').addEventListener('click', function() {
            chartCategoryFormulaParts.pop();
            renderChartCategoryFormula();
        });

        document.getElementById('clearFormula').addEventListener('click', function() {
            chartCategoryFormulaParts = [];
            renderChartCategoryFormula();
        });

        document.getElementById('chartCategoryFormulaModal').addEventListener('click', function(e) {
            if (e.target === this) closeChartCategoryFormulaModal();
        });

        // Delete modal close handlers
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const deleteModal = document.getElementById('deleteModal');
                if (!deleteModal.classList.contains('hidden')) closeDeleteModal();
                const editModal = document.getElementById('editModal');
                if (!editModal.classList.contains('hidden')) closeEditModal();
                const formulaModal = document.getElementById('chartCategoryFormulaModal');
                if (!formulaModal.classList.contains('hidden')) closeChartCategoryFormulaModal();
            }
        });
    </script>
</x-app-layout>