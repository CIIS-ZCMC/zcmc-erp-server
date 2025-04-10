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
        Schema::create('type_of_functions', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('type');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_of_functions', function(Blueprint $table){
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('type_of_functions');
    }
};
