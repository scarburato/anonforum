<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";

/**
 * Script che aggiunge ovvero modifica le propreità di un admin
 * Gli passo tramite query string:
 * - user               l'ID dell admin da modificare/creare
 * - privilege level    Il livello da assegnarli
 */

if(! $auth->has_privilege(Auth\LEVEL_USER_EDIT))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(
    empty($_GET["user"]) || empty($_GET["privilege_level"]) ||
    ($_GET["user"] === "root" && $_GET["privilege_level"] !== "root")
)
{
    include _FILEROOT ."/lib/pages/error/bad_request.php";
    die();
}

$server = new \mysqli_wrapper\mysqli();

$stm = $server->prepare("REPLACE INTO Administrator(username, `privilege level`) VALUES (?, ?)");
$stm->bind_param("ss", $_GET["user"], $_GET["privilege_level"]);
$stm->execute();

header("Location: index.php");