<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRecord extends Model
{
    protected $fillable = [
        'difficulty',
        'player_hits',
        'ai_hits',
        'winner',
        'player_moves',
        'ai_moves',
        'score'
    ];

    protected $casts = [
        'player_moves' => 'array',
        'ai_moves' => 'array',
    ];
} 