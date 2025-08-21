<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focal_points', function (Blueprint $table) {
            $table->id();
            // هذا أهم سطر: يربط هذا الجدول بجدول المناقصات
            $table->foreignId('internal_tender_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('department');
            $table->text('other_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focal_points');
    }
};
