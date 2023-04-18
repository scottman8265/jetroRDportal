<?php

session_start();

$groups = $_SESSION['groups'];

$group = $_POST['group'];

$title = $groups[$group];

$html = "<fieldset>";

$html .= "<legend>Choose A View Option</legend>";

$html .= '<label for="view" class="sortView center ui-button ui-corner-all left-float">
           <p>Choose</p><p>' . $title . '(s)</p><p>To View</p>
                                     <input type="radio" name="sortView" data-id="' . $group . '" id="viewOnly" class="sortView"/></label>';

$html .= '<label for="sort" class="sortView center ui-button ui-corner-all right-float">
           <p>View All</p><p>' . $title . '(s)</p>
                                     <input type="radio" name="sortView" data-id="' . $group . '" id="sortBy" class="sortView"/></label>';

$html .= "</fieldset>";

$html .= "<div class='center'><button id='selectViewSort' class='selectViewSort button-div ui-button ui-corner-all'>Select View Option</button></div>";

echo $html;

