<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\ActualMoney;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Menu;
use App\Models\MenuIndex;
use App\Models\Ordered;
use App\Models\PricingReport;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;
use Carbon\Carbon;
use App\Helpers\ChartHelper;

class ReportController extends Controller {
    public function index() {
        $branchs = [];
        
        if (Auth::user()->role == 1) {
            $branchs = DB::table('settings')
                ->orderBy('name', 'asc')
                ->where('is_deleted', 0)
                //->where('store_code', Auth::user()->store_code) 
                ->where('id', '!=', 1) // Exclude main/admin branch if needed
                ->get();
        }
        
        return view('admin.reports.index', compact('branchs'));
    }

    /**
     * Apply branch filtering to a query builder instance.
     * If $userBranch is provided, filter by that branch value.
     * Otherwise, if $branchCodes is a non-empty array, apply whereIn on the column.
     */
    //ken start update of methods  used to balancesheet ken 25230601    - April 25, 2026
    private function applyBranchFilterToQuery($query, string $column, $userBranch, array $branchCodes = [])
    {
        if ($userBranch) {
            return $query->where($column, $userBranch);
        }

        if (!empty($branchCodes)) {
            return $query->whereIn($column, $branchCodes);
        }

        return $query;
    }
    //ken added method used in DAILY SALES REPORT ken 252306001  APril 24, 2026
    private function normalizePaymentType(string $rawType): string
    {
        return strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim($rawType)));
    }
    //ken added method used in DAILY SALES REPORT ken 252306001  APril 24, 2026
    private function computePaymentBucket(string $paymentType, string $rawPaymentType, bool $isArMode, bool $hasPaymentLog, bool $isArRow): ?string
    {
        if ($isArMode && !$hasPaymentLog) {
            return null;
        }

        if ($rawPaymentType === '') {
            return $isArRow ? null : 'CASH';
        }

        if (preg_match('/\b(CHECK|CHEQUE)\b/i', $paymentType)) {
            return 'CHECK';
        }

        if (preg_match('/\b(GCASH)\b/i', $paymentType)) {
            return 'GCASH';
        }

        if (preg_match('/\b(MAYA|PAYMAYA)\b/i', $paymentType)) {
            return 'MAYA';
        }

        if (preg_match('/\b(BANK\s*TRANSFER|BANK|BANCNET|BDO|BPI|METROBANK|LANDBANK|PNB|UNIONBANK)\b/i', $paymentType)) {
            return 'BANK TRANSFER';
        }

        if (preg_match('/\b(CASH|CASHIER|CASH PAYMENT)\b/i', $paymentType)) {
            return 'CASH';
        }

        return $paymentType !== '' ? $paymentType : null;
    }
    // //ken added method used in DAILY SALES REPORT ken 252306001  APril 24, 2026
    // private function buildDailySRPaymentData($results): array
    // {
    //     $dailySRPaymentColumns = ['CASH'];
    //     $dailySRPaymentLogMap = [];
    //     $dailySRPaymentLogNumberMap = [];
    //     $dailySRRows = [];
    //     $dailyTotals = ['amount' => 0, 'ar' => 0, 'payments' => []];  /* // DITO LANG */
    //     $arTotals = ['amount' => 0, 'ar' => 0, 'payments' => []];
    //     $dailyPaymentBreakdown = [];
    //     $arPaymentBreakdown = [];

    //     $dailySRTranIds = [];
    //     $dailySRTranNumbers = [];

    //     foreach ($results as $dailySRResult) {
    //         $dailySRTranId = $dailySRResult->trans_id ?? $dailySRResult->tran_id ?? $dailySRResult->id ?? null;
    //         if (!is_null($dailySRTranId) && $dailySRTranId !== '') {
    //             $dailySRTranIds[] = $dailySRTranId;
    //         }

    //         $dailySRTranNumber = trim($dailySRResult->number ?? $dailySRResult->tran_number ?? '');
    //         if ($dailySRTranNumber !== '') {
    //             $dailySRTranNumbers[] = $dailySRTranNumber;
    //         }
    //     }

    //     $dailySRTranIds = array_values(array_unique($dailySRTranIds));
    //     $dailySRTranNumbers = array_values(array_unique($dailySRTranNumbers));

    //     if (!empty($dailySRTranIds) || !empty($dailySRTranNumbers)) {
    //         $paymentLogsQuery = DB::table('payment_logs')
    //             ->select('id', 'tran_id', 'tran_number', 'label', 'amount', 'balance')
    //             ->where('is_deleted', 0)
    //             ->where(function ($query) use ($dailySRTranIds, $dailySRTranNumbers) {
    //                 if (!empty($dailySRTranIds)) {
    //                     $query->whereIn('tran_id', $dailySRTranIds);
    //                 }

    //                 if (!empty($dailySRTranNumbers)) {
    //                     if (!empty($dailySRTranIds)) {
    //                         $query->orWhereIn('tran_number', $dailySRTranNumbers);
    //                     } else {
    //                         $query->whereIn('tran_number', $dailySRTranNumbers);
    //                     }
    //                 }
    //             })
    //             ->orderBy('id', 'desc')
    //             ->get();

    //         foreach ($paymentLogsQuery as $dailySRPaymentLog) {
    //             $dailySRMapKey = strval($dailySRPaymentLog->tran_id);
    //             if (!isset($dailySRPaymentLogMap[$dailySRMapKey])) {
    //                 $dailySRPaymentLogMap[$dailySRMapKey] = [];
    //             }
    //             $dailySRPaymentLogMap[$dailySRMapKey][] = $dailySRPaymentLog;

    //             $dailySRNumberKey = trim($dailySRPaymentLog->tran_number ?? '');
    //             if ($dailySRNumberKey !== '') {
    //                 if (!isset($dailySRPaymentLogNumberMap[$dailySRNumberKey])) {
    //                     $dailySRPaymentLogNumberMap[$dailySRNumberKey] = [];
    //                 }
    //                 $dailySRPaymentLogNumberMap[$dailySRNumberKey][] = $dailySRPaymentLog;
    //             }
    //         }
    //     }

    //     foreach ($results as $result) {
    //         $customerName = trim($result->payor_name ?? $result->payor_number ?? 'Walk-in');
    //         $tranId = $result->trans_id ?? $result->tran_id ?? $result->id ?? null;
    //         $tranMapKey = strval($tranId);
    //         $tranNumberKey = trim($result->number ?? $result->tran_number ?? '');
    //         $paymentLogs = $dailySRPaymentLogMap[$tranMapKey] ?? ($dailySRPaymentLogNumberMap[$tranNumberKey] ?? []);
    //         $paymentLog = !empty($paymentLogs) ? $paymentLogs[0] : null;
    //         $hasPaymentLog = !empty($paymentLogs);

    //         $paymentTypeRaw = trim($result->mode_of_payment ?? '');
    //         $paymentType = $this->normalizePaymentType($paymentTypeRaw);
    //         $isArMode = in_array($paymentType, ['AR', 'A/R'], true);

    //         $rowAmount = floatval($result->total ?? 0);
    //         $paymentLogsByBucket = [];
    //         $totalPaidAmount = 0.0;
    //         foreach ($paymentLogs as $paymentLogItem) {
    //             $logLabel = trim($paymentLogItem->label ?? '');
    //             $logType = $this->normalizePaymentType($logLabel);
    //             $bucket = $this->computePaymentBucket($logType, $logLabel, false, true, false) ?? 'CASH';
    //             $amount = floatval($paymentLogItem->amount ?? 0);
    //             if ($amount <= 0) {
    //                 continue;
    //             }
    //             $paymentLogsByBucket[$bucket] = ($paymentLogsByBucket[$bucket] ?? 0) + $amount;
    //             $totalPaidAmount += $amount;
    //         }

    //         if (empty($paymentLogsByBucket) && !$isArMode) {
    //             $isUnpaid = (isset($result->payment_class) && intval($result->payment_class) === 0)
    //                 || (isset($result->is_paid) && intval($result->is_paid) === 0)
    //                 || strtoupper(trim($result->status ?? '')) === 'UNPAID'
    //                 || strtoupper(trim($result->order_status ?? '')) === 'UNPAID';

    //             if ($paymentTypeRaw === '' && $isUnpaid) {
    //                 // For unpaid AR rows with blank mode_of_payment, do not create a fake payment bucket.
    //                 $totalPaidAmount = 0;
    //             } else {
    //                 $bucket = (!is_null($paymentType) && $paymentType !== '') ? $paymentType : 'CASH';
    //                 $paymentLogsByBucket[$bucket] = $rowAmount;
    //                 $totalPaidAmount = $rowAmount;
    //             }
    //         }

    //         //ken added start here 20250624001 - ken - added logic to handle overpayment in daily sales report
    //         // If the customer paid more than the sale total, treat the displayed paid amount as the total only.
    //         if (!$isArMode && $totalPaidAmount > $rowAmount) {
    //             $overpayment = $totalPaidAmount - $rowAmount;
    //             foreach (array_reverse(array_keys($paymentLogsByBucket), true) as $bucketKey) {
    //                 if ($overpayment <= 0) {
    //                     break;
    //                 }

    //                 $bucketAmount = $paymentLogsByBucket[$bucketKey];
    //                 if ($bucketAmount <= $overpayment) {
    //                     unset($paymentLogsByBucket[$bucketKey]);
    //                     $overpayment -= $bucketAmount;
    //                 } else {
    //                     $paymentLogsByBucket[$bucketKey] = $bucketAmount - $overpayment;
    //                     $overpayment = 0;
    //                 }
    //             }

    //             $totalPaidAmount = $rowAmount;
    //         }
    //         // end of overpayment handling logic

    //         $arBalance = max(0, $rowAmount - $totalPaidAmount);
    //         $paidAmountFromLog = $totalPaidAmount;

    //         if ($isArMode && $hasPaymentLog) {
    //             $paymentTypeRaw = trim($paymentLog->label ?? '');
    //         }

    //         $paymentType = $this->normalizePaymentType($paymentTypeRaw);

    //         $isUnpaid = (isset($result->payment_class) && intval($result->payment_class) === 0)
    //             || (isset($result->is_paid) && intval($result->is_paid) === 0)
    //             || strtoupper(trim($result->status ?? '')) === 'UNPAID'
    //             || strtoupper(trim($result->order_status ?? '')) === 'UNPAID';

    //         if ($isArMode && $hasPaymentLog) {
    //             $isUnpaid = $arBalance > 0;
    //         }

    //         $isNullModeAr = $isUnpaid && $paymentTypeRaw === '';
    //         $isArFlag = isset($result->ar_collection) && intval($result->ar_collection) === 1;
    //         $isArRow = $isArMode
    //             ? ($hasPaymentLog ? ($arBalance > 0) : $isUnpaid)
    //             : ($isUnpaid && ($isNullModeAr || $isArFlag));

    //         $reportType = $isArFlag ? 'ar' : 'sales';

    //         if ($reportType === 'sales') {
    //             $arDisplayAmount = $arBalance;
    //             $mopDisplayAmount = $totalPaidAmount;
    //         } else {
    //             $arDisplayAmount = $isArRow ? $arBalance : 0;
    //             $mopDisplayAmount = $paidAmountFromLog;
    //         }

    //         $paymentBucket = null;
    //         if (count($paymentLogsByBucket) === 1) {
    //             $paymentBucket = array_key_first($paymentLogsByBucket);
    //         } elseif (count($paymentLogsByBucket) > 1) {
    //             $paymentBucket = 'MULTIPLE';
    //         }

    //         foreach (array_keys($paymentLogsByBucket) as $bucket) {
    //             if ($bucket !== null && !in_array($bucket, $dailySRPaymentColumns, true)) {
    //                 $dailySRPaymentColumns[] = $bucket;
    //             }
    //         }

    //         $statusCode = strtoupper(trim($result->status ?? ''));
    //         $orderStatusNormalized = strtoupper(trim($result->order_status ?? ''));
    //         if ($statusCode === 'UNPAID' && $orderStatusNormalized === 'CANCELLED') {
    //             continue;
    //         }

    //         if (!empty($paymentLogsByBucket)) {
    //             foreach ($paymentLogsByBucket as $bucket => $amount) {
    //                 if ($bucket === null) {
    //                     continue;
    //                 }
    //                 if ($reportType === 'sales') {
    //                     $dailyPaymentBreakdown[$bucket] = ($dailyPaymentBreakdown[$bucket] ?? 0) + $amount;
    //                 }
    //                 if ($reportType === 'ar') {
    //                     $arPaymentBreakdown[$bucket] = ($arPaymentBreakdown[$bucket] ?? 0) + $amount;
    //                 }
    //             }
    //         }

            


    //         $highlightArInSales = ($reportType === 'sales' && $arDisplayAmount > 0);
    //         $form2307 = $result->form_2307 ?? '';
    //         $remarks = $result->remarks ?? $result->status ?? '';

    //         $dailySRRows[] = (object) [
    //             'customer_name' => $customerName,
    //             'number' => $result->number,
    //             'row_amount' => $rowAmount,
    //             'payment_bucket' => $paymentBucket,
    //             'payments' => $paymentLogsByBucket,
    //             'mop_display_amount' => $mopDisplayAmount,
    //             'ar_display_amount' => $arDisplayAmount,
    //             'report_type' => $reportType,
    //             'form2307' => $form2307,
    //             'remarks' => $remarks,
    //             'highlight_ar_in_sales' => $highlightArInSales,
    //             'status' => strtoupper(trim($result->status ?? '')),
    //             'order_status' => strtoupper(trim($result->order_status ?? '')),
    //         ];

    //         if ($reportType === 'sales') {   /* //DITO ALNG PAPALITAN */
    //             $dailyTotals['amount'] += $rowAmount;
    //             $dailyTotals['ar'] += $arDisplayAmount;
    //             if ($paymentBucket !== null) {
    //                 $dailyPaymentBreakdown[$paymentBucket] = ($dailyPaymentBreakdown[$paymentBucket] ?? 0); // ensure key exists
    //             }
    //         }

    //         if ($reportType === 'ar') {
    //             $arTotals['amount'] += $rowAmount;
    //             $arTotals['ar'] += $arDisplayAmount;
    //             if ($paymentBucket !== null) {
    //                 $arPaymentBreakdown[$paymentBucket] = ($arPaymentBreakdown[$paymentBucket] ?? 0); // ensure key exists
    //             }
    //         }
    //     }

    //     $preferredDailySROrder = ['CASH', 'GCASH', 'MAYA', 'BANK TRANSFER', 'CHECK'];
    //     $orderedDailySRPaymentColumns = [];
    //     foreach ($preferredDailySROrder as $preferredColumn) {
    //         if (in_array($preferredColumn, $dailySRPaymentColumns, true)) {
    //             $orderedDailySRPaymentColumns[] = $preferredColumn;
    //         }
    //     }
    //     foreach ($dailySRPaymentColumns as $paymentColumn) {
    //         if (!in_array($paymentColumn, $orderedDailySRPaymentColumns, true)) {
    //             $orderedDailySRPaymentColumns[] = $paymentColumn;
    //         }
    //     }
    //     $dailySRPaymentColumns = $orderedDailySRPaymentColumns;

    //     $dailyTotals['payments'] = array_fill_keys($dailySRPaymentColumns, 0);
    //     $arTotals['payments'] = array_fill_keys($dailySRPaymentColumns, 0);

    //     foreach ($dailyPaymentBreakdown as $paymentColumn => $amount) {
    //         if (isset($dailyTotals['payments'][$paymentColumn])) {
    //             $dailyTotals['payments'][$paymentColumn] = $amount;
    //         }
    //     }
    //     foreach ($arPaymentBreakdown as $paymentColumn => $amount) {
    //         if (isset($arTotals['payments'][$paymentColumn])) {
    //             $arTotals['payments'][$paymentColumn] = $amount;
    //         }
    //     }

    //     return [
    //         'dailySRRows' => $dailySRRows,
    //         'dailySRPaymentColumns' => $dailySRPaymentColumns,
    //         'dailyTotals' => $dailyTotals,
    //         'arTotals' => $arTotals,
    //     ];
    // }
    
    //ken UPDATED method used in DAILY SALES REPORT ken 252306001  JULY 2, 2026 - SIMPLIFIED WITH AGGREGATED SQL
    private function buildDailySRPaymentData($results): array
    {
        $dailySRPaymentColumns = ['CASH'];
        $dailySRRows = [];
        $dailyTotals = ['amount' => 0, 'ar' => 0, 'service_charge' => 0, 'payments' => []];
        $arTotals = ['amount' => 0, 'ar' => 0, 'service_charge' => 0, 'payments' => []];
        $dailyPaymentBreakdown = [];
        $arPaymentBreakdown = [];

        $transactionIds = [];
        $tableIds = [];
        foreach ($results as $row) {
            $tranId = $row->trans_id ?? $row->tran_id ?? $row->id ?? null;
            if (!is_null($tranId) && $tranId !== '') {
                $transactionIds[] = $tranId;
            }
            $tableId = $row->table ?? $row->table_id ?? null;
            if (!is_null($tableId) && $tableId !== '') {
                $tableIds[] = $tableId;
            }
        }
        $transactionIds = array_unique($transactionIds);
        $tableIds = array_unique($tableIds);

        $tableNames = [];
        if (!empty($tableIds)) {
            $tableNames = DB::table('tables')->whereIn('id', $tableIds)->pluck('name', 'id')->toArray();
        }

        $paymentsByTransaction = [];
        if (!empty($transactionIds)) {
            $paymentLogs = DB::table('payment_logs as pl')
                ->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                ->select('pl.tran_id', 'pl.label', 'pl.amount', 'pl.balance', 'pl.type', 'mop.name as mop_name')
                ->where('pl.is_deleted', 0)
                ->whereIn('pl.tran_id', $transactionIds)
                ->orderBy('pl.id', 'desc')
                ->get();

            foreach ($paymentLogs as $log) {
                if (!isset($paymentsByTransaction[$log->tran_id])) {
                    $paymentsByTransaction[$log->tran_id] = [];
                }
                $paymentsByTransaction[$log->tran_id][] = $log;
            }
        }

        foreach ($results as $result) {
            $rowAmount = floatval($result->total ?? 0);
            $tranId = $result->trans_id ?? $result->tran_id ?? $result->id ?? null;
            $paymentLogs = $paymentsByTransaction[$tranId] ?? [];

            $paymentTypeRaw = trim($result->mode_of_payment ?? '');
            $paymentType = $this->normalizePaymentType($paymentTypeRaw);
            $isArMode = in_array($paymentType, ['AR', 'A/R'], true);
            $isMultipleMop = strtoupper($paymentTypeRaw) === 'MULTIPLE';

            $paymentLogsByBucket = [];
            $totalPaidAmount = 0.0;
            $hasArPaymentLog = false;
            $maxBalance = 0;

            foreach ($paymentLogs as $log) {
                if (intval($log->type ?? 0) === 1) {
                    $hasArPaymentLog = true;
                }
                $maxBalance = max($maxBalance, floatval($log->balance ?? 0));

                $logLabel = trim($log->label ?? '');
                $bucket = null;

                if ($isMultipleMop && !empty($log->mop_name)) {
                    $bucket = $this->computePaymentBucket(
                        $this->normalizePaymentType($log->mop_name),
                        $log->mop_name,
                        false,
                        true,
                        false
                    );
                }

                if ($bucket === null) {
                    $bucket = $this->computePaymentBucket(
                        $this->normalizePaymentType($logLabel),
                        $logLabel,
                        false,
                        true,
                        false
                    );
                }

                if ($isMultipleMop && strtoupper($bucket ?? '') === 'MULTIPLE') {
                    $bucket = 'CASH';
                }

                $bucket = $bucket ?? 'CASH';
                $amount = floatval($log->amount ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                $paymentLogsByBucket[$bucket] = ($paymentLogsByBucket[$bucket] ?? 0) + $amount;
                $totalPaidAmount += $amount;
            }

            if (empty($paymentLogsByBucket) && !$isArMode) {
                $isUnpaid = (isset($result->payment_class) && intval($result->payment_class) === 0)
                    || (isset($result->is_paid) && intval($result->is_paid) === 0)
                    || strtoupper(trim($result->status ?? '')) === 'UNPAID'
                    || strtoupper(trim($result->order_status ?? '')) === 'UNPAID';

                if ($paymentTypeRaw === '' && $isUnpaid) {
                    $totalPaidAmount = 0;
                } else {
                    $bucket = $isMultipleMop ? 'CASH' : ((!is_null($paymentType) && $paymentType !== '') ? $paymentType : 'CASH');
                    $paymentLogsByBucket[$bucket] = $rowAmount;
                    $totalPaidAmount = $rowAmount;
                }
            }

            if (!$isArMode && $totalPaidAmount > $rowAmount) {
                $overpayment = $totalPaidAmount - $rowAmount;
                foreach (array_reverse(array_keys($paymentLogsByBucket), true) as $bucketKey) {
                    if ($overpayment <= 0) break;
                    $bucketAmount = $paymentLogsByBucket[$bucketKey];
                    if ($bucketAmount <= $overpayment) {
                        unset($paymentLogsByBucket[$bucketKey]);
                        $overpayment -= $bucketAmount;
                    } else {
                        $paymentLogsByBucket[$bucketKey] = $bucketAmount - $overpayment;
                        $overpayment = 0;
                    }
                }
                $totalPaidAmount = $rowAmount;
            }

            $arBalance = max(0, $rowAmount - $totalPaidAmount);
            if ($maxBalance > 0) {
                $arBalance = max($arBalance, $maxBalance);
            }

            if (!empty($paymentLogs) && !$hasArPaymentLog) {
                $isArMode = false;
            }

            if ($hasArPaymentLog && !empty($paymentLogs)) {
                $paymentTypeRaw = trim($paymentLogs[0]->label ?? '');
            }

            $paymentType = $this->normalizePaymentType($paymentTypeRaw);
            $isUnpaid = (isset($result->payment_class) && intval($result->payment_class) === 0)
                || (isset($result->is_paid) && intval($result->is_paid) === 0)
                || strtoupper(trim($result->status ?? '')) === 'UNPAID'
                || strtoupper(trim($result->order_status ?? '')) === 'UNPAID';

            if ($isArMode && !empty($paymentLogs)) {
                $isUnpaid = $arBalance > 0;
            }

            $isNullModeAr = $isUnpaid && $paymentTypeRaw === '';
            $isArFlag = $hasArPaymentLog;
            $isArRow = $isArFlag ? (!empty($paymentLogs) ? ($arBalance > 0) : $isUnpaid) : ($isUnpaid && $isNullModeAr);

            $reportType = $isArFlag ? 'ar' : 'sales';

            if ($reportType === 'sales') {
                $arDisplayAmount = $arBalance;
                $mopDisplayAmount = $totalPaidAmount;
            } else {
                $arDisplayAmount = $isArRow ? $arBalance : 0;
                $mopDisplayAmount = $totalPaidAmount;
            }

            $paymentBucket = null;
            if (count($paymentLogsByBucket) === 1) {
                $paymentBucket = array_key_first($paymentLogsByBucket);
            } elseif (count($paymentLogsByBucket) > 1) {
                $paymentBucket = 'MULTIPLE';
            }

            foreach (array_keys($paymentLogsByBucket) as $bucket) {
                if ($bucket !== null && !in_array($bucket, $dailySRPaymentColumns, true)) {
                    $dailySRPaymentColumns[] = $bucket;
                }
            }

            $statusCode = strtoupper(trim($result->status ?? ''));
            $orderStatusNormalized = strtoupper(trim($result->order_status ?? ''));
            if ($statusCode === 'UNPAID' && $orderStatusNormalized === 'CANCELLED') {
                continue;
            }

            foreach ($paymentLogsByBucket as $bucket => $amount) {
                if ($bucket !== null) {
                    if ($reportType === 'sales') {
                        $dailyPaymentBreakdown[$bucket] = ($dailyPaymentBreakdown[$bucket] ?? 0) + $amount;
                    }
                    if ($reportType === 'ar') {
                        $arPaymentBreakdown[$bucket] = ($arPaymentBreakdown[$bucket] ?? 0) + $amount;
                    }
                }
            }

            $tableId = $result->table ?? $result->table_id ?? null;
            $customerName = 'Walk-in';
            if (!is_null($tableId) && $tableId !== '') {
                $customerName = trim($tableNames[$tableId] ?? $customerName);
            }
            if (empty($customerName)) {
                $customerName = trim($result->payor_name ?? $result->payor_number ?? 'Walk-in');
            }

            $serviceCharge = floatval($result->service_charge ?? 0);
            $form2307 = $result->form_2307 ?? $result->form2307 ?? '';

            $dailySRRows[] = (object) [
                'customer_name' => $customerName,
                'number' => $result->number,
                'display_number' => $result->number,
                'row_amount' => $rowAmount,
                'service_charge' => $serviceCharge,
                'payment_bucket' => $paymentBucket,
                'payments' => $paymentLogsByBucket,
                'mop_display_amount' => $mopDisplayAmount,
                'ar_display_amount' => $arDisplayAmount,
                'report_type' => $reportType,
                'form2307' => $form2307,
                'remarks' => $result->remarks ?? $result->status ?? '',
                'highlight_ar_in_sales' => ($reportType === 'sales' && $arDisplayAmount > 0),
                'status' => strtoupper(trim($result->status ?? '')),
                'order_status' => strtoupper(trim($result->order_status ?? '')),
            ];

            if ($reportType === 'sales') {
                $dailyTotals['amount'] += $rowAmount;
                $dailyTotals['ar'] += $arDisplayAmount;
                $dailyTotals['service_charge'] += $serviceCharge;
                if ($paymentBucket !== null) {
                    $dailyPaymentBreakdown[$paymentBucket] = ($dailyPaymentBreakdown[$paymentBucket] ?? 0);
                }
            }

            if ($reportType === 'ar') {
                $arTotals['amount'] += $rowAmount;
                $arTotals['ar'] += $arDisplayAmount;
                $arTotals['service_charge'] += $serviceCharge;
                if ($paymentBucket !== null) {
                    $arPaymentBreakdown[$paymentBucket] = ($arPaymentBreakdown[$paymentBucket] ?? 0);
                }
            }
        }

        $preferredDailySROrder = ['CASH', 'GCASH', 'MAYA', 'BANK TRANSFER', 'CHECK'];
        $orderedDailySRPaymentColumns = [];
        foreach ($preferredDailySROrder as $preferredColumn) {
            if (in_array($preferredColumn, $dailySRPaymentColumns, true)) {
                $orderedDailySRPaymentColumns[] = $preferredColumn;
            }
        }
        foreach ($dailySRPaymentColumns as $paymentColumn) {
            if (!in_array($paymentColumn, $orderedDailySRPaymentColumns, true)) {
                $orderedDailySRPaymentColumns[] = $paymentColumn;
            }
        }
        $dailySRPaymentColumns = array_values(array_diff($orderedDailySRPaymentColumns, ['MULTIPLE']));

        $dailyTotals['payments'] = array_fill_keys($dailySRPaymentColumns, 0);
        $arTotals['payments'] = array_fill_keys($dailySRPaymentColumns, 0);

        foreach ($dailyPaymentBreakdown as $paymentColumn => $amount) {
            if (isset($dailyTotals['payments'][$paymentColumn])) {
                $dailyTotals['payments'][$paymentColumn] = $amount;
            }
        }
        foreach ($arPaymentBreakdown as $paymentColumn => $amount) {
            if (isset($arTotals['payments'][$paymentColumn])) {
                $arTotals['payments'][$paymentColumn] = $amount;
            }
        }

        return [
            'dailySRRows' => $dailySRRows,
            'dailySRPaymentColumns' => $dailySRPaymentColumns,
            'dailyTotals' => $dailyTotals,
            'arTotals' => $arTotals,
        ];
    }

    private function buildDailySROnlineData($results): array
    {
        $rows = [];
        $totalAmount = 0;

        foreach ($results as $result) {
            $paymentTypeRaw = trim($result->mode_of_payment ?? '');
            $normalizedPaymentType = strtoupper(preg_replace('/\s*\/\s*/', ' / ', $paymentTypeRaw));
            $isOnline = preg_match('/\b(GCASH|MAYA|PAYMAYA|BANK TRANSFER|ONLINE|MOBILE|FBPAY|DEBIT|VISA|MASTERCARD|CREDIT CARD|BANK|BANCNET|E-?WALLET|GRAB\s*PAY|BDO|PAYMAYA QR)\b/i', $normalizedPaymentType);

            if (!$isOnline) {
                continue;
            }

            $customerName = trim($result->payor_name ?? $result->payor_number ?? 'Walk-in');
            $rowAmount = floatval($result->total ?? 0);

            $rows[] = (object) [
                'ref' => $result->number ?? $result->tran_number ?? '',
                'payment_method' => $paymentTypeRaw,
                'customer_name' => $customerName,
                'amount' => $rowAmount,
            ];

            $totalAmount += $rowAmount;
        }

        return [
            'rows' => $rows,
            'totalAmount' => $totalAmount,
        ];
    }

    private function buildDailySRCheckData($results): array
    {
        $rows = [];
        $totalAmount = 0;

        foreach ($results as $result) {
            $paymentTypeRaw = trim($result->mode_of_payment ?? '');
            $paymentType = strtoupper(preg_replace('/\s*\/\s*/', ' / ', $paymentTypeRaw));
            $isCheckPayment = preg_match('/\b(CHECK|CHEQUE)\b/i', $paymentType);

            if (!$isCheckPayment) {
                continue;
            }

            $customerName = trim($result->payor_name ?? $result->payor_number ?? 'Walk-in');
            $rowAmount = floatval($result->total ?? 0);
            $chequeNo = $result->cheque_no ?? $result->check_no ?? $result->number ?? '';
            $bankName = $result->bank_name ?? $result->bank ?? $paymentTypeRaw;
            $chequeDateRaw = $result->cheque_date ?? $result->check_date ?? ($result->created_at ?? '');
            $chequeDate = $chequeDateRaw ? date('M d, Y', strtotime($chequeDateRaw)) : '';

            $rows[] = (object) [
                'cheque_no' => $chequeNo,
                'bank_name' => $bankName,
                'cheque_date' => $chequeDate,
                'customer_name' => $customerName,
                'amount' => $rowAmount,
            ];

            $totalAmount += $rowAmount;
        }

        return [
            'rows' => $rows,
            'totalAmount' => $totalAmount,
        ];
    }
    // HANGANG DITO LANG ANG NA UPDATE SA METHOD NA TO. JULY 2, 2026

    private function buildBalanceSheetData(string $startDate, string $endDate, $userBranch, array $branchCodes = []): array
    {
        $cash = 0;
        if (Schema::hasTable('transactions')) {
            $cashQuery = DB::table('transactions')
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->whereRaw('LOWER(mode_of_payment) = ?', ['cash'])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate);

            if ($userBranch) {
                $cashQuery->where('branch_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $cashQuery->whereIn('branch_code', $branchCodes);
            }

            $cash = $cashQuery->sum('total');
        }

        $receivables = 0;
        if (Schema::hasTable('account_receivables')) {
            $receivablesQuery = DB::table('account_receivables')
                ->where('balance', '>', 0)
                ->where('tran_date', '>=', $startDate)
                ->where('tran_date', '<', $endDate);

            if ($userBranch) {
                $receivablesQuery->where('branch_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $receivablesQuery->whereIn('branch_code', $branchCodes);
            }

            $receivables = $receivablesQuery->sum('balance');
        }

        $inventories = 0;
        if (Schema::hasTable('inventories')) {
            $inventoriesQuery = DB::table('inventories')->select(DB::raw('SUM(quantity * price) as total'));
            $inventories = (float) $inventoriesQuery->value('total');
        }

        $prepayments = 0;
        if (Schema::hasTable('prepayments')) {
            $prepaymentsQuery = DB::table('prepayments')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate);

            if ($userBranch && Schema::hasColumn('prepayments', 'branch_code')) {
                $prepaymentsQuery->where('branch_code', $userBranch);
            } elseif (!empty($branchCodes) && Schema::hasColumn('prepayments', 'branch_code')) {
                $prepaymentsQuery->whereIn('branch_code', $branchCodes);
            }

            $prepayments = $prepaymentsQuery->sum('amount');
        }

        $propertyAndEquipment = 0;
        if (Schema::hasTable('assets')) {
            $propertyAndEquipmentQuery = DB::table('assets')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate);

            if ($userBranch && Schema::hasColumn('assets', 'branch_code')) {
                $propertyAndEquipmentQuery->where('branch_code', $userBranch);
            } elseif (!empty($branchCodes) && Schema::hasColumn('assets', 'branch_code')) {
                $propertyAndEquipmentQuery->whereIn('branch_code', $branchCodes);
            }

            $propertyAndEquipment = (float) $propertyAndEquipmentQuery->select(DB::raw('SUM(acquisition_cost - accumulated_depreciation) as total'))->value('total');
        }

        $trade_payables = 0;
        if (Schema::hasTable('inventory_transactions')) {
            $tradePayablesQuery = DB::table('inventory_transactions')
                ->where('is_paid', 0)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate);

            /* if ($userBranch) {
                $tradePayablesQuery->where('branch', $userBranch);
            } elseif (!empty($branchCodes)) {
                $tradePayablesQuery->whereIn('branch', $branchCodes);
            } */

            $trade_payables = $tradePayablesQuery->sum('amount');
        }

        $loans_payable = 0;
        $capital = 0;
        $totalCurrentAssets = $cash + $receivables + $inventories + $prepayments;
        $totalAssets = $totalCurrentAssets + $propertyAndEquipment;
        $totalLiabilities = $trade_payables + $loans_payable;
        $totalLiabilitiesEquity = $totalLiabilities + $capital;

        return [
            'cash' => $cash,
            'receivables' => $receivables,
            'inventories' => $inventories,
            'prepayments' => $prepayments,
            'propertyAndEquipment' => $propertyAndEquipment,
            'trade_payables' => $trade_payables,
            'loans_payable' => $loans_payable,
            'capital' => $capital,
            'totalCurrentAssets' => $totalCurrentAssets,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalLiabilitiesEquity' => $totalLiabilitiesEquity,
        ];
    }
    /* END OF METHODS FOR BALANCE SHEET AND INCOME STATEMENT 4-25-2026 KEN 252306001 */

    public function generate(Request $request) {
        $start = $request->start;
        $end = $request->end;
        
        $startDate = date('Y-m-d H:i:s', strtotime($start));
        $endDate = date('Y-m-d H:i:s', strtotime($end));

        $category = $request->category;
        $report = $request->report;
        $grandTotals = [];
        $settings = DB::table('settings')->where('id', 1)->first();
        $chartData = null;
        //ken update branch query 8-26-2026
        $branchs = DB::table('settings')
            ->orderBy('name', 'asc')
            ->where('is_deleted', 0)
            ->get();

        session([
            'report_filters' => [
                'category' => $request->category,
                'report' => $request->report,
                'start' => $request->start,
                'end' => $request->end,
                'branch' => $request->branch,
            ]
        ]);     
        // end of ken update with session 8-26-2026
        
        // Initialize resultsCount to prevent undefined variable error
        $resultsCount = 0;
        // Get current user and their branch for filtering
        $user = Auth::user();
        
        // Get selected branch from form (only for admin)
        $selectedBranch = null;
        $userBranch = null;

        //ken start update aug 28-2026
        if ($user->role == 1) {
            // Admin user: accept explicit branch selection only if it belongs to the admin's store
            $selectedBranch = $request->input('branch');
            if (!$selectedBranch) {
                abort(422, 'Please select a branch.');
            }

            if ($selectedBranch) {
                // Validate the selected branch against the settings table.
                $branchExists = DB::table('settings')
                    ->where('store_code', $selectedBranch)
                    ->where('is_deleted', 0)
                    ->exists();

                if ($branchExists) {
                    $userBranch = $selectedBranch;
                } else {
                    abort(404, 'Selected branch was not found.');
                }
            } else {
                // If 'all' is selected, filter by the user's store_code
                $userBranch = $user->store_code;
            }
        } else {
            // Non-admin user - use their store_code
            $userBranch = $user->store_code;
            $selectedBranch = $userBranch;
        }
        // END OF KEN UPDATE 8-28-2026

        // Also restrict by store_code: only include branches belonging to the user's store
        $storeCode = $user->store_code ?? null;
        $branchCodes = [];
        if ($storeCode) {
            // Branch records for this app are stored in the `settings` table.
            // Fetch branch codes for this store from `settings` where store_code matches.
            $branchCodes = DB::table('settings')
                ->where('store_code', $storeCode)
                ->where('is_deleted', 0)
                ->pluck('branch_code')
                ->toArray();
        }
        
        $selectedBranchName = optional($branchs->firstWhere('store_code', $selectedBranch))->name;  // KEN slectedbranhc define UPDATE 8-28-2026
        
         // ken added start here 20250623001 - ken - added 4-19-2025 dailysalereport and Accounting report (income statement) 4-25-2026 ken 252306001
        if ($category == 'dailySR') {
            $userBranch = $user->store_code ?? null;

            // ===== 1. GET MOP COLUMNS =====
            $mopColumn = Schema::hasTable('mode_of_payment')
                ? (Schema::hasColumn('mode_of_payment', 'mop_name') ? 'mop_name' : 'name')
                : null;

            $dailySRPaymentColumns = [];
            if ($userBranch && $mopColumn) {
                $dailySRPaymentColumns = DB::table('mode_of_payment')
                    ->where('store_code', $userBranch)
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'asc')
                    ->pluck($mopColumn)
                    ->map(function ($value) {
                        return strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) $value)));
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }

            if (empty($dailySRPaymentColumns)) {
                $dailySRPaymentColumns = ['CASH'];
            }

            // ===== 2. PAYMENT SUMMARY QUERY =====

            $paymentSummaryQuery = DB::table('payment_logs as pl')
                ->select('pl.tran_number', DB::raw('SUM(pl.amount) as paid_amount'))
                ->where('pl.store_code', $userBranch)
                ->where('pl.created_at', '>=', $startDate)
                ->where('pl.created_at', '<', $endDate);

            // Join with mode_of_payment to get the payment method name
            if (Schema::hasColumn('payment_logs', 'mop_id') && Schema::hasTable('mode_of_payment')) {
                $paymentSummaryQuery->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                    ->addSelect(DB::raw('UPPER(TRIM(COALESCE(mop.name, pl.label, "CASH"))) as mop'))
                    ->groupBy('pl.tran_number', DB::raw('UPPER(TRIM(COALESCE(mop.name, pl.label, "CASH")))'));
            } elseif (Schema::hasColumn('payment_logs', 'mop')) {
                $paymentSummaryQuery->addSelect(DB::raw('UPPER(TRIM(pl.mop)) as mop'))
                    ->groupBy('pl.tran_number', DB::raw('UPPER(TRIM(pl.mop))'));
            } elseif (Schema::hasColumn('payment_logs', 'label')) {
                $paymentSummaryQuery->addSelect(DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH"))) as mop'))
                    ->groupBy('pl.tran_number', DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH")))'));
            } else {
                $paymentSummaryQuery->addSelect(DB::raw('"CASH" as mop'))
                    ->groupBy('pl.tran_number');
            }

            $paymentSummaryRows = $paymentSummaryQuery->get()->groupBy('tran_number');
            

            // Log payment details for each transaction
            foreach ($paymentSummaryRows as $tranNumber => $payments) {
                foreach ($payments as $payment) {
                }
            }

            // ===== 3. MAIN SALES QUERY =====
            // Get ALL transactions for the date range

            $salesQuery = DB::table('transactions as t')
                ->select(
                    't.id',
                    't.number as ref',
                    // Try to get customer name from multiple sources
                    DB::raw("
                        COALESCE(
                            t.payor_name, 
                            t.payor_number, 
                            tables.name,
                            CONCAT('Table ', t.table),
                            'Walk-in'
                        ) as customer_name
                    "),
                    DB::raw('COALESCE(t.total, 0) as amount'),
                    DB::raw('COALESCE(t.service_charge, 0) as service_charge'),
                    't.status',
                    't.mode_of_payment',
                    't.order_status',
                    't.branch_code',
                    't.branch',
                    't.total as total_amount',
                    't.table',
                    'tables.name as table_name', // Get the table name
                    DB::raw('"SALES" as report_type')
                )
                // Join with tables table to get table name
                ->leftJoin('tables', 't.table', '=', 'tables.id')
                ->leftJoinSub(
                    DB::table('payment_logs as pl')
                        ->select(
                            'pl.tran_number', 
                            DB::raw('SUM(pl.amount) as paid_amount')
                        )
                        // Join with mode_of_payment inside the subquery
                        ->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                        ->where('pl.store_code', $userBranch)
                        ->where('pl.created_at', '>=', $startDate)
                        ->where('pl.created_at', '<', $endDate)
                        ->groupBy('pl.tran_number'),
                    'payment_totals',
                    function ($join) {
                        $join->on('payment_totals.tran_number', '=', 't.number');
                    }
                )
                ->selectRaw('COALESCE(payment_totals.paid_amount, 0) as paid_amount')
                ->selectRaw('GREATEST(COALESCE(t.total, 0) + COALESCE(t.service_charge, 0) - COALESCE(payment_totals.paid_amount, 0), 0) as ar_balance')
                ->selectRaw('CASE WHEN GREATEST(COALESCE(t.total, 0) + COALESCE(t.service_charge, 0) - COALESCE(payment_totals.paid_amount, 0), 0) > 0 THEN 1 ELSE 0 END as highlight_ar_in_sales')
                ->where('t.created_at', '>=', $startDate)
                ->where('t.created_at', '<', $endDate)
                ->where('t.order_status', '!=', 'CANCELLED')
                ->where('t.store_code', $userBranch);

            // Apply store filtering - filter by store_code instead of branch_code
            \Log::debug('Store filtering:', [
                'user_store_code' => $userBranch,
            ]);
            
            if ($userBranch) {
                $salesQuery->where('t.store_code', $userBranch);
            }


            $dailySRRows = $salesQuery->orderBy('t.id', 'desc')->get();
            

            // Log sample customer names
            // ===== 4. PROCESS RESULTS =====
            $dailySRRows = $dailySRRows->map(function ($row) use ($paymentSummaryRows, $dailySRPaymentColumns) {

                // Basic fields
                $row->display_number = $row->ref;
                $row->row_amount = (float) ($row->amount ?? 0);
                $row->ar_display_amount = (float) ($row->ar_balance ?? 0);
                $row->form2307 = (float) ($row->service_charge ?? 0);
                $row->status = $row->status ?? '';
                
                // Determine if this is an AR transaction (unpaid or partially paid)
                $row->is_ar_transaction = ($row->order_status === 'UNPAID' || (float)$row->ar_balance > 0);
                
                // Initialize payments array
                $row->payments = array_fill_keys($dailySRPaymentColumns, 0);
                
                // Get payments using the transaction number (ref)
                $rowPayments = $paymentSummaryRows[$row->ref] ?? collect();
                
       
                // Add payments to the breakdown
                foreach ($rowPayments as $paymentRow) {
                    $paymentColumn = strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) ($paymentRow->mop ?? ''))));
                    if ($paymentColumn === '') {
                        $paymentColumn = 'CASH';
                    }


                    if (!array_key_exists($paymentColumn, $row->payments)) {
                        $row->payments[$paymentColumn] = 0;
                    }

                    $row->payments[$paymentColumn] += (float) ($paymentRow->paid_amount ?? 0);
                }

                return $row;
            });

            // ===== 5. ADD MISSING PAYMENT COLUMNS =====
            foreach ($dailySRRows as $row) {
                foreach (array_keys($row->payments ?? []) as $paymentColumn) {
                    if (!in_array($paymentColumn, $dailySRPaymentColumns, true)) {
                        $dailySRPaymentColumns[] = $paymentColumn;
                    }
                }
            }

            // Order payment columns
            $preferredOrder = ['CASH', 'GCASH', 'MAYA', 'BANK TRANSFER', 'CHECK', 'ONLINE BANKING'];
            $orderedColumns = [];
            foreach ($preferredOrder as $preferredColumn) {
                if (in_array($preferredColumn, $dailySRPaymentColumns, true)) {
                    $orderedColumns[] = $preferredColumn;
                }
            }
            foreach ($dailySRPaymentColumns as $paymentColumn) {
                if (!in_array($paymentColumn, $orderedColumns, true)) {
                    $orderedColumns[] = $paymentColumn;
                }
            }
            $dailySRPaymentColumns = array_values(array_diff($orderedColumns, ['MULTIPLE']));
            
 
            // ===== 6. CALCULATE TOTALS =====
            $dailyTotals = [
                'amount' => (float) $dailySRRows->sum('amount'),
                'service_charge' => (float) $dailySRRows->sum('service_charge'),
                'ar' => (float) $dailySRRows->sum('ar_balance'),
                'payments' => [],
            ];

            foreach ($dailySRPaymentColumns as $paymentColumn) {
                $dailyTotals['payments'][$paymentColumn] = (float) $dailySRRows->sum(function ($row) use ($paymentColumn) {
                    return (float) ($row->payments[$paymentColumn] ?? 0);
                });
            }


            // ===== 7. AR COLLECTION =====
            // Get AR collections from payment_logs where type=1 (AR payments)

            $arCollectionQuery = DB::table('payment_logs as pl')
                ->select(
                    'pl.id as payment_id',
                    'pl.tran_number',
                    'pl.amount as payment_amount',
                    'pl.created_at as payment_date',
                    't.id as transaction_id',
                    't.number as ref',
                    't.total as amount',
                    't.service_charge',
                    't.status',
                    't.order_status',
                    't.branch_code',
                    't.branch',
                    DB::raw("
                        COALESCE(
                            t.payor_name, 
                            t.payor_number, 
                            tables.name,
                            CONCAT('Table ', t.table),
                            'Walk-in'
                        ) as customer_name
                    "),
                    't.table',
                    'tables.name as table_name',
                    DB::raw('"AR_COLLECTION" as report_type')
                )
                ->leftJoin('transactions as t', function($join) use ($userBranch) {
                    $join->on('pl.tran_number', '=', 't.number')
                        ->where('t.store_code', $userBranch);
                })
                ->leftJoin('tables', 't.table', '=', 'tables.id')
                ->where('pl.store_code', $userBranch)
                ->where('pl.created_at', '>=', $startDate)
                ->where('pl.created_at', '<', $endDate)
                ->where('pl.type', 1); // AR collection payments

            // Apply store filtering - filter by store_code instead of branch_code
            if ($userBranch) {
                $arCollectionQuery->where('t.store_code', $userBranch);
            }


            $arCollectionRows = $arCollectionQuery
                ->orderBy('pl.created_at', 'desc')
                ->get();
            

            // Process AR Collection rows
            $arCollectionRows = $arCollectionRows->map(function ($row) use ($dailySRPaymentColumns) {
                
                // Calculate total amount including service charge
                $totalAmount = (float) ($row->amount ?? 0) + (float) ($row->service_charge ?? 0);
                
                // Get the AR payment amount
                $arPaymentAmount = (float) ($row->payment_amount ?? 0);
                
                // Calculate remaining AR balance (total - AR payment)
                $arBalance = max(0, $totalAmount - $arPaymentAmount);
                
                // Basic fields
                $row->display_number = $row->ref ?? $row->tran_number;
                $row->row_amount = $totalAmount;
                $row->ar_display_amount = $arBalance;
                $row->paid_amount = $arPaymentAmount;
                $row->form2307 = (float) ($row->service_charge ?? 0);
                $row->is_ar_transaction = true;
                $row->status = $row->status ?? '';

                // Get payment method details for this specific AR payment
                $paymentMethod = DB::table('payment_logs as pl')
                    ->select(DB::raw('UPPER(TRIM(COALESCE(mop.name, pl.label, "CASH"))) as mop'))
                    ->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                    ->where('pl.id', $row->payment_id)
                    ->first();

                $paymentColumn = strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) ($paymentMethod->mop ?? 'CASH'))));
                if ($paymentColumn === '') {
                    $paymentColumn = 'CASH';
                }


                // Initialize payments array
                $row->payments = array_fill_keys($dailySRPaymentColumns, 0);

                // Add the AR payment to the appropriate column
                if (!array_key_exists($paymentColumn, $row->payments)) {
                    $row->payments[$paymentColumn] = 0;
                }

                $row->payments[$paymentColumn] = $arPaymentAmount;

      
                return $row;
            });

            // Add missing payment columns from AR collections
            foreach ($arCollectionRows as $row) {
                foreach (array_keys($row->payments ?? []) as $paymentColumn) {
                    if (!in_array($paymentColumn, $dailySRPaymentColumns, true)) {
                        $dailySRPaymentColumns[] = $paymentColumn;
                    }
                }
            }

            // Re-order payment columns after adding AR columns
            $preferredOrder = ['CASH', 'GCASH', 'MAYA', 'BANK TRANSFER', 'CHECK', 'ONLINE BANKING'];
            $orderedColumns = [];
            foreach ($preferredOrder as $preferredColumn) {
                if (in_array($preferredColumn, $dailySRPaymentColumns, true)) {
                    $orderedColumns[] = $preferredColumn;
                }
            }
            foreach ($dailySRPaymentColumns as $paymentColumn) {
                if (!in_array($paymentColumn, $orderedColumns, true)) {
                    $orderedColumns[] = $paymentColumn;
                }
            }
            $dailySRPaymentColumns = array_values(array_diff($orderedColumns, ['MULTIPLE']));

            // Calculate AR Collection Totals
            $arTotals = [
                'amount' => (float) $arCollectionRows->sum('row_amount'),
                'service_charge' => (float) $arCollectionRows->sum('form2307'),
                'ar' => (float) $arCollectionRows->sum('ar_display_amount'),
                'payments' => [],
            ];

            foreach ($dailySRPaymentColumns as $paymentColumn) {
                $arTotals['payments'][$paymentColumn] = (float) $arCollectionRows->sum(function ($row) use ($paymentColumn) {
                    return (float) ($row->payments[$paymentColumn] ?? 0);
                });
            }


            // ===== 8. FINALIZE =====
            $results = $dailySRRows;
            $resultsCount = $dailySRRows->count();
            

            return view('admin.reports.centralized', compact(
                'branchs',
                'results',
                'category',
                'report',
                'startDate',
                'endDate',
                'resultsCount',
                'selectedBranch',
                'dailySRRows',
                'arCollectionRows',
                'dailySRPaymentColumns',
                'dailyTotals',
                'arTotals',
            ));
        }
        if ($category == 'accounting') {
            //new update for income state report 6-7-2026 ken 252306001
            //ken update  start here 20250624001 - ken - service_charge/FORM 2307 in income statement report
            //ken update IS June 14, 2026
             if ($report == 'incomeState') {
                $results = collect([]);
                $resultsCount = 0;
                $cogsValue = 0;

                $monthStart = Carbon::parse($startDate)->startOfMonth();
                $monthEnd = Carbon::parse($endDate)->startOfMonth();
                $months = [];
                $monthLabels = [];

                while ($monthStart <= $monthEnd) {
                    $key = $monthStart->format('Y-m');
                    $label = $monthStart->format('F');
                    $months[$key] = $label;
                    $monthLabels[] = $label;
                    $monthStart->addMonth();
                }

                $salesByMonth = array_fill_keys(array_keys($months), 0);

                //ken added 20250624001 - ken - added service_charge/FORM 2307 in income statement report
                $serviceChargeByMonth = array_fill_keys(array_keys($months), 0);
                if (Schema::hasTable('transactions')) {
                    $salesQuery = DB::table('transactions')
                        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(total) as total'))
                        //->where('status', 'PAID')
                        ->where('order_status', '!=', 'CANCELLED')
                        ->where('created_at', '>=', $startDate)
                        ->where('created_at', '<', $endDate)
                        ->groupBy('month');

                    /*if ($userBranch) {
                        $salesQuery->where('branch_code', $userBranch);
                    } elseif (!empty($branchCodes)) {
                        $salesQuery->whereIn('branch_code', $branchCodes);
                    }*/
                    if ($userBranch) {
                        $salesQuery->where('store_code', $userBranch);
                    }

                    foreach ($salesQuery->get() as $row) {
                        if (isset($salesByMonth[$row->month])) {
                            $salesByMonth[$row->month] = (float) $row->total;
                        }
                    }

                    $serviceChargeQuery = DB::table('transactions')
                        ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(service_charge) as total'))
                        ->where('order_status', '!=', 'CANCELLED')
                        ->where('created_at', '>=', $startDate)
                        ->where('created_at', '<', $endDate)
                        ->groupBy('month');

                    if ($userBranch) {
                        $serviceChargeQuery->where('branch_code', $userBranch);
                    } elseif (!empty($branchCodes)) {
                        $serviceChargeQuery->whereIn('branch_code', $branchCodes);
                    }

                    foreach ($serviceChargeQuery->get() as $row) {
                        if (isset($serviceChargeByMonth[$row->month])) {
                            $serviceChargeByMonth[$row->month] = (float) $row->total;
                        }
                    }
                }
                /*$salesStatic = DB::table('income_statement')
                    ->where('id', 1)
                    ->first();*/
                    //dd($salesStatic);
                //$netSalesTotal = $salesStatic->sales;
                $netSalesTotal = array_sum($salesByMonth) + array_sum($serviceChargeByMonth);
                $salesPctByMonth = [];
                foreach ($months as $monthKey => $label) {
                    $salesPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0 ? 100.0 : 0.0;
                }
                    //ken added 20250624001 - ken - added revolving budget in income statement report
                    $revolvingRows = collect([]);
                    if (Schema::hasTable('revolving_transactions')) {
                        $revolvingQuery = DB::table('revolving_transactions as rt')
                            ->leftJoin('expenses_categories as ec', 'rt.category_id', '=', 'ec.id')
                            ->select(
                                DB::raw('DATE_FORMAT(rt.created_at, "%Y-%m") as month'),
                                DB::raw('SUM(rt.amount) as amount'),
                                DB::raw('COALESCE(ec.name, "Uncategorized Expenses") as category'),
                                DB::raw('"Revolving Budget" as item')
                            )
                            ->where('rt.created_at', '>=', $startDate)
                            ->where('rt.created_at', '<', $endDate)
                            ->where('rt.is_deleted', 0)
                            ->whereNotNull('rt.category_id')
                            ->groupBy(DB::raw('DATE_FORMAT(rt.created_at, "%Y-%m")'), 'rt.category_id', 'ec.name')
                            ->orderBy('ec.name');

                        if ($userBranch && Schema::hasColumn('revolving_transactions', 'branch_code')) {
                            $revolvingQuery->where('rt.branch_code', $userBranch);
                        } elseif (!empty($branchCodes) && Schema::hasColumn('revolving_transactions', 'branch_code')) {
                            $revolvingQuery->whereIn('rt.branch_code', $branchCodes);
                        }

                        $revolvingRows = $revolvingQuery->get();
                    }
                    //kend end of revolving budget query
                $cogsByMonth = array_fill_keys(array_keys($months), 0);
                if (Schema::hasTable('ordered') && Schema::hasTable('menus')) {                    
                    $cogsQuery = DB::table('ordered')
                        ->join('transactions', 'ordered.tran_id', '=', 'transactions.id')
                        ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                        ->select(DB::raw('DATE_FORMAT(ordered.created_at, "%Y-%m") as month'), DB::raw('SUM(ordered.quantity * menus.food_cost) as total'))
                        ->where('transactions.status', 'PAID')
                        ->where('transactions.order_status', '!=', 'CANCELLED')
                        ->where('ordered.created_at', '>=', $startDate)
                        ->where('ordered.created_at', '<', $endDate)
                        ->groupBy('month');                        

                    if ($userBranch) {
                        $cogsQuery->where('transactions.branch_code', $userBranch);
                    } elseif (!empty($branchCodes)) {
                        $cogsQuery->whereIn('transactions.branch_code', $branchCodes);
                    }

                    foreach ($cogsQuery->get() as $row) {
                        if (isset($cogsByMonth[$row->month])) {
                            $cogsByMonth[$row->month] = (float) $row->total;
                        }
                    }
                }
                $cogsTotal = array_sum($cogsByMonth);

                $cogsPctByMonth = [];
                

                $grossProfitByMonth = [];
                $grossProfitPctByMonth = [];
                
                $grossProfitTotal = array_sum($grossProfitByMonth);

                $expenseDefinitions = [
    
                ];

                $expenseCategoryData = [];
                $totalOperatingExpenses = 0;
                $revolvingBudgetTotal = 0;
                $cashOverage = 0;
                $cashOverageByMonth = array_fill_keys(array_keys($months), 0);                

                if (Schema::hasTable('inventory_transactions') && Schema::hasTable('expenses_categories')) {
                    $categoryIndex = [];
                    $itemIndex = [];

                    $allExpenseCategories = DB::table('expenses_categories')
                        ->where('is_deleted', 0)
                        ->where('is_is', 0)
                        ->pluck('name')
                        ->toArray();

                    foreach ($allExpenseCategories as $categoryLabel) {
                        $categoryIndex[$categoryLabel] = count($expenseCategoryData);
                        $itemIndex[$categoryLabel] = [];
                        $expenseCategoryData[] = [
                            'category' => $categoryLabel,
                            'total' => 0,
                            'total_by_month' => array_fill_keys(array_keys($months), 0),
                            'items' => [],
                        ];
                    }

                    $expenseRows = DB::table('inventory_transactions as it')
                        ->select(
                            'ec.name as category',
                            'it.remarks as item',
                            DB::raw('DATE_FORMAT(it.created_at, "%Y-%m") as month'),
                            DB::raw('SUM(it.amount) as amount')
                        )
                        ->join('expenses_categories as ec', 'it.category_id', '=', 'ec.id')
                        ->where('ec.is_is', 0) // update 6/14/2026
                        ->where('it.is_deleted', 0) // update 6/14/2026
                        ->where('ec.is_deleted', 0)
                        ->where('it.exp_type', 1)
                        //->where('ec.is_is', 0)
                        ->where('it.created_at', '>=', $startDate)
                        ->where('it.created_at', '<', $endDate)
                        ->groupBy('ec.name', 'it.remarks', 'month')
                        ->orderBy('ec.name')
                        ->orderBy('it.remarks')
                        ->get();

                    $is_field = DB::table('income_statement')
                    ->where('id', 2) //change id 1 to id 2
                    ->first();

                    // KEN UPDATED 20260607001 - FIXED COGS MONTHLY CALCULATION
                    // Using formula: COGS = beginning + cogs - scrap - ending (per month)
                    $cogsValueByMonth = array_fill_keys(array_keys($months), 0);
                    
                    $cogsQuery = DB::table('inventory_transactions as it')
                        ->join('expenses_categories as ec', 'it.category_id', '=', 'ec.id')
                        ->select(
                            DB::raw('DATE_FORMAT(it.created_at, "%Y-%m") as month'),
                            DB::raw('SUM(it.amount) as total')
                        )
                        ->where('ec.is_deleted', 0)
                        ->where('it.is_deleted', 0)
                        ->where('it.exp_type', 0)
                        ->where('it.created_at', '>=', $startDate)
                        ->where('it.created_at', '<', $endDate)
                        ->groupBy('month'); 
                                           

                    foreach ($cogsQuery->get() as $row) {
                        if (isset($cogsValueByMonth[$row->month])) {
                            $cogsValueByMonth[$row->month] = (float) $row->total;
                        }
                    }

                    // Apply formula per month: COGS = beginning + cogs - scrap - ending
                    // beginning: Starting inventory balance
                    // cogs: Monthly sum of inventory transactions (exp_type = 0)
                    // scrap: Waste/scrap amount from income_statement
                    // ending: Ending inventory balance from income_statement
                    foreach ($months as $monthKey => $label) {
                        $cogsValueByMonth[$monthKey] = ($is_field->beginning ?? 0) + ($cogsValueByMonth[$monthKey] ?? 0) - ($is_field->scrap ?? 0) - ($is_field->ending ?? 0);
                    }

                    $cogsValue = array_sum($cogsValueByMonth);
                    
                    // Calculate COGS percentage per month
                    foreach ($months as $monthKey => $label) {
                        $cogsPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                            ? ($cogsValueByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                            : 0.0;
                    }

                    // Calculate Gross Profit per month using monthly COGS (including service charges)
                    foreach ($months as $monthKey => $label) {
                        $grossProfitByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) + ($serviceChargeByMonth[$monthKey] ?? 0) - ($cogsValueByMonth[$monthKey] ?? 0) + ($cashOverageByMonth[$monthKey] ?? 0);
                        $grossProfitPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                            ? ($grossProfitByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                            : 0.0;
                    }
                    // Recalculate Gross Profit Total after monthly values are populated
                    $grossProfitTotal = array_sum($grossProfitByMonth);


                    foreach ($expenseRows as $row) {
                        $categoryLabel = trim($row->category) ?: 'Uncategorized';
                        $itemLabel = trim($row->item) ?: 'Unnamed Item';
                        $monthKey = $row->month;
                        $amount = (float) $row->amount;

                        $totalOperatingExpenses += $amount;

                        if (!isset($categoryIndex[$categoryLabel])) {
                            $categoryIndex[$categoryLabel] = count($expenseCategoryData);
                            $itemIndex[$categoryLabel] = [];
                            $expenseCategoryData[] = [
                                'category' => $categoryLabel,
                                'total' => 0,
                                'total_by_month' => array_fill_keys(array_keys($months), 0),
                                'items' => [],
                            ];
                        }

                        $categoryPos = $categoryIndex[$categoryLabel];
                        $expenseCategoryData[$categoryPos]['total'] += $amount;
                        if (isset($expenseCategoryData[$categoryPos]['total_by_month'][$monthKey])) {
                            $expenseCategoryData[$categoryPos]['total_by_month'][$monthKey] += $amount;
                        }

                        if (!isset($itemIndex[$categoryLabel][$itemLabel])) {
                            $itemIndex[$categoryLabel][$itemLabel] = count($expenseCategoryData[$categoryPos]['items']);
                            $expenseCategoryData[$categoryPos]['items'][] = [
                                'item' => $itemLabel,
                                'total' => 0,
                                'amount_by_month' => array_fill_keys(array_keys($months), 0),
                                'percent' => 0,
                            ];
                        }

                        $itemPos = $itemIndex[$categoryLabel][$itemLabel];
                        $expenseCategoryData[$categoryPos]['items'][$itemPos]['total'] += $amount;
                        if (isset($expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey])) {
                            $expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey] += $amount;
                        }
                    }
                    /* ken added 20250624001 - ken - added revolving budget in income statement report */
                    foreach ($revolvingRows as $row) {
                        $categoryLabel = trim($row->category) ?: 'Uncategorized';
                        $itemLabel = trim($row->item) ?: 'Revolving Budget';
                        $monthKey = $row->month;
                        $amount = (float) $row->amount;

                        $totalOperatingExpenses += $amount;
                        $revolvingBudgetTotal += $amount;

                        if (!isset($categoryIndex[$categoryLabel])) {
                            $categoryIndex[$categoryLabel] = count($expenseCategoryData);
                            $itemIndex[$categoryLabel] = [];
                            $expenseCategoryData[] = [
                                'category' => $categoryLabel,
                                'total' => 0,
                                'total_by_month' => array_fill_keys(array_keys($months), 0),
                                'items' => [],
                            ];
                        }

                        $categoryPos = $categoryIndex[$categoryLabel];
                        $expenseCategoryData[$categoryPos]['total'] += $amount;
                        if (isset($expenseCategoryData[$categoryPos]['total_by_month'][$monthKey])) {
                            $expenseCategoryData[$categoryPos]['total_by_month'][$monthKey] += $amount;
                        }

                        if (!isset($itemIndex[$categoryLabel][$itemLabel])) {
                            $itemIndex[$categoryLabel][$itemLabel] = count($expenseCategoryData[$categoryPos]['items']);
                            $expenseCategoryData[$categoryPos]['items'][] = [
                                'item' => $itemLabel,
                                'total' => 0,
                                'amount_by_month' => array_fill_keys(array_keys($months), 0),
                                'percent' => 0,
                            ];
                        }

                        $itemPos = $itemIndex[$categoryLabel][$itemLabel];
                        $expenseCategoryData[$categoryPos]['items'][$itemPos]['total'] += $amount;
                        if (isset($expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey])) {
                            $expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey] += $amount;
                        }
                    }

                    /* end for results loop to calculate total revolving budget */
                    usort($expenseCategoryData, function ($a, $b) {
                        return strcasecmp($a['category'], $b['category']);
                    });

                    foreach ($expenseCategoryData as &$categoryData) {
                        usort($categoryData['items'], function ($a, $b) {
                            return strcasecmp($a['item'], $b['item']);
                        });

                        foreach ($categoryData['items'] as &$itemData) {
                            $itemData['percent'] = $totalOperatingExpenses > 0
                                ? ($itemData['total'] / $totalOperatingExpenses) * 100
                                : 0.0;
                        }
                        unset($itemData);
                    }
                    unset($categoryData);
                }

                $operatingByMonth = array_fill_keys(array_keys($months), 0);
                foreach ($expenseCategoryData as $expense) {
                    foreach ($expense['total_by_month'] as $monthKey => $value) {
                        $operatingByMonth[$monthKey] += $value;
                    }
                }
                $totalOperatingExpenses = array_sum($operatingByMonth);
                $operatingPctByMonth = [];
                foreach ($months as $monthKey => $label) {
                    $operatingPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                        ? ($operatingByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                        : 0.0;
                }

                $netIncomeByMonth = [];
                $netIncomePctByMonth = [];
                foreach ($months as $monthKey => $label) {
                    // KEN UPDATED 20260607001 - Net income now uses monthly COGS instead of total COGS
                    $netIncomeByMonth[$monthKey] = ($grossProfitByMonth[$monthKey] ?? 0) - ($operatingByMonth[$monthKey] ?? 0);
                    $netIncomePctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                        ? ($netIncomeByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                        : 0.0;
                }
                // Calculate Net Income Total: Gross Profit Total - Total Operating Expenses
                $netIncomeTotal = $grossProfitTotal - $totalOperatingExpenses;

                // Extract Cash Overage from expenseCategoryData to ensure it matches the Operating Expenses section
                // KEN UPDATED 20260607002 - Fixed Cash Overage not displaying
                // NOTE: Cash Overage displays ₱0.00 if there's no inventory_transactions data within the selected date range
                $cashOverageByMonth = array_fill_keys(array_keys($months), 0);
                $cashOverage = 0;
                
                foreach ($expenseCategoryData as $expense) {
                    if (strtolower(trim($expense['category'])) === 'cash overage') {
                        $cashOverageByMonth = $expense['total_by_month'];
                        $cashOverage = array_sum($cashOverageByMonth);
                        break;
                    }
                }
                
                // If Cash Overage not found in expenses, query it directly from inventory_transactions
                // This handles cases where Cash Overage is marked as is_is = 1
                if ($cashOverage === 0) {
                    $cashOverageQuery = DB::table('inventory_transactions as it')
                        ->join('expenses_categories as ec', 'it.category_id', '=', 'ec.id')
                        ->select(
                            DB::raw('DATE_FORMAT(it.created_at, "%Y-%m") as month'),
                            DB::raw('SUM(it.amount) as total')
                        )
                        ->where('ec.is_deleted', 0)
                        ->where('it.exp_type', 1)
                        ->where('it.created_at', '>=', $startDate)
                        ->where('it.created_at', '<', $endDate)
                        ->whereRaw('LOWER(TRIM(ec.name)) = ?', ['cash overage'])
                        ->groupBy('month');

                    if ($userBranch) {
                        $cashOverageQuery->where('it.branch_code', $userBranch);
                    } elseif (!empty($branchCodes)) {
                        $cashOverageQuery->whereIn('it.branch_code', $branchCodes);
                    }

                    foreach ($cashOverageQuery->get() as $row) {
                        if (isset($cashOverageByMonth[$row->month])) {
                            $cashOverageByMonth[$row->month] = (float) $row->total;
                        }
                    }
                    $cashOverage = array_sum($cashOverageByMonth);
                }                

                return view('admin.reports.centralized', compact(
                    'branchs',
                    'settings',
                    'category',
                    'report',
                    'startDate',
                    'endDate',
                    'results',
                    'resultsCount',
                    'selectedBranch',
                    'months',
                    'monthLabels',
                    'salesByMonth',
                    'serviceChargeByMonth',
                    'salesPctByMonth',
                    'netSalesTotal',
                    'cogsValueByMonth',
                    'cogsPctByMonth',
                    'cogsValue',
                    'cogsTotal',
                    'grossProfitByMonth',
                    'grossProfitPctByMonth',
                    'grossProfitTotal',
                    'expenseCategoryData',
                    'operatingByMonth',
                    'operatingPctByMonth',
                    'totalOperatingExpenses',
                    'netIncomeByMonth',
                    'netIncomePctByMonth',
                    'netIncomeTotal',
                    'revolvingBudgetTotal',
                    'cashOverage',
                    'cashOverageByMonth'
                ));
            }
            // end of update for june 14, 2026 
            //ken update end here 20250624001 - ken - added service_charge/FORM 2307 in income statement report
            //  end of update for income state report 6-7-2026 ken 252306001

            if ($report == 'balsheet') {
                $balanceSheet = $this->buildBalanceSheetData($startDate, $endDate, $userBranch, $branchCodes);

                return view('admin.reports.centralized', array_merge(
                    [
                        'branchs' => $branchs,
                        'settings' => $settings,
                        'category' => $category,
                        'report' => $report,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'results' => collect([]),
                        'resultsCount' => 0,
                        'selectedBranch' => $selectedBranch,
                    ],
                    $balanceSheet
                ));
            }

            $cash = 0;
            if (Schema::hasTable('transactions')) {
                $cashQuery = DB::table('transactions')
                    ->where('status', 'PAID')
                    ->whereRaw('LOWER(mode_of_payment) = ?', ['cash'])
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);

                if ($userBranch) {
                    $cashQuery->where('branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $cashQuery->whereIn('branch_code', $branchCodes);
                }

                $cash = $cashQuery->sum('total');
            }

            $receivables = 0;
            if (Schema::hasTable('account_receivables')) {
                $receivablesQuery = DB::table('account_receivables')
                    ->where('balance', '>', 0)
                    ->where('tran_date', '>=', $startDate)
                    ->where('tran_date', '<', $endDate);

                if ($userBranch) {
                    $receivablesQuery->where('branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $receivablesQuery->whereIn('branch_code', $branchCodes);
                }

                $receivables = $receivablesQuery->sum('balance');
            }

            $inventories = 0;
            if (Schema::hasTable('inventories')) {
                $inventoriesQuery = DB::table('inventories')->select(DB::raw('SUM(quantity * price) as total'));
                $inventories = (float) $inventoriesQuery->value('total');
            }

            $prepayments = 0;
            if (Schema::hasTable('prepayments')) {
                $prepaymentsQuery = DB::table('prepayments')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);

                if ($userBranch && Schema::hasColumn('prepayments', 'branch_code')) {
                    $prepaymentsQuery->where('branch_code', $userBranch);
                } elseif (!empty($branchCodes) && Schema::hasColumn('prepayments', 'branch_code')) {
                    $prepaymentsQuery->whereIn('branch_code', $branchCodes);
                }

                $prepayments = $prepaymentsQuery->sum('amount');
            }

            $propertyAndEquipment = 0;
            if (Schema::hasTable('assets')) {
                $propertyAndEquipmentQuery = DB::table('assets')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);

                if ($userBranch && Schema::hasColumn('assets', 'branch_code')) {
                    $propertyAndEquipmentQuery->where('branch_code', $userBranch);
                } elseif (!empty($branchCodes) && Schema::hasColumn('assets', 'branch_code')) {
                    $propertyAndEquipmentQuery->whereIn('branch_code', $branchCodes);
                }

                $propertyAndEquipment = (float) $propertyAndEquipmentQuery->select(DB::raw('SUM(acquisition_cost - accumulated_depreciation) as total'))->value('total');
            }

            $trade_payables = 0;
            if (Schema::hasTable('inventory_transactions')) {
                $tradePayablesQuery = DB::table('inventory_transactions')
                    ->where('is_paid', 0)
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);

            /*  if ($userBranch) {
                    $tradePayablesQuery->where('branch', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $tradePayablesQuery->whereIn('branch', $branchCodes);
                } */

                $trade_payables = $tradePayablesQuery->sum('amount');
            }

            $loans_payable = 0;
            $capital = 0;

            $results = collect([]);
            $resultsCount = 0;
            $totalCurrentAssets = $cash + $receivables + $inventories + $prepayments;
            $totalAssets = $totalCurrentAssets + $propertyAndEquipment;
            $totalLiabilities = $trade_payables + $loans_payable;
            $totalLiabilitiesEquity = $totalCurrentAssets + $capital;

            return view('admin.reports.centralized', compact(
                'branchs',
                'settings',
                'category',
                'report',
                'startDate',
                'endDate',
                'results',
                'resultsCount',
                'cash',
                'receivables',
                'inventories',
                'prepayments',
                'propertyAndEquipment',
                'totalCurrentAssets',
                'totalAssets',
                'trade_payables',
                'loans_payable',
                'totalLiabilities',
                'capital',
                'totalLiabilitiesEquity',
                'selectedBranch'
            ));
        }
        /* end of ken add for accounting reports 252306001 - 4-23-2026 */

        if ($category == 'sales') {
            $results = Transaction::with('thisCashier')
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('order_status', '!=', 'PREPARING');
            
            // ADDED: Branch filtering for non-admin users or restrict to store branches
            if ($userBranch) {
                $results->where('transactions.branch_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $results->whereIn('transactions.branch_code', $branchCodes);
            }
            
            $results = $results->orderBy('transactions.id', 'desc')
                ->get();

            $salesQuery = DB::table('transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as stotal'),
                    DB::raw('SUM(service_charge) as sctotal'),
                    DB::raw('0 as etotal')
                )
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->where('order_status', '!=', 'PREPARING')
                // ->whereBetween('created_at', [$startDate, $endDate])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate);
            
            // ADDED: Branch filtering for non-admin users or restrict to store branches
            if ($userBranch) {
                $salesQuery->where('branch_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $salesQuery->whereIn('branch_code', $branchCodes);
            }
            
            $salesQuery->groupBy('date');
            // $results = DB::table('transactions')
            //     ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 'transactions.mode_of_payment', 'transactions.type', 'transactions.created_at as date', 'users.name as cashier')
            //     ->join('users', 'transactions.cashier', '=', 'users.id')
            //     // ->whereBetween('created_at', [$startDate, $endDate])
            //     ->where('transactions.created_at', '>=', $startDate)
            //     ->where('transactions.created_at', '<', $endDate)
            //     ->where('transactions.status', 'PAID')
            //     ->where('transactions.order_status', '!=', 'CANCELLED')
            //     ->orderBy('transactions.id', 'desc')
            //     ->get();

            $resultsCount = $results->count();


            if ($report == 'summary') {
                /* $results = DB::table('transactions')
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get(); */
                $results = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=','PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering by selected branch or by store branches
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $results->whereIn('transactions.branch_code', $branchCodes);
                }
                
                $results = $results->orderBy('transactions.id', 'desc')
                    ->get();

                $auditted = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.sales_type', '1')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering by selected branch or by store branches
                if ($userBranch) {
                    $auditted->where('transactions.branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $auditted->whereIn('transactions.branch_code', $branchCodes);
                }
                
                $auditted = $auditted->orderBy('transactions.id', 'desc')
                    ->get();

                $notAuditted = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.sales_type', '0')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering by selected branch or by store branches
                if ($userBranch) {
                    $notAuditted->where('transactions.branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $notAuditted->whereIn('transactions.branch_code', $branchCodes);
                }
                
                $notAuditted = $notAuditted->orderBy('transactions.id', 'desc')
                    ->get();

                // food finds
                $foodFinds = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('table', '=', 59)
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering by selected branch or by store branches
                if ($userBranch) {
                    $foodFinds->where('transactions.branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $foodFinds->whereIn('transactions.branch_code', $branchCodes);
                }
                
                $foodFinds = $foodFinds->orderBy('transactions.id', 'desc')
                    ->get();

                // studio
                $studio = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('table', '=', 60)
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering by selected branch or by store branches
                if ($userBranch) {
                    $studio->where('transactions.branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $studio->whereIn('transactions.branch_code', $branchCodes);
                }
                
                $studio = $studio->orderBy('transactions.id', 'desc')
                    ->get();

                // complimentaries
                $foodCost = DB::table('ordered as ord')
                    ->leftJoin('transactions as tr', 'tr.id', '=', 'ord.tran_id')
                    ->select('tr.created_at as date','tr.id as transid', 'tr.created_at', 'tr.total', 'tr.amount', 'tr.status', 'tr.order_status', 
                        'ord.id as ordid', 'ord.tran_id', 'ord.type', 'ord.food_cost', 'ord.created_at', 'ord.quantity')
                    ->where('tr.status', '=','PAID')
                    ->where('tr.order_status', '!=', 'CANCELLED')
                    ->where('ord.type', '=', 1)
                    ->where('tr.created_at', '>=', $startDate)
                    ->where('tr.created_at', '<', $endDate);
                
                // ADDED: Branch filtering by selected branch or by store branches
                if ($userBranch) {
                    $foodCost->where('tr.branch_code', $userBranch);
                } elseif (!empty($branchCodes)) {
                    $foodCost->whereIn('tr.branch_code', $branchCodes);
                }
                
                $foodCost = $foodCost->orderBy('tr.id', 'desc')
                    ->get();
                
            // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    
                $grandTotals = [
                    'total' => 0,
                    'discount' => 0,
                    'pwd' => 0,
                    'lessVat' => 0,
                    'vatSales' => 0,
                    'vat' => 0,
                    'vatExempt' => 0,
                    'sc' => 0,
                    'net' => 0,
                    'served_total' => 0,
                    'served_discount' => 0,
                    'served_pwd' => 0,
                    'served_lessVat' => 0,
                    'served_vatSales' => 0,
                    'served_vat' => 0,
                    'served_vatExempt' => 0,
                    'served_sc' => 0,
                    'served_net' => 0,
                    'not_served_total' => 0,
                    'not_served_discount' => 0,
                    'not_served_pwd' => 0,
                    'not_served_lessVat' => 0,
                    'not_served_vatSales' => 0,
                    'not_served_vat' => 0,
                    'not_served_vatExempt' => 0,
                    'not_served_sc' => 0,
                    'not_served_net' => 0
                ];
                
                // Calculate totals
                foreach ($results as $transaction) {
                    $grandTotals['total'] += $transaction->total ?? 0;
                    $grandTotals['discount'] += $transaction->lime_discount ?? 0;
                    $grandTotals['pwd'] += $transaction->pwd ?? 0;
                    $grandTotals['vat'] += $transaction->vat ?? 0;
                    $grandTotals['vatSales'] += $transaction->vatSales ?? 0;
                    $grandTotals['vatExempt'] += $transaction->vatExemptSales ?? 0;
                    $grandTotals['lessVat'] += $transaction->lessVatDeduction ?? 0;
                    $grandTotals['sc'] += $transaction->service_charge ?? 0;
                    
                    // Check if served/audited
                    $isServed = isset($transaction->is_audit) ? $transaction->is_audit : 
                            (isset($transaction->order_status) && $transaction->order_status == 'SERVED');
                    
                    if ($isServed) {
                        $grandTotals['served_total'] += $transaction->total ?? 0;
                        $grandTotals['served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['served_sc'] += $transaction->service_charge ?? 0;
                    } else {
                        $grandTotals['not_served_total'] += $transaction->total ?? 0;
                        $grandTotals['not_served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['not_served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['not_served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['not_served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['not_served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['not_served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['not_served_sc'] += $transaction->service_charge ?? 0;
                    }
                }
                
                // Calculate net totals
                $grandTotals['net'] = $grandTotals['total'] - ($grandTotals['sc'] + $grandTotals['vat']);
                $grandTotals['served_net'] = $grandTotals['served_total'] - ($grandTotals['served_sc'] + $grandTotals['served_vat']);
                $grandTotals['not_served_net'] = $grandTotals['not_served_total'] - ($grandTotals['not_served_sc'] + $grandTotals['not_served_vat']);
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                $paymentChartData = ChartHelper::generateZReadingPaymentChart($results);
                $deductionsChartData = ChartHelper::generateZReadingDeductionsChart($results);
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                return view('admin.reports.centralized', compact( 
                'branchs',
                'results', 
                'category', 
                'paymentChartData',
                'deductionsChartData',
                'report', 
                'startDate', 
                'endDate', 
                'chartData', 
                'grandTotals', 
                'resultsCount',
                'selectedBranch'
                ));
            // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
            } elseif (($report == 'compli')){
                $results = DB::table('transactions')
                    ->select('transactions.id as tid','transactions.number','menus.name as menuName',DB::raw("CONCAT(users.lastname, ', ', users.firstname) as userName"),'ordered.created_at as dateOrdered','ordered.quantity','menus.food_cost','staff.fname','staff.lname')
                    ->leftjoin('ordered', 'transactions.id','=', 'ordered.tran_id')
                    ->leftjoin('menus', 'menus.id','=', 'ordered.menu_id')
                    ->leftjoin('staff', 'staff.id','=', 'ordered.cashier')
                    ->leftjoin('users', 'users.id','=', 'transactions.cashier')
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('ordered.type',1);
                
                // Apply branch/store filter
                $results = $this->applyBranchFilterToQuery($results, 'transactions.branch_code', $userBranch, $branchCodes);

                $results = $results->orderBy('transactions.id', 'desc')
                    ->get();
                $resultsCount = $results->count();
                return view('admin.reports.list_complimentary', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
            } elseif (($report == 'compli_break')){
                $results = DB::table('transactions')
                    ->leftJoin('ordered', 'transactions.id', '=', 'ordered.tran_id')
                    ->leftJoin('menus', 'menus.id', '=', 'ordered.menu_id')
                    ->leftJoin('staff', 'staff.id', '=', 'ordered.cashier') // Adjusted to handle multiple servers if needed
                    ->leftJoin('users', 'users.id', '=', 'transactions.cashier') // Adjusted to handle multiple cashiers if needed
                    ->select(
                        'transactions.id AS tid',
                        'transactions.number',
                        DB::raw('GROUP_CONCAT(menus.name ORDER BY ordered.created_at SEPARATOR ", ") AS menuNames'),
                        DB::raw('MAX(DATE_FORMAT(ordered.created_at, "%Y-%m-%d %H:%i:%s")) AS dateOrdered'),
                        DB::raw('GROUP_CONCAT(ordered.food_cost ORDER BY ordered.created_at SEPARATOR ", ") AS foodCosts'), 
                        DB::raw('GROUP_CONCAT(ordered.quantity ORDER BY ordered.created_at SEPARATOR ", ") AS quantities'), 
                        DB::raw('SUM(ordered.quantity) AS totalQuantity'),
                        DB::raw('SUM(menus.food_cost * ordered.quantity) AS totalFoodCost'),
                        DB::raw('GROUP_CONCAT(DISTINCT CONCAT(staff.fname, " ", staff.lname) ORDER BY ordered.created_at SEPARATOR ", ") AS serverNames'), 
                        DB::raw('GROUP_CONCAT(DISTINCT CONCAT(users.lastname, ", ", users.firstname) ORDER BY transactions.created_at SEPARATOR ", ") AS cashierNames') 
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('ordered.type', 1);
                
                // Apply branch/store filter
                $results = $this->applyBranchFilterToQuery($results, 'transactions.branch_code', $userBranch, $branchCodes);

                $results = $results->groupBy('transactions.id', 'transactions.number')
                    ->orderBy('transactions.id', 'DESC')
                    ->get();

                return view('admin.reports.list_complimentary', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));

            } elseif (($report == 'beverages')){
                $results = DB::table('ordered')
                    ->select('menus.name',DB::raw('SUM(ordered.quantity) as quantity'),'menus.price')
                    ->leftjoin('menus', 'menus.id','=', 'ordered.menu_id')
                    ->leftjoin('staff', 'staff.id','=', 'ordered.cashier')
                    ->leftjoin('transactions', 'transactions.id', '=', 'ordered.tran_id')
                    ->where('ordered.department',2)
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate);
                
                // Apply branch/store filter
                $results = $this->applyBranchFilterToQuery($results, 'transactions.branch_code', $userBranch, $branchCodes);

                $results = $results->groupBy('ordered.menu_id','menus.name','menus.price')
                    ->orderBy('quantity', 'DESC')
                    ->get();
                $resultsCount = $results->count();
                return view('admin.reports.list_complimentary', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
            } elseif (($report == 'food')){
                $results = DB::table('ordered')
                    ->select('menus.name',DB::raw('SUM(ordered.quantity) as quantity'),'menus.price')
                    ->leftjoin('menus', 'menus.id','=', 'ordered.menu_id')
                    ->leftjoin('staff', 'staff.id','=', 'ordered.cashier')
                    ->leftjoin('transactions', 'transactions.id', '=', 'ordered.tran_id')
                    ->where('ordered.department',1)
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                }
                
                $results = $results->groupBy('ordered.menu_id','menus.name','menus.price')
                    ->orderBy('quantity', 'DESC')
                    ->get();
                $resultsCount = $results->count();
                return view('admin.reports.list_complimentary', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
            } elseif (($report == 'pay_summary')) {
                $results = DB::table('transactions')
                    ->select('created_at as date','mode_of_payment', 'status', 'order_status'
                    )
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->get();

                $auditted = DB::table('transactions')
                    ->select('created_at as date','mode_of_payment', 'status', 'order_status'
                    )
                    ->where('sales_type', '1')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $auditted->where('branch', $userBranch);
                }
                
                $auditted = $auditted->get();

                $notauditted = DB::table('transactions')
                    ->select('created_at as date','mode_of_payment', 'status', 'order_status'
                    )
                    ->where('sales_type', '0')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $notauditted->where('branch', $userBranch);
                }
                
                $notauditted = $notauditted->get();

                return view('admin.reports.summary_sep', compact('results', 'settings', 'category', 'startDate', 'endDate', 'report', 'auditted', 'notauditted'));

            } elseif (($report == 'pay_amount_summary')) {
                $results = DB::table('transactions')
                    ->select('created_at as date','mode_of_payment', 'status', 'order_status', 'total'
                    )
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->get();

                $auditted = DB::table('transactions')
                    ->select('created_at as date','mode_of_payment', 'status', 'order_status', 'total'
                    )
                    ->where('sales_type', '1')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $auditted->where('branch', $userBranch);
                }
                
                $auditted = $auditted->get();

                $notauditted = DB::table('transactions')
                    ->select('created_at as date','mode_of_payment', 'status', 'order_status', 'total'
                    )
                    ->where('sales_type', '0')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $notauditted->where('branch', $userBranch);
                }
                
                $notauditted = $notauditted->get();

                return view('admin.reports.summary_sepa', compact('results', 'settings', 'category', 'startDate', 'endDate', 'report', 'auditted', 'notauditted'));
            } elseif (($report == 'served_list')) {
                $results = Transaction::with('thisCashier')
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    // ->where('transactions.status', 'PAID')
                    
                    // ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    ->where('order_status', '!=', 'PREPARING')
                    ->where('sales_type', '1');
                
                // Apply branch/store filter
                $results = $this->applyBranchFilterToQuery($results, 'transactions.branch_code', $userBranch, $branchCodes);

                $results = $results->orderBy('transactions.id', 'desc')
                    ->get();

                $salesQuery = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(total) as stotal'),
                        DB::raw('SUM(service_charge) as sctotal'),
                        DB::raw('0 as etotal')
                    )
                    
                    ->where('sales_type', '1')
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    // ->where('status', 'PAID')
                    // ->where('order_status', '!=', 'CANCELLED')
                    ->where('order_status', '!=', 'PREPARING')
                    // ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $salesQuery->where('branch', $userBranch);
                }
                
                $salesQuery->groupBy('date');
                // $results = DB::table('transactions')
                //     ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 'transactions.mode_of_payment', 'transactions.type', 'transactions.created_at as date', 'users.name as cashier')
                //     ->join('users', 'transactions.cashier', '=', 'users.id')
                //     // ->whereBetween('created_at', [$startDate, $endDate])
                //     ->where('transactions.created_at', '>=', $startDate)
                //     ->where('transactions.created_at', '<', $endDate)
                //     ->where('transactions.status', 'PAID')
                //     ->where('transactions.order_status', '!=', 'CANCELLED')
                //     ->orderBy('transactions.id', 'desc')
                //     ->get();

                $resultsCount = $results->count();

            } elseif (($report == 'notServed_list')) {
                $results = Transaction::with('thisCashier')
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    ->where('sales_type', '0')
                    // ->where('transactions.status', 'PAID')
                    
                    // ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('order_status', '!=', 'PREPARING');
                
                // Apply branch/store filter
                $results = $this->applyBranchFilterToQuery($results, 'transactions.branch_code', $userBranch, $branchCodes);

                $results = $results->orderBy('transactions.id', 'desc')
                    ->get();

                $salesQuery = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(total) as stotal'),
                        DB::raw('SUM(service_charge) as sctotal'),
                        DB::raw('0 as etotal')
                    )
                    ->where('sales_type', '0')
                    // ->where('status', 'PAID')
                    
                    // ->where('order_status', '!=', 'CANCELLED')
                    ->where('order_status', '!=', 'PREPARING')
                    ->where('table', '!=', 60)
                    ->where('table', '!=', 59)
                    // ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $salesQuery->where('branch', $userBranch);
                }
                
                $salesQuery->groupBy('date');
                // $results = DB::table('transactions')
                //     ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 'transactions.mode_of_payment', 'transactions.type', 'transactions.created_at as date', 'users.name as cashier')
                //     ->join('users', 'transactions.cashier', '=', 'users.id')
                //     // ->whereBetween('created_at', [$startDate, $endDate])
                //     ->where('transactions.created_at', '>=', $startDate)
                //     ->where('transactions.created_at', '<', $endDate)
                //     ->where('transactions.status', 'PAID')
                //     ->where('transactions.order_status', '!=', 'CANCELLED')
                //     ->orderBy('transactions.id', 'desc')
                //     ->get();

                $resultsCount = $results->count();
            } else if ($report == 'zreading') {
                /* $results = DB::table('transactions')
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get(); */
                $results = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=','PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                }
                
                $results = $results->orderBy('transactions.id', 'desc')
                    ->get();
            
                $auditted = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.sales_type', '1')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $auditted->where('transactions.branch', $userBranch);
                }
                
                $auditted = $auditted->orderBy('transactions.id', 'desc')
                    ->get();

                $notAuditted = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.sales_type', '0')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $notAuditted->where('transactions.branch', $userBranch);
                }
                
                $notAuditted = $notAuditted->orderBy('transactions.id', 'desc')
                    ->get();

                // food finds
                $foodFinds = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $foodFinds->where('transactions.branch', $userBranch);
                }
                
                $foodFinds = $foodFinds->orderBy('transactions.id', 'desc')
                    ->get();

                // studio
                $studio = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('table', '=', 60)
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $studio->where('transactions.branch', $userBranch);
                }
                
                $studio = $studio->orderBy('transactions.id', 'desc')
                    ->get();

                // complimentaries
                $foodCost = DB::table('ordered as ord')
                    ->leftJoin('transactions as tr', 'tr.id', '=', 'ord.tran_id')
                    ->select('tr.created_at as date','tr.id as transid', 'tr.created_at', 'tr.total', 'tr.amount', 'tr.status', 'tr.order_status', 
                        'ord.id as ordid', 'ord.tran_id', 'ord.type', 'ord.food_cost', 'ord.created_at', 'ord.quantity')
                    ->where('tr.status', '=','PAID')
                    ->where('tr.order_status', '!=', 'CANCELLED')
                    ->where('ord.type', '=', 1)
                    ->where('tr.created_at', '>=', $startDate)
                    ->where('tr.created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $foodCost->where('tr.branch', $userBranch);
                }
                
                $foodCost = $foodCost->orderBy('tr.id', 'desc')
                    ->get();


                $MtransactionIds = DB::table('transactions')
                    ->select('id')
                    ->where('status', 'PAID')
                    ->where('sales_type', '0')
                    ->where('mode_of_payment', 'MULTIPLE')
                    ->where('order_status', '!=', 'CANCELLED')
                    ->whereBetween('created_at', [$startDate, $endDate]);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $MtransactionIds->where('branch', $userBranch);
                }
                
                $MtransactionIds = $MtransactionIds->pluck('id');
        
                $multiplepayment = DB::table('payment_orders')
                    ->select('payment_orders.created_at as date', 'payment_orders.id as mpid', 'payment_orders.amount', 'payment_orders.tran_id', 'payment_orders.mop')
                    ->whereIn('tran_id', $MtransactionIds)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    
                $grandTotals = [
                    'total' => 0,
                    'discount' => 0,
                    'pwd' => 0,
                    'lessVat' => 0,
                    'vatSales' => 0,
                    'vat' => 0,
                    'vatExempt' => 0,
                    'sc' => 0,
                    'net' => 0,
                    'served_total' => 0,
                    'served_discount' => 0,
                    'served_pwd' => 0,
                    'served_lessVat' => 0,
                    'served_vatSales' => 0,
                    'served_vat' => 0,
                    'served_vatExempt' => 0,
                    'served_sc' => 0,
                    'served_net' => 0,
                    'not_served_total' => 0,
                    'not_served_discount' => 0,
                    'not_served_pwd' => 0,
                    'not_served_lessVat' => 0,
                    'not_served_vatSales' => 0,
                    'not_served_vat' => 0,
                    'not_served_vatExempt' => 0,
                    'not_served_sc' => 0,
                    'not_served_net' => 0
                ];
                
                // Calculate totals
                foreach ($results as $transaction) {
                    $grandTotals['total'] += $transaction->total ?? 0;
                    $grandTotals['discount'] += $transaction->lime_discount ?? 0;
                    $grandTotals['pwd'] += $transaction->pwd ?? 0;
                    $grandTotals['vat'] += $transaction->vat ?? 0;
                    $grandTotals['vatSales'] += $transaction->vatSales ?? 0;
                    $grandTotals['vatExempt'] += $transaction->vatExemptSales ?? 0;
                    $grandTotals['lessVat'] += $transaction->lessVatDeduction ?? 0;
                    $grandTotals['sc'] += $transaction->service_charge ?? 0;
                    
                    // Check if served/audited
                    $isServed = isset($transaction->is_audit) ? $transaction->is_audit : 
                            (isset($transaction->order_status) && $transaction->order_status == 'SERVED');
                    
                    if ($isServed) {
                        $grandTotals['served_total'] += $transaction->total ?? 0;
                        $grandTotals['served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['served_sc'] += $transaction->service_charge ?? 0;
                    } else {
                        $grandTotals['not_served_total'] += $transaction->total ?? 0;
                        $grandTotals['not_served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['not_served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['not_served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['not_served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['not_served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['not_served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['not_served_sc'] += $transaction->service_charge ?? 0;
                    }
                }
                // Generate all chart data using ChartHelper
                // Generate all chart data using ChartHelper
                $summaryChartData = ChartHelper::generateFromTableData($results, $category, $report);

                // For payment and deductions charts, we need to call them separately
                // In your controller, update the payment chart generation:
                $paymentChartData = ChartHelper::generateZReadingPaymentChart($results, $multiplepayment ?? null);
                $deductionsChartData = ChartHelper::generateZReadingDeductionsChart($results);

                // Make sure we have summaryChartData for the main chart
                $chartData = $summaryChartData; // Use summaryChartData for the main chart

                // Debug logging
                \Log::info('Chart data generated for Z-Reading:', [
                    'summary_exists' => !empty($summaryChartData),
                    'payment_exists' => !empty($paymentChartData),
                    'deductions_exists' => !empty($deductionsChartData),
                    'results_count' => $results->count(),
                ]);

                return view('admin.reports.centralized', compact( 
                    'branchs',
                    'results', 
                    'category', 
                    'report', 
                    'startDate', 
                    'endDate', 
                    'summaryChartData', 
                    'paymentChartData', 
                    'deductionsChartData',
                    'chartData', // Add this
                    'grandTotals', 
                    'resultsCount',
                    'selectedBranch','multiplepayment','results', 'settings', 'auditted','notAuditted', 'foodFinds', 'studio', 'foodCost'));               
            } elseif (($report == 'pax')){
                $results = DB::table('transactions')
                    ->select(
                    DB::raw('DATE(transactions.created_at) as date'),
                    DB::raw('SUM(total_customer) as total_customer')
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=','PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED');
                
                // Apply branch/store filter
                $results = $this->applyBranchFilterToQuery($results, 'transactions.branch_code', $userBranch, $branchCodes);

                $results = $results->groupBy(DB::raw('DATE(transactions.created_at)'))
                    ->orderBy('date', 'asc')
                    ->get();

                $resultsCount = $results->count();

                return view('admin.reports.totalpax', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report')); 
            }elseif (($report == 'discount')){
                $results = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(transactions.created_at) as date'), 
                        'transactions.lime_discount', 
                        DB::raw('SUM(transactions.total) as total_sum'), 
                        DB::raw('COUNT(transactions.id) as transaction_count'), 
                        DB::raw('SUM(transactions.ld_value) as ld_value')
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->whereIn('transactions.lime_discount', [5, 10, 20, 50, 05, 010, 020, 050]);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                }
                
                $results = $results->groupBy(DB::raw('DATE(transactions.created_at)'), 'transactions.lime_discount')
                    ->orderBy('date', 'asc')
                    ->orderBy('transactions.lime_discount', 'asc') 
                    ->get();
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);

                return view('admin.reports.centralized', compact('results', 'settings','chartData', 'category','branchs', 'resultsCount', 'startDate', 'endDate', 'report'));
            }elseif (($report == 'srpwd')){
                // START DEBUGGING
                \Log::info('SR/PWD Report Generation Started', [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'userBranch' => $userBranch,
                    'category' => $category,
                    'report' => $report
                ]);
                
                try {
                    $query = DB::table('transactions')
                        ->join('seniors', 'transactions.id', '=', 'seniors.tran_id')
                        ->select(
                            DB::raw('DATE(transactions.created_at) as date'),  
                            DB::raw('COUNT(transactions.id) as transaction_count'), 
                            DB::raw('SUM(transactions.pwd) as pwd_total'),
                            'seniors.category',
                            'transactions.number',
                            'transactions.customer_with_discount',
                            DB::raw('SUM(transactions.total) as total_sales') // Added for debugging
                        )
                        ->where('transactions.created_at', '>=', $startDate)
                        ->where('transactions.created_at', '<', $endDate)
                        ->where('transactions.status', '=', 'PAID')
                        ->where('transactions.order_status', '=', 'COMPLETED')
                        ->where('transactions.total', '!=', 0)
                        ->whereIn('seniors.category', ['sr','pwd','Athlete','single', 'solo parent']);
                    
                    // Log the raw SQL for debugging
                    \Log::debug('SR/PWD Raw SQL Query', [
                        'sql' => $query->toSql(),
                        'bindings' => $query->getBindings()
                    ]);
                    
                    // ADDED: Branch filtering
                    if ($userBranch) {
                        $query->where('transactions.branch', $userBranch);
                        \Log::info('Branch filter applied', ['branch_id' => $userBranch]);
                    }
                    
                    // Log count before grouping
                    $preGroupCount = $query->count();
                    \Log::info('Pre-group count', ['count' => $preGroupCount]);
                    
                    $results = $query->groupBy(
                            DB::raw('DATE(transactions.created_at)'), 
                            'seniors.category',
                            'transactions.number',
                            'transactions.customer_with_discount'
                        )  
                        ->orderBy('date', 'asc')
                        ->get();
                    
                    // DEBUGGING: Log detailed results
                    \Log::info('SR/PWD Query Results', [
                        'total_records' => $results->count(),
                        'sample_data' => $results->take(3)->map(function($item) {
                            return [
                                'date' => $item->date,
                                'category' => $item->category,
                                'transaction_count' => $item->transaction_count,
                                'pwd_total' => $item->pwd_total,
                                'customer_with_discount' => $item->customer_with_discount,
                                'total_sales' => $item->total_sales
                            ];
                        })->toArray()
                    ]);
                    
                    // Check if we have data
                    if ($results->isEmpty()) {
                        \Log::warning('SR/PWD query returned no results');
                        
                        // Log transactions that match criteria but don't have senior records
                        $transactionsWithoutSeniors = DB::table('transactions')
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<', $endDate)
                            ->where('status', '=', 'PAID')
                            ->where('order_status', '=', 'COMPLETED')
                            ->where('total', '!=', 0)
                            ->whereNotIn('id', function($query) {
                                $query->select('tran_id')->from('seniors');
                            })
                            ->when($userBranch, function($q) use ($userBranch) {
                                return $q->where('branch', $userBranch);
                            })
                            ->count();
                        
                        \Log::info('Transactions without senior records', ['count' => $transactionsWithoutSeniors]);
                    } else {
                        // Log summary statistics
                        $categories = $results->groupBy('category');
                        \Log::info('Results by category', [
                            'categories' => $categories->map(function($items, $category) {
                                return [
                                    'count' => $items->count(),
                                    'total_transactions' => $items->sum('transaction_count'),
                                    'total_pwd_amount' => $items->sum('pwd_total'),
                                    'avg_pwd_per_transaction' => $items->avg('pwd_total')
                                ];
                            })->toArray()
                        ]);
                    }
                    
                    // Generate chart data with debugging
                    \Log::info('Generating chart data for SR/PWD report');
                    $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                    
                    // Debug chart data
                    if ($chartData) {
                        \Log::info('Chart data generated successfully', [
                            'chart_type' => gettype($chartData),
                            'is_array' => is_array($chartData),
                            'is_collection' => $chartData instanceof \Illuminate\Support\Collection,
                            'structure' => is_array($chartData) ? array_keys($chartData) : 'N/A'
                        ]);
                        
                        // Log sample chart data
                        if (is_array($chartData) && isset($chartData[0])) {
                            \Log::debug('Sample chart data', [
                                'title' => $chartData[0]['title'] ?? 'No title',
                                'labels_count' => count($chartData[0]['labels'] ?? []),
                                'datasets_count' => count($chartData[0]['datasets'] ?? [])
                            ]);
                        }
                    } else {
                        \Log::warning('No chart data generated for SR/PWD report');
                    }
                    
                    $resultsCount = $results->count();
                    
                    \Log::info('SR/PWD Report Generation Completed', [
                        'results_count' => $resultsCount,
                        'has_chart_data' => !empty($chartData),
                        'view' => 'admin.reports.centralized'
                    ]);
                    
                    return view('admin.reports.centralized', compact( 
                        'branchs','settings', 'category', 'resultsCount', 'chartData', 'startDate', 'endDate', 'report'));
                        
                } catch (\Exception $e) {
                    \Log::error('SR/PWD Report Generation Error', [
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString(),
                        'category' => $category,
                        'report' => $report
                    ]);
                    
                    // Return with error flag
                    return view('admin.reports.centralized', compact(
                        'branchs','settings', 'category', 'startDate', 'endDate', 'report'))
                        ->with('error', 'Failed to generate SR/PWD report: ' . $e->getMessage());
                }
                //return view('admin.reports.totalsrpwd', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
                //leo end 5/8/25


            //start ken 11-17-2025    
            }elseif (($report == 'hoursale')){
                // Get hourly summary data for the main table
                $hourlySummary = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('HOUR(created_at) as hour'),
                        DB::raw('COUNT(id) as hourly_transactions'),
                        DB::raw('SUM(total) as hourly_sales'),
                        DB::raw('SUM(pwd) as hourly_discount') // PWD is the discount amount
                    )
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $hourlySummary->where('branch', $userBranch);
                }
                
                $hourlySummary = $hourlySummary->groupBy(DB::raw('DATE(created_at)'), DB::raw('HOUR(created_at)'))
                    ->orderBy('date', 'asc')
                    ->orderBy('hour', 'asc')
                    ->get();

                // Get all transactions for modal details - include pwd if it exists
                $allTransactions = DB::table('transactions')
                    ->select(
                        'transactions.created_at as date',
                        'transactions.number',
                        'transactions.total as total_sales',
                        'transactions.pwd as pwd_total' // Include PWD for modal display
                    )
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('order_status', '!=', 'CANCELLED');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $allTransactions->where('branch', $userBranch);
                }
                
                $allTransactions = $allTransactions->orderBy('created_at', 'asc')
                    ->get();

                $resultsCount = $hourlySummary->count();
                
                // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    

                // FIX: You need to pass the hourlySummary data to results for the table
                $results = $hourlySummary; // Add this line
                
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                // If you only want the first chart (sales distribution)
                if (is_array($chartData) && count($chartData) > 0) {
                    $chartData = $chartData[0]; // Get only the first chart
                }                
                return view('admin.reports.centralized', compact( 
                    'hourlySummary',
                    'allTransactions',
                    'branchs',
                    'results', // This will now contain the hourly summary data
                    'category', 
                    'report', 
                    'startDate', 
                    'endDate', 
                    'chartData', 
                    'grandTotals', 
                    'resultsCount',
                    'selectedBranch'
                ));
            // KEN ADDED eND HERE 20250623001 - KEN - ADDED 1-7-2026
            } elseif ($report == 'server') {
                // Server Report - Show transactions with server information
                $results = Transaction::with('thisCashier')
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('order_status', '!=', 'PREPARING');
                
                // ADDED: Branch filtering for non-admin users
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                }
                
                $results = $results->orderBy('transactions.id', 'desc')
                    ->get();

                $resultsCount = $results->count();
                
                // Generate chart data for server report
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                
                // Debug: Check what's being returned
                \Log::info('Server report chart data:', [
                    'has_chart_data' => !empty($chartData),
                    'results_count' => $results->count(),
                    'first_result' => $results->first() ? [
                        'has_thisCashier' => isset($results->first()->thisCashier),
                        'cashier_name' => $results->first()->thisCashier->name ?? 'not set'
                    ] : 'no results'
                ]);
                return view('admin.reports.centralized', compact( 
                    'branchs',
                    'results', 
                    'category', 
                    'report', 
                    'startDate', 
                    'endDate', 
                    'chartData', 
                    'grandTotals', 
                    'resultsCount',
                    'selectedBranch'
                ));
            }
            
            // LINE 3175: ken 20250623001 start of Xreading Sept 7, 2026
            elseif ($report == 'xreading') {
                // ============ X-READING REPORT - SIMPLIFIED ============
                
                // ---- 1. HOURLY SALES ----
                // Build the ordered quantities subquery (using ordered_duplicate)
                $orderedQuantities = DB::table('ordered_duplicate as o')
                    ->join('transactions as ot', function($join) {
                        $join->on('ot.local_id', '=', 'o.tran_id')
                            ->on('ot.store_code', '=', 'o.store_code');
                    })
                    ->select(
                        'o.tran_id',
                        'o.store_code',
                        DB::raw('SUM(o.quantity) as sold_quantity')
                    )
                    ->where('ot.created_at', '>=', $startDate)
                    ->where('ot.created_at', '<', $endDate)
                    ->where('ot.status', 'PAID')
                    ->where('ot.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('ot.store_code', $userBranch);
                    })
                    ->groupBy('o.tran_id', 'o.store_code');

                // Build the main hourly summary query with the join filter
                $hourlyQuery = DB::table('sales_report_transactions_duplicate as t')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), function($join) {
                        $join->on('t.report_id', '=', 'srt.local_id');
                    })
                    ->leftJoinSub($orderedQuantities, 'ordered_totals', function($join) {
                        $join->on('t.orig_local_id', '=', 'ordered_totals.tran_id')
                            ->on('t.store_code', '=', 'ordered_totals.store_code');
                    })
                    ->select(
                        DB::raw('DATE(t.created_at) as date'),
                        DB::raw('HOUR(t.created_at) as hour'),
                        DB::raw('COUNT(DISTINCT t.id) as hourly_transactions'),
                        DB::raw('SUM(t.total) as hourly_sales'),
                        DB::raw('SUM(t.pwd_amount) as hourly_discount'),
                        DB::raw('SUM(t.sc_amount) as service_charge_total'),
                        DB::raw('SUM(t.vat) as vat_total'),
                        DB::raw('SUM(t.vatSales) as vatable_sales_total'),
                        DB::raw('SUM(t.vatExemptSales) as vat_exempt_total'),
                        DB::raw('SUM(t.lessVatDeduction) as less_vat_total'),
                        DB::raw('SUM(t.total_customer) as hourly_customer'),
                        DB::raw('COALESCE(SUM(ordered_totals.sold_quantity), 0) as sold_quantity')
                    )
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', 'PAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->groupBy(DB::raw('DATE(t.created_at)'), DB::raw('HOUR(t.created_at)'))
                    ->orderBy('date', 'asc')
                    ->orderBy('hour', 'asc');

                // Execute the query
                $hourlySummary = $hourlyQuery->get();

                // Check if any deleted items were included (should be 0)
                $deletedItemsIncluded = DB::table('sales_report_transactions_duplicate as t')
                    ->leftJoin('sales_report_transaction as srt', 't.report_id', '=', 'srt.local_id')
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', 'PAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->where(function($q) {
                        $q->where('srt.is_deleted', '=', 1)
                        ->orWhereNull('srt.local_id');
                    })
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->count();

                // Simple log alert if deleted items are found
                if ($deletedItemsIncluded > 0) {
                    \Log::warning('DELETED ITEMS ISSUE: ' . $deletedItemsIncluded . ' deleted records were found and excluded from the report');
                } else {
                    \Log::info('No DELETED ITEM found');
                }

                // Calculate grand totals
                $hourlyGrandTotal = [
                    'transactions' => $hourlySummary->sum('hourly_transactions'),
                    'sales'        => $hourlySummary->sum('hourly_sales'),
                    'discount'     => $hourlySummary->sum('hourly_discount'),
                    'customer'     => $hourlySummary->sum('hourly_customer'),
                ];

                // ---- 2. CATEGORY & MENU SUMMARY ----
                $menuSalesData = DB::table('ordered_duplicate as o')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as str'), function($join) {
                        $join->on('o.report_id', '=', 'str.local_id');
                    })
                    ->join('sales_report_transactions_duplicate as srtd', function($join) {
                        $join->on('srtd.orig_local_id', '=', 'o.tran_id')
                            ->on('srtd.store_code', '=', 'o.store_code');
                    })
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), function($join) {
                        $join->on('srtd.report_id', '=', 'srt.local_id');
                    })
                    ->join('menus as m', 'o.menu_uuid', '=', 'm.is_uuid')
                    ->join('menu_categories as mc', 'm.category_id', '=', 'mc.id')
                    ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
                    ->select(
                        'mc.id as category_id',
                        'mc.name as category_name',
                        'm.id as menu_id',
                        'm.name as menu_name',
                        DB::raw('COALESCE(b.name, "N/A") as brand_name'),
                        DB::raw('SUM(o.amount) as total_amount'),
                        DB::raw('SUM(o.quantity) as total_quantity'),
                        DB::raw('COUNT(DISTINCT o.tran_id) as transaction_count')
                    )
                    ->where('o.created_at', '>=', $startDate)
                    ->where('o.created_at', '<', $endDate)
                    ->where('srtd.status', 'PAID')
                    ->where('srtd.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('o.store_code', $userBranch);
                    })
                    ->groupBy('mc.id', 'mc.name', 'm.id', 'm.name', 'b.name')
                    ->get();
                    
                // Build category structure (same as before)
                $categoryMap = [];
                $categoryGrandTotal = 0;

                foreach ($menuSalesData as $row) {
                    $catId = $row->category_id;

                    if (!isset($categoryMap[$catId])) {
                        $categoryMap[$catId] = (object) [
                            'category_id' => $row->category_id,
                            'category_name' => $row->category_name,
                            'total_amount' => 0,
                            'total_quantity' => 0,
                            'menus' => []
                        ];
                    }

                    $categoryMap[$catId]->total_amount += (float) $row->total_amount;
                    $categoryMap[$catId]->total_quantity += (float) $row->total_quantity;
                    $categoryGrandTotal += (float) $row->total_amount;

                    $categoryMap[$catId]->menus[] = (object) [
                        'menu_name' => $row->menu_name,
                        'menu_id' => $row->menu_id,
                        'brand_name' => $row->brand_name,
                        'quantity_sold' => (float) $row->total_quantity,
                        'menu_amount' => (float) $row->total_amount
                    ];
                }

                // Sort categories and menus
                $categorySummary = array_values($categoryMap);
                usort($categorySummary, function($a, $b) {
                    return $b->total_amount <=> $a->total_amount;
                });

                foreach ($categorySummary as $cat) {
                    usort($cat->menus, function($a, $b) {
                        return $b->menu_amount <=> $a->menu_amount;
                    });
                }

                // ---- 5. TOP SELLING ITEMS (by amount) ----
                $topSellingItems = $menuSalesData
                    ->sortByDesc('total_amount')
                    ->take(8)
                    ->values();

                // ---- 6. TOP FREQUENTLY SOLD ITEMS ----
                $topItems = $menuSalesData
                    ->sortByDesc('total_quantity')
                    ->take(8)
                    ->values();

                // ---- 3. AGGREGATED TRANSACTION SUMMARIES ----
                // PAID transactions summary
                $transactionData = DB::table('sales_report_transactions_duplicate as srtd')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), 'srtd.report_id', '=', 'srt.local_id')
                    ->select(
                        DB::raw('SUM(srtd.total) as total_sales'),
                        DB::raw('SUM(srtd.sc_amount) as total_sc_amount'),
                        DB::raw('SUM(srtd.vat) as total_vat'),
                        DB::raw('SUM(srtd.vatSales) as total_vatSales'),
                        DB::raw('SUM(srtd.vatExemptSales) as total_vatExemptSales'),
                        DB::raw('SUM(srtd.owner_discount_value) as total_discount'),
                        DB::raw('SUM(srtd.lessVatDeduction) as total_lessVatDeduction'),
                        DB::raw('SUM(srtd.pwd_amount) as total_pwd_discount')
                    )
                    ->where('srtd.created_at', '>=', $startDate)
                    ->where('srtd.created_at', '<', $endDate)
                    ->where('srtd.status', 'PAID')
                    ->where('srtd.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('srtd.store_code', $userBranch);
                    })
                    ->first();

                // LOG: Check PAID transactions
                \Log::info('PAID TRANSACTION DATA', [
                    'total_sales' => $transactionData->total_sales ?? 0,
                    'total_discount' => $transactionData->total_discount ?? 0,
                    'total_sc_amount' => $transactionData->total_sc_amount ?? 0,
                    'total_pwd_discount' => $transactionData->total_pwd_discount ?? 0,
                ]);

                // ---- UNPAID TRANSACTIONS - FIXED ----
                \Log::info('=== STARTING UNPAID TRANSACTION QUERY ===');
                \Log::info('Date Range:', ['start' => $startDate, 'end' => $endDate]);
                \Log::info('Branch Filter:', ['userBranch' => $userBranch]);

                // First, let's check if there are ANY UNPAID transactions in the date range
                $unpaidCheckQuery = DB::table('sales_report_transactions_duplicate as t')
                    ->select(
                        't.id',
                        't.number',
                        't.total',
                        't.status',
                        't.order_status',
                        't.created_at',
                        't.store_code'
                    )
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', '=', 'UNPAID')
                    ->where('t.order_status', '!=', 'CANCELLED');

                if ($userBranch) {
                    $unpaidCheckQuery->where('t.store_code', $userBranch);
                }

                $unpaidCheckResults = $unpaidCheckQuery->get();

                \Log::info('UNPAID TRANSACTIONS FOUND (RAW):', [
                    'count' => $unpaidCheckResults->count(),
                    'transactions' => $unpaidCheckResults->toArray()
                ]);

                // Now check with the srt join (sales_report_transaction) to see if any are being filtered out
                $unpaidWithSrtQuery = DB::table('sales_report_transactions_duplicate as t')
                    ->join('sales_report_transaction as srt', 't.report_id', '=', 'srt.local_id')
                    ->select(
                        't.id',
                        't.number',
                        't.total',
                        't.status',
                        't.order_status',
                        't.created_at',
                        't.store_code',
                        'srt.is_deleted',
                        'srt.local_id as srt_local_id'
                    )
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', '=', 'UNPAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->where('srt.is_deleted', '=', 0);

                if ($userBranch) {
                    $unpaidWithSrtQuery->where('t.store_code', $userBranch);
                }

                $unpaidWithSrtResults = $unpaidWithSrtQuery->get();

                \Log::info('UNPAID TRANSACTIONS WITH SRT JOIN (is_deleted=0):', [
                    'count' => $unpaidWithSrtResults->count(),
                    'transactions' => $unpaidWithSrtResults->toArray()
                ]);

                // FIXED: UNPAID transactions summary - SIMPLIFIED VERSION (no payment_logs join)
                $unpaidData = DB::table('sales_report_transactions_duplicate as t')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), 't.report_id', '=', 'srt.local_id')
                    ->select(
                        DB::raw('SUM(t.total) as total_unpaid'),
                        DB::raw('COUNT(DISTINCT t.number) as unpaid_count')
                    )
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', '=', 'UNPAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->first();

                \Log::info('UNPAID SUMMARY RESULT (FIXED):', [
                    'total_unpaid' => $unpaidData->total_unpaid ?? 0,
                    'unpaid_count' => $unpaidData->unpaid_count ?? 0,
                    'raw_data' => $unpaidData
                ]);

                // Get the actual SQL query for debugging
                $unpaidSql = DB::table('sales_report_transactions_duplicate as t')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), 't.report_id', '=', 'srt.local_id')
                    ->select(
                        DB::raw('SUM(t.total) as total_unpaid'),
                        DB::raw('COUNT(DISTINCT t.number) as unpaid_count')
                    )
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', '=', 'UNPAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->toSql();

                $unpaidBindings = DB::table('sales_report_transactions_duplicate as t')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), 't.report_id', '=', 'srt.local_id')
                    ->select(
                        DB::raw('SUM(t.total) as total_unpaid'),
                        DB::raw('COUNT(DISTINCT t.number) as unpaid_count')
                    )
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', '=', 'UNPAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->getBindings();

                \Log::info('UNPAID QUERY SQL:', [
                    'sql' => $unpaidSql,
                    'bindings' => $unpaidBindings
                ]);

                // CANCELLED transactions summary (with validation)
                $cancelledData = DB::table('sales_report_transactions_duplicate as srtd')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), 'srtd.report_id', '=', 'srt.local_id')
                    ->select(
                        DB::raw('SUM(srtd.total) as total_cancelled'),
                        DB::raw('COUNT(*) as cancelled_count')
                    )
                    ->where('srtd.created_at', '>=', $startDate)
                    ->where('srtd.created_at', '<', $endDate)
                    ->where('srtd.order_status', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('srtd.store_code', $userBranch);
                    })
                    ->first();

                \Log::info('CANCELLED DATA:', [
                    'total_cancelled' => $cancelledData->total_cancelled ?? 0,
                    'cancelled_count' => $cancelledData->cancelled_count ?? 0
                ]);

                // VOID transactions summary (with validation)
                $voidData = DB::table('sales_report_transactions_duplicate as srtd')
                    ->join(DB::raw('(SELECT DISTINCT local_id FROM sales_report_transaction WHERE is_deleted = 0 AND created_at >= "' . $startDate . '" AND created_at < "' . $endDate . '") as srt'), 'srtd.report_id', '=', 'srt.local_id')
                    ->select(
                        DB::raw('SUM(srtd.total) as total_void'),
                        DB::raw('COUNT(*) as void_count')
                    )
                    ->where('srtd.created_at', '>=', $startDate)
                    ->where('srtd.created_at', '<', $endDate)
                    ->where('srtd.status', 'VOID')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('srtd.store_code', $userBranch);
                    })
                    ->first();

                \Log::info('VOID DATA:', [
                    'total_void' => $voidData->total_void ?? 0,
                    'void_count' => $voidData->void_count ?? 0
                ]);

                // ---- 4. SUMMARY CALCULATIONS ----
                $grossSales = (float) ($transactionData->total_sales ?? 0) + 
                            (float) ($transactionData->total_discount ?? 0) + 
                            (float) ($transactionData->total_sc_amount ?? 0) + 
                            (float) ($transactionData->total_pwd_discount ?? 0);
                
                $lessReturns = (float) ($cancelledData->total_cancelled ?? 0);
                $totalDiscounts = (float) ($transactionData->total_discount ?? 0) + 
                                (float) ($transactionData->total_sc_amount ?? 0) + 
                                (float) ($transactionData->total_pwd_discount ?? 0);
                
                $serviceCharge = 0.00;
                $netSalesAfterSc = $grossSales + $serviceCharge;
                $salesCharge = (float) ($unpaidData->total_unpaid ?? 0);
                
                \Log::info('SALES CHARGE CALCULATION:', [
                    'salesCharge_from_unpaidData' => $salesCharge,
                    'unpaidData_total_unpaid' => $unpaidData->total_unpaid ?? 'NULL',
                    'unpaidData_unpaid_count' => $unpaidData->unpaid_count ?? 'NULL'
                ]);
                
                $vat12 = (float) ($transactionData->total_vat ?? 0);
                $vatableSales = (float) ($transactionData->total_vatSales ?? 0);
                $vatExemptSales = (float) ($transactionData->total_vatExemptSales ?? 0);
                $discounts = (float) ($transactionData->total_discount ?? 0) + 
                            (float) ($transactionData->total_sc_amount ?? 0);
                $lessVatSeniorPwd = (float) ($transactionData->total_lessVatDeduction ?? 0);
                $discountSeniorPwd = (float) ($transactionData->total_pwd_discount ?? 0);
                $grandTotal = $netSalesAfterSc - $totalDiscounts;

                // ---- 9. CASHIER RUNNING TOTAL ----
                $runningTotalTransactions = DB::table('sales_report_transactions_duplicate as srtd')
                    ->select(DB::raw('SUM(srtd.total) as running_total'))
                    ->where('srtd.status', 'PAID')
                    ->where('srtd.order_status', '!=', 'CANCELLED')
                    ->whereNotExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('sales_report_transaction as srt')
                            ->whereRaw('srt.local_id = srtd.report_id')
                            ->where('srt.is_deleted', '=', 1);
                    })
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('srtd.store_code', $userBranch);
                    })
                    ->first();
                $runningTotal = (float) ($runningTotalTransactions->running_total ?? 0);

                $beginningTotalTransactions = DB::table('sales_report_transactions_duplicate as srtd')
                    ->select(DB::raw('SUM(srtd.total) as beginning_total'))
                    ->where('srtd.created_at', '<', $startDate)
                    ->where('srtd.status', 'PAID')
                    ->where('srtd.order_status', '!=', 'CANCELLED')
                    ->whereNotExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('sales_report_transaction as srt')
                            ->whereRaw('srt.local_id = srtd.report_id')
                            ->where('srt.is_deleted', '=', 1);
                    })
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('srtd.store_code', $userBranch);
                    })
                    ->first();
                $beginningTotal = (float) ($beginningTotalTransactions->beginning_total ?? 0);
                $endingTotal = $beginningTotal + $grandTotal;

                // Check for deleted transactions that were excluded
                if ($userBranch) {
                    $deletedCount = DB::table('sales_report_transactions_duplicate as srtd')
                        ->join('sales_report_transaction as srt', 'srtd.report_id', '=', 'srt.local_id')
                        ->where('srt.is_deleted', '=', 1)
                        ->where('srtd.store_code', $userBranch)
                        ->where('srtd.status', 'PAID')
                        ->where('srtd.order_status', '!=', 'CANCELLED')
                        ->count();
                    
                    if ($deletedCount > 0) {
                        \Log::warning('DELETED ITEMS ISSUE: ' . $deletedCount . ' deleted records were found and excluded from the report');
                    }
                }
                
                $xreadingSummary = [
                    'gross_sales' => $grossSales,
                    'less_returns' => $lessReturns,
                    'net_sales_after_sc' => $netSalesAfterSc,
                    'sales_charge' => $salesCharge,
                    'vat_12' => $vat12,
                    'vatable_sales' => $vatableSales,
                    'vat_exempt_sales' => $vatExemptSales,
                    'service_charge' => $serviceCharge,
                    'discounts' => $discounts,
                    'less_vat_senior_pwd' => $lessVatSeniorPwd,
                    'discount_senior_pwd' => $discountSeniorPwd,
                    'grand_total' => $grandTotal,
                    'runningTotal' => $runningTotal,
                    'beginningTotal' => $beginningTotal,
                    'endingTotal' => $endingTotal,        
                ];

                // ---- 7. MODE OF PAYMENT SUMMARY ----
                $mopColumn = null;
                if (Schema::hasTable('mode_of_payment')) {
                    if (Schema::hasColumn('mode_of_payment', 'mop_name')) {
                        $mopColumn = 'mop_name';
                    } elseif (Schema::hasColumn('mode_of_payment', 'name')) {
                        $mopColumn = 'name';
                    }
                }

                // Get detailed transactions with payment logs for verification
                $detailedTransactions = DB::table('sales_report_transactions_duplicate as t')
                    ->join('sales_report_transaction as srt', function($join) use ($startDate, $endDate) {
                        $join->on('t.report_id', '=', 'srt.local_id')
                            ->where('srt.is_deleted', 0)
                            ->where('srt.created_at', '>=', $startDate)
                            ->where('srt.created_at', '<', $endDate);
                    })
                    ->leftJoin('payment_logs as pl', function($join) use ($startDate, $endDate) {
                        $join->on('t.orig_local_id', '=', 'pl.tran_id')
                            ->on('t.store_code', '=', 'pl.store_code')
                            ->where('pl.created_at', '>=', $startDate)
                            ->where('pl.created_at', '<', $endDate);
                    })
                    ->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('t.status', 'PAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->select(
                        't.id',
                        't.number',
                        't.orig_local_id',
                        't.report_id',
                        't.store_code',
                        't.status',
                        't.order_status',
                        't.created_at as transaction_created_at',
                        'pl.id as payment_log_id',
                        'pl.amount as payment_amount',
                        'pl.label as payment_label',
                        'pl.mop_id',
                        'pl.tran_id as payment_tran_id',
                        'pl.created_at as payment_created_at',
                        DB::raw('UPPER(TRIM(COALESCE(mop.' . ($mopColumn ?? 'name') . ', pl.label, "CASH"))) as mop_name')
                    )
                    ->orderBy('t.number', 'ASC')
                    ->orderBy('pl.created_at', 'ASC')
                    ->get();

                // Check for mismatched data (transactions with orig_local_id but no matching payment_logs in date range)
                $transactionsWithNoPayments = $detailedTransactions
                    ->groupBy('number')
                    ->filter(function($group) {
                        return $group->whereNotNull('payment_log_id')->count() == 0;
                    });

                if ($transactionsWithNoPayments->count() > 0) {
                    \Log::warning('MOP SUMMARY: ' . $transactionsWithNoPayments->count() . ' transaction(s) have no payment logs within the date range');
                    foreach ($transactionsWithNoPayments as $number => $items) {
                        $first = $items->first();
                        \Log::warning('  └─ Transaction: ' . $number . ' | Orig Local ID: ' . $first->orig_local_id . ' | Store: ' . $first->store_code);
                    }
                }

                // PAID transactions - regular MOP summary
                $paymentSummary = DB::table('payment_logs as pl')  
                    ->join('sales_report_transactions_duplicate as t', function($join) {
                        $join->on('pl.tran_id', '=', 't.orig_local_id')
                            ->on('pl.store_code', '=', 't.store_code');
                    })
                    ->join('sales_report_transaction as srt', function($join) use ($startDate, $endDate) {
                        $join->on('t.report_id', '=', 'srt.local_id')
                            ->where('srt.is_deleted', 0)
                            ->where('srt.created_at', '>=', $startDate)
                            ->where('srt.created_at', '<', $endDate);
                    })
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('pl.created_at', '>=', $startDate)
                    ->where('pl.created_at', '<', $endDate)
                    ->where('t.status', 'PAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->when($mopColumn && Schema::hasTable('mode_of_payment') && Schema::hasColumn('payment_logs', 'mop_id'), function($q) use ($mopColumn) {
                        return $q->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                                ->select(
                                    DB::raw('UPPER(TRIM(COALESCE(mop.' . $mopColumn . ', pl.label, "CASH"))) as mop'),
                                    DB::raw('SUM(pl.amount) as total_amount'),
                                    DB::raw('COUNT(pl.id) as total_count'),
                                    DB::raw('COUNT(DISTINCT t.number) as transaction_count')
                                )
                                ->groupBy(DB::raw('UPPER(TRIM(COALESCE(mop.' . $mopColumn . ', pl.label, "CASH")))'));
                    })
                    ->when(!$mopColumn && Schema::hasColumn('payment_logs', 'label'), function($q) {
                        return $q->select(
                                    DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH"))) as mop'),
                                    DB::raw('SUM(pl.amount) as total_amount'),
                                    DB::raw('COUNT(pl.id) as total_count'),
                                    DB::raw('COUNT(DISTINCT t.number) as transaction_count')
                                )
                                ->groupBy(DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH")))'));
                    })
                    ->when(!$mopColumn && !Schema::hasColumn('payment_logs', 'label'), function($q) {
                        return $q->select(
                                    DB::raw('"CASH" as mop'),
                                    DB::raw('SUM(pl.amount) as total_amount'),
                                    DB::raw('COUNT(pl.id) as total_count'),
                                    DB::raw('COUNT(DISTINCT t.number) as transaction_count')
                                );
                    })
                    ->orderByDesc('total_amount')
                    ->get();

                $paidGrandTotal = $paymentSummary->sum('total_amount');
                $paidTotalCount = $paymentSummary->sum('total_count');

                // Add VOID/CANCELLED and Sales Charge as additional rows
                $voidCancelledAmount = (float) ($voidData->total_void ?? 0) + (float) ($cancelledData->total_cancelled ?? 0);
                $voidCancelledCount = (int) ($voidData->void_count ?? 0) + (int) ($cancelledData->cancelled_count ?? 0);

                $salesChargeAmount = (float) ($unpaidData->total_unpaid ?? 0);
                $salesChargeCount = (int) ($unpaidData->unpaid_count ?? 0);

                \Log::info('SALES CHARGE ROW DATA:', [
                    'salesChargeAmount' => $salesChargeAmount,
                    'salesChargeCount' => $salesChargeCount,
                    'unpaidData_total_unpaid' => $unpaidData->total_unpaid ?? 0,
                    'unpaidData_unpaid_count' => $unpaidData->unpaid_count ?? 0
                ]);

                $voidRow = (object) [
                    'mop' => 'VOID/CANCELLED',
                    'total_amount' => $voidCancelledAmount,
                    'total_count' => $voidCancelledCount,
                    'is_special' => true
                ];

                $salesChargeRow = (object) [
                    'mop' => 'SALES CHARGE (UNPAID)',
                    'total_amount' => $salesChargeAmount,
                    'total_count' => $salesChargeCount,
                    'is_special' => true
                ];

                $paymentSummary->push($voidRow);
                $paymentSummary->push($salesChargeRow);

                $allGrandTotal = $paymentSummary->sum('total_amount');
                $allTotalCount = $paymentSummary->sum('total_count');

                // ---- 8. CASHIER PAYMENT BREAKDOWN ----
                $cashierPayments = DB::table('payment_logs as pl')
                    ->join('sales_report_transactions_duplicate as t', function($join) {
                        $join->on('pl.tran_id', '=', 't.orig_local_id')
                            ->on('pl.store_code', '=', 't.store_code');
                    })
                    ->join('sales_report_transaction as srt', function($join) use ($startDate, $endDate) {
                        $join->on('t.report_id', '=', 'srt.local_id')
                            ->where('srt.is_deleted', 0)
                            ->where('srt.created_at', '>=', $startDate)
                            ->where('srt.created_at', '<', $endDate);
                    })
                    ->join('users as u', 't.cashier', '=', 'u.id')
                    ->where('t.created_at', '>=', $startDate)
                    ->where('t.created_at', '<', $endDate)
                    ->where('pl.created_at', '>=', $startDate)
                    ->where('pl.created_at', '<', $endDate)
                    ->where('t.status', 'PAID')
                    ->where('t.order_status', '!=', 'CANCELLED')
                    ->when($userBranch, function($q) use ($userBranch) {
                        return $q->where('t.store_code', $userBranch);
                    })
                    ->when($mopColumn && Schema::hasTable('mode_of_payment') && Schema::hasColumn('payment_logs', 'mop_id'), function($q) use ($mopColumn) {
                        return $q->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                                ->select(
                                    DB::raw("CONCAT_WS(' ', TRIM(u.firstname), TRIM(u.lastname)) as cashier_name"),
                                    DB::raw('UPPER(TRIM(COALESCE(mop.' . $mopColumn . ', pl.label, "CASH"))) as mop'),
                                    DB::raw('COUNT(pl.id) as total_count'),
                                    DB::raw('SUM(pl.amount) as total_amount'),
                                    DB::raw('COUNT(DISTINCT t.number) as transaction_count')
                                )
                                ->groupBy('u.id', 'u.username', 'u.firstname', 'u.lastname', DB::raw('UPPER(TRIM(COALESCE(mop.' . $mopColumn . ', pl.label, "CASH")))'));
                    })
                    ->when(!$mopColumn && Schema::hasColumn('payment_logs', 'label'), function($q) {
                        return $q->select(
                                    DB::raw("CONCAT_WS(' ', TRIM(u.firstname), TRIM(u.lastname)) as cashier_name"),
                                    DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH"))) as mop'),
                                    DB::raw('COUNT(pl.id) as total_count'),
                                    DB::raw('SUM(pl.amount) as total_amount'),
                                    DB::raw('COUNT(DISTINCT t.number) as transaction_count')
                                )
                                ->groupBy('u.id', 'u.username', 'u.firstname', 'u.lastname', DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH")))'));
                    })
                    ->when(!$mopColumn && !Schema::hasColumn('payment_logs', 'label'), function($q) {
                        return $q->select(
                                    DB::raw("CONCAT_WS(' ', TRIM(u.firstname), TRIM(u.lastname)) as cashier_name"),
                                    DB::raw('"CASH" as mop'),
                                    DB::raw('COUNT(pl.id) as total_count'),
                                    DB::raw('SUM(pl.amount) as total_amount'),
                                    DB::raw('COUNT(DISTINCT t.number) as transaction_count')
                                )
                                ->groupBy('u.id', 'u.username', 'u.firstname', 'u.lastname');
                    })
                    ->orderBy('u.username')
                    ->get();

                // Format cashier data
                $cashierData = [];
                foreach ($cashierPayments as $payment) {
                    $cashierName = $payment->cashier_name ?? 'UNKNOWN';
                    if (!isset($cashierData[$cashierName])) {
                        $cashierData[$cashierName] = [
                            'payments' => [],
                            'total' => 0,
                            'transactions' => 0
                        ];
                    }
                    $cashierData[$cashierName]['payments'][] = [
                        'mop' => $payment->mop,
                        'count' => $payment->total_count,
                        'amount' => (float) $payment->total_amount,
                        'transaction_count' => (int) ($payment->transaction_count ?? 0)
                    ];
                    $cashierData[$cashierName]['total'] += (float) $payment->total_amount;
                    $cashierData[$cashierName]['transactions'] += (int) ($payment->transaction_count ?? 0);
                }
                ksort($cashierData);

                // ---- FINAL LOGGING ----
                \Log::info('=== FINAL SUMMARY DATA PASSED TO VIEW ===');
                \Log::info('xreadingSummary:', $xreadingSummary);
                \Log::info('paymentSummary (MOP data):', [
                    'count' => $paymentSummary->count(),
                    'data' => $paymentSummary->toArray()
                ]);

                // Specifically log the Sales Charge row
                $salesChargeRowData = $paymentSummary->filter(function($item) {
                    return $item->mop === 'SALES CHARGE (UNPAID)';
                })->first();

                \Log::info('SALES CHARGE ROW IN PAYMENT SUMMARY:', [
                    'exists' => !is_null($salesChargeRowData),
                    'data' => $salesChargeRowData ? [
                        'mop' => $salesChargeRowData->mop,
                        'total_amount' => $salesChargeRowData->total_amount,
                        'total_count' => $salesChargeRowData->total_count,
                        'is_special' => $salesChargeRowData->is_special ?? false
                    ] : null
                ]);

                $resultsCount = $hourlySummary->count();
                $results = $hourlySummary;

                return view('admin.reports.centralized', compact(
                    'branchs', 'results', 'category', 'report', 'startDate', 'endDate', 'settings',
                    'resultsCount', 'selectedBranch',
                    'hourlySummary', 'hourlyGrandTotal',
                    'categorySummary', 'categoryGrandTotal',
                    'paymentSummary', 'allGrandTotal', 'allTotalCount', 'paidTotalCount', 'paidGrandTotal',
                    'topItems', 'topSellingItems', 'xreadingSummary', 'cashierData', 'selectedBranchName'
                ));
            }              
            // LINE 3928:ken 20250623001 END of Xreading Sept 7, 2026
            // KEN ADDED end HERE 20250623001 - KEN - ADDED 1-7-2026
            //ken 11-17-2025
            // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    
                $grandTotals = [
                    'total' => 0,
                    'discount' => 0,
                    'pwd' => 0,
                    'lessVat' => 0,
                    'vatSales' => 0,
                    'vat' => 0,
                    'vatExempt' => 0,
                    'sc' => 0,
                    'net' => 0,
                    'served_total' => 0,
                    'served_discount' => 0,
                    'served_pwd' => 0,
                    'served_lessVat' => 0,
                    'served_vatSales' => 0,
                    'served_vat' => 0,
                    'served_vatExempt' => 0,
                    'served_sc' => 0,
                    'served_net' => 0,
                    'not_served_total' => 0,
                    'not_served_discount' => 0,
                    'not_served_pwd' => 0,
                    'not_served_lessVat' => 0,
                    'not_served_vatSales' => 0,
                    'not_served_vat' => 0,
                    'not_served_vatExempt' => 0,
                    'not_served_sc' => 0,
                    'not_served_net' => 0
                ];
                
                // Calculate totals
                foreach ($results as $transaction) {
                    $grandTotals['total'] += $transaction->total ?? 0;
                    $grandTotals['discount'] += $transaction->lime_discount ?? 0;
                    $grandTotals['pwd'] += $transaction->pwd ?? 0;
                    $grandTotals['vat'] += $transaction->vat ?? 0;
                    $grandTotals['vatSales'] += $transaction->vatSales ?? 0;
                    $grandTotals['vatExempt'] += $transaction->vatExemptSales ?? 0;
                    $grandTotals['lessVat'] += $transaction->lessVatDeduction ?? 0;
                    $grandTotals['sc'] += $transaction->service_charge ?? 0;
                    
                    // Check if served/audited
                    $isServed = isset($transaction->is_audit) ? $transaction->is_audit : 
                            (isset($transaction->order_status) && $transaction->order_status == 'SERVED');
                    
                    if ($isServed) {
                        $grandTotals['served_total'] += $transaction->total ?? 0;
                        $grandTotals['served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['served_sc'] += $transaction->service_charge ?? 0;
                    } else {
                        $grandTotals['not_served_total'] += $transaction->total ?? 0;
                        $grandTotals['not_served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['not_served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['not_served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['not_served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['not_served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['not_served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['not_served_sc'] += $transaction->service_charge ?? 0;
                    }
                }
                
                // Calculate net totals
                $grandTotals['net'] = $grandTotals['total'] - ($grandTotals['sc'] + $grandTotals['vat']);
                $grandTotals['served_net'] = $grandTotals['served_total'] - ($grandTotals['served_sc'] + $grandTotals['served_vat']);
                $grandTotals['not_served_net'] = $grandTotals['not_served_total'] - ($grandTotals['not_served_sc'] + $grandTotals['not_served_vat']);
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                return view('admin.reports.centralized', compact( 
                'branchs',
                'results', 
                'category', 
                'report', 
                'startDate', 
                'endDate', 
                'chartData', 
                'grandTotals', 
                'resultsCount',
                'selectedBranch'
                ));
            // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025            
        } else if ($category == 'expenses') {
            if ($report == 'unpaid') {
                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    // ->where('type', 'INCOMING')
                    ->where('payment_class',  '=', 0);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->orderBy('created_at', 'desc')
                    ->get();

                $results = DB::table('inventory_transactions')
                    ->select(
                        'inventory_transactions.id as id',
                        'inventory_transactions.created_at as date', 
                        'inventory_transactions.inv_id as inv_id', 
                        'inventories.name as nn', 
                        'inventory_transactions.amount as amount', 
                        'inventory_transactions.quantity as quantity', 
                        'inventory_transactions.remarks as remarks', 
                        DB::raw("CONCAT(users.lastname, ', ', users.firstname) as cashier")
                    )
                    ->leftJoin('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
                    ->join('users', 'inventory_transactions.user_id', '=', 'users.id')
                    ->where('inventory_transactions.created_at', '>=', $startDate)
                    ->where('inventory_transactions.created_at', '<', $endDate)
                    ->where('inventory_transactions.type', 'INCOMING')
                    ->where('inventory_transactions.is_paid', 0)
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('type', 'INCOMING')
                    ->where('payment_class',  '=', 0)
                    ->orderBy('inventory_transactions.created_at', 'desc')
                    ->orderBy('inventory_transactions.id', 'asc')
                    ->get();
            } elseif ($report == 'cos') {
                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    // ->where('type', 'INCOMING')
                    ->orderBy('inventory_transactions.e_id', 'asc')
                    ->where('exp_type',  '=', '0')
                    ->where('is_deleted',  0);
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->orderBy('created_at', 'desc')
                    ->get();
            
            } elseif ($report == 'ooe') {
                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    // ->where('type', 'INCOMING')
                    ->where('exp_type',  '=', '1');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->orderBy('created_at', 'desc')
                    ->get();


            } else {

                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                // if ($userBranch) {
                //     $results->where('branch', $userBranch);
                // }
                
                $results = $results->orderBy('created_at', 'desc')
                    ->get();
            }

            $resultsCount = $results->count();

            if ($report == 'summary') {
                    // Original query to get individual records
                    $detailedResults = DB::table('inventory_transactions')
                        ->select('inventory_transactions.id as id', 'inventory_transactions.is_paid as is_paid', 
                                'inventory_transactions.created_at as date', 'inventory_transactions.inv_id as inv_id', 
                                'inventories.name as nn', 'inventory_transactions.amount as amount', 
                                'inventory_transactions.quantity as quantity', 'inventory_transactions.remarks as remarks', 
                                DB::raw("CONCAT(users.lastname, ', ', users.firstname) as cashier"), 
                                'inventory_transactions.exp_type as exp_type', 
                                'inventory_transactions.unit as unit', 'inventory_transactions.unit_price as unit_price')
                        ->leftJoin('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
                        ->join('users', 'inventory_transactions.user_id', '=', 'users.id')
                        ->where('inventory_transactions.created_at', '>=', $startDate)
                        ->where('inventory_transactions.created_at', '<', $endDate);
                    
                    // ADDED: Branch filtering
                    if ($userBranch) {
                        $detailedResults->where('inventory_transactions.branch', $userBranch);
                    }
                    
                    $detailedResults = $detailedResults->orderBy('inventory_transactions.created_at', 'desc')
                        ->get();
                    
                    // NEW: Group by date for summary view
                    $results = DB::table('inventory_transactions')
                        ->select(
                            DB::raw('DATE(created_at) as date'),
                            DB::raw('SUM(amount) as total_amount'),
                            DB::raw('SUM(quantity) as total_items'),
                            DB::raw('COUNT(id) as transaction_count')
                        )
                        ->where('created_at', '>=', $startDate)
                        ->where('created_at', '<', $endDate);
                    
                    // ADDED: Branch filtering
                    if ($userBranch) {
                        $results->where('branch', $userBranch);
                    }
                    
                    $results = $results->groupBy(DB::raw('DATE(created_at)'))
                        ->orderBy('date', 'desc')
                        ->get();
                    
                    // Prepare grand totals for the summary
                    $grandTotals = [
                        'total_amount' => 0,
                        'total_items' => 0,
                        'total_transactions' => 0
                    ];
                    
                    foreach ($results as $row) {
                        $grandTotals['total_amount'] += $row->total_amount ?? 0;
                        $grandTotals['total_items'] += $row->total_items ?? 0;
                        $grandTotals['total_transactions'] += $row->transaction_count ?? 0;
                    }
                    
                    // Keep the detailed results for other calculations if needed
                    $chartData = ChartHelper::generateFromTableData($detailedResults, $category, $report);
                    
                    return view('admin.reports.centralized', compact( 
                        'branchs',
                        'results', // This now contains grouped by date data
                        'category', 
                        'report', 
                        'startDate', 
                        'endDate', 
                        'chartData', 
                        'grandTotals', 
                        'resultsCount',
                        'selectedBranch'
                    ));
                    // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                }                
        } else if ($category == 'both') {
            $salesQuery = DB::table('transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as stotal'),
                    DB::raw('SUM(service_charge) as sctotal'),
                    DB::raw('0 as etotal')
                )
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                // ->whereBetween('created_at', [$start, $end])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('date');

            $expensesQuery = DB::table('inventory_transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('0 as stotal'),
                    DB::raw('0 as sctotal'),
                    DB::raw('SUM(amount) as etotal')
                )
                ->where('type', 'INCOMING')
                // ->whereBetween('created_at', [$start, $end])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('date');

            $results = $salesQuery->union($expensesQuery)->orderBy('date')->get();
            $results = $results->groupBy('date')->map(function ($item) {
                return [
                    'date' => $item[0]->date,
                    'stotal' => $item->sum('stotal'),
                    'sctotal' => $item->sum('sctotal'),
                    'etotal' => $item->sum('etotal'),
                ];
            })->values();
                    // Keep the detailed results for other calculations if needed
                    $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                    
                    return view('admin.reports.centralized', compact( 
                        'branchs',
                        'results', // This now contains grouped by date data
                        'category', 
                        'report', 
                        'startDate', 
                        'endDate', 
                        'chartData', 
                        'grandTotals', 
                        'resultsCount',
                        'selectedBranch'
                    ));
        } else if ($category == 'inventory'){ 
            $categoryId = $request->category_id;
            if ($report == 'logs') {
                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('type', 'OUTGOING');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->orderBy('id', 'desc')
                    ->get();

                // Generate chart data for inventory logs
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                
                $resultsCount = $results->count();
                
                // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    
                $grandTotals = [
                    'total' => 0,
                    'transactions' => $resultsCount
                ];
                
                // Calculate totals for inventory logs
                foreach ($results as $transaction) {
                    // Calculate quantity change
                    $quantityBefore = floatval($transaction->quantity_before ?? 0);
                    $quantityAfter = floatval($transaction->quantity_after ?? 0);
                    $quantityChange = abs($quantityAfter - $quantityBefore);
                    
                    if ($quantityAfter > $quantityBefore) {
                        $grandTotals['total_in'] = ($grandTotals['total_in'] ?? 0) + $quantityChange;
                    } else {
                        $grandTotals['total_out'] = ($grandTotals['total_out'] ?? 0) + $quantityChange;
                    }
                }

                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                // If you only want the first chart (sales distribution)
                if (is_array($chartData) && count($chartData) > 0) {
                    $chartData = $chartData[0]; // Get only the first chart
                }      
                return view('admin.reports.centralized', compact( 
                    'branchs',
                    'results', 
                    'category', 
                    'report', 
                    'startDate', 
                    'endDate', 
                    'chartData', 
                    'grandTotals', 
                    'resultsCount',
                    'selectedBranch'
                ));
            }  else if ($report == 'stock') {
                $results = Menu::with('category')->orderBy('quantity', 'desc');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->get();
            }   
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025         
        } else if ($category == 'menu') {
            if ($report == 'rank') {
                // 240506001 - leo - 11/17/25 - start
                $categoryId = $request->category_id;

                $results = DB::table('ordered')
                    ->select('ordered.menu_uuid', 'menus.name', DB::raw('SUM(ordered.quantity) as quantity'), 'menus.price', DB::raw('SUM(ordered.quantity * menus.price) as total'))
                    ->join('menus', 'ordered.menu_uuid', '=', 'menus.is_uuid')
                    ->join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
                    ->join('transactions', 'ordered.tran_id', '=', 'transactions.local_id')
                    ->groupBy('ordered.menu_uuid', 'menus.name','menus.price')
                    ->where('ordered.store_code', $storeCode)
                    ->where('menus.store_code', $storeCode)
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate)
                    ->when($categoryId, function ($results, $categoryId) {
                        return $results->where('menu_categories.id', $categoryId);
                    });
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                }
                
                $results = $results->orderBy('quantity', 'desc')
                    ->get();
                
                    
                
                $totalAmount = $results->sum('total'); 

                $menucats = DB::table('menu_categories')
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                $resultsCount = $results->count();
            // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    

                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                return view('admin.reports.centralized', compact( 
                'branchs',
                'results', 
                'category', 
                'report', 
                'startDate', 
                'endDate', 
                'chartData', 
                'grandTotals', 
                'resultsCount',
                'selectedBranch',
                'menucats',
                'categoryId',
                'totalAmount'
                ));
            // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 

                // 240506001 - leo - 11/17/25 - end

                $results = DB::table('ordered')
                    ->select('ordered.menu_id', 'menus.name', DB::raw('SUM(ordered.quantity) as quantity'))
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->join('transactions', 'ordered.tran_id', '=', 'transactions.id')
                    ->groupBy('ordered.menu_id', 'menus.name')
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('transactions.branch_code', $userBranch);
                }
                
                
            } else if ($report == 'stock') {
                $results = Menu::with('category')->orderBy('quantity', 'desc');
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
                $results = $results->get();
            }

            $resultsCount = $results->count();
        } else if ($category == 'invmenu') {
            $results = [];
            //$menus = Menu::with('category')->where('is_deleted', 0)->orderBy('name', 'asc')->paginate(100);
            $menus = Menu::with('category')->orderBy('quantity', 'desc');
            
            // ADDED: Branch filtering
            if ($userBranch) {
                $menus->where('branch', $userBranch);
            }
            
            $menus = $menus->get();
            
            $raws = Inventory::with('category')->orderBy('quantity', 'desc');
            
            // ADDED: Branch filtering
            if ($userBranch) {
                $raws->where('branch', $userBranch);
            }
            
            $raws = $raws->get();
            
            foreach ($menus as $menu) {
                $result = new stdClass();
                $result->name = $menu->name;
                $result->category = $menu->category->name;
                $result->quantity = $menu->quantity;
                $results[] = $result;
            }
            // foreach ($raws as $raw) {
            //     $result = new stdClass();
            //     $result->name = $raw->name;
            //     $result->category = $raw->category->name;
            //     $result->quantity = $raw->quantity;
            //     $results[] = $result;
            // }
            $resultsCount = count($results);
        } else if ($category == 'waste') {
            if ($report == 'raw') {
                $results = DB::table('wastes')
                    ->select('wastes.iid', 'wastes.created_at', 'wastes.quantity', 'inventories.name', 'wastes.cost','wastes.waste_remarks')
                    ->join('inventories', 'wastes.iid', '=', 'inventories.id')
                    ->where('wastes.on', 'INVENTORY')
                    ->where('wastes.created_at', '>=', $startDate)
                    ->where('wastes.created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('inventories.branch', $userBranch);
                }
                
                $results = $results->orderBy('wastes.created_at', 'desc')
                    ->get();
            } else if ($report == 'menu') {
                $results = DB::table('wastes')
                    ->select('wastes.iid', 'wastes.created_at', 'wastes.quantity', 'menus.name', 'wastes.cost','wastes.waste_remarks')
                    ->join('menus', 'wastes.iid', '=', 'menus.id')
                    ->where('wastes.on', 'MENU')
                    ->where('wastes.created_at', '>=', $startDate)
                    ->where('wastes.created_at', '<', $endDate);
                
                // ADDED: Branch filtering
                if ($userBranch) {
                    $results->where('menus.branch', $userBranch);
                }
                
                $results = $results->orderBy('wastes.created_at', 'desc')
                    ->get();
            }

            $resultsCount = $results->count();
        } else if ($category == 'remit') {
            $sales = Transaction::select(DB::raw('SUM(total) as total'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('mode_of_payment', 'CASH');
            
            // ADDED: Branch filtering
            if ($userBranch) {
                $sales->where('branch', $userBranch);
            }
            
            $sales = $sales->get();

            $servicecharge = Transaction::select(DB::raw('SUM(service_charge) as total'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('mode_of_payment', 'CASH');
            
            // ADDED: Branch filtering
            if ($userBranch) {
                $servicecharge->where('branch_code', $userBranch);
            }
            
            $servicecharge = $servicecharge->get();

            $expenses = InventoryTransaction::select(DB::raw('SUM(amount) as amount'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('type', 'INCOMING')
                ->where('is_paid', 1);
            
            // ADDED: Branch filtering
            if ($userBranch) {
                $expenses->where('branch_code', $userBranch);
            }
            
            $expenses = $expenses->get();

            $results = [];

            $salesObj = new stdClass();
            $salesObj->name = 'Sales';
            $salesObj->amount = $sales[0]->total ?? 0;
            $results[] = $salesObj;

            $SCObj = new stdClass();
            $SCObj->name = 'Performance Incentives';
            $SCObj->amount = ($servicecharge[0]->total ?? 0) / 2;
            $results[] = $SCObj;

            $expObj = new stdClass();
            $expObj->name = 'Expenses';
            $expObj->amount = $expenses[0]->amount ?? 0;
            $results[] = $expObj;

            $totalObj = new stdClass();
            $totalObj->name = 'Total';
            $texpenses = ($expenses[0]->amount ?? 0) + (($servicecharge[0]->total ?? 0) / 2);
            $totalObj->amount = ($sales[0]->total ?? 0) - $texpenses;
            $results[] = $totalObj;

            $resultsCount = count($results);
        }
            // KEN ADDED START HERE 20250623001 - KEN - ADDED 12-21-2025    
                $grandTotals = [
                    'total' => 0,
                    'discount' => 0,
                    'pwd' => 0,
                    'lessVat' => 0,
                    'vatSales' => 0,
                    'vat' => 0,
                    'vatExempt' => 0,
                    'sc' => 0,
                    'net' => 0,
                    'served_total' => 0,
                    'served_discount' => 0,
                    'served_pwd' => 0,
                    'served_lessVat' => 0,
                    'served_vatSales' => 0,
                    'served_vat' => 0,
                    'served_vatExempt' => 0,
                    'served_sc' => 0,
                    'served_net' => 0,
                    'not_served_total' => 0,
                    'not_served_discount' => 0,
                    'not_served_pwd' => 0,
                    'not_served_lessVat' => 0,
                    'not_served_vatSales' => 0,
                    'not_served_vat' => 0,
                    'not_served_vatExempt' => 0,
                    'not_served_sc' => 0,
                    'not_served_net' => 0
                ];
                
                // Calculate totals
                foreach ($results as $transaction) {
                    $grandTotals['total'] += $transaction->total ?? 0;
                    $grandTotals['discount'] += $transaction->lime_discount ?? 0;
                    $grandTotals['pwd'] += $transaction->pwd ?? 0;
                    $grandTotals['vat'] += $transaction->vat ?? 0;
                    $grandTotals['vatSales'] += $transaction->vatSales ?? 0;
                    $grandTotals['vatExempt'] += $transaction->vatExemptSales ?? 0;
                    $grandTotals['lessVat'] += $transaction->lessVatDeduction ?? 0;
                    $grandTotals['sc'] += $transaction->service_charge ?? 0;
                    
                    // Check if served/audited
                    $isServed = isset($transaction->is_audit) ? $transaction->is_audit : 
                            (isset($transaction->order_status) && $transaction->order_status == 'SERVED');
                    
                    if ($isServed) {
                        $grandTotals['served_total'] += $transaction->total ?? 0;
                        $grandTotals['served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['served_sc'] += $transaction->service_charge ?? 0;
                    } else {
                        $grandTotals['not_served_total'] += $transaction->total ?? 0;
                        $grandTotals['not_served_discount'] += $transaction->lime_discount ?? 0;
                        $grandTotals['not_served_pwd'] += $transaction->pwd ?? 0;
                        $grandTotals['not_served_vat'] += $transaction->vat ?? 0;
                        $grandTotals['not_served_vatSales'] += $transaction->vatSales ?? 0;
                        $grandTotals['not_served_vatExempt'] += $transaction->vatExemptSales ?? 0;
                        $grandTotals['not_served_lessVat'] += $transaction->lessVatDeduction ?? 0;
                        $grandTotals['not_served_sc'] += $transaction->service_charge ?? 0;
                    }
                }
                
                // Calculate net totals
                $grandTotals['net'] = $grandTotals['total'] - ($grandTotals['sc'] + $grandTotals['vat']);
                $grandTotals['served_net'] = $grandTotals['served_total'] - ($grandTotals['served_sc'] + $grandTotals['served_vat']);
                $grandTotals['not_served_net'] = $grandTotals['not_served_total'] - ($grandTotals['not_served_sc'] + $grandTotals['not_served_vat']);
                $chartData = ChartHelper::generateFromTableData($results, $category, $report);
                // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
                return view('admin.reports.centralized', compact( 
                'branchs',
                'results', 
                'category', 
                'report', 
                'startDate', 
                'endDate', 
                'chartData', 
                'grandTotals', 
                'resultsCount',
                'selectedBranch'
                ));
            // KEN ADDED eND HERE 20250623001 - KEN - ADDED 12-21-2025 
    }













    public function print($start, $end, $category, $report) {
        $settings = DB::table('settings')->where('id', 1)->first();
        $startDate = date('Y-m-d H:i:s', strtotime($start));
        $endDate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($end)));

        // $startDate = date('M j, Y', strtotime($start));
        // $endDate = date('M j, Y', strtotime($end));

        if ($category == 'sales') {

            $results = DB::table('transactions')
                ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 'transactions.mode_of_payment', 'transactions.type', 'transactions.created_at as date', DB::raw("CONCAT(users.firstname, ' ', users.lastname) as cashier"))
                ->join('users', 'transactions.cashier', '=', 'users.id')
                // ->whereBetween('created_at', [$start, $end])
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get();

            $resultsCount = $results->count();

            if ($report == 'summary') {
                return view('admin.reports.print_summary_se', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
            }
        } else if ($category == 'expenses') {
            if ($report == 'unpaid') {
                $results = DB::table('inventory_transactions')
                    ->select('inventory_transactions.id as id', 'inventory_transactions.is_paid as is_paid', 'inventory_transactions.created_at as date', 'inventory_transactions.inv_id as inv_id', 'inventories.name as nn', 'inventory_transactions.amount as amount', 'inventory_transactions.quantity as quantity', 'inventory_transactions.remarks as remarks', DB::raw("CONCAT(users.firstname, ' ', users.lastname) as cashier"))
                    ->leftJoin('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
                    ->join('users', 'inventory_transactions.user_id', '=', 'users.id')
                    // ->whereBetween('inventory_transactions.created_at', [$startDate, $endDate])
                    ->where('inventory_transactions.created_at', '>=', $startDate)
                    ->where('inventory_transactions.created_at', '<', $endDate)
                    ->where('inventory_transactions.type', 'INCOMING')
                    ->where('inventory_transactions.is_paid', 0)
                    ->orderBy('inventory_transactions.created_at', 'desc')
                    ->get();
            } else {
                $results = DB::table('inventory_transactions')
                    ->select('inventory_transactions.id as id', 'inventory_transactions.is_paid as is_paid', 'inventory_transactions.created_at as date', 'inventory_transactions.inv_id as inv_id', 'inventories.name as nn', 'inventory_transactions.amount as amount', 'inventory_transactions.quantity as quantity', 'inventory_transactions.remarks as remarks', DB::raw("CONCAT(users.firstname, ' ', users.lastname) as cashier"))
                    ->leftJoin('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
                    ->join('users', 'inventory_transactions.user_id', '=', 'users.id')
                    // ->whereBetween('inventory_transactions.created_at', [$startDate, $endDate])
                    ->where('inventory_transactions.created_at', '>=', $startDate)
                    ->where('inventory_transactions.created_at', '<', $endDate)
                    ->where('inventory_transactions.type', 'INCOMING')
                    ->orderBy('inventory_transactions.created_at', 'desc')
                    ->get();
            }

            // $results = DB::table('inventory_transactions')
            //     ->select('inventory_transactions.created_at as date', 'inventories.name as nn', 'inventory_transactions.amount as amount', 'inventory_transactions.quantity as quantity', 'inventory_transactions.inv_id as inv_id', 'inventory_transactions.remarks as remarks')
            //     ->leftJoin('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
            //     // ->whereBetween('inventory_transactions.created_at', [$start, $end])
            //     ->where('inventory_transactions.created_at', '>=', $startDate)
            //     ->where('inventory_transactions.created_at', '<', $endDate)
            //     ->where('inventory_transactions.type', 'INCOMING')
            //     ->orderBy('inventory_transactions.id', 'desc')
            //     ->get();

            $resultsCount = $results->count();

            if ($report == 'summary') {
                return view('admin.reports.print_summary_se', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
            }
        } else if ($category == 'both') {
            $salesQuery = DB::table('transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as stotal'),
                    DB::raw('SUM(service_charge) as sctotal'),
                    DB::raw('0 as etotal')
                )
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                // ->whereBetween('created_at', [$start, $end])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('date');

            $expensesQuery = DB::table('inventory_transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('0 as stotal'),
                    DB::raw('0 as sctotal'),
                    DB::raw('SUM(amount) as etotal')
                )
                ->where('type', 'INCOMING')
                // ->whereBetween('created_at', [$start, $end])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('date');

            $results = $salesQuery->union($expensesQuery)->orderBy('date')->get();
            $results = $results->groupBy('date')->map(function ($item) {
                return [
                    'date' => $item[0]->date,
                    'stotal' => $item->sum('stotal'),
                    'sctotal' => $item->sum('sctotal'),
                    'etotal' => $item->sum('etotal'),
                ];
            })->values();
            return view('admin.reports.print_summary_sec', compact('results', 'category', 'settings', 'startDate', 'endDate', 'report'));
        } else if ($category == 'inventory') {

            if ($report == 'logs') {
                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('type', 'OUTGOING')
                    ->orderBy('id', 'desc')
                    ->get();


                // $results = DB::table('inventory_transactions')
                //     ->select('inventory_transactions.created_at as date', 'inventories.name as nn', 'inventory_transactions.amount as amount', 'inventory_transactions.quantity as quantity', 'inventory_transactions.quantity_before as quantity_before', 'inventory_transactions.quantity_after as quantity_after', 'inventory_transactions.remarks as remarks')
                //     ->join('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
                //     ->where('inventory_transactions.created_at', '>=', $startDate)
                //     ->where('inventory_transactions.created_at', '<', $endDate)
                //     ->where('inventory_transactions.type', 'OUTGOING')
                //     ->orderBy('inventory_transactions.id', 'desc')
                //     ->get();
            } else {
                // $results = Inventory::with('category')
                //     ->orderBy('name', 'asc')
                //     ->get();
                //$results = Inventory::with('category')->orderBy('name', 'asc')->get();
                $results = DB::table('inventories')
                    ->select('inventories.created_at as date', 'inventories.name as nn', 'categories.name as cn', 'inventories.quantity as quantity')
                    ->join('categories', 'inventories.category_id', '=', 'categories.id')
                    ->where('inventories.is_deleted', '0')
                    ->orderBy('inventories.name', 'asc')
                    ->get();
            }

            $resultsCount = $results->count();
        } else if ($category == 'menu') {
            if ($report == 'rank') {
                $results = DB::table('ordered')
                    ->select('ordered.menu_id', 'menus.name', DB::raw('SUM(ordered.quantity) as quantity'))
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->groupBy('ordered.menu_id', 'menus.name')
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate)
                    ->orderBy('quantity', 'desc')
                    ->get();
            } else if ($report == 'stock') {
                $results = Menu::with('category')->orderBy('quantity', 'desc')->get();
            }

            $resultsCount = $results->count();
        } else if ($category == 'invmenu') {
            $results = Menu::with('category')->get();

            dd($results);


            $resultsCount = $results->count();
        } else if ($category == 'waste') {
            if ($report == 'raw') {
                $results = DB::table('wastes')
                    ->select('wastes.iid', 'wastes.created_at', 'wastes.quantity', 'inventories.name', 'wastes.cost','wastes.waste_remarks')
                    ->join('inventories', 'wastes.iid', '=', 'inventories.id')
                    ->where('wastes.on', 'INVENTORY')
                    ->where('wastes.created_at', '>=', $startDate)
                    ->where('wastes.created_at', '<', $endDate)
                    ->orderBy('wastes.created_at', 'desc')
                    ->get();
            } else if ($report == 'menu') {
                $results = DB::table('wastes')
                    ->select('wastes.iid', 'wastes.created_at', 'wastes.quantity', 'menus.name', 'wastes.cost','wastes.waste_remarks')
                    ->join('menus', 'wastes.iid', '=', 'menus.id')
                    ->where('wastes.on', 'MENU')
                    ->where('wastes.created_at', '>=', $startDate)
                    ->where('wastes.created_at', '<', $endDate)
                    ->orderBy('wastes.created_at', 'desc')
                    ->get();
            }

            $resultsCount = $results->count();
        } else if ($category == 'remit') {
            $sales = Transaction::select(DB::raw('SUM(total) as total'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('mode_of_payment', 'CASH')
                ->get();

            $servicecharge = Transaction::select(DB::raw('SUM(service_charge) as total'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('mode_of_payment', 'CASH')
                ->get();

            $expenses = InventoryTransaction::select(DB::raw('SUM(amount) as amount'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('type', 'INCOMING')
                ->where('is_paid', 1)
                ->get();

            $results = [];

            $salesObj = new stdClass();
            $salesObj->name = 'Sales';
            $salesObj->amount = $sales[0]->total;
            $results[] = $salesObj;

            $SCObj = new stdClass();
            $SCObj->name = 'Performance Incentives';
            $SCObj->amount = $servicecharge[0]->total/2;
            $results[] = $SCObj;

            $expObj = new stdClass();
            $expObj->name = 'Expenses';
            $expObj->amount = $expenses[0]->amount;
            $results[] = $expObj;

            $totalObj = new stdClass();
            $totalObj->name = 'Total';
            $texpenses = $expenses[0]->amount + ($servicecharge[0]->total/2);
            $totalObj->amount = $sales[0]->total - $texpenses;
            $results[] = $totalObj;

            $resultsCount = 1;
        }

        if ($report == 'list' || $report == 'logs' || $report == 'stock' || $report == 'rank' || $category == 'waste' || $report == 'unpaid' || $category == 'remit') {
            return view('admin.reports.print_list', compact('results', 'settings', 'category', 'resultsCount', 'startDate', 'endDate', 'report'));
        } else if ($report == 'summary') {
            return view('admin.reports.print_summary_se', compact('results', 'settings'));
        }
    }

    public function printzreading($start, $end, $report) {
        
        DB::table('report_forprints')->where('id', 1)
            ->update([
                'print' => 0,
            ]);
            
        $settings = DB::table('settings')->where('id', 1)->first();
        $startDate = date('Y-m-d H:i:s', strtotime($start));
        $endDate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($end)));


        $results = DB::table('transactions')
                ->select('transactions.created_at as date','transactions.*'
                )
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', '=','PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get();

                $auditted = DB::table('transactions')
                ->select('transactions.created_at as date','transactions.*'
                )
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.sales_type', '1')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get();

                $notAuditted = DB::table('transactions')
                ->select('transactions.created_at as date','transactions.*'
                )
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.sales_type', '0')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get();

                // food finds
                $foodFinds = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->orderBy('transactions.id', 'desc')
                    ->get();

                // studio
                $studio = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('table', '=', 60)
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->orderBy('transactions.id', 'desc')
                    ->get();

                // complimentaries
                $foodCost = DB::table('ordered as ord')
                    ->leftJoin('transactions as tr', 'tr.id', '=', 'ord.tran_id')
                    ->select('tr.created_at as date','tr.id as transid', 'tr.created_at', 'tr.total', 'tr.amount', 'tr.status', 'tr.order_status', 
                        'ord.id as ordid', 'ord.tran_id', 'ord.type', 'ord.food_cost', 'ord.created_at', 'ord.quantity')
                    ->where('tr.status', '=','PAID')
                    ->where('tr.order_status', '!=', 'CANCELLED')
                    ->where('ord.type', '=', 1)
                    ->where('tr.created_at', '>=', $startDate)
                    ->where('tr.created_at', '<', $endDate)
                    

                    ->orderBy('tr.id', 'desc')
                    ->get();


                    $MtransactionIds = DB::table('transactions')
                    ->select('id')
                    ->where('status', 'PAID')
                    ->where('sales_type', '0')
                    ->where('mode_of_payment', 'MULTIPLE')
                    ->where('order_status', '!=', 'CANCELLED')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('id');
            
                $multiplepayment = DB::table('payment_orders')
                    ->select('payment_orders.created_at as date', 'payment_orders.id as mpid', 'payment_orders.amount', 'payment_orders.tran_id', 'payment_orders.mop')
                    ->whereIn('tran_id', $MtransactionIds)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();

                $voidorder = DB::table('ordered_remove')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

                $ordered = DB::table('ordered')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();


                return view('admin.reports.printzreading', compact('ordered','voidorder','multiplepayment','results', 'settings', 'startDate', 'endDate', 'report','auditted','notAuditted', 'foodFinds', 'studio', 'foodCost'));
            } 













    public function payExpenses(Request $request) {
        DB::table('inventory_transactions')->where('id', $request->id)->update([
            'is_paid' => 1,
            'created_at' => date('Y-m-d', strtotime($request->date)) . ' ' . date('H:i:s')
        ]);

        echo 'Mard as Paid Successful';
    }











    public function updateExpenses(Request $request) {
        $it = DB::table('inventory_transactions')->where('id', $request->id)->first();
        if ($it->inv_id != 0) {
            $item = DB::table('inventories')->where('id', $it->inv_id)->first();
        }

        if ($request->quantity > $it->quantity) {
            if ($item->quantity < ($request->quantity - $it->quantity)) {
                echo 'Invalid Quantity';
            } else {
                if ($it->inv_id != 0) {
                    DB::table('inventories')->where('id', $item->id)->update([
                        'quantity' => $item->quantity - ($request->quantity - $it->quantity)
                    ]);
                }

                DB::table('inventory_transactions')->where('id', $request->id)->update([
                    'remarks' => $request->name,
                    'amount' => $request->amount,
                    'quantity' => $request->quantity,
                    'quantity_after' => $it->quantity_before + $request->quantity,
                    'created_at' => $request->date,
                ]);

                echo 'Update Successful';
            }
        } else {
            if ($it->inv_id != 0) {
                DB::table('inventories')->where('id', $item->id)->update([
                    'quantity' => $item->quantity + ($it->quantity - $request->quantity)
                ]);
            }

            DB::table('inventory_transactions')->where('id', $request->id)->update([
                'remarks' => $request->name,
                'amount' => $request->amount,
                'quantity' => $request->quantity,
                'quantity_after' => $it->quantity_before + $request->quantity,
                'created_at' => $request->date,
            ]);

            echo 'Update Successful';
        }
    }

    public function paymentExpenses(Request $request) {
        // Retrieve the inventory transaction by ID
        $it = DB::table('inventory_transactions')->where('id', $request->id)->first();
    
        // Check if the transaction exists
        if (!$it) {
            return response()->json('Transaction not found', 404);
        }
    
        // Update the inventory transaction record
        DB::table('inventory_transactions')->where('id', $request->id)->update([
            'updated_at' => $request->date,
            'ap_payment' => $request->mop,       // Updating payment mode
            'payment_class' => 1                 // Set payment class to 1
        ]);
    
        return response()->json('Payment Successful');
    }
    
    

    public function deleteExpenses(Request $request) {
        DB::table('inventory_transactions')->where('id', $request->id)->delete();

        echo 'Delete Successful';
    }









    public function updateSales(Request $request) {
        $tran = Transaction::where('id', $request->id)->first();
        $tran->total = $request->amount;
        $tran->created_at = $request->date;
        $tran->mode_of_payment = $request->mop;
        $tran->save();
        
        echo 'Update Successful';
    }

    public function deleteSales(Request $request) {
        Transaction::where('id', $request->id)->delete();

        echo 'Delete Successful';
    }






    public function financialReport(){
        $lastDay = date('t');
        $month = date('m');
        $year = date('Y');
        $startDate = $year.'-'.$month.'-01 00:00:01';
        $endDate = $year.'-'.$month.'-31 23:59:59';

        $sales = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_per_day'))
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('status', 'PAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->groupBy('date')
            ->get();

        $expenses = InventoryTransaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total_per_day'))
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('type', 'INCOMING')
            ->where('is_paid', 1)
            ->groupBy('date')
            ->get();

        $actuals = ActualMoney::where('date', '>=', $startDate)
            ->where('date', '<', $endDate)
            ->get();

        $account_payables = InventoryTransaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total_per_day'))
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('type', 'INCOMING')
            ->where('is_paid', 0)
            ->groupBy('date')
            ->get();

        return view('admin.reports.financial-report', compact('sales', 'expenses', 'actuals', 'account_payables', 'month', 'year', 'lastDay'));
    }

    public function generateFinancialReport(Request $request){
        $lastDay = date('t');
        $month = $request->month;
        $year = $request->year;
        $startDate = $year.'-'.$month.'-01 00:00:01';
        $endDate = $year.'-'.$month.'-31 23:59:59';

        $sales = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_per_day'))
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('status', 'PAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->groupBy('date')
            ->get();

        $expenses = InventoryTransaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total_per_day'))
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('type', 'INCOMING')
            ->where('is_paid', 1)
            ->groupBy('date')
            ->get();

        $actuals = ActualMoney::where('date', '>=', $startDate)
            ->where('date', '<', $endDate)
            ->get();

        $account_payables = InventoryTransaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total_per_day'))
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('type', 'INCOMING')
            ->where('is_paid', 0)
            ->groupBy('date')
            ->get();

        return view('admin.reports.financial-report', compact('sales', 'expenses', 'actuals', 'account_payables', 'month', 'year', 'lastDay'));
    }
    
    public function getActual(Request $request){
        $actual = ActualMoney::where('date', $request->date)->first();

        echo json_encode($actual);
    }
    
    public function updateActual(Request $request){
        $date = $request->date;
        $liquid_cash = $request->liquid_cash;
        $cash_on_hand = $request->cash_on_hand;
        $gcash = $request->gcash;
        $bank = $request->bank;
        $pending_remit = $request->pending_remit;

        $actual = ActualMoney::where('date', $date)->first();
        if($actual == null){
            $actual = new ActualMoney;
            $actual->date = $date;
        }
        $actual->liquid_cash = $liquid_cash;
        $actual->cash_on_hand = $cash_on_hand;
        $actual->gcash = $gcash;
        $actual->bank = $bank;
        $actual->pending_remit = $pending_remit;
        $actual->save();

        echo 'Financial Report has been updated successfully.';
    }









    public function pricingReport(){
        $setting = Setting::where('id', 1)->first();
        $menus = Menu::where('is_combo', 0)->where('is_deleted', 0)->where('is_hidden', 0)->get();

        if (date('j') == 1) {
            PricingReport::query()->update(['previous_price_per_serving_2' => DB::raw('previous_price_per_serving_1')]);
            PricingReport::query()->update(['previous_price_per_serving_1' => DB::raw('price_per_servings')]);
        }

        foreach($menus as $menu){
            $ingredient_expense = 0;
            $ingredients = Ingredient::where('menu_id', $menu->id)->where('is_deleted', 0)->get();

            foreach($ingredients as $ing){
                $inv_tran = InventoryTransaction::where('inv_id', $ing->inventory_id)->where('type', 'INCOMING')->orderBy('id','desc')->first();
                if($inv_tran != null){
                    $price_per_unit = $inv_tran->amount / $inv_tran->quantity;
                    $price = $ing->quantity * $price_per_unit;
                    $ingredient_expense += $price;
                }
            }

            $after_buffer_margin = $ingredient_expense + ($ingredient_expense * (float)$setting->buffer_margin);
            $after_markup = $after_buffer_margin + ($after_buffer_margin * (float)$setting->markup);
            $after_price_adjustment = $after_markup + ($after_markup * (float)$setting->price_adjustment);
            $after_staff_incentives = $after_price_adjustment + (float)$setting->staff_incentives;
            $after_manager_incentives = $after_staff_incentives + ($after_staff_incentives * (float)$setting->manager_incentives);
            $after_vat = $after_manager_incentives + ($after_manager_incentives * (float)$setting->vat);

            $total_expense = $after_vat;
            if($menu->servings != 0){
                $ingredient_expense_per_serving = $ingredient_expense / $menu->servings;
                $price_per_serving = $total_expense / $menu->servings;
            }else{
                $ingredient_expense_per_serving = "N/A";
                $price_per_serving = "N/A";
            }

            $menuPricingReport = PricingReport::where('menu_id', $menu->id)->first();

            if($menuPricingReport == null){
                $menuPricingReport = new PricingReport();
            }
            $menuPricingReport->menu_id = $menu->id;
            $menuPricingReport->menu = $menu->name;
            $menuPricingReport->ingredient_expense = $ingredient_expense_per_serving;
            $menuPricingReport->number_of_servings = $menu->servings;
            $menuPricingReport->price_per_servings = $price_per_serving;
            $menuPricingReport->selling_price = $menu->price;
            
            if($menu->servings != 0){
                $menuPricingReport->additional_income = $menu->price - $price_per_serving;
            }else{
                $menuPricingReport->additional_income = "N/A";
            }
            $menuPricingReport->save();
        }

        
        $menus = Menu::where('is_combo', 1)->where('is_deleted', 0)->where('is_hidden', 0)->where('id', 6)->get();

        foreach($menus as $menu){
            $ingredient_expense = 0;
            $ingredients = Ingredient::where('menu_id', $menu->id)->where('is_deleted', 0)->get();

            foreach($ingredients as $ing){
                $pr = PricingReport::where('menu_id', $ing->inventory_id)->first();
                if($pr && $pr->ingredient_expense != "N/A"){
                    $price = $pr->ingredient_expense / $pr->number_of_servings;
                    $totalPrice = $price * $ing->quantity;
                    $ingredient_expense += $totalPrice;
                }else{
                    $ingredient_expense = 0;
                    break;
                }
            }

            $after_buffer_margin = $ingredient_expense + ($ingredient_expense * (float)$setting->buffer_margin);
            $after_markup = $after_buffer_margin + ($after_buffer_margin * (float)$setting->markup);
            $after_price_adjustment = $after_markup + ($after_markup * (float)$setting->price_adjustment);
            $after_staff_incentives = $after_price_adjustment + (float)$setting->staff_incentives;
            $after_manager_incentives = $after_staff_incentives + ($after_staff_incentives * (float)$setting->manager_incentives);
            $after_vat = $after_manager_incentives + ($after_manager_incentives * (float)$setting->vat);

            $total_expense = $after_vat;

            if($menu->servings != 0){
                $ingredient_expense_per_serving = $ingredient_expense / $menu->servings;
                $price_per_serving = $total_expense / $menu->servings;
            }else{
                $ingredient_expense_per_serving = "N/A";
                $price_per_serving = "N/A";
            }


            $menuPricingReport = PricingReport::where('menu_id', $menu->id)->first();

            if($menuPricingReport == null){
                $menuPricingReport = new PricingReport();
            }
            $menuPricingReport->menu_id = $menu->id;
            $menuPricingReport->menu = $menu->name;
            $menuPricingReport->ingredient_expense = $ingredient_expense_per_serving;
            $menuPricingReport->number_of_servings = $menu->servings;
            $menuPricingReport->price_per_servings = $price_per_serving;
            $menuPricingReport->selling_price = $menu->price;
            
            if($menu->servings != 0 || $price_per_serving != "N/A"){
                $menuPricingReport->additional_income = $menu->price - $price_per_serving;
            }else{
                $menuPricingReport->additional_income = "N/A";
            }
            
            $menuPricingReport->additional_income = $menu->price - $price_per_serving;
            $menuPricingReport->save();
        }

        $pricingReports = PricingReport::get();

        return view('admin.reports.pricing-report', compact('pricingReports'));
    }











    public function viewPricingReport(Request $request){
        $pricingReport = PricingReport::where('id', $request->id)->first();
        $menu = Menu::where('id', $pricingReport->menu_id)->first();
        $setting = Setting::where('id', 1)->first();
        $result = '';
        $ingrow = '';
        
        $buffer_percent = (float)$setting->buffer_margin * 100;
        $markup_percent = (float)$setting->markup * 100;
        $adjustment_percent = (float)$setting->price_adjustment * 100;
        $staff_incentive = (float)$setting->staff_incentives;
        $incentive_percent = (float)$setting->manager_incentives * 100;
        $vat_percent = (float)$setting->vat * 100;

        $ingredient_expense = 0;
        $ingredients = Ingredient::where('menu_id', $menu->id)->where('is_deleted', 0)->get();

        if($menu->is_combo == 1){
            foreach($ingredients as $ing){
                $inv = Menu::where('id', $ing->inventory_id)->first();
                $pr = PricingReport::where('menu_id', $ing->inventory_id)->first();
                
                if($pr->ingredient_expense != "N/A"){
                    $price = $pr->ingredient_expense / $pr->number_of_servings;
                    $totalPrice = $price * $ing->quantity;
                    $ingredient_expense += $totalPrice;
                }else{
                    $ingredient_expense = 0;
                    break;
                }

                // $price = $pr->ingredient_expense / $pr->number_of_servings;
                // $totalPrice = $price * $ing->quantity;
                // $ingredient_expense += $totalPrice;

                $ingrow .= '
                    <tr class="border-b">
                        <td class="px-4 py-1 text-left border-r">
                            '.$inv->name.'
                        </td>
                        <td class="px-4 text-right border-r">
                            '.number_format($ing->quantity, 2, '.', ',').'
                        </td>
                        <td class="px-4 text-right border-r">
                            ₱ '.number_format($price, 2, '.', ',').'
                        </td>
                        <td class="px-4 text-right">
                            ₱ '.number_format($totalPrice, 2, '.', ',').'
                        </td>
                    </tr>
                ';
            }

            $ingredient_expense_per_serving = $ingredient_expense / $menu->servings;
            
            $buffer_margin = $ingredient_expense * (float)$setting->buffer_margin;
            $after_buffer_margin = $ingredient_expense + $buffer_margin;

            $markup = $after_buffer_margin * (float)$setting->markup;
            $after_markup = $after_buffer_margin + $markup;

            $price_adjustment = $after_markup * (float)$setting->price_adjustment;
            $after_price_adjustment = $after_markup + $price_adjustment;

            $staff_incentives = (float)$setting->staff_incentives;
            $after_staff_incentives = $after_price_adjustment + $staff_incentives;

            $manager_incentives = $after_staff_incentives * (float)$setting->manager_incentives;
            $after_manager_incentives = $after_staff_incentives + $manager_incentives;

            $vat = $after_manager_incentives * (float)$setting->vat;
            $after_vat = $after_manager_incentives + $vat;

            $total_expense = $after_vat;
            $price_per_serving = $total_expense / $menu->servings;
        }else{
            foreach($ingredients as $ing){
                $price_per_unit = 0;
                $price = 0;
                $inv = Inventory::where('id', $ing->inventory_id)->first();
                $inv_tran = InventoryTransaction::where('inv_id', $ing->inventory_id)->where('type', 'INCOMING')->orderBy('id','desc')->first();
                if($inv_tran != null){

                    $price_per_unit = $inv_tran->amount / $inv_tran->quantity;
                    $price = $ing->quantity * $price_per_unit;
                    $ingredient_expense += $price;
                }

                $ingrow .= '
                    <tr class="border-b">
                        <td class="px-4 py-1 text-left border-r">
                            '.$inv->name.'
                        </td>
                        <td class="px-4 text-right border-r">
                            '.number_format($ing->quantity, 2, '.', ',').'
                        </td>
                        <td class="px-4 text-right border-r">
                            ₱ '.number_format($price_per_unit, 2, '.', ',').'
                        </td>
                        <td class="px-4 text-right">
                            ₱ '.number_format($price, 2, '.', ',').'
                        </td>
                    </tr>
                ';
            }
    
            // $ingredient_expense_per_serving = $ingredient_expense / $menu->servings;
            
            $buffer_margin = $ingredient_expense * (float)$setting->buffer_margin;
            $after_buffer_margin = $ingredient_expense + $buffer_margin;

            $markup = $after_buffer_margin * (float)$setting->markup;
            $after_markup = $after_buffer_margin + $markup;

            $price_adjustment = $after_markup * (float)$setting->price_adjustment;
            $after_price_adjustment = $after_markup + $price_adjustment;

            $staff_incentives = (float)$setting->staff_incentives;
            $after_staff_incentives = $after_price_adjustment + $staff_incentives;

            $manager_incentives = $after_staff_incentives * (float)$setting->manager_incentives;
            $after_manager_incentives = $after_staff_incentives + $manager_incentives;

            $vat = $after_manager_incentives * (float)$setting->vat;
            $after_vat = $after_manager_incentives + $vat;
    
            $total_expense = $after_vat;

            if($menu->servings != 0){
                $ingredient_expense_per_serving = $ingredient_expense / $menu->servings;
                $price_per_serving = $total_expense / $menu->servings;
            }else{
                $ingredient_expense_per_serving = 0;
                $price_per_serving = 0;
            }
            
            // $price_per_serving = $total_expense / $menu->servings;
        }

        $result .= '
            <div class="flex items-center mb-4">
                <p class="text-sm">Yield (# of pax):</p>
                <p class="ml-2 font-medium">'.$menu->servings.'</p>
            </div>
            <div class="w-full border">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="py-1 border-r">
                                Ingredients
                            </th>
                            <th class="border-r">
                                Quantity of Recipe Unit
                            </th>
                            <th class="border-r">
                                Price per Unit
                            </th>
                            <th>
                                Total Cost per Serving
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        '.$ingrow.'

                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Ingredients Expense
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($ingredient_expense, 2, '.', ',').'
                            </td>
                        </tr>







                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                '.$buffer_percent.'% Buffer Margin
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($buffer_margin, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Total After Buffer Margin
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($after_buffer_margin, 2, '.', ',').'
                            </td>
                        </tr>


                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                '.$markup_percent.'% Markup
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($markup, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Total After Markup
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($after_markup, 2, '.', ',').'
                            </td>
                        </tr>


                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                '.$adjustment_percent.'% Price Adjustment for Uncontrollable
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($price_adjustment, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Total After Price Adjustment
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($after_price_adjustment, 2, '.', ',').'
                            </td>
                        </tr>


                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Staff Incentives
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($staff_incentive, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                '.$incentive_percent.'% Manager Incentives
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($manager_incentives, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Total After Staff Incen
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($after_manager_incentives, 2, '.', ',').'
                            </td>
                        </tr>


                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                '.$vat_percent.'% VAT 12%
                            </td>
                            <td class="px-4 text-right">
                                ₱ '.number_format($vat, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Total After VAT
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($after_vat, 2, '.', ',').'
                            </td>
                        </tr>


                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                # of Servings
                            </td>
                            <td class="px-4 font-bold text-right">
                                '.$menu->servings.'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Price Per Serving
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($price_per_serving, 2, '.', ',').'
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td colspan="3" class="px-4 py-1 text-right border-r">
                                Final Selling Price
                            </td>
                            <td class="px-4 font-bold text-right">
                                ₱ '.number_format($pricingReport->selling_price, 2, '.', ',').'
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        ';

        echo $result;
    }


    public function centralizedPrint($start, $end, $category, $report)
    {
        $settings = DB::table('settings')->where('id', 1)->first();
        
        $startDate = date('Y-m-d H:i:s', strtotime($start));
        $endDate = date('Y-m-d H:i:s', strtotime($end));
        $branchs = [];
            
        if (Auth::user()->role == 1) {
            $branchs = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        }
        // Re-run the same queries as in the generate method
        // Copy the relevant query logic based on category and report
        
        // ken start dded Xreading-report print Aug. 26 2026
        if ($category === 'sales' && $report === 'xreading') {
            $generatedReport = $this->generate(new Request([
                'start' => $start,
                'end' => $end,
                'category' => $category,
                'report' => $report,
                'branch' => request('branch', 'all'),
            ]));

            $printData = $generatedReport->getData();
            $printData['branchSettings'] = DB::table('settings')
                ->where('store_code', request('branch'))
                ->where('is_deleted', 0)
                ->first();

            return view('admin.reports.x-reading-print', $printData);
        }
        // ken end added Xreading-report print Aug. 26 2026
        
        if ($category == 'sales') {
            if ($report == 'list' || $report == 'served_list' || $report == 'notServed_list') {
                $results = Transaction::with('thisCashier')
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('order_status', '!=', 'PREPARING')
                    ->orderBy('transactions.id', 'desc')
                    ->get();
                    
                // Convert to array for consistent printing
                $results = $results->map(function($item) {
                    return [
                        'Transaction ID' => $item->id,
                        'Transaction Number' => $item->number,
                        'Date' => $item->created_at,
                        'Total Amount' => $item->total,
                        'Mode of Payment' => $item->mode_of_payment,
                        'Cashier' => $item->thisCashier ? $item->thisCashier->name : 'N/A',
                        'Status' => $item->status,
                        'Order Status' => $item->order_status,
                    ];
                })->toArray();
                    
            } elseif ($report == 'summary') {
                $dailySummaries = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(transactions.created_at) as date'),
                        DB::raw('SUM(CASE WHEN order_status = "SERVED" THEN total ELSE 0 END) as served_total'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN total ELSE 0 END) as not_served_total'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN total ELSE 0 END) as total_amount'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN pwd ELSE 0 END) as total_discount'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN pwd ELSE 0 END) as total_pwd_senior'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN (total - vat - service_charge) ELSE 0 END) as total_less_vat'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN (total - vat - service_charge) ELSE 0 END) as total_vat_sales'),
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN vat ELSE 0 END) as total_vat'),
                        DB::raw('0 as total_vat_exempt_sales'), // Adjust based on your business logic
                        DB::raw('SUM(CASE WHEN order_status != "SERVED" AND order_status != "CANCELLED" THEN service_charge ELSE 0 END) as total_service_charge')
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->groupBy(DB::raw('DATE(transactions.created_at)'))
                    ->orderBy('date', 'asc')
                    ->get();
                
                // Format results to match the image structure
                $results = [];
                foreach ($dailySummaries as $daily) {
                    // Add date row
                    $results[] = [
                        'type' => 'date',
                        'date' => date('M d, Y', strtotime($daily->date)),
                        'total_amount' => '¥ ' . number_format($daily->total_amount, 2),
                        'total_discount' => '',
                        'total_pwd_senior' => '',
                        'total_less_vat' => '',
                        'total_vat_sales' => '',
                        'total_vat' => '',
                        'total_vat_exempt_sales' => '',
                        'total_service_charge' => ''
                    ];
                    
                    // Add served row
                    $results[] = [
                        'type' => 'served',
                        'date' => 'SERVED',
                        'total_amount' => '¥ ' . number_format($daily->served_total, 2),
                        'total_discount' => '¥ 0.00',
                        'total_pwd_senior' => '¥ 0.00',
                        'total_less_vat' => '¥ 0.00',
                        'total_vat_sales' => '¥ 0.00',
                        'total_vat' => '¥ 0.00',
                        'total_vat_exempt_sales' => '¥ 0.00',
                        'total_service_charge' => '¥ 0.00'
                    ];
                    
                    // Add not served row
                    $results[] = [
                        'type' => 'not_served',
                        'date' => 'NOT SERVED',
                        'total_amount' => '¥ ' . number_format($daily->not_served_total, 2),
                        'total_discount' => '¥ ' . number_format($daily->total_discount, 2),
                        'total_pwd_senior' => '¥ ' . number_format($daily->total_pwd_senior, 2),
                        'total_less_vat' => '¥ ' . number_format($daily->total_less_vat, 2),
                        'total_vat_sales' => '¥ ' . number_format($daily->total_vat_sales, 2),
                        'total_vat' => '¥ ' . number_format($daily->total_vat, 2),
                        'total_vat_exempt_sales' => '¥ ' . number_format($daily->total_vat_exempt_sales, 2),
                        'total_service_charge' => '¥ ' . number_format($daily->total_service_charge, 2)
                    ];
                }           
            } elseif ($report == 'hoursale') {
                $results = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('HOUR(created_at) as hour'),
                        DB::raw('COUNT(id) as transaction_count'),
                        DB::raw('SUM(total) as total_sales'),
                        DB::raw('SUM(pwd) as total_discount')
                    )
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('status', 'PAID')
                    ->where('order_status', '!=', 'CANCELLED')
                    ->groupBy(DB::raw('DATE(created_at)'), DB::raw('HOUR(created_at)'))
                    ->orderBy('date', 'asc')
                    ->orderBy('hour', 'asc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->date,
                        'Hour' => sprintf('%02d:00', $item->hour),
                        'Transaction Count' => $item->transaction_count,
                        'Total Sales' => $item->total_sales,
                        'Total Discount' => $item->total_discount,
                    ];
                })->toArray();
                    
            } elseif ($report == 'compli' || $report == 'compli_break') {
                $results = DB::table('transactions')
                    ->leftJoin('ordered', 'transactions.id', '=', 'ordered.tran_id')
                    ->leftJoin('menus', 'menus.id', '=', 'ordered.menu_id')
                    ->leftJoin('staff', 'staff.id', '=', 'ordered.cashier')
                    ->leftJoin('users', 'users.id', '=', 'transactions.cashier')
                    ->select(
                        'transactions.id AS transaction_id',
                        'transactions.number',
                        'menus.name as menu_name',
                        'ordered.quantity',
                        'menus.food_cost',
                        DB::raw('CONCAT(staff.fname, " ", staff.lname) as server_name'),
                        DB::raw('CONCAT(users.firstname, " ", users.lastname) as cashier_name'),
                        'ordered.created_at as date_ordered'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('ordered.type', 1)
                    ->orderBy('transactions.id', 'DESC')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Transaction ID' => $item->transaction_id,
                        'Transaction Number' => $item->number,
                        'Menu Item' => $item->menu_name,
                        'Quantity' => $item->quantity,
                        'Food Cost' => $item->food_cost,
                        'Server' => $item->server_name,
                        'Cashier' => $item->cashier_name,
                        'Date Ordered' => $item->date_ordered,
                    ];
                })->toArray();
                    
            } elseif ($report == 'beverages' || $report == 'food') {
                $results = DB::table('ordered')
                    ->select('menus.name',DB::raw('SUM(ordered.quantity) as quantity'),'menus.price')
                    ->leftjoin('menus', 'menus.id','=', 'ordered.menu_id')
                    ->where('ordered.department', $report == 'beverages' ? 2 : 1)
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate)
                    ->groupBy('ordered.menu_id','menus.name','menus.price')
                    ->orderBy('quantity', 'DESC')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Menu Item' => $item->name,
                        'Quantity Sold' => $item->quantity,
                        'Price' => $item->price,
                        'Total Revenue' => $item->quantity * $item->price,
                    ];
                })->toArray();
                    
            } elseif ($report == 'zreading') {
                $results = DB::table('transactions')
                    ->select('transactions.created_at as date','transactions.*')
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=','PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->orderBy('transactions.id', 'desc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->date,
                        'Transaction ID' => $item->id,
                        'Transaction Number' => $item->number,
                        'Total Amount' => $item->total,
                        'Mode of Payment' => $item->mode_of_payment,
                        'Service Charge' => $item->service_charge,
                        'VAT' => $item->vat,
                        'PWD Discount' => $item->pwd,
                        'Status' => $item->status,
                        'Sales Type' => $item->sales_type == 1 ? 'Audited' : 'Not Audited',
                    ];
                })->toArray();
                    
            } elseif ($report == 'pax') {
                $results = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(transactions.created_at) as date'),
                        DB::raw('SUM(total_customer) as total_customer')
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=','PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->groupBy(DB::raw('DATE(transactions.created_at)'))
                    ->orderBy('date', 'asc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->date,
                        'Total Customers' => $item->total_customer,
                    ];
                })->toArray();
                    
            } elseif ($report == 'discount') {
                $results = DB::table('transactions')
                    ->select(
                        DB::raw('DATE(transactions.created_at) as date'), 
                        'transactions.lime_discount', 
                        DB::raw('SUM(transactions.total) as total_sum'), 
                        DB::raw('COUNT(transactions.id) as transaction_count'), 
                        DB::raw('SUM(transactions.ld_value) as discount_value')
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=', 'PAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->whereIn('transactions.lime_discount', [5, 10, 20, 50, 05, 010, 020, 050])
                    ->groupBy(DB::raw('DATE(transactions.created_at)'), 'transactions.lime_discount')
                    ->orderBy('date', 'asc')
                    ->orderBy('transactions.lime_discount', 'asc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->date,
                        'Discount %' => $item->lime_discount . '%',
                        'Transaction Count' => $item->transaction_count,
                        'Total Sales' => $item->total_sum,
                        'Discount Value' => $item->discount_value,
                    ];
                })->toArray();
                    
            } elseif ($report == 'srpwd') {
                $results = DB::table('transactions')
                    ->join('seniors', 'transactions.id', '=', 'seniors.tran_id')
                    ->select(
                        DB::raw('DATE(transactions.created_at) as date'),  
                        DB::raw('COUNT(transactions.id) as transaction_count'), 
                        DB::raw('SUM(transactions.pwd) as pwd_total'),
                        'seniors.category',
                        'transactions.number',
                        'transactions.customer_with_discount'
                    )
                    ->where('transactions.created_at', '>=', $startDate)
                    ->where('transactions.created_at', '<', $endDate)
                    ->where('transactions.status', '=', 'PAID')
                    ->where('transactions.order_status', '=', 'COMPLETED')
                    ->where('transactions.total', '!=', 0)
                    ->whereIn('seniors.category', ['sr','pwd','Athlete','single'])
                    ->groupBy(DB::raw('DATE(transactions.created_at)'), 'seniors.category','transactions.number','transactions.customer_with_discount')
                    ->orderBy('transaction_count', 'asc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->date,
                        'Category' => strtoupper($item->category),
                        'Transaction Number' => $item->number,
                        'Customer with Discount' => $item->customer_with_discount,
                        'Transaction Count' => $item->transaction_count,
                        'PWD Discount Total' => $item->pwd_total,
                    ];
                })->toArray();
            }
            
        } elseif ($category == 'expenses') {
            if ($report == 'list' || $report == 'cos' || $report == 'ooe' || $report == 'unpaid') {
                $query = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate);
                
                if ($report == 'unpaid') {
                    $query->where('payment_class', '=', 0);
                } elseif ($report == 'cos') {
                    $query->where('exp_type', '=', '0');
                } elseif ($report == 'ooe') {
                    $query->where('exp_type', '=', '1');
                }
                
                $results = $query->orderBy('created_at', 'desc')->get();
                
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->created_at,
                        'Item Name' => $item->inv ? $item->inv->name : 'N/A',
                        'Quantity' => $item->quantity,
                        'Unit Price' => $item->unit_price,
                        'Total Amount' => $item->amount,
                        'Remarks' => $item->remarks,
                        'Cashier' => $item->user ? $item->user->name : 'N/A',
                        'Payment Status' => $item->payment_class == 1 ? 'Paid' : 'Unpaid',
                        'Expense Type' => $item->exp_type == 0 ? 'Cost of Sales' : 'Operating Expense',
                    ];
                })->toArray();
                    
            } elseif ($report == 'summary') {
                $results = DB::table('inventory_transactions')
                    ->select('inventory_transactions.*', 'inventories.name as item_name', DB::raw("CONCAT(users.firstname, ' ', users.lastname) as cashier"))
                    ->leftJoin('inventories', 'inventory_transactions.inv_id', '=', 'inventories.id')
                    ->join('users', 'inventory_transactions.user_id', '=', 'users.id')
                    ->where('inventory_transactions.created_at', '>=', $startDate)
                    ->where('inventory_transactions.created_at', '<', $endDate)
                    ->orderBy('inventory_transactions.created_at', 'desc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->created_at,
                        'Item Name' => $item->item_name,
                        'Quantity' => $item->quantity,
                        'Unit' => $item->unit,
                        'Unit Price' => $item->unit_price,
                        'Total Amount' => $item->amount,
                        'Remarks' => $item->remarks,
                        'Cashier' => $item->cashier,
                        'Payment Status' => $item->is_paid ? 'Paid' : 'Unpaid',
                        'Expense Type' => $item->exp_type == 0 ? 'Cost of Sales' : 'Operating Expense',
                    ];
                })->toArray();
            }
            
        } elseif ($category == 'both') {
            $salesQuery = DB::table('transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as stotal'),
                    DB::raw('SUM(service_charge) as sctotal'),
                    DB::raw('SUM(vat) as vattotal'),
                    DB::raw('SUM(pwd) as pwdtotal'),
                    DB::raw('0 as etotal')
                )
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('date');

            $expensesQuery = DB::table('inventory_transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('0 as stotal'),
                    DB::raw('0 as sctotal'),
                    DB::raw('0 as vattotal'),
                    DB::raw('0 as pwdtotal'),
                    DB::raw('SUM(amount) as etotal')
                )
                ->where('type', 'INCOMING')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('date');

            $results = $salesQuery->union($expensesQuery)->orderBy('date')->get();

            $results = $results->groupBy('date')->map(function ($item) {
                return [
                    'date' => $item[0]->date,
                    'stotal' => $item->sum('stotal'),
                    'sctotal' => $item->sum('sctotal'),
                    'etotal' => $item->sum('etotal'),
                    'vattotal' => $item->sum('vattotal'),
                    'pwdtotal' => $item->sum('pwdtotal'),
                ];
            })->values()->toArray();
            
            // Format for printing
            $formattedResults = [];
            foreach ($results as $result) {
                $formattedResults[] = [
                    'Date' => $result['date'],
                    'Sales Total' => $result['stotal'],
                    'Service Charge' => $result['sctotal'],
                    'VAT Total' => $result['vattotal'],
                    'PWD Discount' => $result['pwdtotal'],
                    'Expenses Total' => $result['etotal'],
                    'Net Profit' => $result['stotal'] - $result['etotal'],
                ];
            }
            $results = $formattedResults;
            
        } elseif ($category == 'inventory') {
            if ($report == 'logs') {
                $results = InventoryTransaction::with('user', 'inv')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<', $endDate)
                    ->where('type', 'OUTGOING')
                    ->orderBy('id', 'desc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->created_at,
                        'Item Name' => $item->inv ? $item->inv->name : 'N/A',
                        'Quantity Before' => $item->quantity_before,
                        'Quantity Used' => $item->quantity,
                        'Quantity After' => $item->quantity_after,
                        'Amount' => $item->amount,
                        'Remarks' => $item->remarks,
                        'User' => $item->user ? $item->user->name : 'N/A',
                    ];
                })->toArray();
                    
            } else {
                $results = DB::table('inventories')
                    ->select('inventories.created_at as date', 'inventories.name', 'categories.name as category_name', 'inventories.quantity as quantity')
                    ->join('categories', 'inventories.category_id', '=', 'categories.id')
                    ->where('inventories.is_deleted', '0')
                    ->orderBy('inventories.name', 'asc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date Added' => $item->date,
                        'Item Name' => $item->name,
                        'Category' => $item->category_name,
                        'Current Stock' => $item->quantity,
                    ];
                })->toArray();
            }
            
        } elseif ($category == 'menu') {
            if ($report == 'rank') {
                $results = DB::table('ordered')
                    ->select('ordered.menu_id', 'menus.name', DB::raw('SUM(ordered.quantity) as quantity'), 'menus.price', DB::raw('SUM(ordered.quantity * menus.price) as total'))
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
                    ->groupBy('ordered.menu_id', 'menus.name','menus.price')
                    ->where('ordered.created_at', '>=', $startDate)
                    ->where('ordered.created_at', '<', $endDate)
                    ->orderBy('total' , 'desc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Menu Item' => $item->name,
                        'Quantity Sold' => $item->quantity,
                        'Unit Price' => $item->price,
                        'Total Revenue' => $item->total,
                    ];
                })->toArray();
                    
            } else if ($report == 'stock') {
                $results = Menu::with('category')->orderBy('quantity', 'desc')->get();
                
                $results = $results->map(function($item) {
                    return [
                        'Menu Item' => $item->name,
                        'Category' => $item->category ? $item->category->name : 'N/A',
                        'Current Stock' => $item->quantity,
                        'Price' => $item->price,
                        'Food Cost' => $item->food_cost,
                    ];
                })->toArray();
            }
            
        } elseif ($category == 'invmenu') {
            $results = [];
            $menus = Menu::with('category')->orderBy('quantity', 'desc')->get();
            foreach ($menus as $menu) {
                $results[] = [
                    'Item Type' => 'Menu',
                    'Item Name' => $menu->name,
                    'Category' => $menu->category ? $menu->category->name : 'N/A',
                    'Current Stock' => $menu->quantity,
                ];
            }
            
            $raws = Inventory::with('category')->orderBy('quantity', 'desc')->get();
            foreach ($raws as $raw) {
                $results[] = [
                    'Item Type' => 'Raw Material',
                    'Item Name' => $raw->name,
                    'Category' => $raw->category ? $raw->category->name : 'N/A',
                    'Current Stock' => $raw->quantity,
                ];
            }
            
        } elseif ($category == 'waste') {
            if ($report == 'raw') {
                $results = DB::table('wastes')
                    ->select('wastes.iid', 'wastes.created_at', 'wastes.quantity', 'inventories.name', 'wastes.cost','wastes.waste_remarks')
                    ->join('inventories', 'wastes.iid', '=', 'inventories.id')
                    ->where('wastes.on', 'INVENTORY')
                    ->where('wastes.created_at', '>=', $startDate)
                    ->where('wastes.created_at', '<', $endDate)
                    ->orderBy('wastes.created_at', 'desc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->created_at,
                        'Item Name' => $item->name,
                        'Quantity Wasted' => $item->quantity,
                        'Cost' => $item->cost,
                        'Remarks' => $item->waste_remarks,
                    ];
                })->toArray();
                    
            } else if ($report == 'menu') {
                $results = DB::table('wastes')
                    ->select('wastes.iid', 'wastes.created_at', 'wastes.quantity', 'menus.name', 'wastes.cost','wastes.waste_remarks')
                    ->join('menus', 'wastes.iid', '=', 'menus.id')
                    ->where('wastes.on', 'MENU')
                    ->where('wastes.created_at', '>=', $startDate)
                    ->where('wastes.created_at', '<', $endDate)
                    ->orderBy('wastes.created_at', 'desc')
                    ->get();
                    
                $results = $results->map(function($item) {
                    return [
                        'Date' => $item->created_at,
                        'Menu Item' => $item->name,
                        'Quantity Wasted' => $item->quantity,
                        'Cost' => $item->cost,
                        'Remarks' => $item->waste_remarks,
                    ];
                })->toArray();
            }
            
        } elseif ($category == 'remit') {
            $sales = Transaction::select(DB::raw('SUM(total) as total'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('mode_of_payment', 'CASH')
                ->first();

            $servicecharge = Transaction::select(DB::raw('SUM(service_charge) as total'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('transactions.status', 'PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->where('mode_of_payment', 'CASH')
                ->first();

            $expenses = InventoryTransaction::select(DB::raw('SUM(amount) as amount'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->where('type', 'INCOMING')
                ->where('is_paid', 1)
                ->first();

            $results = [
                [
                    'Item' => 'Sales (Cash)',
                    'Amount' => $sales->total ?? 0
                ],
                [
                    'Item' => 'Performance Incentives (50% of Service Charge)',
                    'Amount' => ($servicecharge->total ?? 0) / 2
                ],
                [
                    'Item' => 'Expenses (Paid)',
                    'Amount' => $expenses->amount ?? 0
                ],
                [
                    'Item' => 'Total Remittance',
                    'Amount' => ($sales->total ?? 0) - (($servicecharge->total ?? 0) / 2) - ($expenses->amount ?? 0)
                ]
            ];
        }

        $resultsCount = count($results);

        return view('admin.reports.print_centralized', compact(
            'results', 'settings', 'category', 'resultsCount', 
            'startDate', 'endDate', 'report', 'branchs'
        ));
    }      
}
