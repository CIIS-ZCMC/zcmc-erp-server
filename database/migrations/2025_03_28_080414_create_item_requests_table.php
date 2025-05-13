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
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('image')->nullable();
            $table->string('variant')->nullable();
            $table->float('estimated_budget')->default(0);
            $table->string('status')->default("pending");
            $table->unsignedBigInteger('item_unit_id')->nullable();
            $table->foreign('item_unit_id')->references('id')->on('item_units');
            $table->unsignedBigInteger('item_category_id')->nullable();
            $table->foreign('item_category_id')->references('id')->on('item_categories');
            $table->unsignedBigInteger('item_classification_id')->nullable();
            $table->foreign('item_classification_id')->references('id')->on('item_classifications');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->foreign('requested_by')->references('id')->on('users');
            $table->unsignedBigInteger('action_by')->nullable();
            $table->foreign('action_by')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('item_requests');
    }
};
