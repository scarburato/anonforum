<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";

if(! $auth->has_privilege(Auth\LEVEL_USER_EDIT))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["user"]))
{
    include _FILEROOT ."/lib/pages/error/bad_request.php";
    die();
}

if($_GET["user"] === "root")
    throw new RuntimeException("This user cannot be deleted!");

$server = new \mysqli_wrapper\mysqli();
$stm = $server->prepare("DELETE FROM Administrator WHERE username = ?");
$stm->bind_param("s", $_GET["user"]);
$stm->execute();

if($_GET["user"] === $auth->get_user_id())
    header("Location: ../exit.php");
else
    header("Location: ./index.php");