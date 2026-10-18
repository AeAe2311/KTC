# Laravel API Tutorial: How APIs Work and How to Create Them

This document explains how the APIs in this Laravel project work and provides a step-by-step guide on creating new ones. Based on the existing `routes/api.php` and `ApiController`.

## 1. What Are APIs in Laravel?
- **APIs (Application Programming Interfaces)** are endpoints that allow different systems (like your Android app, based on the sync routes) to communicate with your Laravel backend. They handle requests (GET, POST, etc.) and return responses, often as JSON data.
- In Laravel, API routes are defined in `routes/api.php`. These routes are grouped under the "api" middleware, which means they're meant for external consumption (not web pages).
- Your routes mostly use **GET** for fetching data (e.g., kitchen orders) and **POST** for syncing data from Android (e.g., uploading transactions).
- Controllers (like `ApiController`) contain the logic: they query your database, process data, and return it. Laravel automatically converts arrays or Eloquent collections to JSON when returned from a controller method.

**Key Concepts:**
- **Routes**: Define the URL and HTTP method (e.g., `GET /api/kitchen`).
- **Controllers**: Handle the request, run logic (e.g., database queries), and return a response.
- **Middleware**: Protects or modifies requests (e.g., authentication).
- **Responses**: Usually JSON, but can be plain data like in your `kitchen()` method, which returns a database result directly.

From your code, APIs here seem focused on:
- Fetching data for POS systems (e.g., kitchen/bar orders, transactions).
- Syncing data from Android devices (e.g., uploading menus, users).
- Handling approvals and transfers between branches.

## 2. How Do the Existing APIs Work?
Let's look at a few examples from your `routes/api.php` and `ApiController.php`:

- **Route Example (GET /api/kitchen)**:
  ```php
  Route::get('/kitchen', [ApiController::class, 'kitchen'])->name('api.kitchen');
  ```
  - This defines a GET endpoint at `/api/kitchen`.
  - When someone (e.g., a POS system) hits this URL, it calls the `kitchen()` method in `ApiController`.
  - **What it does**: Queries the `prints` and `ordered` tables to get unprinted kitchen orders (for departments 1, 2, 4). It joins multiple tables (menus, users, etc.) and returns the results as a collection.
  - **Response**: Returns JSON like `[{"id": 1, "tran_id": 123, "quantity": 2, ...}]`. Laravel auto-converts this to JSON.
  - **Why it works**: It's simple—fetch data and return it. No input needed for GET requests.

- **Route Example (POST /api/sync_transaction)**:
  ```php
  Route::post('/sync_transaction', [SyncApiAndroidController::class, 'sync_transaction']);
  ```
  - This is a POST endpoint for syncing data from Android.
  - It expects data in the request body (e.g., JSON with transaction details).
  - The controller method would validate and save the data to your database.

- **Controller Method Breakdown (from ApiController)**:
  - Methods like `kitchen()` use Laravel's DB facade for raw queries or Eloquent models.
  - They return data directly (e.g., `return $orderResult;`), which Laravel turns into JSON.
  - No views or HTML—APIs return data, not pages.

**Testing an API**:
- You can test GET routes in a browser or with tools like Postman: Visit `http://your-domain/api/kitchen`.
- For POST, use Postman to send JSON data.

## 3. How to Make New APIs
Creating APIs in Laravel is straightforward. Follow these steps. I'll use a simple example: Adding a new GET endpoint to fetch all menus.

### Step 1: Define the Route in `routes/api.php`
- Open `routes/api.php`.
- Add a new route. For example, to get all menus:
  ```php
  Route::get('/menus', [ApiController::class, 'getAllMenus'])->name('api.menus');
  ```
  - `Route::get()`: For fetching data.
  - `/menus`: The URL path (full URL: `/api/menus`).
  - `[ApiController::class, 'getAllMenus']`: Points to the `getAllMenus` method in `ApiController`.
  - `->name('api.menus')`: Optional, for easy referencing.

  **Tip**: For POST (e.g., creating data), use `Route::post('/create-menu', [ApiController::class, 'createMenu']);`.

### Step 2: Add the Method to the Controller
- Open `app/Http/Controllers/ApiController.php`.
- Add a new public method. For our example:
  ```php
  public function getAllMenus() {
      // Query the menus table (using DB or Eloquent)
      $menus = DB::table('menus')->where('is_deleted', 0)->get();  // Or use Menu::all() if you have a Menu model
      
      // Return the data (Laravel auto-converts to JSON)
      return $menus;
  }
  ```
  - **Logic**: Use `DB::table()` for raw queries or `Menu::where('is_deleted', 0)->get()` if you have an Eloquent model (check your `app/Models/Menu.php`).
  - **Input Handling**: For GET, no input needed. For POST, use `$request->input('field')` or validate with `$request->validate([...])`.
  - **Response**: Return an array/collection. Add `return response()->json($data, 200);` for custom status codes.
  - **Error Handling**: Wrap in try-catch or use Laravel's validation for robustness.

  **Full Example in Controller**:
  ```php
  use App\Models\Menu;  // If using Eloquent

  public function getAllMenus() {
      $menus = Menu::where('is_deleted', 0)->select('id', 'name', 'price')->get();
      return response()->json(['menus' => $menus], 200);
  }
  ```

### Step 3: Test It
- Run your Laravel app (e.g., `php artisan serve`).
- Visit `http://localhost:8000/api/menus` in a browser or Postman.
- Check for JSON output. If it fails, look at Laravel logs (`storage/logs/laravel.log`) or use `dd()` in the method for debugging.

### Step 4: Advanced Tips
- **Authentication**: For protected APIs, add middleware like `Route::middleware('auth:sanctum')->get('/protected', ...);`.
- **Validation**: For POST routes, validate input:
  ```php
  public function createMenu(Request $request) {
      $request->validate([
          'name' => 'required|string',
          'price' => 'required|numeric',
      ]);
      // Save to DB...
      return response()->json(['message' => 'Menu created'], 201);
  }
  ```
- **Models**: Use Eloquent for cleaner code (e.g., `Menu::create($request->all())`).
- **Pagination**: For large data, use `$menus->paginate(10);`.
- **Resources**: For transforming data, create API Resources (`php artisan make:resource MenuResource`).
- **Versioning**: Group routes under prefixes like `Route::prefix('v1')->group(function () { ... });` for API versioning.

**Common Mistakes to Avoid**:
- Forgetting to import models/classes at the top of the controller.
- Returning HTML instead of data—APIs should return JSON.
- Not handling errors (e.g., return `response()->json(['error' => 'Not found'], 404);` for missing data).

## Notes
- This is based on your current project structure as of February 12, 2026.
- For more details, refer to the Laravel documentation: https://laravel.com/docs/api
- If you need to create a specific API, refer back to this guide and adapt the examples.