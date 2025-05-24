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
        Schema::create('item_reference_terminologies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('system'); // Variant, Snomed, etc..
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->fullText([ 'code', 'identifier',  'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_reference_terminologies', function(Blueprint $table){
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('item_reference_terminologies');
    }
};
