<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnerships', function (Blueprint $table) {
            $table->id();
            // ✅✅✅ الأعمدة السحرية للعلاقة متعددة الأشكال ✅✅✅
            $table->morphs('partnerable'); // ستنشئ عمودين: partnerable_id و partnerable_type
            
            $table->string('company_name');
            $table->string('person_name');
            $table->string('phone');
            $table->string('email');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnerships');
    }
};
