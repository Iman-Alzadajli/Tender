<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            // نضيف الحقل الجديد بعد حقل client_type
            $table->string('client_name')->nullable()->after('client_type');
        });
    }

    public function down(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            $table->dropColumn('client_name');
        });
    }
};
