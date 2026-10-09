<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('road_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_id')->unique()->constrained('infrastructures')->cascadeOnDelete();
            $table->string('road_type', 100);
            $table->string('start_point')->nullable();
            $table->string('end_point')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->unsignedInteger('lanes')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_details');
    }
};
