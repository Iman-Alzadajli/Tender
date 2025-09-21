<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['internal_tenders', 'e_tenders', 'other_tenders'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // إضافة الأعمدة الجديدة بعد عمود has_third_party
                $table->string('partnership_company')->nullable()->after('has_third_party');
                $table->string('partnership_person')->nullable()->after('partnership_company');
                $table->string('partnership_phone')->nullable()->after('partnership_person');
                $table->string('partnership_email')->nullable()->after('partnership_phone');
                $table->text('partnership_details')->nullable()->after('partnership_email');
            });
        }
    }

    public function down(): void
    {
        $tables = ['internal_tenders', 'e_tenders', 'other_tenders'];
        $columns = ['partnership_company', 'partnership_person', 'partnership_phone', 'partnership_email', 'partnership_details'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
