<style>

.expenses-page-shell,
.revolving-page-shell {
    min-height: 100vh;
}

.expenses-page,
.revolving-page {
    min-height: calc(100vh / 0.85);
    width: calc(100% / 0.85);
    zoom: 0.85;
}

.empty-state-row > td {
    height: calc(100vh - 320px);
    min-height: 220px;
    vertical-align: middle;
}

#inventoryTable .empty-state-row > td,
#approvalTable .empty-state-row > td {
    height: calc(100vh - 380px);
}

.expenses-page,
.revolving-page {
    font-size: clamp(12px, 0.85vw + 8px, 14px);
}

@media (max-width: 1280px) {
    .summary-card { min-width: 120px; }
}

@media (max-width: 1024px) {
    .expenses-table,
    .revolving-table,
    .journal-total-table { min-width: 720px; }

    .expenses-table > thead > tr > th,
    .revolving-table > thead > tr > th,
    .journal-scroll > .revolving-table > thead > tr > th {
        font-size: 10px;
        padding: 8px 10px;
    }

    tbody.transaction-group > tr > td.txn-col,
    tbody.txn-block > tr > td.txn-col,
    .revolving-table td.sticky-col {
        width: 130px;
        min-width: 130px;
        max-width: 130px;
        padding: 10px 8px;
    }
}

@media (max-width: 768px) {
    .expenses-page-shell,
    .revolving-page-shell {
        margin-left: 0 !important;
        min-height: 100dvh;
    }
    .expenses-page,
    .revolving-page {
        padding: 0.5rem;
        min-height: calc(100dvh / 0.85);
        width: calc(100vw / 0.85);
    }
    #filterForm { width: 100%; }
    #filterForm > * { flex: 1 1 auto; }
    .search-shell.open { width: min(260px, calc(100vw - 96px)); }
    #approveModal .modal-card { width: 100%; max-height: 85vh; }
    .empty-state-row > td { height: calc(100dvh - 260px); }
}

@media (max-width: 480px) {
    .journal-filter-btn { font-size: 11px; padding: 6px 10px; }
    .summary-card { min-width: 100%; }
}
</style>