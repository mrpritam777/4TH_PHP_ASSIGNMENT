<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_id')->unique()->constrained('infrastructures')->cascadeOnDelete();
            $table->string('bridge_type', 100);
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->unsignedInteger('lanes')->default(2);
            $table->string('material', 100)->nullable();
            $table->string('load_capacity', 100)->nullable();
            $table->string('structural_condition', 20)->default('good');
            $table->string('surface_condition', 20)->default('good');
            $table->string('safety_status', 20)->default('safe');
            $table->date('last_inspection_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_details');
    }
};
