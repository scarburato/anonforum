<aside id="sections_list">
    <nav>
        <ul>
            <li><a href="<?= _WEBROOT ?>administrative/index.php">Main panel</a></li>
            <li><a href="<?= _WEBROOT ?>administrative/passwd/index.php">Change password</a></li>
            <?php
            if($auth->has_privilege(Auth\LEVEL_EDIT_SECTION)) { ?>
                <li><a href="<?= _WEBROOT ?>administrative/sections/index.php">Manage sections</a></li>
            <?php } ?>
            <?php if($auth->has_privilege(Auth\LEVEL_USER_EDIT)) { ?>
                <li><a href="<?= _WEBROOT ?>administrative/user/index.php">Manage admins</a></li>
            <?php } ?>
            <?php if($auth->has_privilege(Auth\LEVEL_BAN_USER_SITE)) { ?>
                <li><a href="<?= _WEBROOT ?>administrative/bans">Banned users</a></li>
            <?php } ?>
        </ul>
    </nav>
</aside>