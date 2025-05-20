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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email', 150);
            $table->string('subject', 150);
            $table->text('body')->nullable();
            $table->string('status', 50); // e.g., 'Sent', 'Failed'
            $table->text('error_message')->nullable(); // Store error details if the email fails
            $table->timestamp('sent_at')->nullable(); // When the email was sent
            $table->timestamps(); // Includes created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
