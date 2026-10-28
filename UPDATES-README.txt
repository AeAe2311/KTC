INCOME STATEMENT README with COGS not yet API
CONTROLLER: 
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\app\Http\Controllers\IncomeStatementController.php
FILE: IncomeStatementController.php -- NEW FILE

VIEWS:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\admin\reports\income-statement.blade.php
FILE: income-statement.blade.php -- NEW FILE

LAYOUT: navigation.blade.phh
{{-- LINE 181: Start of IS with Cogs added by ken 10-14-2026  --}}
{{-- LINE 188: end of IS with Cogs added by ken 10-14-2026 --}}

__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

TRIAL BALANCE README

CONTROLLER:
E:\Projects\htdocs\_kumpadres-dev-sept21\app\Http\Controllers\TrialBalanceController.php

VIEWS:
PATH: E:\Projects\htdocs\accounting-system\resources\views\admin\reports\trial_balance.blade.php
FILE: trial_balance.blade.php --- NEW FILE

LAYOUT: navigation.blade.php
{{-- LINE 203: Start of trial balance nav button added by ken 7-29-2026 --}}
{{-- LINE 209: end of trial balance nav button added by ken 7-29-2026 --}}


ROUTE: web.php
// LINE 64: NEW ADDED BY KEN 07-29-2026
// LINE 68: NEW ADDED BY KEN 07-29-2026
// LINE 140: NEW ADDED BY KEN 07-29-2026 trialbalance
// LINE 141: NEW ADDED BY KEN 07-29-2026 trialbalance


__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

REFUND VIEW and SEARCH features: README

CONTROLLER:
// LINE 584: Start of searchbynumber method added by ken 9-30-2026
// LINE 635: End of searchbynumber method added by ken 9-30-2026
// LINE 824: NEw filter added by ken 9-30-2026
// LINE 825: NEw filter added by ken 9-30-2026
// LINE 832: NEw filter added by ken 9-30-2026
// LINE 833: NEw filter added by ken 9-30-2026


VIEWS:
PATH: \htdocs\_kumpadres-dev-sept21\resources\views\user\cashier\transactions-sales.blade.php
FILE: transactions-sales.blade.php  -- WHOLE NEW FILE
PATH: \htdocs\_kumpadres-dev-sept21\resources\views\user\cashier\transactions-sales.blade.php
FILE: transactions.blade.php  -- WHOLE NEW FILE


ROUTE: web.php
// LINE 927: added by ken for search feature of trans 9-30-2026

__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

SAWDUST: README
CONTROLLER: 
PATH: \kumpadres-dev-sept21\app\Http\Controllers\SawdustController.php
FILE: SawdustController.php -- NEW FILE

VIEWS: 
FOLDER PATH: \htdocs\_kumpadres-dev-sept21\resources\views\user\inventory\sawdust
PATH: _kumpadres-dev-sept21\resources\views\user\inventory\sawdust\index.blade.php
FILES: index.blade.php  -- NEW FILE
LAYOUT: navigation.blade.php
START LINE: {{-- LINE 445: start of added nav sawdust by ken 9-30-26 --}}
END LINE: {{-- LINE 466: end of added nav sawdust by ken 9-30-26 --}} 

ROUTE: web.php
    // LINE634: START of ROUTE SAWDUST sept 30, 2026
    Route::get('/inventory/sawdust', [SawdustController::class, 'index'])->name('sawdust.index');
    Route::post('/inventory/sawdust/store', [SawdustController::class, 'store'])->name('sawdust.store');
    // LINE 638: END OF NEW ROUTE 9-30-2026

SQL:
NEW TABLE: sawdust_logs.sql

__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

INVENTORY MOVEMENT README for sort order

CONTROLLER:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\app\Http\Controllers\InventoryMovementController.php
FILE: InventoryMovementController.php -- UPDATES
// LINE 177: START OF SORTING DIRECTION added by ken 10-15-2026
// LINE 186: END OF SORTING DIRECTION added by ken 10-15-2026
// LINE 1090: START OF SORTING DIRECTION added by ken 10-15-2026
// LINE 1097: END OF SORTING DIRECTION added by ken 10-15-2026
// LINE 1224: START OF SORTING DIRECTION added by ken 10-15-2026
// LINE 1231: END OF SORTING DIRECTION added by ken 10-15-2026


VIEW:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\user\inventory\movement\index.blade.php
FILE: index.blade.php -- UPDATES
<!-- LINE 156: START OF SORT DIRECTION TOGGLE BUTTON AND RESET BUTTON UPDATE BY KEN 10-15-2026 -->
<!-- LINE 201: END OF SORT DIRECTION TOGGLE BUTTON AND RESET BUTTON UPDATE BY KEN 10-15-2026 -->
// LINE 842: START of Sort Direction Toggle added by ken 10-15-2026
// LINE 853: END of Sort Direction Toggle added by ken 10-15-2026

