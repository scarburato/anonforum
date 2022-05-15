<?php
include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";

/**
 * Script per l'aggiornamento della parola d'ordine per l'accesso di utente.
 * Controllo se la vecchia passwd è corretta e, in caso affermativo, procedo
 * con l'aggiornamento. Nel caso negativo torno alla pagina di cambia passwd
 * con errore in un param di GET
 */

if(! $auth->has_privilege(Auth\LEVEL_ADMIN))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if($_POST["new_passwd"] !== $_POST["new_passwd_red"])
    throw new RuntimeException("Password verification missmatch");

$server = new mysqli_wrapper\mysqli();
$res = $server->query("SELECT '{$server->real_escape_string($_POST["old_passwd"])}' = A.password FROM Administrator A WHERE A.username = '{$server->real_escape_string($auth->get_user_id())}'");
if(! $res->fetch_array(MYSQL_NUM)[0])
{
    header("Location: index.php?error=wrong_passwd ");
    die();
    //throw new RuntimeException("Wrong password");
}

// Aggiornamento della password
$server->query("UPDATE Administrator SET password = '{$server->real_escape_string($_POST["new_passwd"])}' WHERE username = '{$server->real_escape_string($auth->get_user_id())}'");

?>
<script>window.history.back()</script>
