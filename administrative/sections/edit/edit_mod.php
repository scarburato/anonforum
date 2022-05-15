<?php
include_once "../../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_EDIT_SECTION))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["section"]) || empty($_GET["moderator"]))
{
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}

$server = new \mysqli_wrapper\mysqli();
$query = null;

switch ($_GET["mode"])
{
    case "add":
        $query = $server->prepare("
            REPLACE INTO Moderator(section, administrator) VALUES (?,?);
        ");
        break;
    case "delete":
        $query = $server->prepare("
            DELETE FROM Moderator WHERE section = ? AND administrator = ?
        ");
        break;
    default:
        include _FILEROOT . "/lib/pages/error/bad_request.php";
        die();
}

$query->bind_param("ss", $_GET["section"], $_GET["moderator"]);
try
{
    $query->execute();
}
catch (\mysqli_wrapper\sql_exception $exception)
{
    // Errore foreign key (probabile il moderatore non esiste)
    if($exception->getCode() === 1452)
    {
        header("Location: index.php?section=" . toQueryParameter($_GET["section"]) ."&error=admin_not_exists#addmdr");
        die();
    }

    throw $exception;
}

header("Location: index.php?section=" . toQueryParameter($_GET["section"]));