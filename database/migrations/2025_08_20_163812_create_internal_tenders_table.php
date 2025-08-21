<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_tenders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number');
            $table->string('client_type');
            $table->string('assigned_to');
            $table->date('date_of_purchase')->nullable();
            $table->date('date_of_submission')->nullable();
            $table->string('reviewed_by');
            $table->date('date_of_submission_ba')->nullable();
            $table->date('date_of_submission_after_review')->nullable();
            $table->boolean('has_third_party')->default(false);
            $table->date('last_follow_up_date')->nullable();
            $table->string('follow_up_channel');
            $table->text('follow_up_notes')->nullable();
            $table->string('status')->default('Pending');
            $table->text('reason_of_decline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_tenders');
    }
};
