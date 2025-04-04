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
            $table->unsignedBigInteger('item_classification_id');
            $table->foreign('item_classification_id')->references('id')->on('item_classifications');
            $table->unsignedBigInteger('item_category_id');
            $table->foreign('item_category_id')->references('id')->on('item_categories');
            $table->unsignedBigInteger('item_unit_id');
            $table->foreign('item_unit_id')->references('id')->on('item_units');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('image')->nullable();
            $table->string('variant')->nullable();
            $table->float('estimated_budget')->default(0);
            $table->dateTime('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
