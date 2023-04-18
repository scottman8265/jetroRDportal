<?php

$fileType = isset($_SESSION['fileType']) ? $_SESSION['fileType'] : $_POST['fileType'];

switch ($fileType) {
    case 'masterCounts':
        $title = 'Choose Master Count Log To Load';
        break;
    case 'audits':
        $title =  'Choose Audits To Load';
        break;
    case 'jcmsTesting':
        $title = 'Choose jcms Testing To Load';
        break;
    case 'branchCounts':
        $title = 'Choose Branch(es) to Load';
        break;
    case 'inputFile':
        $title = 'Choose The Cycle Count Input File To Upload';
        break;
    default:
        $title = "There's an Error Somewhere";
        break;
}

$html = "<form id='uploadFileForm'>
        <div style='width: 90%;' class='inline-block'><input id='fileName' name='uploadFile' type='file' class='ui-button ui-corner-all' style='width: 100%' multiple/></div>
        <div class='button-div'><input id='uploadFileButton' type='submit' name='upLoadFile' class='submit ui-button ui-corner-all'></div>
    </form>";



echo json_encode(['html'=>$html, 'title'=>$title]);
