<?php

session_start();

$html = "<div id='uploadDialog' class='ui-dialog ui-dialog-content center' title='Choose File To Upload'>
    <form id='uploadFileForm'>
        <div style='width: 90%;' class='inline-block'><input id='fileName' name='uploadFile' type='file' class='ui-button ui-corner-all' style='width: 100%' /></div>
        <div class='button-div'><input id='uploadFileButton' type='submit' name='upLoadFile' class='ui-button ui-corner-all'></div>
    </form>
</div>";

echo $html;
