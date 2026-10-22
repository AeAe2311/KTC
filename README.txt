VENDORA EXPORT - Restaurant Management System
===========================================

Project Overview:
=================
Vendora Export is a comprehensive restaurant management system built with Laravel 10.x and modern frontend technologies. The system provides complete restaurant operations management including POS, inventory, reporting, procurement, and multi-branch support.

Date: 02-12-2026
Last Updated: Ken
Version: 2.0

TECHNOLOGY STACK:
=================
- **Backend:** Laravel 10.x (PHP 8.1+)
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** MySQL
- **Build Tools:** Vite, PostCSS
- **Charts:** Chart.js
- **Authentication:** Laravel Sanctum
- **Image Processing:** Intervention Image

KEY FEATURES:
=============
- Point of Sale (POS) System
- Inventory Management
- Multi-branch Support
- Procurement & Supplier Management
- Staff Management
- Reporting & Analytics
- Menu Management
- Table Management
- Kitchen Operations
- Waste Management
- Financial Reporting
- User Role Management

PROJECT STRUCTURE:
==================

CONTROLLER:
===========
Core Controllers (app/Http/Controllers/):

**Authentication & User Management:**
- LoginController.php - User authentication
- UserController.php - User CRUD operations
- ProfileController.php - User profile management

**POS & Transactions:**
- POSController.php - Point of Sale operations
- TransactionController.php - Transaction management
- OrderedController.php - Order processing
- TableController.php - Table management
- TableCategoryController.php - Table categorization

**Inventory & Procurement:**
- InventoryController.php - Inventory management
- InventoryTransactionController.php - Inventory transactions
- ProcurementRequestController.php - Procurement requests
- SupplierController.php - Supplier management
- SupplierAccreditationController.php - Supplier accreditation
- SupplierMasterlistController.php - Supplier master list
- SupplierBiddingController.php - Supplier bidding process

**Menu & Kitchen:**
- MenuController.php - Menu item management
- MenuCategoryController.php - Menu categorization
- KitchenController.php - Kitchen operations
- IngredientController.php - Ingredient management
- CategoryController.php - General categories

**Reporting & Analytics:**
- ReportController.php - Comprehensive reporting system
  // ken 250623001 update start - 02-12-2026
  - Updated user name concatenation in multiple query methods
  - Changed 'users.name' to CONCAT(users.firstname, ' ', users.lastname)
  // ken 250623001 update end - 02-12-2026
- ReceiverReportController.php - Receiver reports
- PricingReportController.php - Pricing analytics

**Financial Management:**
- ExpensesController.php - Expense management
- ExpensesCategoryController.php - Expense categorization
- ActualMoneyController.php - Cash management
- RevolvingController.php - Revolving fund management

**System Management:**
- SettingController.php - System settings
- DepartmentController.php - Department management
- StaffController.php - Staff management
- BranchController.php - Branch management
- TransferController.php - Transfer operations

**API & Integration:**
- ApiController.php - API endpoints
- ApiAndroidGetDataController.php - Android app data sync
- SyncApiAndroidController.php - Android synchronization

**Specialized Controllers:**
- WasteController.php - Waste management
- DiscountController.php - Discount management
- TermsController.php - Terms & conditions
- OnlineBankController.php - Online banking integration

MODEL:
======
Database Models (app/Models/):

**Core Business Models:**
- User.php - System users
- Transaction.php - Sales transactions
- Order.php - Customer orders
- Ordered.php - Order items
- Menu.php - Menu items
- Table.php - Restaurant tables

**Inventory Models:**
- Inventory.php - Inventory items
- InventoryTransaction.php - Inventory movements
- Ingredient.php - Recipe ingredients
- Waste.php - Waste records

**Procurement Models:**
- Supplier.php - Suppliers
- ProcurementOrderRequest.php - Procurement requests
- ProcurementOrderedRequest.php - Ordered procurement items

**Organizational Models:**
- Branch.php - Restaurant branches
- Department.php - Departments
- Staff.php - Staff members
- Position.php - Job positions

**Financial Models:**
- ActualMoney.php - Cash registers
- RevolvingBudget.php - Revolving budgets
- Discount.php - Discount configurations

**Reporting Models:**
- PricingReport.php - Pricing reports
- Log.php - System logs

