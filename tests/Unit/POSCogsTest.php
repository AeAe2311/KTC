<?php

namespace Tests\Unit;

use App\Http\Controllers\POSController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class POSCogsTest extends TestCase
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

        Schema::create('purchase_ordered', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('menu_id');
            $table->string('store_code');
            $table->integer('remaining_stocks');
            $table->decimal('price', 10, 4);
            $table->string('sales_invoice')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('ordered_cogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tran_local_id');
            $table->string('tran_uuid');
            $table->unsignedInteger('menu_id');
            $table->string('ordered_uuid');
            $table->unsignedInteger('po_id');
            $table->string('si_number')->nullable();
            $table->integer('quantity');
            $table->decimal('po_price', 10, 4);
            $table->decimal('po_total', 10, 4);
            $table->string('status')->nullable();
            $table->string('store_code');
            $table->string('branch_code');
            $table->timestamps();
        });

        $user = new User();
        $user->forceFill([
            'store_code' => 'store-a',
            'branch_code' => 'branch-a',
        ]);
        Auth::setUser($user);
    }

    public function test_it_records_fifo_allocations_only_for_the_active_store(): void
    {
        DB::table('purchase_ordered')->insert([
            [
                'id' => 1,
                'menu_id' => 10,
                'store_code' => 'store-a',
                'remaining_stocks' => 2,
                'price' => 3.50,
                'sales_invoice' => 'SI-1',
            ],
            [
                'id' => 2,
                'menu_id' => 10,
                'store_code' => 'store-a',
                'remaining_stocks' => 5,
                'price' => 4.25,
                'sales_invoice' => 'SI-2',
            ],
            [
                'id' => 3,
                'menu_id' => 10,
                'store_code' => 'store-b',
                'remaining_stocks' => 20,
                'price' => 2.00,
                'sales_invoice' => 'SI-OTHER',
            ],
            [
                'id' => 4,
                'menu_id' => 11,
                'store_code' => 'store-a',
                'remaining_stocks' => 2,
                'price' => 1.75,
                'sales_invoice' => 'SI-PARTIAL',
            ],
        ]);

        $transaction = (object) [
            'id' => 42,
            'local_id' => null,
            'is_uuid' => 'transaction-uuid',
            'branch_code' => 'branch-a',
        ];
        $recordPosCogs = new ReflectionMethod(POSController::class, 'recordPosCogs');
        $controller = new POSController();

        $recordPosCogs->invoke($controller, $transaction, 10, 4, 'ordered-uuid-1');
        $recordPosCogs->invoke($controller, $transaction, 11, 5, 'ordered-uuid-2');
        $recordPosCogs->invoke($controller, $transaction, 12, 1, 'ordered-uuid-no-lot');

        $this->assertSame(0, (int) DB::table('purchase_ordered')->where('id', 1)->value('remaining_stocks'));
        $this->assertSame(3, (int) DB::table('purchase_ordered')->where('id', 2)->value('remaining_stocks'));
        $this->assertSame(20, (int) DB::table('purchase_ordered')->where('id', 3)->value('remaining_stocks'));
        $this->assertSame(0, (int) DB::table('purchase_ordered')->where('id', 4)->value('remaining_stocks'));

        $allocations = DB::table('ordered_cogs')->orderBy('id')->get();
        $this->assertCount(3, $allocations);
        $this->assertSame([2, 2, 2], $allocations->pluck('quantity')->map(fn ($quantity) => (int) $quantity)->all());
        $this->assertSame([1, 2, 4], $allocations->pluck('po_id')->map(fn ($poId) => (int) $poId)->all());
        $this->assertSame([7.0, 8.5, 3.5], $allocations->pluck('po_total')->map(fn ($total) => (float) $total)->all());
        $this->assertSame('transaction-uuid', $allocations->first()->tran_uuid);
        $this->assertSame('42', $allocations->first()->tran_local_id);
        $this->assertSame('ordered-uuid-1', $allocations->first()->ordered_uuid);
        $this->assertSame('SALES', $allocations->first()->status);
        $this->assertSame('store-a', $allocations->first()->store_code);
        $this->assertSame('branch-a', $allocations->first()->branch_code);
    }
}
