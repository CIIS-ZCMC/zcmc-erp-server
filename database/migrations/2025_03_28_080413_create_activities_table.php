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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_objective_id');
            $table->foreign('application_objective_id')->references('id')->on('application_objectives');
            $table->uuid('activity_uuid');
            $table->string('activity_code');
            $table->string('name');
            $table->boolean('is_gad_related');
            $table->boolean('is_reviewed')->default(false);
            $table->float('cost')->default(0);
            $table->date('start_month');
            $table->date('end_month');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
