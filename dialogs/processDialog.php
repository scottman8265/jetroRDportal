<?php

$fileType = $_POST['fileType'];

switch ($fileType) {
    case 'cycleCounts':
        $title = 'Cycle Count';
        break;
    case 'audit':
        $title = 'Audit';
        break;
    case 'jcmsTesting':
        $title = 'jcms Testing';
        break;
    default:
        $title = "There's and Error Somewhere";
        break;
}


$html = "<div id='processDialog' class='ui-dialog ui-dialog-content center' title='" . $title . " uploaded successfully!'>
    <div class='ui-dialog-content center'>The " . $title . " file was successfully uploaded.  Would you like to process data now?</div>
    <button id='process' class='processButton left-float ui-button ui-corner-all'>Yes</button>
    <button id='cancel' class='processButton right-float ui-button ui-corner-all'>no</button>
</div>";

echo $html;