**Category Models:**
- Category.php - General categories
- MenuCategory.php - Menu categories
- TableCategory.php - Table categories
- ExpensesCategory.php - Expense categories
- ItemBrand.php - Item brands

**Request & Transfer Models:**
- OrderRequest.php - Order requests
- Transfer.php - Transfer records
- BranchTransfer.php - Branch transfers

BLADE TEMPLATES:
===============
View Templates (resources/views/):

**Layouts:**
- layouts/ - Main layout templates
- components/ - Reusable components

**Admin Views:**
- admin/dashboard.blade.php - Admin dashboard
- admin/reports/ - Reporting interfaces
  - centralized.blade.php - Main reporting interface
    // ken 250623001 update start - 02-12-2026
    - Modified export button layout and added column reducer functionality
    - Dynamic column visibility toggle with checkboxes
    // ken 250623001 update end - 02-12-2026
- admin/system-management/ - Administrative functions

**User Views:**
- user/ - User-specific interfaces
  - cashier/ - Cashier operations
  - cook/ - Kitchen operations
  - inventory/ - Inventory management
  - staff/ - Staff interfaces

**Authentication:**
- auth/ - Login/register forms
- profile/ - User profile management

ROUTE:
======
Route Definitions (routes/):

**Web Routes (routes/web.php):**
- Authentication routes (/login, /register, /logout)
- Dashboard (/dashboard)
- POS operations (/pos, /orders, /transactions)
- Inventory management (/inventory/*)
- Menu management (/menu/*)
- Reporting (/report/*)
- System management (/system-management/*)
- API endpoints for AJAX operations

**API Routes (routes/api.php):**
- Mobile app synchronization endpoints
- Data export/import APIs
- External service integrations

**Authentication Routes (routes/auth.php):**
- Sanctum token management
- API authentication

DATABASE:
=========
Database Structure (database/):

**Core Tables:**
- users - System users with role-based access
- transactions - Sales transaction records
- orders - Customer order headers
- ordereds - Order line items
- menus - Menu items with pricing
- tables - Restaurant table definitions
- inventories - Inventory item master data
- inventory_transactions - Inventory movement history

**Supporting Tables:**
- categories - General categorization
- menu_categories - Menu item categories
- suppliers - Supplier information
- staff - Staff member details
- branches - Multi-branch support
- departments - Organizational structure
- settings - System configuration

**Key Migrations:**
- 2014_10_12_000000_create_users_table.php
- 2023_03_14_074351_create_inventories_table.php
- 2023_03_22_085501_create_tables_table.php
- 2023_03_30_023139_create_menus_table.php
- 2023_04_25_013646_create_transactions_table.php
- 2023_05_06_130608_create_inventory_transactions_table.php

CONFIGURATION:
=============
Configuration Files (config/):

- app.php - Application configuration
- database.php - Database connection settings
- auth.php - Authentication configuration
- mail.php - Email configuration
- services.php - Third-party service integration
- sanctum.php - API token configuration

HELPERS:
=======
- app/helpers.php - Global helper functions
- app/Helpers/ChartHelper.php - Chart generation utilities

PUBLIC ASSETS:
=============
- public/assets/ - Static assets (images, documents)
- public/storage/ - File uploads and storage

BUILD SYSTEM:
============
- package.json - Node.js dependencies and scripts
- vite.config.js - Frontend build configuration
- tailwind.config.js - Tailwind CSS configuration
- postcss.config.js - PostCSS processing

TESTING:
=======
- tests/ - PHPUnit and Pest test suites
- phpunit.xml - Test configuration

DEPLOYMENT:
==========
- composer.json - PHP dependencies
- artisan - Laravel command-line interface
- .env.example - Environment configuration template

SPECIAL FEATURES:
================
- Multi-branch restaurant management
- Real-time inventory tracking
- Comprehensive reporting with export capabilities
- Mobile app synchronization
- Supplier bidding system
- Kitchen display system
- Table management with categories
- Waste tracking and reporting
- Financial reconciliation tools

MAINTENANCE NOTES:
=================
- Regular backup of database recommended
- Monitor storage directory for uploaded files
- Update dependencies regularly for security
- Test mobile app synchronization after updates
- Verify report exports after code changes

CONTACT:
=======
For support and maintenance inquiries, refer to the development team documentation.