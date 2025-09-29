<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tender_notes', function (Blueprint $table) {
            //  أضف هذا الحقل. سيخزن هوية المُعدِّل
            $table->foreignId('edited_by_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tender_notes', function (Blueprint $table) {
            // هذا الكود للتراجع عن التغيير إذا احتجت
            $table->dropForeign(['edited_by_id']);
            $table->dropColumn('edited_by_id');
        });
    }
};
