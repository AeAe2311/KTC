<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordered', function (Blueprint $table) {
            $table->index(
                ['store_code', 'tran_id', 'menu_uuid'],
                'ordered_branch_menu_ranking_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ordered', function (Blueprint $table) {
            $table->dropIndex('ordered_branch_menu_ranking_index');
        });
    }
};
