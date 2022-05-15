<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";
/**
 * Questo script bandisce un utente, ad esso si deve passerare tramite query-string:
 * - thread: l'ID del thread su cui l'autore ha partecipato
 * - user:   l'ID dell'utente da bandire
 * - mode:   da dove bandiare l'utente
 *      > "thread" per bandirlo dal thread
 *      > "section" per bandirlo dalla sezione
 *      > "site" per bandirlo dal sito.
 *
 * Se tutto va 0k il server torna 200 Ok
 */

$server = new \mysqli_wrapper\mysqli();

if(! $auth->is_admin_section_from_thread($server, $_GET["thread"]))
{
    include _FILEROOT . "lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["thread"]) || empty($_GET["user"]) || empty($_GET["mode"]))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

switch ($_GET["mode"])
{
    case "thread":
        if(! $auth->has_privilege(\Auth\LEVEL_BAN_USER_THREAD))
        {
            include _FILEROOT . "lib/pages/error/unauthorized.php";
            die();
        }
        $stm = $server->prepare(/** @lang MySQL */
            "UPDATE Poster SET `blocked` = TRUE WHERE `anon id` = ? AND thread = ?
        ");
        break;
    case "section":
        if(! $auth->has_privilege(\Auth\LEVEL_BAN_USER_SECTION))
        {
            include _FILEROOT . "lib/pages/error/unauthorized.php";
            die();
        }
        $stm = $server->prepare("
REPLACE INTO `Banned poster in section`(`poster adress`, section) 
SELECT P.`inet address`, T.section 
FROM Poster P
    INNER JOIN Reply R ON P.thread = R.thread
    INNER JOIN Thread T ON R.thread = T.id
WHERE `anon id` = ? AND P.thread = ?
        ");
        break;
    case "site":
        if(! $auth->has_privilege(\Auth\LEVEL_BAN_USER_SITE))
        {
            include _FILEROOT . "lib/pages/error/unauthorized.php";
            die();
        }
        $stm = $server->prepare("
REPLACE INTO `Banned poster in site`(`poster adress`) 
SELECT P.`inet address` FROM Poster P
WHERE `anon id` = ? AND thread = ?
        ");
        break;
    default:
        include _FILEROOT . "lib/pages/error/bad_request.php";
        die();
}

// BAN-HAMMER
$stm->bind_param("si", $_GET["user"], $_GET["thread"]);
$stm->execute();
?>
<script>
    window.history.back();
</script>
