<?php

$dir = 'C:\Users\Scott\Desktop\completed - bonus hold';

$files = scandir($dir);

foreach ($files as $file) {
	echo $file ."</br>";
}


