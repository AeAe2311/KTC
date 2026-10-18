<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class CreditLimit
{
    public static function violationForNewCredit($tableId, float $newAmount, ?int $excludeTransactionId = null): ?array
    {
        $snapshot = self::snapshot($tableId, $excludeTransactionId);
        $newOutstanding = $snapshot['outstanding_balance'] + $newAmount;

        if ($newOutstanding <= $snapshot['credit_limit'] + 0.0001) {
            return null;
        }

        return [
            'credit_limit' => $snapshot['credit_limit'],
            'outstanding_balance' => $snapshot['outstanding_balance'],
            'new_amount' => $newAmount,
            'new_outstanding' => $newOutstanding,
            'credits_remaining' => $snapshot['credit_limit'] - $snapshot['outstanding_balance'],
        ];
    }

    public static function message(array $violation): string
    {
        return sprintf(
            'Credit limit exceeded. Remaining credit is %s, but this transaction needs %s.',
            number_format($violation['credits_remaining'], 2, '.', ','),
            number_format($violation['new_amount'], 2, '.', ',')
        );
    }

    public static function snapshot($tableId, ?int $excludeTransactionId = null): array
    {
        $storeCode = auth()->user()->store_code ?? null;
        $table = DB::table('tables')->where('id', $tableId)->first();
        $creditLimit = (float) ($table->credit_limit ?? 0);

        $arOutstanding = DB::table('account_receivables')
            ->where('table', $tableId)
            ->where('balance', '>', 0)
            ->where('order_status', '!=', 'CANCELLED');
        self::scopeStore($arOutstanding, $storeCode);

        $unpaidTransactions = DB::table('transactions')
            ->where('table', $tableId)
            ->where('status', 'UNPAID')
            ->whereNotIn('order_status', ['CANCELLED', 'COMPLETED']);
        self::scopeStore($unpaidTransactions, $storeCode);

        if ($excludeTransactionId) {
            $unpaidTransactions->where('id', '!=', $excludeTransactionId);
        }

        $outstandingBalance = (float) $arOutstanding->sum('balance')
            + (float) $unpaidTransactions->sum('total');

        return [
            'credit_limit' => $creditLimit,
            'outstanding_balance' => $outstandingBalance,
            'credits_remaining' => $creditLimit - $outstandingBalance,
        ];
    }

    private static function scopeStore($query, ?string $storeCode): void
    {
        if (!$storeCode) {
            return;
        }

        $query->where(function ($scope) use ($storeCode) {
            $scope->where('store_code', $storeCode)
                ->orWhereNull('store_code');
        });
    }
}
