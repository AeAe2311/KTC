<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_transactions')) {
            return;
        }

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_transactions', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable();
            }

            if (! Schema::hasColumn('inventory_transactions', 'unit_price')) {
                $table->decimal('unit_price', 12, 4)->nullable();
            }

            if (! Schema::hasColumn('inventory_transactions', 'exp_type')) {
                $table->integer('exp_type')->default(0);
            }

            if (! Schema::hasColumn('inventory_transactions', 'unit')) {
                $table->string('unit')->nullable();
            }

            if (! Schema::hasColumn('inventory_transactions', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable();
            }

            if (! Schema::hasColumn('inventory_transactions', 'payment_class')) {
                $table->integer('payment_class')->nullable();
            }

            if (! Schema::hasColumn('inventory_transactions', 'store_code')) {
                $table->string('store_code')->nullable();
            }
        });

        $isPaidColumn = collect(Schema::getColumns('inventory_transactions'))
            ->firstWhere('name', 'is_paid');
        $isPaidType = strtolower($isPaidColumn['type_name'] ?? '');
        $isPaidNullable = $isPaidColumn['nullable'] ?? false;
        $isPaidDefault = trim((string) ($isPaidColumn['default'] ?? ''), "'\"");

        if (! in_array($isPaidType, ['string', 'varchar', 'text', 'character varying'], true)
            || $isPaidNullable
            || $isPaidDefault !== '1') {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                DB::table('inventory_transactions')->whereNull('is_paid')->update(['is_paid' => '1']);
                DB::statement("ALTER TABLE inventory_transactions MODIFY is_paid VARCHAR(255) NOT NULL DEFAULT '1'");
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN is_paid DROP DEFAULT');
                DB::statement("UPDATE inventory_transactions SET is_paid = '1' WHERE is_paid IS NULL");
                DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN is_paid TYPE VARCHAR(255) USING is_paid::text');
                DB::statement("ALTER TABLE inventory_transactions ALTER COLUMN is_paid SET DEFAULT '1'");
                DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN is_paid SET NOT NULL');
            } else {
                throw new \RuntimeException("Cannot convert inventory_transactions.is_paid on the {$driver} driver without Doctrine DBAL.");
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_transactions')
            || ! Schema::hasColumn('inventory_transactions', 'is_paid')) {
            return;
        }

        $hasUnrepresentablePaymentMethods = DB::table('inventory_transactions')
            ->whereNull('is_paid')
            ->orWhereNotIn('is_paid', ['0', '1'])
            ->exists();

        if ($hasUnrepresentablePaymentMethods) {
            throw new \RuntimeException('Cannot restore integer is_paid while nullable or text payment methods are stored.');
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE inventory_transactions MODIFY is_paid INT NOT NULL DEFAULT 1');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN is_paid TYPE INTEGER USING is_paid::integer');
            DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN is_paid SET NOT NULL');
            DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN is_paid SET DEFAULT 1');
        } else {
            throw new \RuntimeException("Cannot restore integer inventory_transactions.is_paid on the {$driver} driver without Doctrine DBAL.");
        }
    }
};
