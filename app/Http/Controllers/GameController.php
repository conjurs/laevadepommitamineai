<?php

namespace App\Http\Controllers;

use App\Services\BattleshipAI;
use Illuminate\Http\Request;
use App\Models\GameRecord;

class GameController extends Controller
{
    private BattleshipAI $ai;
    private const SHIPS = [
        ['name' => 'Carrier', 'size' => 5],
        ['name' => 'Battleship', 'size' => 4],
        ['name' => 'Cruiser', 'size' => 3],
        ['name' => 'Submarine', 'size' => 3],
        ['name' => 'Destroyer', 'size' => 2]
    ];

    public function __construct(BattleshipAI $ai)
    {
        $this->ai = $ai;
    }

    public function index()
    {
        return view('game.board', [
            'ships' => self::SHIPS
        ]);
    }

    public function start(Request $request)
    {
        try {
            $difficulty = $request->input('difficulty', 'medium');
            $this->ai = new BattleshipAI($difficulty);
            
            $aiBoard = $this->ai->generateBoard();
            session([
                'ai_board' => $aiBoard,
                'player_board' => array_fill(0, 10, array_fill(0, 10, 0)),
                'player_hits' => [],
                'ai_hits' => [],
                'current_ship' => 0,
                'game_phase' => 'placement',
                'difficulty' => $difficulty,
                'score' => 0
            ]);
            
            return response()->json([
                'message' => 'Place your ' . self::SHIPS[0]['name'],
                'shipName' => self::SHIPS[0]['name'],
                'shipSize' => self::SHIPS[0]['size'],
                'status' => 'success'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error starting game'], 500);
        }
    }

    public function placeShip(Request $request)
    {
        try {
            $x = $request->input('x');
            $y = $request->input('y');
            $isHorizontal = $request->input('horizontal');
            $currentShipIndex = session('current_ship', 0);
            $playerBoard = session('player_board');

            if ($currentShipIndex >= count(self::SHIPS)) {
                return response()->json(['error' => 'All ships placed'], 400);
            }

            $currentShip = self::SHIPS[$currentShipIndex];
            
            if (!$this->canPlaceShip($playerBoard, $x, $y, $currentShip['size'], $isHorizontal)) {
                return response()->json(['error' => 'Invalid ship placement'], 400);
            }

            $playerBoard = $this->placeShipOnBoard($playerBoard, $x, $y, $currentShip['size'], $isHorizontal);
            $currentShipIndex++;

            session(['player_board' => $playerBoard, 'current_ship' => $currentShipIndex]);

            if ($currentShipIndex >= count(self::SHIPS)) {
                session(['game_phase' => 'playing']);
                return response()->json([
                    'status' => 'success',
                    'message' => 'All ships placed!',
                    'phase' => 'playing'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ship placed! Place your ' . self::SHIPS[$currentShipIndex]['name'],
                'nextShipName' => self::SHIPS[$currentShipIndex]['name'],
                'nextShipSize' => self::SHIPS[$currentShipIndex]['size']
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error placing ship'], 500);
        }
    }

    private function canPlaceShip(array $board, int $x, int $y, int $size, bool $horizontal): bool
    {
        if ($horizontal) {
            if ($y + $size > 10) return false;
            for ($i = 0; $i < $size; $i++) {
                if ($board[$x][$y + $i] === 1) return false;
            }
        } else {
            if ($x + $size > 10) return false;
            for ($i = 0; $i < $size; $i++) {
                if ($board[$x + $i][$y] === 1) return false;
            }
        }
        return true;
    }

    private function placeShipOnBoard(array $board, int $x, int $y, int $size, bool $horizontal): array
    {
        if ($horizontal) {
            for ($i = 0; $i < $size; $i++) {
                $board[$x][$y + $i] = 1;
            }
        } else {
            for ($i = 0; $i < $size; $i++) {
                $board[$x + $i][$y] = 1;
            }
        }
        return $board;
    }

    public function shoot(Request $request)
    {
        if (session('game_phase') !== 'playing') {
            return response()->json(['error' => 'Game not started'], 400);
        }

        $x = $request->input('x');
        $y = $request->input('y');
        
        $aiBoard = session('ai_board');
        $playerBoard = session('player_board');
        $playerHits = session('player_hits', []);
        $aiHits = session('ai_hits', []);
        
        // Process player's shot
        $isHit = $aiBoard[$x][$y] === 1;
        if ($isHit) {
            $aiBoard[$x][$y] = 2; // Mark as hit
        }
        $playerHits[] = [
            'x' => $x,
            'y' => $y,
            'hit' => $isHit
        ];
        
        // Process AI's shot
        $aiShot = $this->ai->makeShot($aiHits);
        $aiHit = $playerBoard[$aiShot['x']][$aiShot['y']] === 1;
        if ($aiHit) {
            $playerBoard[$aiShot['x']][$aiShot['y']] = 2; // Mark as hit
        }
        $aiHits[] = [
            'x' => $aiShot['x'],
            'y' => $aiShot['y'],
            'hit' => $aiHit
        ];
        
        // Update session
        session([
            'ai_board' => $aiBoard,
            'player_board' => $playerBoard,
            'player_hits' => $playerHits,
            'ai_hits' => $aiHits
        ]);
        
        // Check for game over
        $gameOver = $this->checkGameOver($aiBoard, $playerBoard);
        
        if ($gameOver) {
            session(['game_phase' => 'ended']);
        }
        
        return response()->json([
            'hit' => $isHit,
            'message' => $isHit ? 'Hit!' : 'Miss!',
            'ai_shot' => $aiShot,
            'ai_hit' => $aiHit,
            'ai_message' => $aiHit ? 'AI Hit!' : 'AI Miss!',
            'gameOver' => $gameOver !== false,
            'winner' => $gameOver,
            'playerHitCount' => collect($playerHits)->where('hit', true)->count(),
            'aiHitCount' => collect($aiHits)->where('hit', true)->count()
        ]);
    }

    private function checkGameOver($aiBoard, $playerBoard)
    {
        $aiShipsRemaining = false;
        $playerShipsRemaining = false;

        // Check AI board
        foreach ($aiBoard as $row) {
            if (in_array(1, $row)) {  // If there's still an unhit ship cell
                $aiShipsRemaining = true;
                break;
            }
        }

        // Check player board
        foreach ($playerBoard as $row) {
            if (in_array(1, $row)) {  // If there's still an unhit ship cell
                $playerShipsRemaining = true;
                break;
            }
        }

        if (!$aiShipsRemaining) return 'Player';
        if (!$playerShipsRemaining) return 'AI';
        return false;
    }

    public function reset()
    {
        session()->forget([
            'ai_board',
            'player_board',
            'player_hits',
            'ai_hits',
            'current_ship',
            'game_phase'
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Game reset successfully'
        ]);
    }

    private function saveGameRecord($winner)
    {
        GameRecord::create([
            'difficulty' => session('difficulty', 'medium'),
            'player_hits' => collect(session('player_hits'))->where('hit', true)->count(),
            'ai_hits' => collect(session('ai_hits'))->where('hit', true)->count(),
            'winner' => $winner,
            'player_moves' => session('player_hits'),
            'ai_moves' => session('ai_hits'),
            'score' => session('score', 0)
        ]);
    }

    public function history()
    {
        $records = GameRecord::orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('game.history', compact('records'));
    }
} 