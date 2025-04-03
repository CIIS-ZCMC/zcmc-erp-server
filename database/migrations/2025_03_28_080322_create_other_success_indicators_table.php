<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('other_success_indicators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_objective_id');
            $table->foreign('application_objective_id')->references('id')->on('application_objectives');
            $table->text('description');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_success_indicators');
    }
};
