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
            $table->string('github_id')->nullable()->unique()->after('email');
            $table->string('github_username')->nullable()->index()->after('github_id');
            $table->string('avatar')->nullable()->after('github_username');
            $table->text('github_token')->nullable()->after('avatar');
            $table->text('github_refresh_token')->nullable()->after('github_token');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'github_id',
                'github_username',
                'avatar',
                'github_token',
                'github_refresh_token',
            ]);
            $table->string('password')->nullable(false)->change();
        });
    }
};
