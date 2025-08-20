<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            // الخطوة 1: تغيير اسم العمود
            $table->renameColumn('client_name', 'client_type');

            // الخطوة 2: حذف العمود القديم
            $table->dropColumn('quarter');
        });
    }

    public function down(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            // الخطوة 1 (عكسية): إعادة الاسم القديم
            $table->renameColumn('client_type', 'client_name');

            // الخطوة 2 (عكسية): إعادة إنشاء العمود القديم
            $table->string('quarter')->after('status');
        });
    }
};
