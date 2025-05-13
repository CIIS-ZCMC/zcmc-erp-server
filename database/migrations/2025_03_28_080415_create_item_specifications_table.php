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
        Schema::create('item_specifications', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->foreign('item_id')->references('id')->on('items');
            $table->unsignedBigInteger('item_request_id')->nullable();
            $table->foreign('item_request_id')->references('id')->on('item_requests');
            $table->unsignedBigInteger('item_specification_id')->nullable();
            $table->foreign('item_specification_id')->references('id')->on('item_specifications');
            $table->softDeletes();
            $table->timestamps();
            
            $table->fullText(['description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_specifications', function(Blueprint $table){
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('item_specifications');
    }
};
