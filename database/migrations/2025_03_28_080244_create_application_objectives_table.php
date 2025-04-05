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
            $table->unsignedBigInteger('function_objective_id');
            $table->foreign('function_objective_id')->references('id')->on('function_objectives');
            $table->string('objective_code');
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
