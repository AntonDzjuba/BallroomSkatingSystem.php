<?php
function getResults($marks) {
    $marksRows = count($marks); //Number of participants
    $marksCols = count(current($marks)); //Number of judges
    //Adding columns of resulting sums
    for ($r = 0; $r < $marksRows; $r++) {
        for ($res = 1; $res <= $marksRows; $res++) {
            $marks["r" . $r]["res1" . $res] = 0;
        }
    }
    //Filling columns of resulting sums
    for ($r = 0; $r < $marksRows; $r++) {
        for ($c = 0; $c < $marksCols; $c++) {
            for ($res = 1; $res <= $marksRows; $res++) {
                if ($marks["r".$r]["c".$c] <= $res) {
                    $marks["r".$r]["res1".$res] = $marks["r".$r]["res1" . $res] + 1;
                }
            }
        }
    }
    //Adding columns of marks sums
    for ($r = 0; $r < $marksRows; $r++) {
        for ($res = 1; $res <= $marksRows; $res++) {
            $marks["r" . $r]["resSum1" . $res] = 0;
        }
    }
    //Filling columns of marks sums
    for ($r = 0; $r < $marksRows; $r++) {
        for ($c = 0; $c < $marksCols; $c++) {
            for ($res = 1; $res <= $marksRows; $res++) {
                if ($marks["r".$r]["c".$c] <= $res) {
                    $marks["r".$r]["resSum1".$res] = $marks["r".$r]["resSum1".$res] + $marks["r".$r]["c".$c];
                }
            }
        }
    }
    //Adding columns of text results
    for ($r = 0; $r < $marksRows; $r++) {
        for ($res = 1; $res <= $marksRows; $res++) {
            $marks["r" . $r]["resText1" . $res] = "";
        }
    }
    //Adding columns of final results
    for ($r = 0; $r < $marksRows; $r++) {
        $marks["r" . $r]["resFinal"] = "";
    }

    //Calculation
    $majJudges = (int)($marksCols/2 + 1);
    $currentPlace = 1;
    for ($num = 1; $num <= $marksRows; $num++) {
        $majArray = array();
        for ($r = 0; $r < $marksRows; $r++) {
            if($marks["r".$r]["resFinal"] <> "") {
                continue;
            }
            else {
                if($marks["r".$r]["resText1".$num] == "") {
                    $numFormat = $marks["r" . $r]["res1" . $num];
                    if ($numFormat == 0) {
                        $numFormat = '-';
                    }
                    $marks["r" . $r]["resText1" . $num] = $numFormat;
                }
            }
            if($marks["r".$r]["res1".$num] >= $majJudges) {
                $majArray[] = $r;
            }
        }
        if(count($majArray) == 0) {
            //Rule 5 doesn't work. Going to the next step.
            continue;
        }
        elseif(count($majArray) == 1) {
            //Rule 5. One participant has the majority of voices
            $marks["r".$majArray[0]]["resFinal"] = $currentPlace;
            $currentPlace++;
            continue;
        }
        else{
            //Rule 6,7. Several participants have the majority of voices
            $sumsArray = array();
            foreach ($majArray as $key => $value) {
                $sumsArray[$key] = $marks["r".$value]["res1".$num];
            }
            arsort($sumsArray, SORT_NUMERIC);
            $maxSum = reset($sumsArray);
            if(reset($sumsArray) > next($sumsArray)) {
                //Rule 6
                $marks['r'.$majArray[getArrayFirstKey($sumsArray)]]['resFinal'] = $currentPlace;
                $currentPlace++;
                $num--;
            }
            else {
                //Rule 7
                $sumsArray7 = array();
                foreach ($majArray as $key => $value) {
                    $sumsArray7[$key] = $marks["r".$value]["resSum1".$num];
                }
                asort($sumsArray7, SORT_NUMERIC);
                foreach ($sumsArray as $key => $value) {
                    if($value == $maxSum) {
                        if($marks['r'.$majArray[$key]]['resFinal'] == ""){
                            $marks['r'.$majArray[$key]]['resText1'.$num] =
                                $marks['r'.$majArray[$key]]['res1'.$num].'('.$marks['r'.$majArray[$key]]['resSum1'.$num].')';
                        }
                    }
                }
                if(reset($sumsArray7) < next($sumsArray7)) {
                    //Rule 7a
                    $winningRow = $majArray[getArrayFirstKey($sumsArray7)];
                    $marks['r'.$winningRow]['resFinal'] = $currentPlace;
                    $currentPlace++;
                    $num--;
                }
                else {
                    //Rule 7b
                    $minSum = reset($sumsArray7);
                    $majArray7b = array();
                    foreach ($sumsArray7 as $key => $value) {
                        if($value == $minSum) {
                            $majArray7b[] = $majArray[$key];
                        }
                    }
                    getResults7b($marks, $majJudges, $majArray7b, $num, $currentPlace);
                }
            }
        }
    }
    return $marks;
}
function getResults7b(&$marks, $majJudges, $currentMajArray, $currentNum, &$currentPlace) {

    $marksRows = count($marks);

    for($num = $currentNum; $num <= $marksRows; $num++) {
        $majArray = array();
        for ($r = 0; $r < $marksRows; $r++) {
            if (!in_array($r, $currentMajArray)) {
                continue;
            }
            if ($marks["r" . $r]["resFinal"] <> "") {
                continue;
            } else {
                if ($marks["r" . $r]["resText1" . $num] == "") {
                    $numFormat = $marks["r" . $r]["res1" . $num];
                    if ($numFormat == 0) {
                        $numFormat = '-';
                    }
                    $marks["r" . $r]["resText1" . $num] = $numFormat;
                }
            }
            if ($marks["r" . $r]["res1" . $num] >= $majJudges) {
                $majArray[] = $r;
            }
        }
        if (count($majArray) == 0) {
            //Rule 5 doesn't work. Going to the next step. Should never happen here.
            continue;
        } elseif (count($majArray) == 1) {
            //Rule 5
            $marks["r" . $majArray[0]]["resFinal"] = $currentPlace;
            $currentPlace++;
            continue;
        } else {
            //Rules 6,7
            $sumsArray = array();
            foreach ($majArray as $key => $value) {
                $sumsArray[$key] = $marks["r" . $value]["res1" . $num];
            }
            arsort($sumsArray, SORT_NUMERIC);
            $maxSum = reset($sumsArray);
            if (reset($sumsArray) > next($sumsArray)) {
                //Rule 6
                $marks['r' . $majArray[getArrayFirstKey($sumsArray)]]['resFinal'] = $currentPlace;
                $currentPlace++;
                $num--;
            } else {
                //Rule 7
                $sumsArray7 = array();
                foreach ($majArray as $key => $value) {
                    $sumsArray7[$key] = $marks["r" . $value]["resSum1" . $num];
                }
                asort($sumsArray7, SORT_NUMERIC);
                foreach ($sumsArray as $key => $value) {
                    if ($value == $maxSum) {
                        if ($marks['r' . $majArray[$key]]['resFinal'] == "") {
                            $marks['r' . $majArray[$key]]['resText1' . $num] =
                                $marks['r' . $majArray[$key]]['res1' . $num] . '(' . $marks['r' . $majArray[$key]]['resSum1' . $num] . ')';
                        }
                    }
                }
                if (reset($sumsArray7) < next($sumsArray7)) {
                    //Rule 7a
                    $winningRow = $majArray[getArrayFirstKey($sumsArray7)];
                    $marks['r' . $winningRow]['resFinal'] = $currentPlace;
                    $currentPlace++;
                    $num--;
                } else {
                    //Rule 8
                    if ($num == $marksRows) {
                        $minSum = reset($sumsArray7);
                        $majArray7b = array();
                        foreach ($sumsArray7 as $key => $value) {
                            if ($value == $minSum) {
                                $majArray7b[] = $majArray[$key];
                            }
                        }
                        $maxPlace = $currentPlace + count($majArray7b) - 1;
                        $avgPlace = ($currentPlace + $maxPlace) / 2;

                        foreach ($majArray7b as $value) {
                            $marks['r' . $value]['resFinal'] = $avgPlace;
                            $currentPlace++;
                        }
                    }
                }
            }
        }
    }
}
function getArrayFirstKey($array) {
    reset($array);
    return key($array);
}


