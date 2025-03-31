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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aop_application_id')->nullable();
            $table->foreign('aop_application_id')->references('id')->on('aop_applications');
            $table->unsignedBigInteger('ppmp_application_id')->nullable();
            $table->foreign('ppmp_application_id')->references('id')->on('ppmp_applications');
            $table->unsignedBigInteger('action');
            $table->foreign('action')->references('id')->on('users');
            $table->unsignedBigInteger('action_by');
            $table->foreign('action_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
