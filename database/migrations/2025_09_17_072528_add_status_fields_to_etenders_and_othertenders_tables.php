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
        // --- أولاً: تعديل جدول e_tenders ---
        Schema::table('e_tenders', function (Blueprint $table) {
            // حقل لحالة "Under Evaluation"
            $table->decimal('submitted_price', 15, 2)->nullable()->after('reason_of_cancel');

            // حقل لحالة "Awarded to Others (loss)"
            $table->decimal('awarded_price', 15, 2)->nullable()->after('submitted_price');

            // حقل لحالة "Recall"
            $table->string('reason_of_recall')->nullable()->after('awarded_price');
        });

        // --- ثانياً: تعديل جدول other_tenders ---
        Schema::table('other_tenders', function (Blueprint $table) {
            // حقل لحالة "Under Evaluation"
            $table->decimal('submitted_price', 15, 2)->nullable()->after('reason_of_cancel');

            // حقل لحالة "Awarded to Others (loss)"
            $table->decimal('awarded_price', 15, 2)->nullable()->after('submitted_price');

            // حقل لحالة "Recall"
            $table->string('reason_of_recall')->nullable()->after('awarded_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // --- التراجع عن التعديلات في جدول e_tenders ---
        Schema::table('e_tenders', function (Blueprint $table) {
            $table->dropColumn(['submitted_price', 'awarded_price', 'reason_of_recall']);
        });

        // --- التراجع عن التعديلات في جدول other_tenders ---
        Schema::table('other_tenders', function (Blueprint $table) {
            $table->dropColumn(['submitted_price', 'awarded_price', 'reason_of_recall']);
        });
    }
};
