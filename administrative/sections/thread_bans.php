<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_BAN_USER_THREAD))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["thread"]) || empty($_GET["section"]))
{
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}