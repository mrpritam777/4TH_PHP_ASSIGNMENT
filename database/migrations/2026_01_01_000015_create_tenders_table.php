<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('infrastructure_type_id')->constrained('infrastructure_types')->restrictOnDelete();
            $table->string('location');
            $table->decimal('estimated_budget', 14, 2);
            $table->timestamp('deadline');
            $table->json('required_documents')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | closed | awarded | cancelled
            $table->boolean('allow_bid_edit')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('awarded_contractor_id')->nullable()->constrained('contractors')->nullOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->decimal('awarded_amount', 14, 2)->nullable();
            $table->timestamps();

            $table->index(['status', 'deadline']);
            $table->index('infrastructure_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
