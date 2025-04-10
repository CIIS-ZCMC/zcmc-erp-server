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
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->string('code');
            $table->unsignedBigInteger('type_of_function_id');
            $table->foreign('type_of_function_id')->references('id')->on('type_of_functions');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objectives', function(Blueprint $table){
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('objectives');
    }
};
