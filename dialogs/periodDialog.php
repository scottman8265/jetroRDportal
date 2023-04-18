<?php

session_start();

$array = $_POST['return'];

$html = "<fieldset>
    <legend>Select Available Period(s) to View: </legend>";
foreach ($array as $year => $periodArray) {
    $html .= "<div class='fullWidth'>";
    foreach ($periodArray as $period) {
        $html .= '<label data-id="'.$year.':'.$period.'" for="' . $year .':'. $period . '" class="periodSelect ui-button ui-corner-all leftFloat">' . $year . ' ' . $period . '
                                     <input type="checkbox" name="' . $year . $period . '" id="' . $year . ":" . $period . '" class="' . $year . ":" . $period . '">
    </label>';
    }
    $html .= "</div>";
}

$html .= "  </fieldset>";

$html .= "<div class='center'><button id='selectPeriodBtn' class='selectPeriods button-div ui-button ui-corner-all'>Select Period(s)</button></div>";

echo $html;