<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";
/**
 * Questo script PhP permette di effettuare azioni di moderazione su un commento utente.
 * I parametri vengono passati tramite POST dal cliente:
 * - thread:    L'ID del thread dove c'è il commento interessato dall'azione
 * - replies:   L'ID del commento da moderare
 * - action:    L'operazione dal svolgere
 *      > remove Rimuove dalla base di dati il commento e i suoi figli
 *      > lock   Blocca il commento e se $_POST["lock-mode"] === all anche i figli
 */

$server = new \mysqli_wrapper\mysqli();

if(! $auth->is_admin_section_from_thread($server, $_POST["thread"]))
{
    include _FILEROOT . "lib/pages/error/unauthorized.php";
    die();
}

if(empty($_POST["thread"] || empty($_POST["replies"])))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

/**
 * Funzione che blocca ovvero sblocca un commento dato il suo ID
 * L'ID del thread viene presa dal parametro POST
 * @param $id string
 * @param $lock bool
 */
function updateReplyLockStatus($id, $lock)
{
    global $server;
    $lock = $lock ? 1 : 0;

    $server->query(
        "UPDATE Reply SET `is locked` = {$lock} WHERE thread = ". $server->real_escape_string($_POST["thread"])  .
        " AND id = " .  $server->real_escape_string($id));
}

/**
 * Funzione che blocca ovvero sblocca i figli di un commento dato il suo ID
 * L'ID del thread viene presa dal parametro POST
 * @param $id string
 * @param $lock bool
 */
$replies = $server->prepare("SELECT id AS `id` FROM Reply WHERE thread = ? AND replies = ?", false);
function updateReplyLockStatusToChilds($root, $lock)
{
    global $replies;

    $replies->bind_param("ii", $_POST["thread"], $root);
    $replies->execute();

    $replies_res = $replies->get_result();
    while($reply = $replies_res->fetch_assoc())
    {
        updateReplyLockStatus($reply["id"], $lock);
        updateReplyLockStatusToChilds($reply["id"], $lock);
    }
}

switch ($_POST["action"])
{
    case "remove":
        if(! $auth->has_privilege(Auth\LEVEL_REMOVE_REPLY))
        {
            include _FILEROOT . "lib/pages/error/unauthorized.php";
            die();
        }
        /** MySQL >= 5.6.12 */
        # $server->query("DELETE FROM Reply WHERE id = " . $server->real_escape_string($_POST["replies"]));
        /** MySQL antico */
        $server->query("CALL delete_cascade_Reply(" . $server->real_escape_string($_POST["thread"]) . " , " . $server->real_escape_string($_POST["replies"]) . ");");

        break;
    case "lock":
        if(! $auth->has_privilege(Auth\LEVEL_LOCK_REPLY))
        {
            include _FILEROOT . "lib/pages/error/unauthorized.php";
            die();
        }

        updateReplyLockStatus($_POST["replies"], $_POST["lock-mode"] === "yes" || $_POST["lock-mode"] === "yes-all");
        if($_POST["lock-mode"] === "yes-all" || $_POST["lock-mode"] === "no-all")
            updateReplyLockStatusToChilds($_POST["replies"], $_POST["lock-mode"] === "yes-all");
            break;
    default:
        include _FILEROOT . "lib/pages/error/bad_request.php";
        die();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Location: ../index.php?id=" . toQueryParameter($_POST["thread"]));
