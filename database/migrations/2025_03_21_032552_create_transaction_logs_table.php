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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('log_description_id');
            $table->foreign('log_description_id')->references('id')->on('log_descriptions');
            $table->string('user_name')->nullable();
            $table->unsignedBigInteger('referrence_id')->nullable();
            $table->string('referrence_type')->nullable();
            $table->string('ip');
            $table->json('metadata')->nullable();
            $table->json('issue')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
