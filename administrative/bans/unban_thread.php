<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";

if(empty($_GET["thread"]) || empty($_GET["author"])) {
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}

$server = new \mysqli_wrapper\mysqli();

if(! $auth->is_admin_section_from_thread($server, $_GET["thread"])) {
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

$stm = $server->prepare("UPDATE Poster SET blocked = FALSE WHERE `anon id` = ? AND thread = ?");
$stm->bind_param("si", $_GET["author"], $_GET["thread"]);
$stm->execute();
?><script>window.history.back()</script>