<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $fillable = [
        'user_id',
        'difficulty',
        'wins',
        'losses',
        'total_score'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 