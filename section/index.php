<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";

// Se non è definito esco
if(empty($_GET["name"]))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

$server = new mysqli_wrapper\mysqli();

$section = $server->prepare("
SELECT 
       S.`name`, S.`full name`,
       S.description, S.rules
FROM Section S WHERE `name` = ?");
$section->bind_param("s", $_GET["name"]);
$section->execute();
$section->bind_result($section_a, $section_fullname, $description, $rules);

// Not trovato
if(! $section->fetch())
{
    include _FILEROOT . "lib/pages/error/not_found.php";
    die();
}

$section->free_result();
$section->close();

// Titolo della pagina
$title = toHTMLText($section_fullname);
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>

    <link rel="stylesheet" type="text/css" href="css/main.css">

    <script src="../js/lib/net/ajax.js"></script>
    <script src="../js/lib/bbcode/xbbcode.js"></script>
    <script src="../js/lib/template/CardTemplate.js"></script>
    <script src="../js/lib/template/Thread.js"></script>

    <title><?= ($title) ?></title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <header>
        <h1><?= ($title) ?></h1>
        <h2>Description</h2>
        <div class="bbcode-raw"><?= toHTMLText($description) ?></div>
        <h2>Posting rules</h2>
        <div class="bbcode-raw"><?= toHTMLText($rules) ?></div>
    </header>
    <div>
        <?php include "./sections_list.php" ?>
        <main id="main">
            <div id="controls">
                <a class="button" href="../thread/compose/index.php?section=<?= $_GET["name"] ?>">✎ Compose a new thread</a>
                <button class="button" id="button_reload">↺ Reload</button>
            </div>
            <div id="page_content">

            </div>
            <div id="keep-loading">
                <img src="../asset/more.gif" alt="I am loading!">
            </div>
            <div id="stop-loading" hidden>
                ✓ You've reached the end!
            </div>
        </main>
    </div>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>

<template id="thread_template">
    <article class="card">
        <header class="card-header">
            <h2 style="display: block"><!-- Titolo thread --></h2>
            <p style="display: block">
                <span title="Ciao mondo!">
                    <time datetime=""><!-- Data di pubblicazione --></time>
                </span>
                <span title="This thread is pinned" hidden>📌</span>
                <span title="This thread is locked, you can't reply anymore" hidden>🔒</span>
            </p>
        </header>
        <div class="card-content">
            <p><!-- Preview del contentu --></p>
        </div>
        <footer class="card-footer">
            <a class="item is-link" href="../thread/index.php?id=<?php  /**  $id */ ?>">Read</a>
            <a class="item is-link" href="../thread/index.php?id=<?php  /**  $id */ ?>#replies-master">
                Comments [<span class="comment-counter">0</span>]
            </a>
        </footer>
    </article>
</template>

<script src="js/thread_manager.js"></script>
<script>
    var content = document.getElementById("page_content");
    Thread.template = document.getElementById("thread_template");
    XBBCODE.parseNodes(document.body)
</script>

</body>
</html>