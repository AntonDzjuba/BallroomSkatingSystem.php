<!doctype html>
<html lang="ru" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles.css" />
    <title>Skating</title>
    <?php include ('calculation.php') ?>
    <script type="text/javascript">
        function bottom() {
            document.getElementById( 'bottom' ).scrollIntoView();
        }
    </script>
</head>
<?php
if(isset($_POST['lang']) && htmlentities($_POST['lang'])=='en'){
    $textLang = 'en';
    $textHeader = 'Ballroom skating system calculation';
    $textResetButton = 'Reset';
    $textParticipants = 'Participants';
    $textJudges = 'Judges';
    $textRefreshTableOfResultsButton = 'Refresh table of results';
    $textResultsHeader = 'Participant/Judge';
    $textCalculateButton = 'Calculate';
    $textTableOfResults = 'Table of results';
}
else {
    $textLang = 'ru';
    $textHeader = 'Расчет результатов соревнований по системе Skating';
    $textResetButton = 'Сбросить';
    $textParticipants = 'Участники';
    $textJudges = 'Судьи';
    $textRefreshTableOfResultsButton = 'Обновить таблицу оценок';
    $textResultsHeader = 'Участник/Судья';
    $textCalculateButton = 'Рассчитать';
    $textTableOfResults = 'Таблица результатов';
}
?>
<body onload="bottom()">
<p><form method="POST">
    <input tabindex="-1" type="submit" class="btn" name="lang" value="ru">
    <input tabindex="-1" type="submit" class="btn" name="lang" value="en">
</form>
<p><form method="POST">
    <input type = "text" name = "lang" value ="<?php echo $textLang?>" hidden>
    <input tabindex="-1" type="submit" class="btn" name="reset" value="<?php echo $textResetButton?>">
