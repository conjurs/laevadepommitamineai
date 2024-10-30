<?php

namespace App\Services;

class BattleshipAI
{
    private const BOARD_SIZE = 10;
    private string $difficulty;

    public function __construct(string $difficulty = 'medium')
    {
        $this->difficulty = strtolower($difficulty);
    }

    public function generateBoard()
    {
        $board = array_fill(0, self::BOARD_SIZE, array_fill(0, self::BOARD_SIZE, 0));
        $ships = [5, 4, 3, 3, 2]; // Ship sizes

        foreach ($ships as $size) {
            $placed = false;
            while (!$placed) {
                $x = rand(0, self::BOARD_SIZE - 1);
                $y = rand(0, self::BOARD_SIZE - 1);
                $horizontal = rand(0, 1) === 1;

                if ($this->canPlaceShip($board, $x, $y, $size, $horizontal)) {
                    $this->placeShip($board, $x, $y, $size, $horizontal);
                    $placed = true;
                }
            }
        }
        return $board;
    }

    public function makeShot(array $previousShots): array
    {
        switch ($this->difficulty) {
            case 'easy':
                return $this->makeRandomShot($previousShots);
            case 'hard':
                return $this->makeSmartShot($previousShots);
            default: // medium
                return rand(0, 1) === 0 
                    ? $this->makeRandomShot($previousShots)
                    : $this->makeSmartShot($previousShots);
        }
    }

    private function makeRandomShot(array $previousShots): array
    {
        do {
            $x = rand(0, self::BOARD_SIZE - 1);
            $y = rand(0, self::BOARD_SIZE - 1);
        } while ($this->shotExists($previousShots, $x, $y));

        return ['x' => $x, 'y' => $y];
    }

    private function makeSmartShot(array $previousShots): array
    {
        $hits = array_filter($previousShots, fn($shot) => $shot['hit']);
        
        if (empty($hits)) {
            return $this->makeRandomShot($previousShots);
        }

        // Try adjacent cells to previous hits
        foreach ($hits as $hit) {
            $adjacentCells = [
                ['x' => $hit['x'] - 1, 'y' => $hit['y']],
                ['x' => $hit['x'] + 1, 'y' => $hit['y']],
                ['x' => $hit['x'], 'y' => $hit['y'] - 1],
                ['x' => $hit['x'], 'y' => $hit['y'] + 1],
            ];

            foreach ($adjacentCells as $cell) {
                if ($this->isValidCell($cell) && !$this->shotExists($previousShots, $cell['x'], $cell['y'])) {
                    return $cell;
                }
            }
        }

        return $this->makeRandomShot($previousShots);
    }

    private function isValidCell(array $cell): bool
    {
        return $cell['x'] >= 0 && $cell['x'] < self::BOARD_SIZE &&
               $cell['y'] >= 0 && $cell['y'] < self::BOARD_SIZE;
    }

    private function shotExists(array $shots, int $x, int $y): bool
    {
        foreach ($shots as $shot) {
            if ($shot['x'] === $x && $shot['y'] === $y) {
                return true;
            }
        }
        return false;
    }

    private function canPlaceShip(array $board, int $x, int $y, int $size, bool $horizontal): bool
    {
        // Check if ship fits on board
        if ($horizontal) {
            if ($y + $size > self::BOARD_SIZE) return false;
        } else {
            if ($x + $size > self::BOARD_SIZE) return false;
        }

        // Check surrounding area including diagonals
        $startX = max(0, $x - 1);
        $endX = min(9, $horizontal ? $x + 1 : $x + $size);
        $startY = max(0, $y - 1);
        $endY = min(9, $horizontal ? $y + $size : $y + 1);

        for ($i = $startX; $i <= $endX; $i++) {
            for ($j = $startY; $j <= $endY; $j++) {
                if ($board[$i][$j] === 1) return false;
            }
        }

        return true;
    }

    private function placeShip(array &$board, int $x, int $y, int $size, bool $horizontal): void
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
    }
} 