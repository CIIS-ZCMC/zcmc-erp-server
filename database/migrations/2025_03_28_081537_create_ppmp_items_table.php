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
        Schema::create('ppmp_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppmp_application_id');
            $table->foreign('ppmp_application_id')->references('id')->on('ppmp_applications');
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('items');
            $table->unsignedBigInteger('procurement_mode_id')->nullable();
            $table->foreign('procurement_mode_id')->references('id')->on('procurement_modes');
            $table->unsignedBigInteger('item_request_id')->nullable();
            $table->foreign('item_request_id')->references('id')->on('item_requests');
            $table->float('total_quantity')->default(0);
            $table->float('estimated_budget')->default(0);
            $table->float('total_amount')->default(0);
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppmp_items');
    }
};
