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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->boolean('customization')->default(false);
            $table->string('customization_note', 180)->nullable();
            $table->string('purpose', 40);
            $table->date('desired_date')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('customization_unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->boolean('is_demo')->default(true);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
