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
        Schema::create('terminologies_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("category_id")->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unsignedBigInteger("reference_terminology_id")->nullable();
            $table->foreign('reference_terminology_id')->references('id')->on('reference_terminologies');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminologies_categories', function(Blueprint $table){
            $table->dropSoftDeletes();
        });
        
        Schema::dropIfExists('terminologies_categories');
    }
};
