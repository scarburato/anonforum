<!-- ************************************ Finestra  - -->
<div class="window" id="compose-window">
    <div class="window-headbar" id="compose-window-headbar">
        <div class="title">
            <p>Compose</p>
        </div>
        <div class="buttons" id="compose-window-close">
            <img src="../asset/cross.png" aria-label="Close the window" alt="CLOSE" class="window-button"
                 draggable="false">
        </div>
    </div>
    <div>
        <form id="compose-window-form" method="post" action="reply.php">
            <input title="Which thread?" hidden name="thread" value="<?= $_GET["id"] ?>">
            <p>
                <label>Repling to
                    <input name="replies" type="text" readonly value="">
                </label>
            </p>
            <label>Content</label>
            <div id="controls">
                <?php include _FILEROOT . "lib/pages/common/editor_buttons.html" ?>
            </div>
            <div>
                <textarea title="Content" name="content" id="compose_textarea" required></textarea>
            </div>
            <p>
                <button type="submit">Write</button>
            </p>
        </form>
    </div>
    <div id="compose-window-loading" hidden>
        Uploading...
    </div>
</div>

<script>var editor = new Editor("compose_textarea", "controls");</script>

<?php if ($moderator_enabled) { ?>
    <div id="mod-window" class="window">
        <div id="mod-window-headbar" class="window-headbar">
            <div class="title">
                <p>Moderation tool - Comment</p>
            </div>
            <div class="buttons" id="mod-window-close">
                <img src="../asset/cross.png" aria-label="Close the window" alt="CLOSE" class="window-button"
                     draggable="false">
            </div>
        </div>
        <div>

            <form id="mod-window-form" method="post" action="mod_tools/mod_comment.php">
                <input title="Which thread?" hidden name="thread" value="<?= $_GET["id"] ?>">
                <p>
                    <label>Sel <input name="replies" type="text" readonly value="">
                    </label>
                </p>
                <p>
                    <label>Lock
                        <select name="lock-mode">
                            <option value="" selected>Do nothing</option>
                            <option value="no">No</option>
                            <option value="no-all">No. Apply to his children</option>
                            <option value="yes">Yes</option>
                            <option value="yes-all">Yes. Apply to his children</option>
                        </select>
                    </label>
                    <button type="submit" name="action" value="lock">Apply</button>
                </p>
                <p>
                    <button type="submit" name="action" value="remove" onclick="modWindow.alertUser = true;">
                        🗑 Remove comment and his children
                    </button>
                </p>
            </form>
        </div>
    </div>

    <div id="mod-user-window" class="window">
        <div id="mod-user-window-headbar" class="window-headbar">
            <div class="title">
                <p>Moderation tool - User</p>
            </div>
            <div class="buttons" id="mod-user-window-close">
                <img src="../asset/cross.png" aria-label="Close the window" alt="CLOSE" class="window-button"
                     draggable="false">
            </div>
        </div>
        <div>
            <form id="mod-user-window-form" method="get" action="mod_tools/ban_user.php">
                <input type="text" title="" name="thread" hidden value="<?= toHTMLText($_GET["id"]) ?>">
                <label>
                    User
                    <input type="text" name="user" readonly required>
                </label>
                <?php if($auth->has_privilege(\auth\LEVEL_BAN_USER_SITE) || $auth->has_privilege(\auth\LEVEL_BAN_USER_SECTION)) {
                    ?>
                    <label>
                        Network address
                        <input type="text" readonly required id="ip_user_sel">
                    </label>
                <?php
                }?>
                    <button type="submit" name="mode" value="thread">Ban user from this thread</button>
                    <?php if ($auth->has_privilege(\auth\LEVEL_BAN_USER_SECTION)) { ?>
                        <button type="submit" name="mode" value="section">Ban user from the entire section</button>
                    <?php }
                    if ($auth->has_privilege(\auth\LEVEL_BAN_USER_SITE)) { ?>
                        <button type="submit" name="mode" value="site">Ban user site-wise</button>
                    <?php } ?>
            </form>
        </div>
    </div>

    <div id="mod-thread-window" class="window">
        <div id="mod-thread-window-headbar" class="window-headbar">
            <div class="title">
                <p>Moderation tool - Thread</p>
            </div>
            <div class="buttons" id="mod-thread-window-close">
                <img src="../asset/cross.png" aria-label="Close the window" alt="CLOSE" class="window-button"
                     draggable="false">
            </div>
        </div>
        <div>
            <form id="mod-thread-window-form" method="GET" action="mod_tools/mod_thread.php">
                <input title="Which thread?" hidden name="thread" value="<?= $_GET["id"] ?>">
                <p>
                    <label>Pin
                        <select name="pin">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </label>
                    <button type="submit" name="action" value="pin">Apply</button>
                </p>
                <?php
                if($auth->has_privilege(\Auth\LEVEL_PIN_THREAD))
                {
                    ?>
                    <p>
                        <label>Lock
                            <select name="lock">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </label>
                        <button type="submit" name="action" value="lock">Apply</button>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <button type="submit" name="action" value="remove" onclick="modThreadWindow.alertUser = true;">
                        🗑 Remove thread and his replies
                    </button>
                </p>
            </form>
        </div>
    </div>

<?php } ?>