<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['real_estate', 'stocks']);
            $table->decimal('total_value', 20, 2);
            $table->decimal('minimum_investment', 20, 2);
            $table->integer('total_shares');
            $table->integer('available_shares');
            $table->decimal('share_price', 20, 2);
            $table->decimal('expected_roi', 5, 2);
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};