<?php

session_start();

$groups = $_SESSION['groups'];
$_SESSION['group'] = $_POST['group'];

$html = "<fieldset>
            <legend>Select One Option To Group By</legend>";

foreach ($groups as $group => $title) {

    $html .= '<label data-id="'.$group.'" for="' . $group . '" class="periodSelect ui-button ui-corner-all leftFloat">' . $title . '
                                     <input type="radio" name="groupSelect" id="' . $group . '" class="' . $group . '">
    </label>';

}

$html .= "  </fieldset>";

$html .= "<div class='center'><button id='selectGroup' class='selectGroup button-div ui-button ui-corner-all'>Select Group</button></div>";

echo $html;