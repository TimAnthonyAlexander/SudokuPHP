<?php
namespace SudokuPHP;
require(__DIR__ . '/vendor/autoload.php');

// If session is not started, start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (isset($_POST['mode'])){
    $mode = (int) $_POST['mode'];
    if ($mode < 0 || $mode > 2) {
        $mode = 0;
    }
    setcookie('mode', $mode, time() + (86400 * 30), "/"); // 86400 = 1 day
    $_SESSION['rules']->getBoard()->fillBoard();
    $_SESSION['rules']->getBoard()->hideRandomFields($mode);
    header('Location: index.php');
    exit;
}

if (isset($_COOKIE['mode'])) {
    $mode = (int) $_COOKIE['mode'];
    if ($mode < 0 || $mode > 2) {
        $mode = 0;
    }
} else {
    $mode = 0;
    setcookie('mode', $mode, time() + (86400 * 30), "/"); // 86400 = 1 day
}

// If isset session rules, set it
if (isset($_SESSION['rules'])) {
    $rules = $_SESSION['rules'];
} else {
    // Else create new rules
    $rules = new Rules();
    $rules->getBoard()->fillBoard();
    $rules->getBoard()->hideRandomFields($mode);
}

$board = $rules->getBoard();

// There will be a form with three inputs, x, y, and number. These are hidden
// There will be an html table with the board.
// Click on a field will fill the form with the x and y coordinates of the field.
// There will be a list of buttons at the bottom of the page filled with numbers. A click on these will fill the form with the number and submit that form.
if (isset ($_POST['x']) && isset($_POST['y']) && isset($_POST['number'])) {
    $x = $_POST['x'];
    $y = $_POST['y'];
    $number = $_POST['number'];
    if ($board->getField($x, $y)->filled === false || $board->getField($x, $y)->enteredNumber !== 0){
        $board->getField($x, $y)->enteredNumber = $number;
        $board->getField($x, $y)->filled = true;
        $_SESSION['rules'] = $rules;
        header('Location: index.php');
        exit;
    }
}

if (Board::isComplete($board, $rules->settings)) {
    print '<h1>Sudoku completed successfully!</h1>';
    $board->fillBoard();
    $board->hideRandomFields($mode);
    $_SESSION['rules'] = $rules;
    header('Location: index.php');
    exit;
}

if ($board->isFullButNotCorrect()) {
    print '<h2>The sudoku is full but not correct. There\'s a mistake somewhere!</h2>';
}

$_SESSION['rules'] = $rules;

// Now the table

// First the form
echo '<form id="form" action="index.php" method="post">';
echo '<input type="hidden" name="x" id="x">';
echo '<input type="hidden" name="y" id="y">';
echo '<input type="hidden" name="number" id="number">';
echo '</form>';

// Form for the $mode
echo '<form id="mode" action="index.php" method="post">';
print '<h5>Difficulty:</h5>';
print '<input type="radio" id="easy" name="mode" value="0" '.($mode === 0 ? 'checked' : '').' onchange="this.form.submit()">';
print '<label for="easy">Easy</label><br>';
print '<input type="radio" id="medium" name="mode" value="1" '.($mode === 1 ? 'checked' : '').' onchange="this.form.submit()">';
print '<label for="medium">Medium</label><br>';
print '<input type="radio" id="hard" name="mode" value="2" '.($mode === 2 ? 'checked' : '').' onchange="this.form.submit()">';
print '<label for="hard">Hard</label><br>';
echo '</form>';

echo '<table>';
print '<h5>Click on a field and press a number key on your keyboard</h5>';
foreach (range(0, 8) as $y) {
    echo '<tr>';
    foreach (range(0, 8) as $x) {
        $field = $board->getField($x, $y);
        if ($field->filled === true) {
            if ($field->enteredNumber === 0) {
                echo '<td id="'.$y.$x.'" style="background-color: darkgray; width: 40px; height: 40px; text-align: center; border: 2px solid gray;">'.$field->number.'</td>';
            } else {
                echo '<td id="'.$y.$x.'" style="background-color: lime; width: 40px; height: 40px; text-align: center;border: 2px solid gray;" onclick="document.getElementById(\'x\').value = '.$x.'; document.getElementById(\'y\').value = '.$y.'; document.getElementById(\'numbers\').style.display = \'block\';">'.$field->enteredNumber.'</td>';
            }
        } else {
            echo '<td id="'.$y.$x.'" style="background-color: white; width: 40px; height: 40px; text-align: center;border: 2px solid gray;" onclick="document.getElementById(\'x\').value = '.$x.'; document.getElementById(\'y\').value = '.$y.'; document.getElementById(\'numbers\').style.display = \'block\';"></td>';
        }
    }
    echo '</tr>';
}
?>
</table>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
    // This js checks for keyboard input of numbers 1-9 and submits the form with the number, if the x and y are set.
    document.addEventListener('keydown', function(event) {
        if (event.key >= 1 && event.key <= 9) {
            if (document.getElementById('x').value !== '' && document.getElementById('y').value !== '') {
                document.getElementById('number').value = event.key;
                document.getElementById('form').submit();
            }
        }
    });

    // This function marks all fields without a border
    // Then it marks the field for x.value and y.value (id = xy) with a red border
    function checkField() {
        var x = document.getElementById('x').value;
        var y = document.getElementById('y').value;
        var fields = document.getElementsByTagName('td');
        for (var i = 0; i < fields.length; i++) {
            fields[i].style.border = '2px solid gray';
        }
        if (x !== '' && y !== '') {
            console.log(x, y);
            document.getElementById(y+x).style.border = '2px dotted red';
        }
        // This function calls itself after 20ms
        setTimeout(checkField, 20);
    }

    // This function calls checkField() after 20ms
    setTimeout(checkField, 20);
</script>

<!-- Now numbers -->
<div id="numbers" style="display: none;">
    <h5>Click on a number to fill the field</h5>
    <?php
    foreach (range(1, 9) as $number) {
        echo '<button style="width: 39px; height: 50px;" onclick="document.getElementById(\'number\').value = '.$number.'; document.getElementById(\'form\').submit();">'.$number.'</button>';
    }
    ?>
</div>
