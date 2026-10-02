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
        Schema::create('moves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id')->index();
            $table->integer('move_number')->unsigned();
            $table->string('color', 5);            // white | black
            $table->string('uci', 8);
            $table->string('san', 14)->nullable();
            $table->string('fen_before', 100);
            $table->string('fen_after', 100);
            $table->timestamps();

            $table->index(['game_id', 'move_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moves');
    }
};