<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_BAN_USER_THREAD))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["thread"]))
{
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}
$server = new \mysqli_wrapper\mysqli();
$bans = $server->prepare(
        "SELECT `anon id` AS `author`, inet6_ntoa(`inet address`) FROM Poster WHERE thread = ? AND blocked IS TRUE");
$bans->bind_param("s", $_GET["thread"]);
$bans->bind_result($author, $address);
$bans->execute();
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>

    <title>Control panel - Banned users</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../menu.php" ?>
    <main id="main">
        <h1>Banned users in thread</h1>
        <table>
            <thead>
            <tr>
                <th>author</th>
                <?php if($auth->has_privilege(\Auth\LEVEL_BAN_USER_SECTION)) {
                   ?>
                    <th>IPv4/IPv6 address</th>
                    <?php
                }?>
                <th style="width: 1px"></th>
            </tr>
            </thead>
            <tbody>
            <?php
            while($bans->fetch())
            {
                ?>
                <tr>
                    <td><?= $author ?></td>
                    <?php if($auth->has_privilege(\Auth\LEVEL_BAN_USER_SECTION)) {
                        ?>
                        <td><?= $address ?></td>
                        <?php
                    }?>
                    <td>
                        <a class="button" href="<?= urlbuild("./unban_thread.php", [
                            "thread" => $_GET["thread"], "author" => $author
                        ]) ?>" title="unban">🗑</a>
                    </td>
                </tr>
                <?php
            }
            $bans->close();
            ?>
            </tbody>
        </table>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>
