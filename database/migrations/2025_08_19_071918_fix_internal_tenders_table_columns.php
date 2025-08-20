<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * هذه الدالة مسؤولة عن إنشاء الجدول.
     */
    public function up(): void
    {
        Schema::create('internal_tenders', function (Blueprint $table) {
            $table->id(); // عمود الرقم التعريفي (Primary Key)
            $table->string('name'); // اسم المناقصة
            $table->string('number')->nullable(); // رقم المناقصة (اختياري)
            $table->string('client_name'); // اسم العميل
            $table->string('assigned_to')->nullable(); // مُسندة إلى (اختياري)
            $table->date('date_of_submission'); // تاريخ التقديم
            $table->string('quarter'); // الربع السنوي
            $table->string('status')->default('pending'); // الحالة (القيمة الافتراضية هي 'pending')
            $table->timestamps(); // تضيف حقلي created_at و updated_at تلقائيًا
        });
    }

    /**
     * Reverse the migrations.
     * هذه الدالة مسؤولة عن حذف الجدول عند التراجع عن الهجرة (rollback).
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_tenders');
    }
};
