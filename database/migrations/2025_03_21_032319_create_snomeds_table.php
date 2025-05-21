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
        Schema::create('snomeds', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->softDeletes();
            $table->timestamps();

            $table->fullText(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snomeds', function(Blueprint $table){
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('snomeds');
    }
};
