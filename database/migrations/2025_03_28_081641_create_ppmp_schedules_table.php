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
        Schema::create('ppmp_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_request_id');
            $table->foreign('item_request_id')->references('id')->on('item_requests');
            $table->string('month');
            $table->string('year');
            $table->float('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppmp_schedules');
    }
};
