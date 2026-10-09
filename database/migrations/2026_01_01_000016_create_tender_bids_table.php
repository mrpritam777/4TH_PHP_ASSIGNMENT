<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained('contractors')->cascadeOnDelete();
            $table->decimal('bid_amount', 14, 2);
            $table->text('proposal');
            $table->json('documents')->nullable();
            $table->string('status', 20)->default('submitted'); // submitted | under_review | rejected | awarded
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['tender_id', 'contractor_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_bids');
    }
};
