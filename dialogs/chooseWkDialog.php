<?php

require_once '../class/Process.php';

$fileType = $_POST['fileType'];
$wkNum = date("W", strtotime("last saturday"));

if ($wkNum < 10) {
    $wkNum = substr($wkNum, 1);
}

$lnk = new Process();
$sql = "SELECT wkEnd from branchinfo.weekdates WHERE wkNum = " . $wkNum;
$qry = $lnk->query($sql);

$wkEnd = $qry[0]['wkEnd'];

$html = "<div id='chooseWkNum'>";

$html .= "<div id='choiceDiv' class='center'>";

$html .= "<div class='chooseWkBtn ui-button ui-corner-all'>";
$html .= "<div data-change='decrease' class='chooseWk change left-float'><span class='ui-icon ui-icon-triangle-1-s'></span></div>";
$html .= "<div id='wkNum' class='chooseWk wkNum ui-corner-all center'>" . $wkNum . "</div>";
$html .= "<div data-change='increase' class='chooseWk change right-float'><span class='ui-icon ui-icon-triangle-1-n '></span></div>";
$html .= "<div id='wkEndDate'>WK End: " . $wkEnd . "</div>";
$html .= "</div>";
$html .= "</div>";

$html .= "<div id='chooseWk' data-filetype='".$fileType."' class='myButton ui-button ui-corner-all left-float'>Choose Week</div>";
$html .= "<div id='updateWk' data-filetype='".$fileType."' class='myButton ui-button ui-corner-all right-float'>Update Week</div>";

$html .= "</div>";

echo $html;