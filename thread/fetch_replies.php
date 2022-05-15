<?php
define("JSON_MODE", 1);
define("SKIP_AUTH", 1);

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/fetch_managers/__init.hphp";

if (empty($_GET["thread"])) {
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

if (empty($_GET["root"]) || $_GET["root"] == "null")
    $_GET["root"] = null;

$server = new \mysqli_wrapper\mysqli();
$comments = (new RepliesBuilder($server, $_GET["thread"], $_GET["root"]))->fetch()["root"];

echo json_encode($comments, JSON_UNESCAPED_UNICODE);