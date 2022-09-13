<?php
namespace SudokuPHP;


class Field {
    public function __construct(
        public readonly int $row,
        public readonly int $column,
        public readonly string $quadrant,
        public readonly int $number,
        public bool $filled = false,
        public int $enteredNumber = 0,
    ) {}
}
