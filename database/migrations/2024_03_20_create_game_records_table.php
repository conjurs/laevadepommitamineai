<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('game_records', function (Blueprint $table) {
            $table->id();
            $table->string('difficulty');
            $table->integer('player_hits');
            $table->integer('ai_hits');
            $table->string('winner');
            $table->json('player_moves');
            $table->json('ai_moves');
            $table->integer('score');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_records');
    }
}; 