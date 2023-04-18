<?php

require("../class/Process.php");

$task = $_POST['task'];

$lnk = new Process();

$sql = "SELECT month, year FROM auditanalysis.selfaudits";
$qry = $lnk->query($sql);
$count = 1;
foreach ($qry as $per) {
    $array[$per['year']][$per['month']] = $count;
    $count++;
}

$html = "<fieldset>
    <legend>Select Available Months(s) to View: </legend>";

foreach ($array as $year => $months) {
    $html .= "<div class='fullWidth'>";
    foreach ($months as $month => $data) {
        $dt = DateTime::createFromFormat('!m', $month);
        $monthName = $dt->format('M');
        $html .= '<label data-id="' . $year . ':' . $month . '" for="' . $year . ':' . $month . '" class="monthSelect ui-button ui-corner-all leftFloat">' . $monthName . ' ' . $year . '
                                     <input type="checkbox" name="' . $year . $month . '" id="' . $year . ":" . $month . '" class="' . $year . ":" . $month . '">
    </label>';
    }
    $html .= "</div>";
}

$html .= "  </fieldset>";

$html .= "<div class='center'><button id='selectMonthBtn' data-task='" . $task . "' class='selectMonths button-div ui-button ui-corner-all'>Select Month(s)</button></div>";

echo json_encode(['task' => $task, 'html' => $html]);

