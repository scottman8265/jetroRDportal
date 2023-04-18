<?php

$fileCount = $_POST['fileCount'];

$src = '../uploadFile.php';

for ($i = 1; $i<$fileCount + 1; $i++) {

    $dest = '../temp/file_' . $i.".php";

    copy($src, $dest);

}
