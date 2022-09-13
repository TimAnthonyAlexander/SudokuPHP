<?php
namespace SudokuPHP;


class Settings {
    public function __construct(
        public readonly array $rules,
        public readonly int $width,
        public readonly int $height,
        public readonly int $quadrantWidth,
        public readonly int $quadrantHeight,
    ) {}
}
