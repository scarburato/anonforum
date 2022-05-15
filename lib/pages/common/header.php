<header>
    <div id="head-title-container">
        <span id="title" style="display: inline-block">
            <a class="site-tile" href="<?= _WEBROOT ?>">Public forum</a>
            <?php if(isset($title)) { ?>
                <span id="page-title"> - <?= $title ?></span>
            <?php } ?>
        </span>
        <span id="head-buttons">
            <?php if( ! $auth->has_privilege(Auth\LEVEL_ADMIN)) { ?>
                <a class="button" href="<?= _WEBROOT ?>administrative/auth.php">login as admin</a>
            <?php } else { ?>
                <span>Welcome, <em><?= toHTMLText($auth->get_user_id()) ?></em></span>
                <a class="button" href="<?= _WEBROOT ?>administrative/">Control panel</a>
                <a class="button" href="<?= _WEBROOT ?>administrative/exit.php">Log out</a>
            <?php } ?>
            <button id="notificationButton">
                🔔
            </button>
        </span>
    </div>
</header>
