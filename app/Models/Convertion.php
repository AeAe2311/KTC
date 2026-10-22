<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Convertion extends Model
{
    use HasFactory;

    protected static array $unitAliases = [
        'pcs' => 'pc/s',
        'pc' => 'pc/s',
        'piece' => 'pc/s',
        'pieces' => 'pc/s',
        'pc/s' => 'pc/s',
        'kg' => 'kg',
        'kgs' => 'kg',
        'kilogram' => 'kg',
        'kilograms' => 'kg',
        'g' => 'g',
        'gram' => 'g',
        'grams' => 'g',
        'l' => 'l',
        'liter' => 'l',
        'liters' => 'l',
        'ml' => 'ml',
        'milliliter' => 'ml',
        'milliliters' => 'ml',
    ];

    public static function normalizeUnit(string $unit): string
    {
        $unit = trim(strtolower($unit), "'\" ");
        $unit = preg_replace('/\s+/', '', $unit);

        return self::$unitAliases[$unit] ?? $unit;
    }

    public static function convertQuantity(float $quantity, string $from, string $to): float
    {
        $from = self::normalizeUnit($from);
        $to = self::normalizeUnit($to);

        if ($from === $to) {
            return $quantity;
        }

        // Try to find direct conversion
        $conversion = self::where('from', $from)
            ->where('to', $to)
            ->first();

        if ($conversion) {
            return $quantity * floatval($conversion->value);
        }

        // Try reverse conversion
        $reverseConversion = self::where('from', $to)
            ->where('to', $from)
            ->first();

        if ($reverseConversion) {
            return $quantity / floatval($reverseConversion->value);
        }

        // Log warning and return original quantity instead of throwing exception
        Log::warning('Missing unit conversion', [
            'from' => $from,
            'to' => $to,
            'quantity' => $quantity
        ]);
        
        // Return the original quantity as-is (1:1 conversion) instead of throwing error
        return $quantity;
        
        // OR if you want to throw a more helpful exception:
        // throw new \Exception("Missing unit conversion from '{$from}' to '{$to}'. Please add this conversion in the conversions table.");
    }
}