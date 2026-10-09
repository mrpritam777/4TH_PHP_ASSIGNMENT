<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_id')->nullable()->constrained('infrastructures')->nullOnDelete();
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_type', 30)->default('other');
            $table->timestamps();

            $table->index(['contact_type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
