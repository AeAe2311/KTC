<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Transaction — {{ $etax->first()->e_transid ?? '' }}</title>

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/solid.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker.js') }}"></script>

    <style>
        html, body { height: 100%; overflow: hidden; margin: 0; }

        .edit2-shell {
            display: flex; flex-direction: column;
            height: 100vh; overflow: hidden;
            background: #f3f4f6; box-sizing: border-box;
        }
        .edit2-toolbar {
            flex: 0 0 auto; display: flex; align-items: center;
            padding: 10px 16px; background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }
        .edit2-toolbar .title { font-size: 15px; font-weight: 700; color: #111827; }
        .edit2-toolbar .subtitle { font-size: 12px; color: #6b7280; }

        .edit2-body {
            flex: 1 1 auto; min-height: 0;
            display: flex; gap: 12px; padding: 12px; overflow: hidden;
        }
        .edit2-left, .edit2-right {
            display: flex; flex-direction: column;
            min-width: 0; background: #ffffff;
            border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;
            transition: flex-basis 0.3s ease, max-width 0.3s ease, width 0.3s ease, opacity 0.3s ease;
        }
        .edit2-left { flex: 1 1 0; width: 100%; }
        .edit2-right {
            flex: 0 0 0; max-width: 0; width: 0;
            padding: 0; border-width: 0; opacity: 0; overflow: hidden;
        }
        .edit2-body.editing .edit2-left { flex: 1 1 55%; max-width: 55%; }
        .edit2-body.editing .edit2-right {
            flex: 1 1 45%; max-width: none;
            width: auto; border-width: 1px; opacity: 1;
        }

        .edit2-pane-header {
            flex: 0 0 auto; display: flex; align-items: center;
            justify-content: space-between; gap: 8px;
            padding: 10px 14px; background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .edit2-pane-header-title {
            font-size: 13px; font-weight: 700; color: #374151;
            text-transform: uppercase; letter-spacing: 0.03em;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .edit2-pane-body {
            flex: 1 1 auto; min-height: 0; overflow: auto; padding: 12px;
        }

        .edit2-cart-table {
            width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px;
        }
        .edit2-cart-table thead th {
            position: sticky; top: 0; background: #f3f4f6; color: #374151;
            font-weight: 700; text-transform: uppercase; font-size: 10px;
            letter-spacing: 0.04em; padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb; white-space: nowrap; z-index: 2;
        }
        .edit2-cart-table tbody td {
            padding: 10px; border-bottom: 1px solid #f3f4f6;
            color: #374151; vertical-align: middle;
        }
        .edit2-cart-table tbody tr { transition: background-color 0.15s ease; }
        .edit2-cart-table tbody tr:hover { background: #f9fafb; }
        .edit2-cart-table tbody tr.active {
            background: #eff6ff; box-shadow: inset 3px 0 0 #2563eb;
        }
        .edit2-cart-table .num { text-align: right; font-variant-numeric: tabular-nums; }
        .edit2-cart-table .ctr { text-align: center; }

        /* Row actions — gumagana sa <button> */
        .edit2-cart-table .row-actions button {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 6px;
            cursor: pointer; background: transparent; border: 0; padding: 0;
            transition: background-color 0.15s ease, color 0.15s ease;
            font-size: 16px;
        }
        .edit2-cart-table .row-actions button.edit { color: #2563eb; }
        .edit2-cart-table .row-actions button.edit:hover { background: #eff6ff; color: #1d4ed8; }

        .edit2-tax-panel {
            margin-top: 16px; padding: 14px;
            border: 1px solid #e5e7eb; border-radius: 10px; background: #fafbfc;
        }
        .edit2-tax-panel .row {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; padding: 6px 0; font-size: 13px; color: #374151;
            flex-wrap: wrap;
        }
        .edit2-tax-panel .row.total {
            border-top: 1px solid #e5e7eb; margin-top: 6px;
            padding-top: 10px; font-weight: 700; color: #111827;
        }
        .edit2-tax-panel .row .label { color: #6b7280; }
        .edit2-tax-panel .row .value { font-variant-numeric: tabular-nums; font-weight: 600; }

        /* =========================================================
           EDIT FORM — liitan + responsive
           ========================================================= */
        .edit2-form .form-row { margin-bottom: 10px; }
        .edit2-form label {
            display: block; font-size: 11px; font-weight: 600;
            color: #374151; margin-bottom: 3px;
        }
        .edit2-form input, .edit2-form select {
            width: 100%; padding: 7px 9px;
            border: 1px solid #d1d5db; border-radius: 7px;
            background: #f9fafb; font-size: 12.5px; color: #111827;
            outline: none; box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .edit2-form input:focus, .edit2-form select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
            background: #ffffff;
        }
        .edit2-form input[readonly] { background: #f3f4f6; color: #6b7280; }
        .edit2-form .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .edit2-form .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
        .edit2-form .form-actions {
            display: flex; justify-content: flex-end; gap: 8px;
            margin-top: 14px; padding-top: 10px; border-top: 1px solid #e5e7eb;
        }

        /* Stack the grids on narrow panes/screens so nothing overflows */
        @media (max-width: 640px) {
            .edit2-form .grid-2,
            .edit2-form .grid-3 { grid-template-columns: 1fr; }
        }

        .edit2-form .req-star { color: #ef4444; margin-left: 2px; }

        .edit2-form .radio-row { display: flex; align-items: center; gap: 28px; padding-top: 2px; }
        .edit2-form .radio-opt {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12.5px; font-weight: 500; color: #111827;
            cursor: pointer; width: auto; margin: 0;
        }
        .edit2-form .radio-opt input[type="radio"] {
            width: 15px; height: 15px; accent-color: #2563eb;
            cursor: pointer; margin: 0;
        }

        /* Kill native number-input spinners (kagaya sa Add form) */
        .edit2-form input[type="number"]::-webkit-outer-spin-button,
        .edit2-form input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .edit2-form input[type="number"] { -moz-appearance: textfield; }

        .edit2-actions-bar {
            flex: 0 0 auto; display: flex; justify-content: flex-end;
            gap: 10px; padding: 12px 14px;
            border-top: 1px solid #e5e7eb; background: #f9fafb;
        }

        /* DONE button states */
        .submit-button {
            transition: background-color 0.15s ease, opacity 0.15s ease;
        }
        .submit-button.is-disabled {
            cursor: not-allowed !important;
            pointer-events: none;
            opacity: 0.75;
        }

        @media (max-width: 900px) {
            .edit2-body { flex-direction: column; }
            .edit2-left,
            .edit2-body.editing .edit2-left,
            .edit2-body.editing .edit2-right {
                flex: 1 1 auto; max-width: none; width: auto;
            }
        }

        .success-toast-wrap {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(2px);
            align-items: center; justify-content: center; padding: 1rem;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">

    @foreach($etax as $etaxid)
        <input type="hidden" name="edit_id" id="edit_id" value="{{ $etaxid->id }}">
    @endforeach

    <div class="edit2-shell">

        <div class="edit2-toolbar">
            <div>
                <div class="title">Edit Line Items</div>
                <div class="subtitle">
                    @foreach($etax as $hdr)
                        {{ $hdr->e_transid }}
                    @endforeach
                </div>
            </div>
        </div>

        <div id="edit2Body" class="edit2-body">

            {{-- LEFT COLUMN --}}
            <div id="edit2LeftPane" class="edit2-left">
                <div class="edit2-pane-header">
                    <span class="edit2-pane-header-title">Line Items</span>
                </div>
                <div class="edit2-pane-body">
                    <div style="overflow-x:auto;">
                        <table class="edit2-cart-table" id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>Classification</th>
                                    <th class="ctr">Qty</th>
                                    <th class="ctr">Unit</th>
                                    <th>Name</th>
                                    <th class="num">Unit Price</th>
                                    <th class="num">Amount</th>
                                    <th class="ctr">MOP</th>
                                    <th class="ctr">Payment</th>
                                    <th class="ctr">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $inventory)
                                    <tr data-row-id="{{ $inventory->id }}">
                                        <td>{{ $inventory->exp_type == 1 ? 'Other Expenses' : 'Cost of Sales' }}</td>
                                        <td class="ctr">{{ $inventory->quantity }}</td>
                                        <td class="ctr">{{ $inventory->unit }}</td>
                                        <td>{{ $inventory->remarks }}</td>
                                        <td class="num">₱{{ number_format((float) $inventory->unit_price, 2) }}</td>
                                        <td class="num row-value">₱{{ number_format((float) $inventory->amount, 2, '.', ',') }}</td>
                                        <td class="ctr">{{ $inventory->is_paid }}</td>
                                        <td class="ctr {{ $inventory->payment_class == 1 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $inventory->payment_class == 1 ? 'Paid' : 'Unpaid' }}
                                        </td>
                                        <td class="ctr row-actions">
                                            <button type="button"
                                                    onclick="EditModal({{ $inventory->id }})"
                                                    title="Edit"
                                                    class="edit">
                                                <i class="uil uil-pen"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @foreach ($etax as $etax_value)
                        <div class="edit2-tax-panel" id="taxPanel">
                            <div class="row">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="vatBtn" class="sr-only peer" {{ $etax_value->e_vat > 0 ? 'checked' : '' }}>
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900">VAT</span>
                                </label>

                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="taxBtn" class="sr-only peer" {{ $etax_value->e_wtaxpercent > 0 ? 'checked' : '' }}>
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900">W/Tax</span>
                                    <input type="number" id="wtaxPercent" name="wtaxPercent" value="{{ $etax_value->e_wtaxpercent }}"
                                        class="ms-3 border-0 border-b px-1 py-1 text-sm"
                                        style="width: 40px; border-bottom: 1px solid #000;"
                                        min="0" step="0.01"> %
                                </label>
                            </div>
                            <div class="row">
                                <span class="label">Total Sales (VAT Inclusive):</span>
                                <span id="totalDiv" class="value">₱ 0.00</span>
                                <input type="hidden" name="total_value" id="total_value">
                            </div>
                            <div class="row">
                                <span class="label">Less VAT:</span>
                                <span id="vatDiv" class="value">{{ $etax_value->e_vat }}</span>
                                <input type="hidden" name="vat_value" id="vat_value">
                            </div>
                            <div class="row">
                                <span class="label">Less Withholding Tax:</span>
                                <span id="wtaxDiv" class="value">₱ 0.00</span>
                                <input type="hidden" name="wtax_value" id="wtax_value">
                            </div>
                            <div class="row total">
                                <span>Total Amount Due:</span>
                                <span id="amountDue" class="value">₱ 0.00</span>
                                <input type="hidden" name="amount_due" id="amount_due">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="edit2-actions-bar">
                    <a href="#" id="doneBtn" class="submit-button is-disabled"
                       style="padding: 0.5rem 1.5rem; background: #9ca3af; color: #fff; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                        DONE
                    </a>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div id="edit2RightPane" class="edit2-right">
                <div class="edit2-pane-header">
                    <span class="edit2-pane-header-title">Edit Line Item</span>
                </div>
                <div class="edit2-pane-body">
                    <form id="edit-inventory-form" class="edit2-form">
                        <div class="form-row">
                            <label for="edit-exp-type">Expense Type<span class="req-star">*</span></label>
                            <select id="edit-exp-type">
                                <option value="0">Cost of Sales</option>
                                <option value="1">Other Expenses</option>
                            </select>
                        </div>
                        <div class="grid-2">
                            <div class="form-row">
                                <label for="edit-supplier">Supplier<span class="req-star">*</span></label>
                                <select id="edit-supplier"></select>
                            </div>
                            <div class="form-row">
                                <label for="edit-category">Category<span class="req-star">*</span></label>
                                <select id="edit-category"></select>
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="form-row">
                                <label for="edit-remarks">Name<span class="req-star">*</span></label>
                                <input type="text" id="edit-remarks">
                            </div>
                            <div class="form-row">
                                <label for="edit-unit">Unit<span class="req-star">*</span></label>
                                <select id="edit-unit">
                                    <option value="tsp">teaspoon (tsp)</option>
                                    <option value="tbsp">tablespoon (tbsp)</option>
                                    <option value="kg">kilogram (kg)</option>
                                    <option value="g">gram (g)</option>
                                    <option value="mg">milligram (mg)</option>
                                    <option value="oz">ounce (oz)</option>
                                    <option value="lb">pound (lb)</option>
                                    <option value="gal">gallon (gal)</option>
                                    <option value="L">liter (L)</option>
                                    <option value="mL">milliliter (mL)</option>
                                    <option value="c">cup (c)</option>
                                    <option value="ea">each (ea)</option>
                                    <option value="doz">dozen (doz)</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid-3">
                            <div class="form-row">
                                <label for="edit-quantity">Quantity<span class="req-star">*</span></label>
                                <input type="text" inputmode="decimal" autocomplete="off" class="inputNumber" id="edit-quantity">
                            </div>
                            <div class="form-row">
                                <label for="edit-unit-price">Unit Price<span class="req-star">*</span></label>
                                <input type="text" inputmode="decimal" autocomplete="off" class="inputNumber" id="edit-unit-price">
                            </div>
                            <div class="form-row">
                                <label for="edit-amount">Amount</label>
                                <input type="text" inputmode="decimal" autocomplete="off" class="inputNumber" id="edit-amount" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <label>Payment Status<span class="req-star">*</span></label>
                            <div class="radio-row">
                                <label class="radio-opt">
                                    <input type="radio" id="edit-paid" name="edit-payment-class" value="1">
                                    <span>Paid</span>
                                </label>
                                <label class="radio-opt">
                                    <input type="radio" id="edit-unpaid" name="edit-payment-class" value="0">
                                    <span>Unpaid</span>
                                </label>
                            </div>
                        </div>
                        <input type="hidden" id="inventory-id" value="">
                        <div class="form-actions">
                            <button type="button" onclick="closeEditForm()"
                                    style="padding: 0.5rem 1.25rem; background: #e5e7eb; color: #1f2937; border: 0; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700; cursor: pointer;">
                                Cancel
                            </button>
                            <button type="button" id="saveChangesBtn" onclick="saveChanges(this)" disabled
                                    style="padding: 0.5rem 1.25rem; background: #9ca3af; color: #fff; border: 0; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700; cursor: not-allowed; opacity: 0.75; transition: background-color .15s ease, opacity .15s ease;">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* =========================================================
           DIRTY TRACKING — DONE button gray/disabled kapag walang
           changes pa. session-based dahil reload after save/delete.
           ========================================================= */
        const editTxnId = document.getElementById('edit_id')?.value || 'default';
        const dirtyKey = 'editTxnDirty_' + editTxnId;

        function isDirty() { return sessionStorage.getItem(dirtyKey) === '1'; }
        function markDirty() { sessionStorage.setItem(dirtyKey, '1'); updateDoneButtonState(); }
        function clearDirty() { sessionStorage.removeItem(dirtyKey); updateDoneButtonState(); }

        function updateDoneButtonState() {
            const btn = document.getElementById('doneBtn');
            if (!btn) return;
            if (isDirty()) {
                btn.classList.remove('is-disabled');
                btn.style.background = '#1d4ed8';
            } else {
                btn.classList.add('is-disabled');
                btn.style.background = '#9ca3af';
            }
        }

        /* =========================================================
           OPEN / CLOSE EDIT PANE
           ========================================================= */
        function openEditPane() {
            document.getElementById('edit2Body').classList.add('editing');
        }
        function closeEditPane() {
            document.getElementById('edit2Body').classList.remove('editing');
            document.querySelectorAll('.edit2-cart-table tbody tr.active').forEach(function (tr) {
                tr.classList.remove('active');
            });
            editFormSnapshot = null;
            updateSaveButtonState();
        }
        function closeEditForm() { closeEditPane(); }

        /* =========================================================
           VAT/WTAX COMPUTATION
           ========================================================= */
        document.addEventListener('DOMContentLoaded', function () {
            updateDoneButtonState();

            function sumValues(selector) {
                let total = 0;
                document.querySelectorAll(selector).forEach(function (el) {
                    const raw = el.textContent.replace('₱', '').replace(/,/g, '').trim();
                    const n = parseFloat(raw);
                    if (!isNaN(n)) total += n;
                });
                return total;
            }

            const total = sumValues('.row-value');
            const totalValueEl = document.getElementById('total_value');
            if (totalValueEl) totalValueEl.value = total.toFixed(2);

            const vatBtn = document.getElementById('vatBtn');
            const taxBtn = document.getElementById('taxBtn');
            const wtaxPercentInput = document.getElementById('wtaxPercent');
            const totalDiv = document.getElementById('totalDiv');
            const vatDiv = document.getElementById('vatDiv');
            const wtaxDiv = document.getElementById('wtaxDiv');
            const amountDueDiv = document.getElementById('amountDue');

            if (totalDiv) {
                totalDiv.textContent = `₱ ${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            function roundCurrency(v) { return Math.round((v + Number.EPSILON) * 100) / 100; }

            function updateValues() {
                const netSales = vatBtn.checked ? (total / 1.12) : total;
                const vatAmount = vatBtn.checked ? roundCurrency(total - netSales) : 0;
                if (vatDiv) {
                    vatDiv.textContent = `₱ ${vatAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
                document.getElementById('vat_value').value = vatAmount.toFixed(2);

                const wtaxPercent = parseFloat(wtaxPercentInput.value) || 0;
                const wtaxAmount = (taxBtn.checked && wtaxPercent > 0)
                    ? roundCurrency(netSales * (wtaxPercent / 100))
                    : 0;
                if (wtaxDiv) {
                    wtaxDiv.textContent = `₱ ${wtaxAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
                document.getElementById('wtax_value').value = wtaxAmount.toFixed(2);

                const totalWithTax = roundCurrency(total - wtaxAmount);
                if (amountDueDiv) {
                    amountDueDiv.textContent = `₱ ${totalWithTax.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
                document.getElementById('amount_due').value = totalWithTax.toFixed(2);
            }

            if (vatBtn && taxBtn && wtaxPercentInput) {
                vatBtn.addEventListener('change', function () { updateValues(); markDirty(); });
                taxBtn.addEventListener('change', function () { updateValues(); markDirty(); });
                wtaxPercentInput.addEventListener('input', function () { updateValues(); markDirty(); });
                updateValues(); // initial compute lang — hindi pa dirty
            }
        });

        /* =========================================================
           EDIT LINE ITEM — load data into right pane
           ========================================================= */
        function EditModal(inventoryId) {
            document.querySelectorAll('.edit2-cart-table tbody tr').forEach(function (tr) {
                tr.classList.remove('active');
            });
            const row = document.querySelector('.edit2-cart-table tbody tr[data-row-id="' + inventoryId + '"]');
            if (row) row.classList.add('active');

            openEditPane();

            $.ajax({
                url: '{{ route("expenses.editcart2", ["id" => "__inventoryId__"]) }}'.replace("__inventoryId__", inventoryId),
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.error) { alert('Error: ' + data.error); return; }

                    const categoryDropdown = $('#edit-category');
                    categoryDropdown.empty();
                    data.expenses_categories.forEach(function(cat) {
                        categoryDropdown.append($('<option>', { value: cat.id, text: cat.name }));
                    });
                    categoryDropdown.val(data.inventory.category_id);

                    const supplierDropdown = $('#edit-supplier');
                    supplierDropdown.empty();
                    data.suppliers.forEach(function(supplier) {
                        supplierDropdown.append($('<option>', { value: supplier.id, text: supplier.name }));
                    });
                    supplierDropdown.val(data.inventory.supplier_id);

                    $('#inventory-id').val(inventoryId);
                    $('#edit-exp-type').val(data.inventory.exp_type === 'Cost of Sales' ? '0' : '1');
                    $('#edit-quantity').val(data.inventory.quantity);
                    $('#edit-unit').val(data.inventory.unit);
                    $('#edit-remarks').val(data.inventory.remarks);
                    $('#edit-unit-price').val(data.inventory.unit_price);
                    $('#edit-amount').val(data.inventory.amount);

                    var isPaid = data.inventory.payment_class === 'Paid' ? '1' : '0';
                    $('input[name="edit-payment-class"][value="' + isPaid + '"]').prop('checked', true);

                    // snapshot AFTER lahat ng fields naka-set, para malinis ang dirty-check
                    captureFormSnapshot();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                    alert('Error fetching data');
                }
            });
        }

        /* =========================================================
           FORM DIRTY-TRACKING — Save Changes disabled hangga't
           walang binago sa edit line item form
           ========================================================= */
        let editFormSnapshot = null;
        const EDIT_FORM_FIELD_IDS = [
            'edit-exp-type', 'edit-supplier', 'edit-category',
            'edit-remarks', 'edit-unit', 'edit-quantity',
            'edit-unit-price', 'edit-amount'
        ];

        function getFormSnapshot() {
            const snap = {};
            EDIT_FORM_FIELD_IDS.forEach(function (id) {
                const el = document.getElementById(id);
                if (el) snap[id] = el.value;
            });
            const checkedRadio = document.querySelector('input[name="edit-payment-class"]:checked');
            snap['edit-payment-class'] = checkedRadio ? checkedRadio.value : '';
            return snap;
        }

        function captureFormSnapshot() {
            editFormSnapshot = getFormSnapshot();
            updateSaveButtonState();
        }

        function isFormDirty() {
            if (!editFormSnapshot) return false;
            const current = getFormSnapshot();
            return Object.keys(current).some(function (key) {
                return current[key] !== editFormSnapshot[key];
            });
        }

        function updateSaveButtonState() {
            const btn = document.getElementById('saveChangesBtn');
            if (!btn) return;
            if (isFormDirty()) {
                btn.disabled = false;
                btn.style.background = '#2563eb';
                btn.style.cursor = 'pointer';
                btn.style.opacity = '1';
            } else {
                btn.disabled = true;
                btn.style.background = '#9ca3af';
                btn.style.cursor = 'not-allowed';
                btn.style.opacity = '0.75';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var rightPane = document.getElementById('edit2RightPane');
            if (rightPane) {
                rightPane.addEventListener('input', updateSaveButtonState);
                rightPane.addEventListener('change', updateSaveButtonState);
            }
        });

        /* Auto-compute amount + numeric-only inputs (walang spinners, kagaya sa Add form) */
        document.addEventListener('DOMContentLoaded', function() {
            var qtyEl = document.getElementById('edit-quantity');
            var upEl  = document.getElementById('edit-unit-price');
            var amtEl = document.getElementById('edit-amount');
            function computeAmount() {
                var quantity = parseFloat(qtyEl.value) || 0;
                var unitPrice = parseFloat(upEl.value) || 0;
                amtEl.value = (quantity * unitPrice).toFixed(2);
            }
            if (qtyEl && upEl && amtEl) {
                qtyEl.addEventListener('input', computeAmount);
                upEl.addEventListener('input', computeAmount);
                computeAmount();
            }

            // Restrict sa numbers + one decimal point lang — walang up/down arrows
            // dahil type="text" na ito, kagaya ng .inputNumber sa Add form.
            document.querySelectorAll('.edit2-form .inputNumber').forEach(function (input) {
                input.addEventListener('keypress', function (e) {
                    var char = String.fromCharCode(e.which);
                    if (!/^[0-9.]$/.test(char)) { e.preventDefault(); return; }
                    if (char === '.' && input.value.indexOf('.') >= 0) { e.preventDefault(); }
                });
            });
        });

        /* =========================================================
           SAVE LINE ITEM
           ========================================================= */
        function saveChanges(buttonElement) {
            if (buttonElement && buttonElement.disabled) return; // walang changes — huwag tumuloy

            const inventoryId  = document.getElementById('inventory-id').value;
            const expType      = document.getElementById('edit-exp-type').value;
            const supplierId   = document.getElementById('edit-supplier').value;
            const categoryId   = document.getElementById('edit-category').value;
            const name         = document.getElementById('edit-remarks').value;
            const unit         = document.getElementById('edit-unit').value;
            const quantity     = document.getElementById('edit-quantity').value;
            const unitPrice    = document.getElementById('edit-unit-price').value;
            const amount       = document.getElementById('edit-amount').value;
            const checkedRadio = document.querySelector('input[name="edit-payment-class"]:checked');
            const paymentStatus = checkedRadio ? checkedRadio.value : '0';

            const data = {
                inventoryId, exp_type: expType, supplier_id: supplierId,
                category_id: categoryId, name, unit, quantity,
                unit_price: unitPrice, amount, payment_class: paymentStatus
            };

            $.ajax({
                url: '{{ route("expenses.updateEdit2", ["inventoryId" => ":inventoryId"]) }}'.replace(':inventoryId', inventoryId),
                method: 'PUT',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        markDirty();
                        editFormSnapshot = null;
                        updateSaveButtonState();
                        showSuccessToast('Changes saved successfully!');
                        closeEditPane();
                        setTimeout(function () { location.reload(); }, 1400);
                    } else {
                        alert('Failed to save changes: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving data:', error);
                    alert('Error saving data');
                }
            });
        }

        /* =========================================================
           DONE — save VAT/WTax then postMessage to parent
           (naka-guard: hindi tumatakbo kapag walang changes)
           ========================================================= */
        document.addEventListener('DOMContentLoaded', function () {
            const submitBtn = document.getElementById('doneBtn');
            if (!submitBtn) return;

            submitBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (!isDirty()) return; // walang changes — huwag tumuloy

                const edit_id = parseFloat(document.getElementById('edit_id').value);
                const totalvalue = parseFloat(document.getElementById('total_value').value);
                const eVat = parseFloat(document.getElementById('vat_value').value);
                const ewtaxpercent = parseFloat(document.getElementById('wtaxPercent').value);
                const eWtax = parseFloat(document.getElementById('wtax_value').value);
                const eAmount = parseFloat(document.getElementById('amount_due').value);

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const fd = new FormData();
                fd.append('_token', token);
                fd.append('e_edit_id', edit_id);
                fd.append('e_total', totalvalue);
                fd.append('e_vat', eVat);
                fd.append('e_wtaxpercent', ewtaxpercent);
                fd.append('e_wtax', eWtax);
                fd.append('e_amount', eAmount);

                fetch('{{ route("expenses.cart_store2") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        clearDirty();
                        showSuccessToast(data.message || 'Data transferred successfully.');
                        setTimeout(function () {
                            if (window.parent && window.parent !== window) {
                                try {
                                    window.parent.postMessage({ type: 'editTransactionDone' }, '*');
                                } catch (e) {
                                    window.parent.location.reload();
                                }
                            } else {
                                window.location.href = "{{ route('expenses.index') }}";
                            }
                        }, 1400);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(() => {
                    alert('An error occurred.');
                });
            });
        });

        /* =========================================================
           SUCCESS TOAST
           ========================================================= */
        function showSuccessToast(message) {
            const wrap = document.getElementById('successToast');
            const card = document.getElementById('successToastCard');
            const msg  = document.getElementById('successToastMessage');
            const bar  = document.getElementById('successToastBar');
            if (!wrap || !card) return;
            msg.textContent = message || 'Success.';
            wrap.style.display = 'flex';
            requestAnimationFrame(() => {
                card.style.transform = 'scale(1)';
                card.style.opacity = '1';
            });
            bar.style.transition = 'none';
            bar.style.transform  = 'scaleX(1)';
            requestAnimationFrame(() => {
                bar.style.transition = 'transform 1300ms linear';
                bar.style.transform  = 'scaleX(0)';
            });
            setTimeout(() => {
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0';
                setTimeout(() => { wrap.style.display = 'none'; }, 200);
            }, 1400);
        }
    </script>

    <div id="successToast" class="success-toast-wrap" style="display: none;">
        <div id="successToastCard"
             style="background: #fff; border-radius: 1rem; box-shadow: 0 20px 40px rgba(0,0,0,0.15); width: 100%; max-width: 24rem; padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 1rem; transform: scale(0.95); opacity: 0; transition: all 0.2s ease;">
            <div style="width: 4rem; height: 4rem; border-radius: 9999px; background: #d1fae5; display: flex; align-items: center; justify-content: center;">
                <i class="uil uil-check-circle" style="font-size: 2rem; color: #059669;"></i>
            </div>
            <div style="text-align: center;">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0;">Success</h3>
                <p id="successToastMessage" style="font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0;">Data transferred successfully.</p>
            </div>
            <div style="width: 100%; height: 4px; background: #f3f4f6; border-radius: 9999px; overflow: hidden;">
                <div id="successToastBar" style="height: 100%; background: #10b981; width: 100%; transform-origin: left;"></div>
            </div>
        </div>
    </div>
</body>
</html>