<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infrastructures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('infrastructure_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->smallInteger('construction_year')->nullable();
            $table->string('condition', 20)->default('good');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type_id', 'condition']);
            $table->index(['name', 'code', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructures');
    }
};
