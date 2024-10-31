<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('total_score')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'difficulty']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaderboards');
    }
}; 