<?php

namespace App\Services;

class BattleshipAI
{
    private const BOARD_SIZE = 10;
    private const SHIPS = [
        ['size' => 5, 'name' => 'Carrier'],
        ['size' => 4, 'name' => 'Battleship'],
        ['size' => 3, 'name' => 'Cruiser'],
        ['size' => 3, 'name' => 'Submarine'],
        ['size' => 2, 'name' => 'Destroyer']
    ];

    public function generateBoard(): array
    {
        $board = array_fill(0, self::BOARD_SIZE, array_fill(0, self::BOARD_SIZE, 0));
        
        foreach (self::SHIPS as $ship) {
            $placed = false;
            while (!$placed) {
                $horizontal = rand(0, 1) === 1;
                $x = rand(0, self::BOARD_SIZE - 1);
                $y = rand(0, self::BOARD_SIZE - 1);
                
                if ($this->canPlaceShip($board, $x, $y, $ship['size'], $horizontal)) {
                    $this->placeShip($board, $x, $y, $ship['size'], $horizontal);
                    $placed = true;
                }
            }
        }
        
        return $board;
    }

    private function canPlaceShip(array $board, int $x, int $y, int $size, bool $horizontal): bool
    {
        if ($horizontal) {
            if ($y + $size > self::BOARD_SIZE) return false;
            for ($i = 0; $i < $size; $i++) {
                if ($board[$x][$y + $i] === 1) return false;
            }
        } else {
            if ($x + $size > self::BOARD_SIZE) return false;
            for ($i = 0; $i < $size; $i++) {
                if ($board[$x + $i][$y] === 1) return false;
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

    public function makeShot(array $previousShots): array
    {
        do {
            $x = rand(0, self::BOARD_SIZE - 1);
            $y = rand(0, self::BOARD_SIZE - 1);
        } while ($this->shotExists($previousShots, $x, $y));

        return ['x' => $x, 'y' => $y];
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
} 