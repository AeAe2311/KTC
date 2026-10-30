<?php

namespace Tests\Unit;

use App\Http\Controllers\TrialBalanceController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class TrialBalanceExpensesJournalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('e_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->integer('payment_class')->nullable();
            $table->integer('exp_type')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->string('user_id')->nullable();
            $table->string('store_code')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('expenses_journal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('e_id');
            $table->string('e_transid');
            $table->decimal('e_amount', 10, 2)->nullable();
            $table->string('store_code');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lastname')->nullable();
        });

        Schema::create('revolving_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->integer('payment_class')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->string('particulars')->nullable();
            $table->string('description')->nullable();
            $table->string('inv_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('store_code')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_it_reports_one_journal_amount_on_the_first_linked_line_only(): void
    {
        DB::table('inventory_transactions')->insert([
            [
                'id' => 1,
                'e_id' => 10,
                'category_id' => 100,
                'is_deleted' => 0,
                'payment_class' => 1,
                'exp_type' => 1,
                'amount' => 100,
                'remarks' => 'First expense line',
                'user_id' => null,
                'store_code' => 'A',
                'created_at' => '2026-03-10 09:00:00',
            ],
            [
                'id' => 2,
                'e_id' => 10,
                'category_id' => 200,
                'is_deleted' => 0,
                'payment_class' => 1,
                'exp_type' => 1,
                'amount' => 200,
                'remarks' => 'Second expense line',
                'user_id' => null,
                'store_code' => 'A',
                'created_at' => '2026-03-10 09:01:00',
            ],
            [
                'id' => 3,
                'e_id' => 11,
                'category_id' => 100,
                'is_deleted' => 0,
                'payment_class' => 1,
                'exp_type' => 1,
                'amount' => 50,
                'remarks' => 'Unapproved expense',
                'user_id' => null,
                'store_code' => 'A',
                'created_at' => '2026-03-11 09:00:00',
            ],
            [
                'id' => 4,
                'e_id' => 12,
                'category_id' => 100,
                'is_deleted' => 0,
                'payment_class' => 1,
                'exp_type' => 1,
                'amount' => 25,
                'remarks' => 'Other branch expense',
                'user_id' => null,
                'store_code' => 'B',
                'created_at' => '2026-03-12 09:00:00',
            ],
        ]);

        DB::table('expenses_journal')->insert([
            ['id' => 1, 'e_id' => 10, 'e_transid' => 'EXP-10', 'e_amount' => 260, 'store_code' => 'A'],
            ['id' => 2, 'e_id' => 10, 'e_transid' => 'EXP-10', 'e_amount' => 270, 'store_code' => 'A'],
            ['id' => 3, 'e_id' => 12, 'e_transid' => 'EXP-12', 'e_amount' => 25, 'store_code' => 'B'],
        ]);

        $controller = new TrialBalanceController();
        $storeProperty = new ReflectionProperty(TrialBalanceController::class, 'selectedStoreCode');
        $storeProperty->setAccessible(true);
        $storeProperty->setValue($controller, 'A');

        $fetchRows = new ReflectionMethod(TrialBalanceController::class, 'getPlainBreakdownRows');
        $fetchRows->setAccessible(true);

        $firstLineRows = $fetchRows->invoke($controller, 100, null, '2026-03-31');
        $secondLineRows = $fetchRows->invoke($controller, 200, null, '2026-03-31');

        $this->assertCount(1, $firstLineRows);
        $this->assertSame(270.0, $firstLineRows[0]['debit']);
        $this->assertSame('EXP-10', $firstLineRows[0]['reference_no']);
        $this->assertSame('Mar 10, 2026', $firstLineRows[0]['date']);
        $this->assertCount(0, $secondLineRows);
    }
}
