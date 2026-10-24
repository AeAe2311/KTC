<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(
                ['store_code', 'status', 'order_status', 'created_at'],
                'transactions_menu_ranking_filter_index'
            );
            $table->index('local_id', 'transactions_local_id_index');
        });

        Schema::table('ordered', function (Blueprint $table) {
            $table->index(['tran_id', 'menu_uuid'], 'ordered_menu_ranking_join_index');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->index('is_uuid', 'menus_is_uuid_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_menu_ranking_filter_index');
            $table->dropIndex('transactions_local_id_index');
        });

        Schema::table('ordered', function (Blueprint $table) {
            $table->dropIndex('ordered_menu_ranking_join_index');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_is_uuid_index');
        });
    }
};
