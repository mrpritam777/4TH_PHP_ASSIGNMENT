<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_id')->unique()->constrained('infrastructures')->cascadeOnDelete();
            $table->string('hospital_type', 100);
            $table->unsignedInteger('beds')->default(0);
            $table->string('emergency_phone', 20)->nullable();
            $table->boolean('emergency_facility')->default(true);
            $table->string('departments', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_details');
    }
};
