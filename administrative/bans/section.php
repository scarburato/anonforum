<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_BAN_USER_SECTION))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

if(empty($_GET["section"]))
{
    include _FILEROOT . "/lib/pages/error/bad_request.php";
    die();
}
$server = new \mysqli_wrapper\mysqli();
$bans = $server->prepare("
            SELECT inet6_ntoa(`poster adress`) AS `username` FROM `Banned poster in section` WHERE section = ?
            ");
$bans->bind_param("s", $_GET["section"]);
$bans->bind_result($address);
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
        <h1>Banned users in <?= $_GET["section"] ?></h1>
        <table>
            <thead>
            <tr>
                <th>IPv4/IPv6 address</th>
                <th style="width: 1px"></th>
            </tr>
            </thead>
            <tbody>
            <?php
            while($bans->fetch())
            {
                ?>
                <tr>
                    <td><?= $address ?></td>
                    <td>
                        <a class="button" href="<?= urlbuild("./unban.php", [
                            "section" => $_GET["section"], "address" => $address
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
