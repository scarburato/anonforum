<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_EDIT_SECTION))
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

    <title>Control panel - Manage section</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../menu.php" ?>
    <main id="main">
        <h1>Sections</h1>
        <div id="view-section">
            <a
                href="edit/new.php"
                class="button"
                title="Create a new section"
                style="margin: 5px">➕ New section</a>
            <table>
                <thead>
                <tr>
                    <th>name</th>
                    <th>full name</th>
                    <th style="width: 0"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                while($section = $sections->fetch_assoc())
                {
                    ?>
                    <tr>
                        <td><?= toHTMLText($section["name"]) ?></td>
                        <td><?= toHTMLText($section["full name"]) ?></td>
                        <td>
                            <a class="button" href="edit/index.php?section=<?= toQueryParameter($section["name"]) ?>" title="Edit this section">✎</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>
