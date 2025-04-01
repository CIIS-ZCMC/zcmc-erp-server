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
        Schema::create('comment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_comment_id');
            $table->foreign('activity_comment_id')->references('id')->on('activity_comments');
            $table->unsignedBigInteger('action_by');
            $table->foreign('action_by')->references('id')->on('users');
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_logs');
    }
};
