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
        Schema::table('bugs', function (Blueprint $table) {
            $table->string('environment', 50)->default('production')->after('severity');
            $table->string('url', 2048)->nullable()->after('raw_stack');
            $table->string('user_agent', 512)->nullable()->after('url');
            $table->json('context')->nullable()->after('user_agent');

            $table->index(['project_id', 'environment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'environment']);
            $table->dropColumn(['environment', 'url', 'user_agent', 'context']);
        });
    }
};
