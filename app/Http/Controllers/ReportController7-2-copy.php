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
                ->where('store_code', Auth::user()->store_code) 
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
    
    //ken UPDATED method used in DAILY SALES REPORT ken 252306001  May 31, 2026
    private function buildDailySRPaymentData($results): array
    {
        $dailySRPaymentColumns = ['CASH'];
        $dailySRPaymentLogLocalIdMap = [];
        $dailySRPaymentLogMap = [];
        $dailySRPaymentLogNumberMap = [];
        $dailySRRows = [];
        $dailyTotals = ['amount' => 0, 'ar' => 0, 'service_charge' => 0, 'payments' => []]; //  ken addred service_charge AS VALUE TO 2307 IN daily totals
        $arTotals = ['amount' => 0, 'ar' => 0, 'service_charge' => 0, 'payments' => []]; // ken added service_charge AS VALUE TO 2307 in ar totals
        $dailyPaymentBreakdown = [];
        $arPaymentBreakdown = [];

        $dailySRTranIds = [];
        $dailySRTranNumbers = [];
        $dailySRTranNumberCounts = [];
        $dailySRLocalIds = [];
        $dailySRTableIds = [];

        foreach ($results as $dailySRResult) {
            $dailySRLocalId = $dailySRResult->local_id ?? null;
            if (!is_null($dailySRLocalId) && $dailySRLocalId !== '') {
                $dailySRLocalIds[] = $dailySRLocalId;
            }

            $dailySRTranId = $dailySRResult->trans_id ?? $dailySRResult->tran_id ?? $dailySRResult->id ?? null;
            if (!is_null($dailySRTranId) && $dailySRTranId !== '') {
                $dailySRTranIds[] = $dailySRTranId;
            }

            $dailySRTranNumber = trim($dailySRResult->number ?? $dailySRResult->tran_number ?? '');
            if ($dailySRTranNumber !== '') {
                $dailySRTranNumbers[] = $dailySRTranNumber;
                $dailySRTranNumberCounts[$dailySRTranNumber] = ($dailySRTranNumberCounts[$dailySRTranNumber] ?? 0) + 1;
            }

            $dailySRTableId = $dailySRResult->table ?? $dailySRResult->table_id ?? null;
            if (!is_null($dailySRTableId) && $dailySRTableId !== '') {
                $dailySRTableIds[] = $dailySRTableId;
            }
        }

        $dailySRTranIds = array_values(array_unique($dailySRTranIds));
        $dailySRTranNumbers = array_values(array_unique($dailySRTranNumbers));
        $dailySRLocalIds = array_values(array_unique($dailySRLocalIds));
        $dailySRTableIds = array_values(array_unique($dailySRTableIds));

        $dailySRTableNames = [];
        if (!empty($dailySRTableIds)) {
            $dailySRTableNames = DB::table('tables')
                ->whereIn('id', $dailySRTableIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        if (!empty($dailySRLocalIds) || !empty($dailySRTranIds) || !empty($dailySRTranNumbers)) {
            $paymentLogsQuery = DB::table('payment_logs')
                ->select('id', 'local_id', 'tran_id', 'tran_number', 'label', 'amount', 'balance')
                ->where('is_deleted', 0)
                ->where(function ($query) use ($dailySRLocalIds, $dailySRTranIds, $dailySRTranNumbers) {
                    if (!empty($dailySRLocalIds)) {
                        $query->whereIn('local_id', $dailySRLocalIds);
                    }

                    if (!empty($dailySRTranIds)) {
                        if (!empty($dailySRLocalIds)) {
                            $query->orWhereIn('tran_id', $dailySRTranIds);
                        } else {
                            $query->whereIn('tran_id', $dailySRTranIds);
                        }
                    }

                    if (!empty($dailySRTranNumbers)) {
                        if (!empty($dailySRLocalIds) || !empty($dailySRTranIds)) {
                            $query->orWhereIn('tran_number', $dailySRTranNumbers);
                        } else {
                            $query->whereIn('tran_number', $dailySRTranNumbers);
                        }
                    }
                })
                ->orderBy('id', 'desc')
                ->get();

            foreach ($paymentLogsQuery as $dailySRPaymentLog) {
                $dailySRLocalKey = trim($dailySRPaymentLog->local_id ?? '');
                if ($dailySRLocalKey !== '') {
                    if (!isset($dailySRPaymentLogLocalIdMap[$dailySRLocalKey])) {
                        $dailySRPaymentLogLocalIdMap[$dailySRLocalKey] = [];
                    }
                    $dailySRPaymentLogLocalIdMap[$dailySRLocalKey][] = $dailySRPaymentLog;
                }

                $dailySRMapKey = strval($dailySRPaymentLog->tran_id);
                if (!isset($dailySRPaymentLogMap[$dailySRMapKey])) {
                    $dailySRPaymentLogMap[$dailySRMapKey] = [];
                }
                $dailySRPaymentLogMap[$dailySRMapKey][] = $dailySRPaymentLog;

                $dailySRNumberKey = trim($dailySRPaymentLog->tran_number ?? '');
                if ($dailySRNumberKey !== '') {
                    if (!isset($dailySRPaymentLogNumberMap[$dailySRNumberKey])) {
                        $dailySRPaymentLogNumberMap[$dailySRNumberKey] = [];
                    }
                    $dailySRPaymentLogNumberMap[$dailySRNumberKey][] = $dailySRPaymentLog;
                }
            }
        }

        foreach ($results as $result) {
            $tableId = $result->table ?? $result->table_id ?? null;
            $customerName = 'Walk-in';
            if (!is_null($tableId) && $tableId !== '') {
                $customerName = trim($dailySRTableNames[$tableId] ?? $customerName);
            }
            if (empty($customerName)) {
                $customerName = trim($result->payor_name ?? $result->payor_number ?? 'Walk-in');
            }

            $localId = $result->local_id ?? null;
            $tranId = $result->trans_id ?? $result->tran_id ?? $result->id ?? null;
            $tranMapKey = strval($tranId);
            $tranNumberKey = trim($result->number ?? $result->tran_number ?? '');
            $localIdKey = trim((string) $localId);
            $paymentLogs = [];

            if (isset($dailySRPaymentLogMap[$tranMapKey])) {
                $paymentLogs = $dailySRPaymentLogMap[$tranMapKey];
            } elseif ($tranNumberKey !== '' && (($dailySRTranNumberCounts[$tranNumberKey] ?? 0) === 1)) {
                $paymentLogs = $dailySRPaymentLogNumberMap[$tranNumberKey] ?? [];
            } elseif ($localIdKey !== '' && isset($dailySRPaymentLogLocalIdMap[$localIdKey])) {
                $candidateLogs = $dailySRPaymentLogLocalIdMap[$localIdKey];
                foreach ($candidateLogs as $log) {
                    $logTranId = strval($log->tran_id ?? '');
                    $logTranNumber = trim($log->tran_number ?? '');
                    if (($tranMapKey && $logTranId === $tranMapKey) || ($tranNumberKey && $logTranNumber === $tranNumberKey)) {
                        $paymentLogs[] = $log;
                    }
                }
            }

            $paymentLog = !empty($paymentLogs) ? $paymentLogs[0] : null;
            $hasPaymentLog = !empty($paymentLogs);

            $paymentTypeRaw = trim($result->mode_of_payment ?? '');
            $paymentType = $this->normalizePaymentType($paymentTypeRaw);
            $isArMode = in_array($paymentType, ['AR', 'A/R'], true);

            $rowAmount = floatval($result->total ?? 0);
            $paymentLogsByBucket = [];
            $totalPaidAmount = 0.0;
            foreach ($paymentLogs as $paymentLogItem) {
                $logLabel = trim($paymentLogItem->label ?? '');
                $logType = $this->normalizePaymentType($logLabel);
                $bucket = $this->computePaymentBucket($logType, $logLabel, false, true, false) ?? 'CASH';
                $amount = floatval($paymentLogItem->amount ?? 0);
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
                    // For unpaid AR rows with blank mode_of_payment, do not create a fake payment bucket.
                    $totalPaidAmount = 0;
                } else {
                    $bucket = (!is_null($paymentType) && $paymentType !== '') ? $paymentType : 'CASH';
                    $paymentLogsByBucket[$bucket] = $rowAmount;
                    $totalPaidAmount = $rowAmount;
                }
            }

            //ken added start here 20250624001 - ken - added logic to handle overpayment in daily sales report
            // If the customer paid more than the sale total, treat the displayed paid amount as the total only.
            if (!$isArMode && $totalPaidAmount > $rowAmount) {
                $overpayment = $totalPaidAmount - $rowAmount;
                foreach (array_reverse(array_keys($paymentLogsByBucket), true) as $bucketKey) {
                    if ($overpayment <= 0) {
                        break;
                    }

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
            // end of overpayment handling logic

            $arBalance = max(0, $rowAmount - $totalPaidAmount);
            $paidAmountFromLog = $totalPaidAmount;

            $paymentLogBalance = 0;
            foreach ($paymentLogs as $paymentLogItemBalance) {
                $paymentLogBalance = max($paymentLogBalance, floatval($paymentLogItemBalance->balance ?? 0));
            }
            $hasOutstandingBalance = $paymentLogBalance > 0;
            if ($hasOutstandingBalance) {
                $arBalance = max($arBalance, $paymentLogBalance);
            }

            if ($isArMode && $hasPaymentLog) {
                $paymentTypeRaw = trim($paymentLog->label ?? '');
            }

            $paymentType = $this->normalizePaymentType($paymentTypeRaw);

            $isUnpaid = (isset($result->payment_class) && intval($result->payment_class) === 0)
                || (isset($result->is_paid) && intval($result->is_paid) === 0)
                || strtoupper(trim($result->status ?? '')) === 'UNPAID'
                || strtoupper(trim($result->order_status ?? '')) === 'UNPAID';

            if ($isArMode && $hasPaymentLog) {
                $isUnpaid = $arBalance > 0;
            }

            $isNullModeAr = $isUnpaid && $paymentTypeRaw === '';
            $isArFlag = isset($result->ar_collection) && intval($result->ar_collection) === 1;
            $isArRow = $hasOutstandingBalance || $isArMode
                ? ($hasPaymentLog ? ($arBalance > 0) : $isUnpaid)
                : ($isUnpaid && ($isNullModeAr || $isArFlag));

            $reportType = ($hasOutstandingBalance || $isArFlag) ? 'ar' : 'sales';

            if ($reportType === 'sales') {
                $arDisplayAmount = $arBalance;
                $mopDisplayAmount = $totalPaidAmount;
            } else {
                $arDisplayAmount = $isArRow ? $arBalance : 0;
                $mopDisplayAmount = $paidAmountFromLog;
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

            if (!empty($paymentLogsByBucket)) {
                foreach ($paymentLogsByBucket as $bucket => $amount) {
                    if ($bucket === null) {
                        continue;
                    }
                    if ($reportType === 'sales') {
                        $dailyPaymentBreakdown[$bucket] = ($dailyPaymentBreakdown[$bucket] ?? 0) + $amount;
                    }
                    if ($reportType === 'ar') {
                        $arPaymentBreakdown[$bucket] = ($arPaymentBreakdown[$bucket] ?? 0) + $amount;
                    }
                }
            }

            


            $highlightArInSales = ($reportType === 'sales' && $arDisplayAmount > 0);
            $form2307 = $result->form_2307 ?? $result->form2307 ?? ''; /* ken update */
            $serviceCharge = floatval($result->service_charge ?? 0);  /* ken added 20250624001 - ken - added service charge in daily sales report */
            $remarks = $result->remarks ?? $result->status ?? '';

            $dailySRRows[] = (object) [
                'customer_name' => $customerName,
                'number' => $result->number,
                'row_amount' => $rowAmount,
                'service_charge' => $serviceCharge, /* ken added */
                'payment_bucket' => $paymentBucket,
                'payments' => $paymentLogsByBucket,
                'mop_display_amount' => $mopDisplayAmount,
                'ar_display_amount' => $arDisplayAmount,
                'report_type' => $reportType,
                'form2307' => $form2307,
                'remarks' => $remarks,
                'highlight_ar_in_sales' => $highlightArInSales,
                'status' => strtoupper(trim($result->status ?? '')),
                'order_status' => strtoupper(trim($result->order_status ?? '')),
            ];

            if ($reportType === 'sales') {   /* //DITO ALNG PAPALITAN */
                $dailyTotals['amount'] += $rowAmount;
                $dailyTotals['ar'] += $arDisplayAmount;
                $dailyTotals['service_charge'] += $serviceCharge;  // ken added 20250624001 - ken - added service charge in daily sales report
                if ($paymentBucket !== null) {
                    $dailyPaymentBreakdown[$paymentBucket] = ($dailyPaymentBreakdown[$paymentBucket] ?? 0); // ensure key exists
                }
            }

            if ($reportType === 'ar') {
                $arTotals['amount'] += $rowAmount;
                $arTotals['ar'] += $arDisplayAmount;
                $arTotals['service_charge'] += $serviceCharge;  // ken added 20250624001 - ken - added service charge in daily sales report
                if ($paymentBucket !== null) {
                    $arPaymentBreakdown[$paymentBucket] = ($arPaymentBreakdown[$paymentBucket] ?? 0); // ensure key exists
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
        $dailySRPaymentColumns = $orderedDailySRPaymentColumns;

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
    // HANGANG DITO LANG ANG NA UPDATE SA METHOD NA TO. MAY 31, 2026

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
        $branchs = DB::table('settings')
        //->where('store_code', Auth::user()->store_code) 
        ->orderBy('name', 'asc')
        ->where('store_code', Auth::user()->store_code)
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
        // Initialize resultsCount to prevent undefined variable error
        $resultsCount = 0;
        // Get current user and their branch for filtering
        $user = Auth::user();
        
        // Get selected branch from form (only for admin)
        $selectedBranch = null;
        $userBranch = null;

        if ($user->role == 1) {
            // Admin user: accept explicit branch selection only if it belongs to the admin's store
            $selectedBranch = $request->branch_code ?? 'all';
            if ($request->has('branch_code') && $request->branch_code != 'all') {
                // Verify the branch exists and belongs to the user's store_code
                $branchExists = DB::table('settings')
                    ->where('branch_code', $request->branch_code)
                    ->where('store_code', $user->store_code)
                    ->where('is_deleted', 0)
                    ->exists();

                if ($branchExists) {
                    $userBranch = $request->branch_code; // Filter by selected branch
                } else {
                    // Selected branch does not belong to this user's store; ignore selection
                    $userBranch = null;
                }
            }
        } else {
            // Non-admin user - use their assigned branch
            $userBranch = $user->branch;
            $selectedBranch = $userBranch;
        }

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
        
         // ken added start here 20250623001 - ken - added 4-19-2025 dailysalereport and Accounting report (income statement) 4-25-2026 ken 252306001
        if ($category == 'dailySR') {
            $results = Transaction::where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where(function ($query) {  
                                // adjust by harold 5-17 start
                    // $query->where('transactions.status', '!=', 'UNPAID')       
                    //       ->orWhere('transactions.order_status', '!=', 'CANCELLED');
                          $query->where('transactions.order_status', '!=', 'CANCELLED');
                        // adjust by harold 5-17 end
                });

            if ($userBranch) {
                $results->where(function ($query) use ($userBranch) {
                    $query->where('transactions.branch_code', $userBranch)
                          ->orWhere('transactions.branch', $userBranch);
                });
            } elseif (!empty($branchCodes)) {
                $results->where(function ($query) use ($branchCodes) {
                    $query->whereIn('transactions.branch_code', $branchCodes)
                          ->orWhereIn('transactions.branch', $branchCodes);
                });
            }

            $results = $results->orderBy('transactions.id', 'desc')->get();
            $resultsCount = $results->count();

            $dailySRData = $this->buildDailySRPaymentData($results);
            $dailySRRows = $dailySRData['dailySRRows'];
            $dailySRPaymentColumns = $dailySRData['dailySRPaymentColumns'];
            $dailyTotals = $dailySRData['dailyTotals'];
            $arTotals = $dailySRData['arTotals'];

            return view('admin.reports.centralized', compact(
                'branchs',
                'results',
                'category',
                'report',
                'startDate',
                'endDate',
                'resultsCount',
                'selectedBranch',
                // 'selectedBranchName',
                'dailySRRows',
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

                    if ($userBranch) {
                        $salesQuery->where('branch_code', $userBranch);
                    } elseif (!empty($branchCodes)) {
                        $salesQuery->whereIn('branch_code', $branchCodes);
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
                    ->where('id', 1)
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
                if ($userBranch) {
                    $results->where('branch', $userBranch);
                }
                
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
