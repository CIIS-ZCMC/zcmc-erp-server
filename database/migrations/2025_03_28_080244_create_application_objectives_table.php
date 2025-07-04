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
        Schema::create('application_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aop_application_id');
            $table->foreign('aop_application_id')->references('id')->on('aop_applications');
            $table->unsignedBigInteger('objective_id')->nullable();;
            $table->foreign('objective_id')->references('id')->on('objectives');
            $table->unsignedBigInteger('success_indicator_id')->nullable();;
            $table->foreign('success_indicator_id')->references('id')->on('success_indicators');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_objectives');
    }
};
