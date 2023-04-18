<?php

$dir = 'C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Self Audits\0219 Feb';

$fileNames = scandir($dir);

var_dump($fileNames);

foreach ($fileNames as $key => $names) {
    if (strlen($names) > 2) {
        $splitNames = explode('.', $names);
        #echo $splitNames[0] . " - " . $splitNames[1] . "<br>";
        $newName = $splitNames[0] . "19." . $splitNames[1];
        echo $newName . "<br>";

        rename($dir . "\\" . $names, $dir . "\\" . $newName);
    }
}