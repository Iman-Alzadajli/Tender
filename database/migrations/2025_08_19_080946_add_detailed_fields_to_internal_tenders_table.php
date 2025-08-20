<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            // لا يوجد إعادة تسمية، فقط إضافة أعمدة جديدة
            $table->date('date_of_purchase')->nullable()->after('status');
            $table->string('reviewed_by')->nullable()->after('date_of_purchase');
            $table->date('date_of_submission_ba')->nullable()->after('reviewed_by');
            $table->date('date_of_submission_after_review')->nullable()->after('date_of_submission_ba');
            $table->boolean('has_third_party')->default(false)->after('date_of_submission_after_review');
            $table->date('last_follow_up_date')->nullable()->after('has_third_party');
            $table->string('follow_up_channel')->nullable()->after('last_follow_up_date');
            $table->text('follow_up_notes')->nullable()->after('follow_up_channel');
            $table->text('reason_of_decline')->nullable()->after('follow_up_notes');
        });
    }

    public function down(): void
    {
        Schema::table('internal_tenders', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_purchase',
                'reviewed_by',
                'date_of_submission_ba',
                'date_of_submission_after_review',
                'has_third_party',
                'last_follow_up_date',
                'follow_up_channel',
                'follow_up_notes',
                'reason_of_decline',
            ]);
        });
    }
};
