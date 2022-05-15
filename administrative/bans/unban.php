<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_BAN_USER_SITE)) {
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["address"])) {
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}

$server = new \mysqli_wrapper\mysqli();
$stm = null;
if(empty($_GET["section"])) {
    $stm = $server->prepare("DELETE FROM `Banned poster in site` WHERE `poster adress` = inet6_aton(?)");
    $stm->bind_param("s", $_GET["address"]);
}
else
{
    $stm = $server->prepare("DELETE FROM `Banned poster in section` WHERE `poster adress` = inet6_aton(?) AND section = ?");
    $stm->bind_param("ss", $_GET["address"], $_GET["section"]);
}
$stm->execute();
$stm->close();
?><script>window.history.back()</script>