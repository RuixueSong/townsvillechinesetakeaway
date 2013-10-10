<?php

//Get downloaded file
$file = (dirname(__FILE__) . '/../../downloads/' . $_GET["FileName"]);
//Set data for download dialog
header("Content-Type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Length: " . filesize($file));
header("Content-Disposition: attachment; filename=" . $_GET["FileName"]);
//Download file
readfile($file);

