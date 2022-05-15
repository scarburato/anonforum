<?php

use mysqli_wrapper\sql_exception;

define("JSON_MODE", 1);
define("SKIP_AUTH", 1);

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";

$server = new mysqli_wrapper\mysqli();

try
{
    $thread_id = $server->real_escape_string($_POST["thread"]);
    $server->query(
        "INSERT INTO Poster(`inet address`, `thread`) 
VALUES (inet6_aton('{$_SERVER['REMOTE_ADDR']}'), {$thread_id})");
}
catch (sql_exception $e)
{
    // Se c'è la medesima riga inserita (Errore 1062, ER_DUP_ENTRY) allora tutto apposto
    // infatti per il medesimo thread solo una volta mi memorizzo l'autore.
    if($e->getCode() !== 1062)
        throw $e;
}

$_POST["replies"] = ($_POST["replies"] === "ROOT" ? null : $_POST["replies"]);

/**
 * Inserisco una nuova Reply nella base di dati.
 * Per ottenere l'ID dell'autore partendo dall'indirizzo IPv4/IPv6 dell'autore devo
 * guardare nella tabella Poster
 */
$new_reply = $server->prepare("
INSERT INTO Reply(`thread`,`content`, `author`, `replies`) 
SELECT 
    P.`thread`,
    ? AS `content`,
    P.`anon id`, 
    ? AS `replies`
FROM `Poster` P 
    WHERE P.`inet address` = inet6_aton(?) AND P.`thread` = ?
");
$new_reply->bind_param("sisi",  $_POST["content"],  $_POST["replies"], $_SERVER['REMOTE_ADDR'], $_POST["thread"]);
$new_reply->execute();

echo json_encode([
    "thread" => $_POST["thread"],
    "replies" => $_POST["replies"],
    "insertId" => $new_reply->insert_id
]);