<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/19/2019
 * Time: 9:34 AM
 */

require_once 'vendor/autoload.php';

session_start();

$mainCSS      = "css/mainCSS.css";
$menuCSS      = 'css/menu33/menuCSS.css';
$layoutCSS    = 'css/layout198/layoutCSS.css';
$jQueryCSS    = "jQuery/jquery-ui.css";
$themeCSS     = "jQuery/jquery-ui.theme.css";
$structureCSS = "jQuery/jquery-ui.structure.css";
$jQuery       = 'src="https://code.jquery.com/jquery-3.3.1.js"
  integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
  crossorigin="anonymous"';
$jQueryUI     = "jQuery/jquery-ui.min.js";
$mainJS       = "js/main.js";

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <link href="<?php echo $mainCSS ?>" rel="stylesheet">
    <link href="<?php echo $jQueryCSS ?>" rel="stylesheet">
    <link href="<?php echo $menuCSS ?>" rel="stylesheet">
    <link href="<?php echo $layoutCSS ?>" rel="stylesheet">
    <link href="<?php echo $structureCSS ?>" rel="stylesheet">

    <title>Jetro/RD Func Portal</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<div id="content-wrapper" class="ui-corner-all">

    <div id="appTitle"><h1>Jetro/RD Function Portal</h1></div>

    <div id="dolphinContainer" class="ui-corner-top">
        <div id="dolphinNav" class="ui-corner-top">
            <ul id="mainMenu">
                <li id="main" class="mainMenu"><span class="current">Login</span></li>
                <li id="corpAudit" class="mainMenu"><span>Corp Audit</span></li>
                <li id="selfAudit" class="mainMenu"><span>Self Audit</span></li>
                <li id="cycleCount" class="mainMenu"><span>Cycle Count</span></li>
                <li id="jcmsTesting" class="mainMenu"><span>JCMS</span></li>
                <li id="learningCenter" class="mainMenu"><span>Learning Center</span></li>
                <li id="tracker" class="mainMenu"><span>Tracker</span></li>
                <li id="misc" class="mainMenu"><span>Misc</span></li>
            </ul>
        </div>
    </div>


    <div id="task-container" class="task-container ui-corner-all">
        <div id="header" class="ui-widget-header task-header ui-corner-top ">Log In <br> Not Functional</div>

        <div id="leftColumn" class="columns"></div>
        <div id="centerColumn" class="columns">
            <div class="centerInfo"></div>
            <div class="dateInfo"></div>
            <div class="form">
                <form name="tracker" id="form"></form>
            </div>
        </div>
        <div id="rightColumn" class="columns"></div>

        <div id="testDiv"></div>
    </div>

    <div id="middleData" class="middleData"></div>


    <div id='data-container' style="display: none"><h1>This is just a test</h1></div>


    <div class="button-div footer">
        <button class="fillSelfHeaders ui-corner-all ui-button testBtn" onclick="getFiles('../input/auditsSelf', 'auditSelf')">Fill Self Headers</button>
        <button class="fillCorpHeaders ui-corner-all ui-button testBtn" onclick="getFiles('../input/auditsCorp', 'auditCorp')">Fill Corp Headers</button>
        <button class="procAODfiles ui-corner-all ui-button testBtn" onclick="getFiles('../input/aodFiles', 'aod')">AOD Files</button>
        <button class="procJCMSfile ui-corner-all ui-button testBtn" onclick="getFiles('../input/jcms', 'jcms')">JCMS Files</button>
        <button class="procInvfile ui-corner-all ui-button testBtn" onclick="getFiles('../input/inventories', 'inv')">Inv Files</button>
        <button class="testConnection ui-corner-all ui-button testBtn" onclick="testConnection()">Inv Files</button>
    </div>

</div>

<div id="selectDialog" class='ui-dialog ui-dialog-content' title='Select Period(s)'>
    <div id='selectWrap'></div>
</div>

<div id='uploadDialog' class='ui-dialog ui-dialog-content center' title='Choose Files To Upload'></div>

<div id="outputDialog" class="'ui-dialog ui-dialog-content center" title="Output Data"></div>

<script src="https://code.jquery.com/jquery-3.3.1.js"
        integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
        crossorigin="anonymous"></script>
<script src="jQuery/jquery-ui.js"></script>
<script src="js/main.js"></script>

</body>
</html>




