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
        Schema::create('other_tenders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number')->nullable();
            $table->string('client_type')->nullable();
            $table->string('assigned_to')->nullable();
            $table->date('date_of_purchase')->nullable();
            $table->date('date_of_submission');
            $table->string('reviewed_by')->nullable();
            $table->date('date_of_submission_ba')->nullable();
            $table->date('date_of_submission_after_review')->nullable();
            $table->boolean('has_third_party')->default(false);
            $table->date('last_follow_up_date')->nullable();
            $table->string('follow_up_channel')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->string('status')->default('pending');
            $table->text('reason_of_decline')->nullable();
            $table->timestamps(); // يضيف created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_tenders');
    }
};
