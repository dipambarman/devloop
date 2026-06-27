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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('email');
            $table->json('skills')->nullable()->after('bio');
            $table->string('github_url')->nullable()->after('skills');
            $table->string('portfolio_url')->nullable()->after('github_url');
            $table->string('linkedin_url')->nullable()->after('portfolio_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'skills', 'github_url', 'portfolio_url', 'linkedin_url']);
        });
    }
};
