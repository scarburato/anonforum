<?php
define("JSON_MODE", 1);
include_once "../../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";

/**
 * Ritorna ad un amministratore di livello sezione/root l'indirizzo di rete
 * di un utente partendo dai parametri GET
 * - id         Codice autore
 * - thread     Codice thread
 */

if(!$auth->has_privilege(\Auth\LEVEL_BAN_USER_SECTION) || ! $auth->has_privilege(\Auth\LEVEL_BAN_USER_SITE))
{
    include _FILEROOT . "lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["id"]) || empty($_GET["thread"]))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}


$server = new \mysqli_wrapper\mysqli();
$stm = $server->prepare("SELECT inet6_ntoa(`inet address`) FROM Poster WHERE thread = ? AND `anon id` = ?");
$stm->bind_param("ss", $_GET["thread"], $_GET["id"]);
$stm->bind_result($address);
$stm->execute();
if(! $stm->fetch())
{
    include _FILEROOT . "lib/pages/error/not_found.php";
    die();
}

echo json_encode(["address" => $address]);