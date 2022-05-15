<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/auth/__init.hphp";

$server = new mysqli_wrapper\mysqli();

// Lancio eccezzione (?)
$server->query("ciao");
