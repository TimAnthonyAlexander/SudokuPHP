<?php
namespace SudokuPHP;


class Board{
    private array $board = [];
    private array $quadrantFields = [];
    private array $horizontalFields = [];
    private array $verticalFields = [];

    public function __construct(public readonly Settings $settings) {}

    public function fillBoard(): void{
        // This goes through each board field and checks for the rules that apply to it ($this->settings->rules)
        // If the rules are not met, try another number
        // If the rules are met, fill the field with the number and continue

        // If the board is filled, return the board

        for($h = 0; $h < $this->settings->height; $h++){
            for($w = 0; $w < $this->settings->width; $w++){
                $quadrant = $this->calculateQuadrantForPosition($h, $w);

                $numbersOfQuadrant = [];

                foreach ($this->getQuadrantFields($quadrant) as $field) {
                    assert($field instanceof Field);
                    $numbersOfQuadrant[] = $field->number;
                }

                $numbersOfHorizontalRow = [];

                foreach ($this->getHorizontalRowFields($h) as $field) {
                    assert($field instanceof Field);
                    $numbersOfHorizontalRow[] = $field->number;
                }

                $numbersOfVerticalRow = [];

                foreach ($this->getVerticalRowFields($w) as $field) {
                    assert($field instanceof Field);
                    $numbersOfVerticalRow[] = $field->number;
                }

                $allNumbersPossible = range(1, min($this->settings->width, $this->settings->height));

                $numbersPossible = array_diff($allNumbersPossible, $numbersOfQuadrant, $numbersOfHorizontalRow, $numbersOfVerticalRow);

                if (count($numbersPossible) === 0) {
                    $this->board = [];
                    $this->quadrantFields = [];
                    $this->horizontalFields = [];
                    $this->verticalFields = [];
                    $this->fillBoard();
                    return;
                }

                $number = $numbersPossible[array_rand($numbersPossible)];

                $this->board[$h][$w] = new Field(
                    row: $h,
                    column: $w,
                    quadrant: $quadrant,
                    number: $number,
                    filled: true,
                );
                $this->horizontalFields[$h][] = $this->board[$h][$w];
                $this->verticalFields[$w][] = $this->board[$h][$w];
                $this->quadrantFields[$quadrant][] = $this->board[$h][$w];
            }
        }
    }

    public function setEnteredNumber(int $posX, int $posY, int $number): void
    {
        $this->board[$posY][$posX]->enteredNumber = $number;
    }

    public function hideRandomFields(int $mode): void {
        // 0 = Easy = 40% of fields are hidden
        // 1 = Medium = 80% of fields are hidden
        // 2 = Hard = 90% of fields are hidden

        $percentage = match($mode){
            0 => 0.4,
            1 => 0.6,
            2 => 0.8,
            default => 0.4,
        };

        foreach ($this->board as $row) {
            foreach ($row as $field) {
                assert($field instanceof Field);
                if (random_int(0, 100) < $percentage * 100) {
                    $field->filled = false;
                }
            }
        }
    }

    public function getHorizontalRowFields(int $row): array {

        if (isset($this->horizontalFields[$row])) {
            return $this->horizontalFields[$row];
        }

        if (!isset($this->board[$row])) {
            return [];
        }

        $fields = [];
        foreach ($this->board[$row] as $field) {
            assert($field instanceof Field);
            $fields[] = $field;
            if (count($fields) === $this->settings->width) {
                break;
            }
        }

        $this->horizontalFields[$row] = $fields;

        return $fields;
    }

    public function getVerticalRowFields(int $column): array {
        if (isset($this->verticalFields[$column])) {
            return $this->verticalFields[$column];
        }

        $fields = [];
        foreach ($this->board as $row) {
            foreach ($row as $field){
                assert($field instanceof Field);
                if($field->column === $column){
                    $fields[] = $field;
                }
                if(count($fields) === $this->settings->height){
                    break;
                }
            }
        }

        $this->verticalFields[$column] = $fields;

        return $fields;
    }

    public function getQuadrantFields(string $quadrant): array{
        if (isset($this->quadrantFields[$quadrant])) {
            return $this->quadrantFields[$quadrant];
        }


        $quadrantFields = [];

        for ($h = 0; $h < $this->settings->height; $h++) {
            for ($w = 0; $w < $this->settings->width; $w++) {
                if (!isset($this->board[$h][$w])) {
                    continue;
                }

                assert($this->board[$h][$w] instanceof Field);
                if ($this->board[$h][$w]->quadrant === $quadrant) {
                    $quadrantFields[] = $this->board[$h][$w];
                }
            }
        }

        $this->quadrantFields[$quadrant] = $quadrantFields;

        return $quadrantFields;
    }

