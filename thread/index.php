<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/html_helper/__init.hphp";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";

// Se non è definito esco
if (empty($_GET["id"])) {
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

$server = new mysqli_wrapper\mysqli();

/**
 * Ricezione del thread
 */
$thread = $server->prepare("
    SELECT 
        S.`full name`, S.`name`, T.`content`, T.`title`, T.`timestamp`, P.`anon id`, CONCAT('#', HEX(P.`anon color`)) AS `color hex`,
        T.`is pinned`, T.`is locked`
    FROM Thread T
        INNER JOIN `Section` S ON T.`section` = S.`name`
        INNER JOIN `Poster` P ON P.`thread` = T.id AND P.`is op` IS TRUE
    WHERE `id` = ?"
);

$thread->bind_param("i", $_GET["id"]);
$thread->bind_result($section_fullname, $section_name, $post_content, $title, $publish_timestamp, $poster_id, $poster_color, $pinned, $locked);
$thread->execute();

// Not trovato
if (!$thread->fetch()) {
    include _FILEROOT . "lib/pages/error/not_found.php";
    die();
}

/**
 * Se l'utente è bloccato
 */
$user_locked_stm = $server->prepare(/** @lang MySQL */ "
SELECT EXISTS(
    SELECT 1 FROM `Banned poster in section` WHERE `poster adress` = inet6_aton(?) AND section = ? 
) OR EXISTS(
    SELECT 1 FROM `Banned poster in site` WHERE `poster adress` = inet6_aton(?)
) OR EXISTS(
    SELECT 1 FROM Poster WHERE `inet address` = inet6_aton(?) AND thread = ? AND `blocked` IS TRUE
)");
$user_locked_stm->bind_param("sssss", $_SERVER['REMOTE_ADDR'], $section_name, $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_ADDR'], $_GET["id"]);
$user_locked_stm->bind_result($user_locked);
$user_locked_stm->execute();
$user_locked_stm->fetch();
$user_locked_stm->close();

$moderator_enabled = $auth->is_admin_section($server, $section_name);

$title = toHTMLText($title);
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>

    <link rel="stylesheet" type="text/css" href="css/main.css">

    <script src="../js/lib/window/Window.js"></script>

    <script src="../js/lib/editor/Editor.js"></script>
    <script src="../js/lib/bbcode/xbbcode.js"></script>

    <script src="../js/lib/net/ajax.js"></script>

    <script src="../js/lib/template/CardTemplate.js"></script>
    <script src="../js/lib/template/Comment.js"></script>

    <title><?= toHTMLText($section_fullname) ?> - <?= ($title) ?></title>
</head>
<body>
<!-- Testata -->
<?php include _WEBROOT . "lib/pages/common/header.php" ?>
<div id="content">
    <!-- Parte laterale -->
    <?php include _WEBROOT . "section/sections_list.php" ?>
    <!-- Il thread e le sue risposte -->
    <main id="main">
        <article id="post" data-author="<?= $poster_id ?>">
            <header class="info-header">
                <p class="author" style="background-color: <?= $poster_color ?>">
                    <span><?= $poster_id ?></span>
                    <span title="He's the author!">&nbsp;🎤</span>
                </p>
                <p class="info">
                <span title="Ciao mondo!">
                    <time id="threadPublishTime" datetime="<?= $publish_timestamp ?>"><?= $publish_timestamp ?></time>
                </span>
                    <?php if ($locked) { ?>
                        <span id="thread_locked" title="This thread is locked, you can't reply anymore">🔒</span>
                    <?php } ?>
                    <?php if ($pinned) { ?>
                        <span title="This thread is pinned">📌</span>
                    <?php } ?>
                </p>
                <?php if ($moderator_enabled) { ?>
                    <button title="moderation tools for the thread" class="mod-tools"
                            onclick="modThreadWindow.window.open()">
                        🛡 thread
                    </button>
                    <button title="moderation tools for the author" class="mod-tools"
                            onclick="modUserWindow.moderateUser(this)">
                        🛡 Author
                    </button>
                <?php } ?>
            </header>
            <h1><?= ($title) ?></h1>
            <div class="bbcode-raw"><?= toHTMLText($post_content) ?></div>
        </article>
        <?php
        if ($user_locked) {
            ?>
            <div id="user_locked">
                <img src="<?= _WEBROOT ?>/asset/ban%20hammer.png" alt="BANNED!">
                <p>
                    You network address has been banned. You can't add new comment to this thread
                </p>
            </div>
            <?php
        }
        if ($auth->has_privilege(\Auth\LEVEL_ADMIN)) {
            ?>
            <div id="mod_links">
                <p>Links to manage already banned users</p>
                <ul>
                    <?php if ($auth->has_privilege(\Auth\LEVEL_BAN_USER_SITE)) {
                        ?>
                        <li>
                            <a href="<?= _WEBROOT ?>administrative/bans/index.php#site">
                                Banned from the entire site
                            </a>
                        </li>
                        <?php
                    }
                    if ($auth->has_privilege(\Auth\LEVEL_BAN_USER_SECTION)) {
                        ?>
                        <li>
                            <a href="<?= urlbuild(_WEBROOT . "administrative/bans/section.php", [
                                "section" => $section_name
                            ]) ?>">
                                Banned from the entire section
                            </a>
                        </li>
                        <?php
                    }
                    if ($moderator_enabled) {
                        ?>
                        <li>
                            <a href="<?= urlbuild(_WEBROOT . "administrative/bans/thread.php", [
                                "thread" => $_GET["id"]
                            ]) ?>">
                                Banned from this thread
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <div id="replies-ansew" hidden>
            <button
                <?= $locked || $user_locked ? "disabled" : "" ?>
                    style="width: 100%"
                    class="item"
                    data-messageid="ROOT"
                    onclick="composeWindow.onclick(this)"
            >🖉 Reply
            </button>
        </div>
        <div id="replies-orignal-root-button" hidden>
            <p class="replies-info">
                <a href="./index.php?id=<?= $_GET["id"] ?>" onclick="commentManager.switchRootLink(event, null);">
                    ⇱ Show all comments
                </a>
            </p>
        </div>
        <p class="replies-info" id="no_comments" hidden>
            📪 There is nothing to show here....
        </p>

        <!-- Qua vanno le risposte -->
        <div id="replies-master">

        </div>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>

<!-- Le finestre  -->
<?php include "./windows.php" ?>

<!-- *********************************** Template di un commento ************ -->
<template id="reply-prototype">
    <!-- Manca l'heading perché i commenti non hanno titolo -->
    <article class="reply"><!-- id="comment_{ID}" -->
        <div class="reply-content card">
            <header class="card-header info-header">
                <p class="author" style="color: white"><!-- bg-color: author->color -->
                    <span><!-- author->id --></span>
                    <!-- Togliere hidden se è l'autore -->
                    <span title="He's the author!" hidden>🎤</span>
                </p>
                <p class="info">
                <span title="Ciao mondo!">
                    <time datetime="null"></time>
                </span>
                    <span title="Show comments using this comment as the root">
                    <a class="reply-id" href="./index.php?id=XX&root=YY" onclick="commentManager.switchRootLink(event)">
                        <span>↪</span>
                        <span><!-- ID DEL COMMENTO --></span>
                    </a>
                </span>

                    <span title="Probably from you" hidden>👤</span>

                    <span title="This comment is locked, you can't reply anymore" hidden>🔒</span>
                    <span title="The author was banned!" hidden>
                        <img class="lineHeight" src="<?= _WEBROOT ?>/asset/ban%20hammer.png" alt="B">
                    </span>

                </p>
                <?php if ($moderator_enabled) { ?>
                    <button title="Moderation tools for the comment" class="mod-tools"
                            onclick="modWindow.moderateComment(this)">
                        🛡 Comment
                    </button>
                    <button title="Moderation tools for the author" class="mod-tools"
                            onclick="modUserWindow.moderateUser(this)">
                        🛡 Author
                    </button>
                <?php } ?>
            </header>
            <div class="card-content bbcode-raw">
                <!-- <p> CORPO DELLA REPLY </p> -->
            </div>
            <div class="card-footer">
                <button
                        class="item"
                        onclick="composeWindow.onclick(this)"
                >
                    🖉 Reply
                </button>
            </div>
        </div>


        <div class="replies">
            <div class="line-master"></div>
            <div class="line-slave">
                <div class="comments">
                    <!-- ITERAZIONE RICORSIVA QUA -->
                    <!-- MOSTRA IL DIV SE NON HO FINITO -->
                </div>
                <div class="more" hidden>
                    <a
                            onclick="commentManager.switchRootLink(event)"
                            href="./index.php?id=<?php /** thread id*/ ?>&root=<?php /**$comment["comment id"]*/ ?>"
                            title="Replies to this comment are hidden. Click to show them"
                    >
                        ⇲ Show more replies to this comment
                    </a>
                </div>
            </div>
        </div>
    </article>
</template>


<script async src="js/common.js"></script>
<script async src="js/comment_manager.js"></script>
<script async src="js/comment_reply.js"></script>
<?php if ($moderator_enabled) { ?>
    <script async src="js/mod.js"></script>
<?php } ?>
<script>
    // Parso il contenuto del thread
    XBBCODE.parseNodes(document.body);

    // Formatto la data del thread
    /** @type {HTMLTimeElement} */
    var time = document.getElementById("threadPublishTime");
    console.assert(time !== null);
    var dd = new Date(time.dateTime + 'Z');
    time.dateTime = dd.toISOString();
    time.title = dd.toUTCString();
    time.textContent = dd.toLocaleString(DEFAULT_TIME_LOCALE, DEFAULT_TIME_FORMAT);
</script>
</body>
</html>