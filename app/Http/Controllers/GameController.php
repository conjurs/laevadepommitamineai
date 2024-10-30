<?php

namespace App\Http\Controllers;

use App\Services\BattleshipAI;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private BattleshipAI $ai;
    private const SHIPS = [
        ['size' => 5, 'name' => 'Aircraft Carrier'],
        ['size' => 4, 'name' => 'Battleship'],
        ['size' => 3, 'name' => 'Submarine'],
        ['size' => 3, 'name' => 'Destroyer'],
        ['size' => 2, 'name' => 'Patrol Boat']
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

    public function start()
    {
        $aiBoard = $this->ai->generateBoard();
        session([
            'ai_board' => $aiBoard,
            'player_board' => array_fill(0, 10, array_fill(0, 10, 0)),
            'player_hits' => [],
            'ai_hits' => [],
            'current_ship' => 0,
            'game_phase' => 'placement'
        ]);
        
        return response()->json([
            'message' => 'Place your ' . self::SHIPS[0]['name'],
            'shipName' => self::SHIPS[0]['name'],
            'shipSize' => self::SHIPS[0]['size'],
            'status' => 'success'
        ]);
    }

    public function placeShip(Request $request)
    {
        $x = $request->input('x');
        $y = $request->input('y');
        $isHorizontal = $request->input('horizontal');
        $currentShipIndex = session('current_ship', 0);
        $playerBoard = session('player_board');

        // Validate current ship index
        if ($currentShipIndex >= count(self::SHIPS)) {
            session(['game_phase' => 'playing']);
            return response()->json([
                'message' => 'All ships already placed',
                'phase' => 'playing',
                'status' => 'success'
            ]);
        }

        $currentShip = self::SHIPS[$currentShipIndex];
        
        // Validate ship placement
        if (!$this->canPlaceShip($playerBoard, $x, $y, $currentShip['size'], $isHorizontal)) {
            return response()->json([
                'error' => 'Invalid position',
                'message' => 'Cannot place ship here'
            ], 400);
        }

        // Place the ship
        $playerBoard = $this->placeShipOnBoard($playerBoard, $x, $y, $currentShip['size'], $isHorizontal);
        $currentShipIndex++;
        
        session(['player_board' => $playerBoard, 'current_ship' => $currentShipIndex]);

        // Check if all ships are placed
        if ($currentShipIndex >= count(self::SHIPS)) {
            session(['game_phase' => 'playing']);
            return response()->json([
                'message' => 'All ships placed! Game starting...',
                'phase' => 'playing',
                'status' => 'success',
                'shipSize' => $currentShip['size']
            ]);
        }

        // Return next ship info
        $nextShip = self::SHIPS[$currentShipIndex];
        return response()->json([
            'message' => 'Place your ' . $nextShip['name'],
            'shipSize' => $nextShip['size'],
            'nextShipName' => $nextShip['name'],
            'nextShipSize' => $nextShip['size'],
            'status' => 'success'
        ]);
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
        $playerHits = session('player_hits', []);
        
        $isHit = $aiBoard[$x][$y] === 1;
        $playerHits[] = [
            'x' => $x,
            'y' => $y,
            'hit' => $isHit
        ];
        
        session(['player_hits' => $playerHits]);
        
        $aiShot = $this->ai->makeShot(session('ai_hits', []));
        $playerBoard = session('player_board');
        $aiHit = $playerBoard[$aiShot['x']][$aiShot['y']] === 1;
        
        // Check for game over
        $gameOver = $this->checkGameOver($aiBoard, $playerBoard);
        
        return response()->json([
            'hit' => $isHit,
            'message' => $isHit ? 'Hit!' : 'Miss!',
            'ai_shot' => $aiShot,
            'ai_hit' => $aiHit,
            'ai_message' => $aiHit ? 'AI Hit!' : 'AI Miss!',
            'gameOver' => $gameOver !== false,
            'winner' => $gameOver
        ]);
    }

    private function checkGameOver($aiBoard, $playerBoard)
    {
        $aiShipsRemaining = false;
        $playerShipsRemaining = false;

        foreach ($aiBoard as $row) {
            if (in_array(1, $row)) {
                $aiShipsRemaining = true;
                break;
            }
        }

        foreach ($playerBoard as $row) {
            if (in_array(1, $row)) {
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
} 