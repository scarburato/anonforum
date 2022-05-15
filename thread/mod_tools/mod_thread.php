<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";

/**
 * cambia proprietà ad un thread
 * - thread: ID thread
 * -
 */

$server = new \mysqli_wrapper\mysqli();

if(! $auth->is_admin_section_from_thread($server, $_GET["thread"]))
{
    include _FILEROOT . "lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["thread"] || empty($_GET["action"])))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

switch ($_GET["action"])
{
    case "lock":
        $lk = $_GET["lock"] === "yes";
        $stm = $server->prepare("UPDATE Thread SET `is locked` = ? WHERE id = ?");
        $stm->bind_param("ii", $lk, $_GET["thread"]);
        break;
    case "pin":
        if(!$auth->has_privilege(\Auth\LEVEL_PIN_THREAD))
        {
            include _FILEROOT . "lib/pages/error/unauthorized.php";
            die();
        }
        $pin = $_GET["pin"] === "yes";
        $stm = $server->prepare("UPDATE Thread SET `is pinned` = ? WHERE id = ?");
        $stm->bind_param("ii", $pin, $_GET["thread"]);
        break;
    case "remove":
        $stm = $server->prepare("DELETE FROM Thread WHERE id = ?");
        $stm->bind_param("i", $_GET["thread"]);
        header("Location: " . _WEBROOT );
        break;
    default:
        include _FILEROOT . "lib/pages/error/bad_request.php";
        die();
}

$stm->execute();
$stm->close();
?>
<script>window.history.back();</script>
