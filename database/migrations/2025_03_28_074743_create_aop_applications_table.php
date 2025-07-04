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
        Schema::create('aop_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('division_chief_id');
            $table->foreign('division_chief_id')->references('id')->on('users');
            $table->unsignedBigInteger('mcc_chief_id');
            $table->foreign('mcc_chief_id')->references('id')->on('users');
            $table->unsignedBigInteger('planning_officer_id');
            $table->foreign('planning_officer_id')->references('id')->on('users');
            $table->uuid('aop_application_uuid');
            $table->integer('sector_id');
            $table->string('sector');
            $table->text('mission')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('has_discussed')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('year')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aop_applications');
    }
};
