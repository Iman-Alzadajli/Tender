<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['internal_tenders', 'e_tenders', 'other_tenders'];
        $columnsToRemove = [
            'partnership_company',
            'partnership_person',
            'partnership_phone',
            'partnership_email',
            'partnership_details',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumns($table, $columnsToRemove)) {
                Schema::table($table, function (Blueprint $table) use ($columnsToRemove) {
                    $table->dropColumn($columnsToRemove);
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['internal_tenders', 'e_tenders', 'other_tenders'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('partnership_company')->nullable();
                $table->string('partnership_person')->nullable();
                $table->string('partnership_phone')->nullable();
                $table->string('partnership_email')->nullable();
                $table->text('partnership_details')->nullable();
            });
        }
    }
};
