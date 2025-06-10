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
            $table->unsignedBigInteger('ppmp_item_id');
            $table->foreign('ppmp_item_id')->references('id')->on('ppmp_items');
            $table->string('month');
            $table->year('year')->default(date('Y') + 1);
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
