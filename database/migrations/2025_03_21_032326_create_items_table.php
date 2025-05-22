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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_classification_id')->nullable();
            $table->foreign('item_classification_id')->references('id')->on('item_classifications');
            $table->unsignedBigInteger('item_category_id');
            $table->foreign('item_category_id')->references('id')->on('item_categories');
            $table->unsignedBigInteger('item_unit_id');
            $table->foreign('item_unit_id')->references('id')->on('item_units');
            $table->unsignedBigInteger('variant_id');
            $table->foreign('variant_id')->references('id')->on('variants');
            $table->unsignedBigInteger('snomed_id')->nullable();
            $table->foreign('snomed_id')->references('id')->on('snomeds');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('image')->nullable();
            $table->float('estimated_budget')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->fullText(['name', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('items');
    }
};
