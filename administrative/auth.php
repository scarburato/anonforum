<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";


/**
 * Autentico un nuovo utente che sta tentendo l'accesso.
 * Se non l'utente non sta cercando di autenticarsi allora gli invio
 * il documento HTML con la form per procedere all'autenticazione
 */

if($auth->has_privilege(Auth\LEVEL_ADMIN)) {
    header("Location: index.php");
    die("Already in!");
}
if(isset($_POST["username"]))
{
    sleep(1);

    $server = new mysqli_wrapper\mysqli();
    $auth_check = $server->prepare("SELECT A.username, A.`password` = ?, A.`privilege level` FROM Administrator A WHERE A.`username` = ?");
    $auth_check->bind_param("ss", $_POST["password"], $_POST["username"]);
    $auth_check->bind_result($username, $good_password, $level);
    $auth_check->execute();

    // Eseguzione query
    $failure = ! $auth_check->fetch();
    if(! $failure)
        $failure = ! $good_password;

    $auth_check->close();
    $server->close();

    if(! $failure)
    {
        $auth->sync_privileges_from_db($level);
        $auth->set_user_id($username);

        header("Location: index.php");
        die("Auth success!");
    }
}


?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Authentication</title>
    <style>
        body{
            text-align: center;
        }

        form{
            left: 35%;
            position: absolute;
            width: 20%;
            margin: auto;
            color: black;
            background-color: rgba(100, 100, 100, 0.4);
            padding: 3%;
            border-radius: 12px;
        }

        form * {
            margin-top: 5px;
            width: 100%;
        }

        form button{
            width: 50%;
        }

        .error {
            color: #FF0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
<form method="POST">
    <?php if($failure) {
        ?>
        <p class="error">Authentication failed.</p>
    <?php
    } ?>
    <label>Username:<input name="username" type="text" required></label>
    <label>Password:<input name="password" type="password" required></label>
    <button type="submit">Log in</button>
</form>
</body>
</html>