__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

MENU RANKING

CONTROLLER:

HELPLER APP:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\app\Helpers\ChartHelper.php
FILE: ChartHelper.php ---> UPDATED HELPER APPP FOR GRAPH

VIEW:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\admin\reports\centralized.blade.php
FILE: centralized.blade.php
<!-- LINE 376: UPDATE BY KEN 10-23-2026 -->
{{-- LINE 1758: start of update by ken 10-23-2026 MENU RANKING--}}
{{-- LINE 1787: end of update by ken 10-23-2026 MENU RANKING --}}



PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\admin\reports\index.blade.php
FILE: index.blade.php
<!--ken enabled New OPTION FOR MENU RANKING REPORTS 10-23-2026-->


__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

DSR in WEB ADJUSTMENT

CONTROLLER: ReportController.php -- UPDATES
// LINE 792: Start of update to remove float update by ken 10-24-2026
// LINE 793: Start of update to remove float update by ken 10-24-2026
// LINE 794: Start of update to remove float update by ken 10-24-2026
// LINE 893: added by ken 252306001 10-24-2026
// LINE 941: Start of update to remove float update by ken 10-24-2026
// LINE: 942: added by ken 252306001 10-24-2026
// LINE 949: update change to arTotalPaid from arPaymentAmount by ken 252306001 10-24-2026

__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------

CREDIT LIMIT README

CONTROLLER:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\app\Http\Controllers\TableController.php
FILE: TableController.php -- UPDATES
// LINE 69: added by ken 10-2-26
// LINE 73: added by ken 10-2-26
// LINE 78: added by ken 10-2-26
// LINE 104: added by ken 10-2-26
// LINE 109: added by ken 10-2-26
// LINE 115: added by ken 10-2-26

VIEWS:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\admin\system-management\tables\add.blade.php
FILE: add.blade.php  ---- UPDATES
<!-- LINE START 15: start of credit limit field added by ken 10-2-2026 -->
<!-- LINE END 20: End of credit limit field added by ken 10-2-2026 -->3

PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\admin\system-management\tables\edit.blade.php
FILE: edit.blade.php  ---- UPDATES
<!-- LINE START 42: start of credit limit field added by ken 10-2-2026 -->
<!-- LINE END 47: End of credit limit field added by ken 10-2-2026 -->

PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\admin\system-management\tables\index.blade.php
FILE: index.blade.php  ---- UPDATES
<!-- LINE 111-114: Start of header credit limit by ken 10-2-26 -->
<!-- LINE 133-136: Start of header credit limit by ken 10-2-26 -->
<!-- LINE 179: Added data for credit limit by ken 10-2-26 -->
<!-- LINE 181: Added data for credit limit by ken 10-2-26 -->
<!-- LINE 205: Added data for credit limit by ken 10-2-26 -->
<!-- LINE 206: Added data for credit limit by ken 10-2-26 -->
__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------
README for Generate ITEMCODE

Controller: 
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\app\Http\Controllers\MenuController.php
FILE: MenuController.php --- updates
// LINE START 3714: START OF ITEM CODE QUERY ADDED BY KEN 10-26-26
// LINE END 3905: START OF ITEM CODE QUERY ADDED BY KEN 10-26-26


VIEWS:
PATH: C:\xampp\htdocs\_kumpadres-dev-sept21\resources\views\user\cook\menu-preparation.blade.php
FILE: menu-preparation.blade.php -- UPDATES
<!-- LINE START 639: START OF GENERATE ITEM C0DE BY KEN 10-26-26 -->
<!-- LINE END 651: START OF GENERATE ITEM C0DE BY KEN 10-26-26 -->


ROUTE: WEB.PHP
// LINE 618: ADDED BY KEN 10-26-26

__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------
CENTRALIZED EDIT FEATURES

CONTROLLER:MenuController.php
PATH: C:\xampp\htdocs\kumpadres-centralized_edit-newest\app\Http\Controllers\MenuController.php
// LINE 1176: Added by ken 10-21-26
// START LINE 1330: Start of added query by ken 10-21-2026
// END LINE: 1332: End of added query by ken 10-21-2026
// LINE 1771: Start of update centralized edit by Ken 10-21-2026
// LINE 1786: end of update centralized edit by ken 10-21-2026


__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------
LOCATION TRANSFER README





__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------
ORDER PAGE README

CONTROLLER:



__________________________________________________________________________________________________
//////////////////////////////////////////////////////////////////////////////////////////////////
--------------------------------------------------------------------------------------------------