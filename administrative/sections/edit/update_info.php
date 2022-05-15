<?php
include_once "../../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_EDIT_SECTION))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}
if(
    empty($_POST["content"]) ||
    ($_POST["field"] !== "rules" && $_POST["field"] !== "description") ||
    empty($_POST["section"]) )
{
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}

$server = new \mysqli_wrapper\mysqli();
$stm = null;
if($_POST["field"] === "rules")
    $stm = $server->prepare("UPDATE Section SET rules = ? WHERE name = ?");
else
    $stm = $server->prepare("UPDATE Section SET description = ? WHERE name = ?");

$stm->bind_param("ss", $_POST["content"], $_POST["section"]);
$stm->execute();

?><script>window.history.back()</script>