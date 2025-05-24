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
        Schema::create('application_timelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aop_application_id')->nullable();
            $table->foreign('aop_application_id')->references('id')->on('aop_applications');
            $table->unsignedBigInteger('ppmp_application_id')->nullable();
            $table->foreign('ppmp_application_id')->references('id')->on('ppmp_applications');
            $table->unsignedBigInteger('user_id')->comment('User who submitted the application (AOP or PPMP)')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('approver_user_id')->comment('User who approved or processed the application')->nullable();
            $table->foreign('approver_user_id')->references('id')->on('users');
            $table->unsignedBigInteger('current_area_id')->nullable();
            $table->foreign('current_area_id')->references('id')->on('assigned_areas');
            $table->unsignedBigInteger('next_area_id')->nullable();
            $table->foreign('next_area_id')->references('id')->on('assigned_areas');
            $table->enum('status', ['pending', 'approved', 'returned'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('date_approved')->nullable();
            $table->timestamp('date_returned')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_timelines');
    }
};
