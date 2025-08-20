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
        Schema::table('internal_tenders', function (Blueprint $table) {
            // قم بتغيير اسم العمود من الاسم الخاطئ إلى الاسم الصحيح
            $table->renameColumn('date_of_submisson', 'date_of_submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            // في حالة التراجع، قم بإعادة الاسم إلى الحالة الخاطئة
            $table->renameColumn('date_of_submission', 'date_of_submisson');
        });
    }
};
