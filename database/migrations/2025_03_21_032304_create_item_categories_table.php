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
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('item_category_id')->nullable();
            $table->foreign('item_category_id')->references('id')->on('item_categories')->onDelete('set null');
            $table->unsignedBigInteger('item_reference_terminology_id')->nullable();
            $table->foreign('item_reference_terminology_id')->references('id')->on('item_reference_terminologies');
            $table->softDeletes();
            $table->timestamps();
            
            $table->fullText(['name', 'code', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_categories', function(Blueprint $table){
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('item_categories');
    }
};
