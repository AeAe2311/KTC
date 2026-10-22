<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ChartOfAccountsController extends Controller
{
    public function index(Request $request)
    {
        $industries = DB::table('chart_industries')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        $categories = DB::table('chart_sub_acc')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        $classifications = DB::table('chart_classifications')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        $chartCategories = DB::table('chart_categories')->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        // Get expense categories with their related data for the chart of accounts
        $expenseCategories = DB::table('expenses_categories as ec')
            ->where('ec.is_deleted', 0)
            ->leftJoin('chart_industries as inds', 'ec.industry_id', '=', 'inds.id')
            ->leftJoin('chart_sub_acc as cats', 'ec.category_id', '=', 'cats.id')
            ->leftJoin('chart_classifications as clss', 'ec.classification_id', '=', 'clss.id')
            ->leftJoin('chart_categories as cc', 'ec.cc_id', '=', 'cc.id')
            ->select(
                'ec.*',
                'inds.name as industry_name',
                'cats.name as category_name',
                'clss.name as classification_name',
                'cc.name as chart_category_name'
            )
            ->orderBy('ec.created_at', 'desc')
            ->get();

        // Get all expense categories for dropdown
        $allExpenseCategories = DB::table('expenses_categories')
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        $activeTab = $request->input('tab', 'industry');

        return view('admin.reports.chart-of-accounts', compact(
            'industries',
            'categories',
            'classifications',
            'chartCategories',
            'expenseCategories',
            'allExpenseCategories',
            'activeTab'
        ));
    }

    // ===== CHART CATEGORY METHODS =====

    public function storeChartCategory(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('chart_categories')->insert([
            'name' => $request->name,
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'chartCategory');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Chart Category added successfully.');
    }

    public function updateChartCategory(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'slug' => ['required'],
        ]);

        DB::table('chart_categories')->where('id', $request->slug)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'chartCategory');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Chart Category updated successfully.');
    }

    public function deleteChartCategory($id, Request $request)
    {
        DB::table('chart_categories')->where('id', $id)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'chartCategory');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Chart Category deleted successfully.');
    }

    // ===== INDUSTRY METHODS =====

    public function storeIndustry(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('chart_industries')->insert([
            'name' => $request->name,
            'slug' => Str::random(60),
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'industry');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Industry added successfully.');
    }

    public function updateIndustry(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'slug' => ['required'],
        ]);

        DB::table('chart_industries')->where('slug', $request->slug)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'industry');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Industry updated successfully.');
    }

    public function deleteIndustry($slug, Request $request)
    {
        DB::table('chart_industries')->where('slug', $slug)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'industry');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Industry deleted successfully.');
    }

    // ===== SUB ACCOUNT METHODS =====

    public function storeSubaccount(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('chart_sub_acc')->insert([
            'name' => $request->name,
            'slug' => Str::random(60),
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'category');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Sub-account added successfully.');
    }

    public function updateSubaccount(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'slug' => ['required'],
        ]);

        DB::table('chart_sub_acc')->where('slug', $request->slug)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'category');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Sub-account updated successfully.');
    }

    public function deleteSubaccount($slug, Request $request)
    {
        DB::table('chart_sub_acc')->where('slug', $slug)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'category');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Sub-account deleted successfully.');
    }

    // ===== CLASSIFICATION / JOURNAL METHODS =====

    public function storeClassification(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('chart_classifications')->insert([
            'name' => $request->name,
            'slug' => Str::random(60),
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'classification');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Journal added successfully.');
    }

    public function updateClassification(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'slug' => ['required'],
        ]);

        DB::table('chart_classifications')->where('slug', $request->slug)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'classification');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Journal updated successfully.');
    }

    public function deleteClassification($slug, Request $request)
    {
        DB::table('chart_classifications')->where('slug', $slug)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);

        $tab = $request->input('tab', 'classification');
        return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
            ->with('message', 'Journal deleted successfully.');
    }

    // ===== ACCOUNT CODE METHODS =====

    public function storeAccountCode(Request $request)
    {
        $request->validate([
            'industry_id' => ['required'],
            'category_id' => ['required'],
            'classification_id' => ['required'],
            'cc_id' => ['nullable'],
            'code' => ['required'],
            'name' => ['required'],
        ]);

        try {
            DB::beginTransaction();

            $name = $request->name;

            // Check for existing expense category by name (case insensitive)
            $existingCategory = DB::table('expenses_categories')
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->where('is_deleted', 0)
                ->first();

            // Handle empty cc_id as null
            $chartCategoryId = $request->cc_id;
            if ($chartCategoryId === '' || $chartCategoryId === null) {
                $chartCategoryId = null;
            }

            if ($existingCategory) {
                // Update existing category
                DB::table('expenses_categories')
                    ->where('id', $existingCategory->id)
                    ->update([
                        'industry_id' => $request->industry_id,
                        'category_id' => $request->category_id,
                        'classification_id' => $request->classification_id,
                        'cc_id' => $chartCategoryId,
                        'code' => $request->code,
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new expense category
                DB::table('expenses_categories')->insert([
                    'industry_id' => $request->industry_id,
                    'category_id' => $request->category_id,
                    'classification_id' => $request->classification_id,
                    'cc_id' => $chartCategoryId,
                    'code' => $request->code,
                    'name' => $name,
                    'slug' => Str::random(60),
                    'is_deleted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return Redirect::route('report.chart-of-accounts.index', ['tab' => 'accountCode'])
                ->with('message', 'Chart of Accounts added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create Chart of Accounts: ' . $e->getMessage());
        }
    }

    public function updateAccountCode(Request $request)
    {
        $request->validate([
            'slug' => ['required'],
            'industry_id' => ['required'],
            'category_id' => ['required'],
            'classification_id' => ['required'],
            'cc_id' => ['nullable'],
            'code' => ['required'],
            'name' => ['required'],
        ]);

        try {
            DB::beginTransaction();

            // Get the current expense category
            $currentCategory = DB::table('expenses_categories')
                ->where('slug', $request->slug)
                ->where('is_deleted', 0)
                ->first();

            if (!$currentCategory) {
                throw new \Exception('Account code not found');
            }

            // Handle empty cc_id as null
            $chartCategoryId = $request->cc_id;
            if ($chartCategoryId === '' || $chartCategoryId === null) {
                $chartCategoryId = null;
            }

            // Update the expense category
            DB::table('expenses_categories')
                ->where('slug', $request->slug)
                ->update([
                    'industry_id' => $request->industry_id,
                    'category_id' => $request->category_id,
                    'classification_id' => $request->classification_id,
                    'cc_id' => $chartCategoryId,
                    'code' => $request->code,
                    'name' => $request->name,
                    'updated_at' => now(),
                ]);

            DB::commit();

            $tab = $request->input('tab', 'accountCode');
            return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
                ->with('message', 'Chart of Accounts updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update Chart of Accounts: ' . $e->getMessage());
        }
    }

    public function deleteAccountCode($slug, Request $request)
    {
        try {
            DB::beginTransaction();

            // Soft delete the expense category
            DB::table('expenses_categories')
                ->where('slug', $slug)
                ->update([
                    'is_deleted' => 1,
                    'updated_at' => now(),
                ]);

            DB::commit();

            $tab = $request->input('tab', 'accountCode');
            return Redirect::route('report.chart-of-accounts.index', ['tab' => $tab])
                ->with('message', 'Chart of Accounts deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete Chart of Accounts: ' . $e->getMessage());
        }
    }

    // Helper method to get expense categories (for AJAX calls)
    public function getExpenseCategories(Request $request)
    {
        $search = $request->input('search', '');
        $query = DB::table('expenses_categories')
            ->where('is_deleted', 0);

        if (!empty($search)) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        $categories = $query->orderBy('name', 'asc')->get();

        return response()->json($categories);
    }

    public function updateField(Request $request)
    {
        $request->validate([
            'slug' => ['required'],
            'field' => ['required', 'in:industry_id,category_id,classification_id,cc_id,code,name'],
            'value' => ['required'],
        ]);

        try {
            DB::beginTransaction();

            $expenseCategory = DB::table('expenses_categories')
                ->where('slug', $request->slug)
                ->where('is_deleted', 0)
                ->first();

            if (!$expenseCategory) {
                throw new \Exception('Account code not found');
            }

            $value = $request->value;

            // For foreign key fields, verify the value exists
            if (in_array($request->field, ['industry_id', 'category_id', 'classification_id', 'cc_id'])) {
                $tableMap = [
                    'industry_id' => 'chart_industries',
                    'category_id' => 'chart_sub_acc',
                    'classification_id' => 'chart_classifications',
                    'cc_id' => 'chart_categories'
                ];

                // Handle empty/null value for cc_id (allow clearing)
                if ($request->field === 'cc_id' && ($value === '' || $value === null || $value === 'null')) {
                    $value = null;
                } else {
                    $exists = DB::table($tableMap[$request->field])
                        ->where('id', $value)
                        ->where('is_deleted', 0)
                        ->exists();

                    if (!$exists) {
                        throw new \Exception('Selected value does not exist');
                    }
                }
            }

            // Handle empty string for code/name fields
            if (in_array($request->field, ['code', 'name']) && $value === '') {
                throw new \Exception('Value cannot be empty');
            }

            DB::table('expenses_categories')
                ->where('slug', $request->slug)
                ->update([
                    $request->field => $value,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}