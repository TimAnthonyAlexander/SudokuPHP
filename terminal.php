<?php
namespace SudokuPHP;

require(__DIR__ . '/vendor/autoload.php');

// Modes: 0 = Easy, 1 = Medium, 2 = Hard
$mode = 0;

$rules = new Rules();
$rules->getBoard()->fillBoard();
$rules->getBoard()->hideRandomFields($mode);

$stdin = fopen('php://stdin', 'rb');
stream_set_blocking($stdin, 0);
system('stty cbreak -echo');

$posX = 0;
$posY = 0;

function translateKeypress($string) {
    return match ($string) {
        "\033[A" => "UP",
        "\033[B" => "DOWN",
        "\033[C" => "RIGHT",
        "\033[D" => "LEFT",
        "\n" => "ENTER",
        " " => "SPACE",
        "\010", "\177" => "BACKSPACE",
        "\t" => "TAB",
        "\e" => "ESC",
        default => $string,
    };
}

print "\033[2J\033[;H";
$rules->getBoard()->viewForTerminal($posX, $posY);


print PHP_EOL.'Press an arrow key or a number.';




while (!$rules->getBoard()->isComplete($rules->getBoard(), $rules->settings)) {
    $keypress = fgets($stdin);
    $actual = translateKeypress($keypress);
    if ($keypress){

        if ($actual === 'UP') {
            if ($posY > 0 && $posY <= 8) {
                $posY--;
            }
        }
        if ($actual === 'DOWN') {
            if ($posY >= 0 && $posY < 8) {
                $posY++;
            }
        }
        if ($actual === 'LEFT') {
            if ($posX > 0 && $posX <= 8) {
                $posX--;
            }
        }
        if ($actual === 'RIGHT') {
            if ($posX >= 0 && $posX < 8) {
                $posX++;
            }
        }
        print "\033[2J\033[;H";

        // Check if it is a number
        if (is_numeric($actual)) {
            $field = $rules->getBoard()->getField($posX, $posY);
            if ($field->filled === false || $field->enteredNumber !== 0) {
                $field->enteredNumber = (int) $actual;
                $field->filled = true;
            }
        }

        $rules->getBoard()->viewForTerminal($posX, $posY);
        print PHP_EOL.'Press an arrow key or a number.';

        if ($rules->getBoard()->isFullButNotCorrect()) {
            print PHP_EOL.'The sudoku is not correct.';
        }
    }
}

print PHP_EOL.'The sudoku is correct.';
