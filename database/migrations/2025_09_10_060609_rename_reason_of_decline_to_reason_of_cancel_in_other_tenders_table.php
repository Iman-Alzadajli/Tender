<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('other_tenders', function (Blueprint $table) {
          
            $table->renameColumn('reason_of_decline', 'reason_of_cancel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_tenders', function (Blueprint $table) {
          
            $table->renameColumn('reason_of_cancel', 'reason_of_decline');
        });
    }
};
