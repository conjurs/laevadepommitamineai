<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty', 'medium');
        
        $leaderboards = Leaderboard::with('user')
            ->where('difficulty', $difficulty)
            ->orderBy('wins', 'desc')
            ->orderBy('total_score', 'desc')
            ->take(10)
            ->get();

        return view('leaderboard', compact('leaderboards', 'difficulty'));
    }

    public function updateStats($userId, $difficulty, $won, $score)
    {
        $leaderboard = Leaderboard::firstOrNew([
            'user_id' => $userId,
            'difficulty' => $difficulty
        ]);

        if ($won) {
            $leaderboard->wins++;
        } else {
            $leaderboard->losses++;
        }

        $leaderboard->total_score += $score;
        $leaderboard->save();
    }
} 