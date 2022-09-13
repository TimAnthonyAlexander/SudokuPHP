<?php
namespace SudokuPHP;

class Rules {
    private const RULES = ['same_row_same_number', 'same_quadrant_same_number'];
    private const SIZE = [9, 9, 3, 3];

    public readonly Settings $settings;
    private Board $board;

    public function __construct() {
        $this->settings = new Settings(
            rules: self::RULES,
            width: self::SIZE[0],
            height: self::SIZE[1],
            quadrantWidth: self::SIZE[2],
            quadrantHeight: self::SIZE[3],
        );

        $this->board = new Board($this->settings);
    }

    public function getBoard(): Board {
        return $this->board;
    }
}

?>
