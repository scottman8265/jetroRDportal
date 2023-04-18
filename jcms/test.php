<?php

ini_set('memory_limit', '1G');

// xml file path
$path = "../input/jcms/Employee Tests 030421.xml";

// Read entire file into string
$xmlfile = file_get_contents($path);

// Convert xml string into an object
$new = simplexml_load_string($xmlfile);

// Convert into json
$con = json_encode($new);

// Convert into associative array
$newArr = json_decode($con, true);

var_dump($newArr);


