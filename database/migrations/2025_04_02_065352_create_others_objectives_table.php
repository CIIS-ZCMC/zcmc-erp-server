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
        Schema::create('others_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_objective_id');
            $table->foreign('application_objective_id')->references('id')->on('application_objectives');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('others_objectives');
    }
};
