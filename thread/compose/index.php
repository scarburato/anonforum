<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";

// Se non è definito esco
if (empty($_GET["section"])) {
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

$server = new \mysqli_wrapper\mysqli();
?>

<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>
    <link rel="stylesheet" type="text/css" href="../css/main.css">

    <script src="../../js/lib/editor/Editor.js"></script>

    <title>Compose</title>
</head>
<body>
<!-- Testata -->
<?php include _WEBROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
<?php include _WEBROOT . "section/sections_list.php" ?>
<!-- Il thread e le sue risposte -->
<main id="main">
    <form id="publish-form" action="publish.php" method="POST">
        <input name="section" title="" hidden readonly value="<?= toHTMLText($_GET["section"]) ?>">
        <div>
            <label>
                Title:
                <input name="title" type="text" maxlength="64" required>
            </label>
            <label >Content:</label>
        </div>

        <div id="controls">
            <?php include _FILEROOT . "lib/pages/common/editor_buttons.html" ?>
        </div>
        <div>
            <textarea title="Content" name="content" id="compose_textarea" required></textarea>
        </div>
        <div id="sent-div">
            <button type="submit">Publish</button>
        </div>
    </form>
</main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
<script>
var editor = new Editor("compose_textarea", "controls");
</script>
</body>
</html>