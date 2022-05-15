<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_USER_EDIT))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

$server = new mysqli_wrapper\mysqli();
$admins = $server->query("SELECT A.username, A.`privilege level` AS `plev` FROM Administrator A ORDER BY A.username = 'root'");
$title="Users";
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>

    <title>Control panel - User list</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../menu.php" ?>
    <main id="main">
        <h1>User list</h1>
        <a
                href="edit.php"
                class="button"
                title="Create a new user"
                style="margin: 5px">➕ New section</a>
        <table>
            <thead>
            <tr>
                <th>username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            </thead>

            <?php
            while($admin = $admins->fetch_assoc()){
                ?>

                <tr>
                    <td><?= toHTMLText($admin["username"]) ?></td>
                    <td><?= $admin["plev"] ?></td>
                    <td>
                        <a
                                class="button edit_btn <?= $admin["username"] === "root" ? "disabled" : "" ?>"
                        >edit</a>
                        <a
                                href="delete.php?user=<?= toQueryParameter($admin["username"]) ?>"
                                class="button delete_btn <?= $admin["username"] === "root" ? "disabled" : "" ?>"
                                >delete</a>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </main>
    <script>
        var buttons = document.getElementsByClassName("delete_btn");

        for(var i = 0; i < buttons.length; i++)
            buttons[i].onclick = function test(e)
            {
                if(this.classList.contains("disabled") || ! confirm(
                    "Are you sure to delete \"" +
                    this.parentNode.parentNode.children[0].textContent +
                    "\" ??"
                ))
                    e.preventDefault();
            }
    </script>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>
