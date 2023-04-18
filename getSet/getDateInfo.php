<?php


#$fileType = $_POST['fileType'];

$year = date('y');

$wkNum = date("W", strtotime("last saturday"));
$wkEndDay = new DateTime();
$wkEndDay->setISODate($year, $wkNum);
$wkEndDay->modify('+5 days');
$wkEndDate = $wkEndDay->format('m/d/y');
$dayName = $wkEndDay->format('l');
$kkDate = date("m/d/Y", strtotime("last wednesday"));

#$wkNum = 5;

/*echo "Week End Date: " . $wkEndDate . "</br>";
echo "week Number: " . $wkNum .  "</br>";
echo "Day Of Week Week Ending: " . $dayName .  "</br>";*/

echo json_encode(['wkEndDate' => $wkEndDate, 'wkNum' => $wkNum, 'dayName' => $dayName, 'kkDate' => $kkDate]);

