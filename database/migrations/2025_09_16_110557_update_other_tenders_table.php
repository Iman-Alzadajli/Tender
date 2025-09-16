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
            // date_of_submission_ba إلى last_date_of_clarification
            $table->renameColumn('date_of_submission_ba', 'last_date_of_clarification');
            
            // إضافة عمود جديد submission_by
            $table->string('submission_by')->nullable()->after('last_date_of_clarification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_tenders', function (Blueprint $table) {
       
            $table->renameColumn('last_date_of_clarification', 'date_of_submission_ba');
            $table->dropColumn('submission_by');
        });
    }
};