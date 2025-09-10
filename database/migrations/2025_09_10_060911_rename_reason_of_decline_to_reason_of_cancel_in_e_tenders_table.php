<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_tenders', function (Blueprint $table) {
            // إعادة تسمية العمود في جدول e_tenders
            $table->renameColumn('reason_of_decline', 'reason_of_cancel');
        });
    }

    public function down(): void
    {
        Schema::table('e_tenders', function (Blueprint $table) {
            // التراجع عن التغيير
            $table->renameColumn('reason_of_cancel', 'reason_of_decline');
        });
    }
};
