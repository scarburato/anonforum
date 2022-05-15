<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";

$server = new mysqli_wrapper\mysqli();

if(empty($_POST["title"]) || empty($_POST["content"]))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

// Inserimento del post
$new_thread = $server->prepare("INSERT INTO Thread(`section`, `title`, `content`) VALUES (?,?,?)");
$new_thread->bind_param("sss", $_POST["section"], $_POST["title"], $_POST["content"]);
$new_thread->execute();

// Inserimento dell'autore del post
$server->query(
    "INSERT INTO Poster(`inet address`, `thread`, `is op`) 
VALUES (inet6_aton('{$_SERVER["REMOTE_ADDR"]}'), {$new_thread->insert_id}, TRUE)");


header("Location: ../index.php?id={$new_thread->insert_id}");