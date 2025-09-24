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
        Schema::table('users', function (Blueprint $table) {
            // ✅ إضافة عمود الحالة، مع قيمة افتراضية 'active'
            $table->string('status')->default('active')->after('remember_token');

            // ✅ إضافة عمود تاريخ آخر تسجيل دخول، يمكن أن يكون فارغاً
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // لحذف الأعمدة عند التراجع عن الـ migration
            $table->dropColumn(['status', 'last_login_at']);
        });
    }
};
