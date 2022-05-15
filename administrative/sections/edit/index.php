<?php
define("_WEBROOT", "../../../");

include_once "../../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_EDIT_SECTION))
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

$section = $server->prepare("
SELECT `full name`, description, rules FROM Section WHERE name = ?
");
$section->bind_param("s", $_GET["section"]);
$section->bind_result($full_name, $description, $rules);
$section->execute();
if(! $section->fetch())
{
    include _FILEROOT . "/lib/pages/error/not_found.php";
    die();
}
$section->close();

$title="edit section";
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>
    <link rel="stylesheet" type="text/css" href="../css/main.css">

    <title>Control panel - Edit Section</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../../menu.php" ?>
    <main id="main">
        <h1>Community information</h1>
        <h2>Description</h2>
        <form method="POST" action="update_info.php">
            <input type="text" required hidden title="" name="section" value="<?= $_GET["section"] ?>">
            <div id="description_controls">
                <?php include _FILEROOT . "lib/pages/common/editor_buttons.html" ?>
            </div>
            <div>
                <textarea title="Content" name="content" id="description_textarea" required><?= toHTMLText($description) ?></textarea>
            </div>
            <button type="submit" name="field" value="description">💾 Save</button>
        </form>
        <h2>Rules</h2>
        <form method="POST" action="update_info.php">
            <input type="text" required hidden title="" name="section" value="<?= $_GET["section"] ?>">
            <div id="rules_controls">
                <?php include _FILEROOT . "lib/pages/common/editor_buttons.html" ?>
            </div>
            <div>
                <textarea title="Content" name="content" id="rules_textarea" required><?= toHTMLText($rules) ?></textarea>
            </div>
            <button type="submit" name="field" value="rules">💾 Save</button>
        </form>
        <h1>Community moderator</h1>
        <h2 id="addmdr">Add a moderator</h2>
        <form action="edit_mod.php" method="get">
            <?php if($_GET["error"] === "admin_not_exists")
                {
                   ?>
                    <p class="error">
                        Username does not exists!
                    </p>
            <?php
                }?>
            <input type="text" title="" name="mode" value="add" required hidden>
            <input type="text" title="" name="section" value="<?= toQueryParameter($_GET["section"]) ?>" required hidden>
            <label>
                new moderator:
                <input type="text" name="moderator" placeholder="administrator's username" required >
            </label>
            <button type="submit">➕ Add moderator</button>
        </form>
        <h2>Remove moderators</h2>
        <table>
            <thead>
            <tr>
                <th>Username</th>
                <th style="width: 0"></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $moderators = $server->prepare("
            SELECT administrator AS `username` FROM Moderator WHERE section = ?
            ");
            $moderators->bind_param("s", $_GET["section"]);
            $moderators->bind_result($username);
            $moderators->execute();
            while($moderators->fetch())
            {
                ?>
                <tr>
                    <td><?= toHTMLText($username) ?></td>
                    <td>
                        <a class="button" href="<?= urlbuild("edit_mod.php", [
                                "mode" => "delete", "section" => $_GET["section"], "moderator" => $username
                        ]) ?>" title="Remove">🗑</a>
                    </td>
                </tr>
                <?php
            }
            $moderators->close();
            ?>
            </tbody>
        </table>
        <h1>Banned users in section</h1>
        <a class="button" href="<?= urlbuild("../../bans/section.php", [
            "section" => $_GET["section"]
        ]) ?>">Click here to view the list</a>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>
