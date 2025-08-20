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
        Schema::create('focal_points', function (Blueprint $table) {
            $table->id();
            
            // ربط نقطة الاتصال بالمناقصة التابعة لها
            // onDelete('cascade') يعني أنه إذا تم حذف مناقصة، سيتم حذف كل نقاط الاتصال المرتبطة بها
            $table->foreignId('internal_tender_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('department');
            $table->text('other_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('focal_points');
    }
};
