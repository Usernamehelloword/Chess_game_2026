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
            $table->integer('rating')->unsigned()->default(1200);
            $table->string('avatar', 12)->default('♞');
            $table->string('bio', 255)->nullable();
            $table->boolean('is_admin')->default(false);
            $table->integer('games_played')->unsigned()->default(0);
            $table->integer('wins')->unsigned()->default(0);
            $table->integer('losses')->unsigned()->default(0);
            $table->integer('draws')->unsigned()->default(0);
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'rating', 'avatar', 'bio', 'is_admin',
                'games_played', 'wins', 'losses', 'draws', 'last_seen_at',
            ]);
        });
    }
};