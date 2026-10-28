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
        Schema::create('slack_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('webhook_url', 500);
            $table->string('channel', 100)->nullable();
            $table->json('events');
            $table->boolean('is_active')->default(true);
            $table->string('secret', 100)->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_webhooks');
    }
};
