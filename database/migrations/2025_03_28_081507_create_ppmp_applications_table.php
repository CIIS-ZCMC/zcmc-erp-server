<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ppmp_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aop_application_id');
            $table->foreign('aop_application_id')->references('id')->on('aop_applications');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('division_chief_id');
            $table->foreign('division_chief_id')->references('id')->on('users');
            $table->unsignedBigInteger('budget_officer_id');
            $table->foreign('budget_officer_id')->references('id')->on('users');
            $table->unsignedBigInteger('planning_officer_id');
            $table->foreign('planning_officer_id')->references('id')->on('users');
            $table->uuid('ppmp_application_uuid');
            $table->float('ppmp_total')->default(0);
            $table->string('status')->default("pending");
            $table->boolean('is_draft')->default(false);
            $table->string('remarks')->nullable();
            $table->year('year')->default(Carbon::now()->year);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppmp_applications');
    }
};
