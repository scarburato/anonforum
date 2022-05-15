<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_BAN_USER_SITE))
{
include _FILEROOT . "/lib/pages/error/unauthorized.php";
die();
}

$server = new mysqli_wrapper\mysqli();
$sections = $server->query("
SELECT
S.name, S.`full name`
FROM Section S
");

$title="Sections";
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
        <h1>Banned users</h1>
        <h2>In a section</h2>
        <form action="section.php" method="get">
            <label>
                Section name:
                <input type="text" required name="section">
            </label>
            <button type="submit">Search</button>
        </form>
        <h2>In a thread</h2>
        <form action="thread.php" method="get">
            <label>
                Thread id:
                <input type="number" required name="thread">
            </label>
            <button type="submit">Search</button>
        </form>
        <h2 id="site">Site wise</h2>
        <table>
            <thead>
            <tr>
                <th>IPv4/IPv6 address</th>
                <th style="width: 1px"></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $bans = $server->query("
            SELECT inet6_ntoa(`poster adress`) AS `inet` FROM `Banned poster in site`
            ");

            while($address = $bans->fetch_assoc()["inet"])
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
            ?>
            </tbody>
        </table>

    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>