    public function calculateQuadrantForPosition(int $row, int $column): string {
        $quadrantWidth = $this->settings->width / $this->settings->quadrantWidth;
        $quadrantHeight = $this->settings->height / $this->settings->quadrantHeight;

        $quadrantRow = floor($row / $quadrantHeight);
        $quadrantColumn = floor($column / $quadrantWidth);

        return $quadrantRow .'--'. $quadrantColumn;
    }

    public function getBoardAsNumberTwoDimensionalArray(): array {
        $board = [];
        foreach ($this->board as $row) {
            foreach ($row as $field) {
                assert($field instanceof Field);
                $board[$field->row][$field->column] = $field->number;
            }
        }
        return $board;
    }

    public function getNumberInPosition(int $posX, int $posY): int {
        return $this->board[$posY][$posX]->number;
    }

    public function getSelectedNumber(int $posX, int $posY): int {
        return $this->board[$posY][$posX]->enteredNumber === 0 ? ($this->board[$posY][$posX]->number ?? '_') : $this->board[$posY][$posX]->enteredNumber;
    }

    public function isFieldFilled(int $posX, int $posY): bool {
        return $this->board[$posY][$posX]->filled;
    }

    public function setFieldFilled(int $posX, int $posY, bool $filled): void {
        $this->board[$posY][$posX]->filled = $filled;
    }

    public function viewForTerminal(int $posX, int $posY): void {
        foreach ($this->board as $y => $row) {
            foreach ($row as $x => $field) {
                assert($field instanceof Field);
                if ($field->filled){
                    if($x === $posX && $y === $posY){
                        if ($field->enteredNumber !== 0){
                            // use a different color than prefilled numbers
                            echo " \e[1;35m" . $field->enteredNumber . "\e[0m";
                        } else{
                            echo " \e[1;31m" . $field->number . "\e[0m";
                        }
                    } else {
                        if ($field->enteredNumber !== 0) {
                            echo " \e[1;33m" . $field->enteredNumber . "\e[0m";
                        } else {
                            echo " \e[1;37m" . $field->number . "\e[0m";
                        }
                    }
                } else {
                    if ($x === $posX && $y === $posY) {
                        // Just display _ for empty fields selected and whitespace for empty fields not selected
                        echo " \e[1;31m_\e[0m";
                    } else {
                        echo "  ";
                    }
                }
            }
            echo PHP_EOL;
        }
    }

    public static function isComplete(Board $oldBoard, Settings $settings): bool {
        $board = clone $oldBoard;

        for($h = 0; $h < $settings->height; $h++){
            for($w = 0; $w < $settings->width; $w++){
                $quadrant = $board->calculateQuadrantForPosition($h, $w);

                $numbersOfQuadrant = [];

                foreach ($board->getQuadrantFields($quadrant) as $field) {
                    assert($field instanceof Field);
                    $numbersOfQuadrant[] = $field->number;
                }

                $numbersOfHorizontalRow = [];

                foreach ($board->getHorizontalRowFields($h) as $field) {
                    assert($field instanceof Field);
                    $numbersOfHorizontalRow[] = $field->number;
                }

                $numbersOfVerticalRow = [];

                foreach ($board->getVerticalRowFields($w) as $field) {
                    assert($field instanceof Field);
                    $numbersOfVerticalRow[] = $field->number;
                }

                $allNumbersPossible = range(1, min($settings->width, $settings->height));

                $numbersPossible = array_diff($allNumbersPossible, $numbersOfQuadrant, $numbersOfHorizontalRow, $numbersOfVerticalRow);

                if (count($numbersPossible) === 0) {
                    return false;
                }

                $number = $numbersPossible[array_rand($numbersPossible)];

                $board[$h][$w] = new Field(
                    row: $h,
                    column: $w,
                    quadrant: $quadrant,
                    number: $number,
                    filled: true,
                );
                $board->horizontalFields[$h][] = $board->board[$h][$w];
                $board->verticalFields[$w][] = $board->board[$h][$w];
                $board->quadrantFields[$quadrant][] = $board->board[$h][$w];
            }
        }

        return true;
    }

    public function getField(int $posX, int $posY): Field {
        return $this->board[$posY][$posX];
    }

    public function isFullButNotCorrect(): bool {
        if (self::isComplete($this, $this->settings)) {
            return false;
        }

        foreach ($this->board as $row) {
            foreach ($row as $field) {
                assert($field instanceof Field);
                if ($field->filled === false) {
                    return false;
                }
            }
        }

        return true;
    }
}
