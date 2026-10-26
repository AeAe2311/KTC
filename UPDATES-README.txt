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

\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

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

////////////////////////////////////////////////////////////////////////////////////////////////////////

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

////////////////////////////////////////////////////////////////////////////////////////////////////

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



//////////////////////////////////////////////////////////////////////////////////////////////////

DSR in WEB ADJUSTMENT

CONTROLLER: ReportController.php -- UPDATES
// LINE 792: Start of update to remove float update by ken 10-24-2026
// LINE 793: Start of update to remove float update by ken 10-24-2026
// LINE 794: Start of update to remove float update by ken 10-24-2026
// LINE 893: added by ken 252306001 10-24-2026
// LINE 941: Start of update to remove float update by ken 10-24-2026
// LINE: 942: added by ken 252306001 10-24-2026
// LINE 949: update change to arTotalPaid from arPaymentAmount by ken 252306001 10-24-2026

////////////////////////////////////////////////////////////////////////////////////////////////////

CREDIT LIMIT README

CONTROLLER:

////////////////////////////////////////////////////////////////////////////////////////////////////

ORDER PAGE README

CONTROLLER:



//////////////////////////////////////////////////////////////////////////////////////////////////////
CENTRALIZED EDIT FEATURES

CONTROLLER:
PATH: 


/////////////////////////////////////////////////////////////////////////////////////////////////////
README for Generate ITEMCODE

Controller: 