</form>
<p><h2><?php echo $textHeader?></h2>
<form method="POST">
    <input type = "text" name = "lang" value ="<?php echo $textLang?>" hidden>
    <table><tr><th>
                <label for="participants"><?php echo $textParticipants?>:</label><br>
                <textarea tabindex="1" id="participants" name="participants" rows="8" cols="30"><?php if( isset( $_POST['participants'] ) ) {
                    echo trim(htmlentities($_POST['participants']));} ?></textarea>
    </th><th>
                <label for="judges"><?php echo $textJudges?>:</label><br>
                <textarea tabindex="2" id="judges" name="judges" rows="8" cols = "30"><?php if( isset( $_POST['judges'] ) ) {
                    echo trim(htmlentities($_POST['judges']));} ?></textarea>
    </th></tr></table>
    <p><input type="submit" class="btn" name="refreshPlaces" value="<?php echo $textRefreshTableOfResultsButton?>"></p>
    <?php
    if( isset( $_POST['participants'] ) && trim(htmlentities($_POST['participants']))<>""
            && isset( $_POST['judges'] ) && trim(htmlentities($_POST['judges']))<>"" ) {

        $judges = trim(htmlentities($_POST['judges']));
        $participants = trim(htmlentities($_POST['participants']));

        $judges = explode("\n", $judges);
        $participants = explode("\n", $participants);

        echo '<table class="bordered"><tr><th>&nbsp;'.$textResultsHeader.'&nbsp;</th>';
        $participantsCount = count($participants);
        foreach ($judges as $judge) {
            $judge = trim($judge);
            echo '<th>' . $judge . '</th>';
        }
        echo '</tr>';
        if (isset($_POST['marks']) && !isset($_POST['refreshPlaces'])) {
            $marks = $_POST['marks'];
            $marksRows = count($marks);
            $marksCols = count(current($marks));
        } else {
            $marks = array();
            $marksRows = 0;
            $marksCols = 0;
        }
        $r = 0;
        foreach ($participants as $participant) {
            $participant = trim($participant);
            echo '<tr><td>&nbsp;' . $participant . '</td>';
            for ($c = 0; $c < count($judges); $c++) {
                if (!empty($marks) && $marksRows > $r && $marksCols > $c) {
                    $val = " value = " . $marks["r" . $r]["c" . $c];
                } else {
                    $val = "";
                }
                if (isset($_POST['refreshPlaces'])) {
                    $val = "";
                }
                echo '<td><div class="number">';
                echo '<button tabindex="-1" class="number-minus" type="button" onclick="this.nextElementSibling.stepDown();
                        this.nextElementSibling.onchange();">-</button>';
                echo '<input class = "inputNumber" tabindex = "' . ($c + 3) . '" type="number" min="1" max=' . $participantsCount . ' 
                    name=marks[r' . $r . '][c' . $c . ']' . $val . '>';
                echo '<button tabindex="-1" class="number-plus" type="button" onclick="this.previousElementSibling.stepUp();
                        this.previousElementSibling.onchange();">+</button>';
                echo '</div>';
            }
            echo '</tr>';
            $r++;
        }
        echo '</table>';
        echo '<br><input type="submit" class="btn" name="countPlaces" value="'.$textCalculateButton.'">';
        $extendedResultChecked = "";
        //For testing
        //if (isset($_POST['extendedResult'])) {
        //    $extendedResultChecked = "checked";
        //}
        //echo '<label> <input type="checkbox" name="extendedResult" '.$extendedResultChecked.'> Show extended data</label>';
        //For testing
        echo '</form>';

        if (!empty($marks) && $marksRows == count($participants) && $marksCols == count($judges)) {
            //Checking $marks
            $showResults = true;
            foreach ($marks as $mark) {
                foreach ($mark as $item) {
                    if (empty($item)) {
                        echo '<h4>Для расчета необходимо заполнить все оценки.</h4>';
                        $showResults = false;
                        break;
                    }
                }
                if (!$showResults) {
                    break;
                }
            }
            if ($showResults) {
                for ($col = 0; $col < $marksCols; $col++) {
                    $usedPlaces = array();
                    for ($row = 0; $row < $marksRows; $row++) {
                        if (in_array($marks['r' . $row]['c' . $col], $usedPlaces)) {
                            echo '<h4>' . $judges[$col] . ': Оценки не должны повторяться!</h4>';
                            $showResults = false;
                        } else {
                            $usedPlaces[] = $marks['r' . $row]['c' . $col];
                        }
                    }
                }
            }
            //Printing results
            if ($showResults) {
                echo '<br>';
                echo '<h3>'.$textTableOfResults.':</h3>';
                $marksExt = getResults($marks);
                echo '<table class="bordered"><tr><th>&nbsp;'.$textResultsHeader.'&nbsp;</th>';
                //Table head
                foreach ($judges as $judge) {
                    $judge = trim($judge);
                    echo '<th>' . $judge . '</th>';
                }
                if ($extendedResultChecked) {
                    for ($i = 1; $i <= count($participants); $i++) {
                        echo '<th class="centered">';
                        if ($i == 1) {
                            echo '1(sum of places)';
                        } else {
                            echo '1-' . $i . '(sum of places)';
                        }
                        echo '</th>';
                    }
                    for ($i = 1; $i <= count($participants); $i++) {
                        echo '<th class="centered">';
                        if ($i == 1) {
                            echo '1(sum of marks)';
                        } else {
                            echo '1-' . $i . '(sum of marks)';
                        }
                        echo '</th>';
                    }
                }
                for ($i = 1; $i <= count($participants); $i++) {
                    echo '<th class="centered">';
                    if ($i == 1) {
                        echo '1';
                    } else {
                        echo '&nbsp;1-' . $i . '&nbsp;';
                    }
                    echo '</th>';
                }
                echo '<th>&nbsp;Итог&nbsp;</th>';
                echo '</tr>';
                //Results
                $num = 0;
                foreach ($marksExt as $key => $value) {
                    echo '<tr>';
                    echo '<td>&nbsp;' . $participants[$num] . '</td>';
                    foreach ($value as $valueKey => $valueData)
                        if (!$extendedResultChecked && (startsWith($valueKey, "res1") or startsWith($valueKey, "resSum"))) {
                            continue;
                        } else {
                            echo '<td rel="' . $valueKey . '" class="centered">' . $valueData . '</td>';
                        }
                    echo "</tr>";
                    $num++;
                }
                echo "</table>";
            }
        }
    }
    ?>
    <br>
    <div id="bottom"></div>
</body>
</html>

<?php
function startsWith( $haystack, $needle ): bool
{
$length = strlen( $needle );
return substr( $haystack, 0, $length ) === $needle;
}
?>