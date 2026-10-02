<?php

use Illuminate\Contracts\Database\Query\Builder;
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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('mode', 10)->default('multi');          // multi | bot
            $table->string('status', 12)->default('waiting');      // waiting | active | finished | abandoned
            $table->unsignedBigInteger('white_user_id')->nullable()->index();
            $table->unsignedBigInteger('black_user_id')->nullable()->index();
            $table->string('white_side', 8)->default('user');      // user | bot
            $table->string('black_side', 8)->default('user');
            $table->string('winner', 8)->nullable();               // white | black | draw
            $table->string('result_reason', 40)->nullable();
            $table->string('time_control', 16)->default('10+5');
            $table->integer('time_base')->unsigned()->default(600);
            $table->integer('time_increment')->unsigned()->default(5);
            $table->string('difficulty', 16)->nullable();          // bot games
            $table->boolean('rated')->default(true);
            $table->integer('white_clock_ms')->nullable();
            $table->integer('black_clock_ms')->nullable();
            $table->string('draw_offered_by', 8)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['white_user_id', 'status']);
            $table->index(['black_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};