<?php
define("_WEBROOT", "../../");

include_once "../../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_ADMIN))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}
$title="Set a password";
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>

    <title>Control panel - set a new password</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../menu.php" ?>
    <main id="main">
        <h1>Set a new password</h1>
        <form method="POST" action="passwd.php">
            <p>
                <label>
                    Current password:<br>
                    <input id="old_passwd_input_field" type="password" name="old_passwd" required>
                </label>
            </p>
            <?php if($_GET["error"] === "wrong_passwd") {
                ?>
                <p class="error">
                    You entered a wrong password!
                </p>
                <?php
            } ?>
            <p>
                <label>
                    New password:<br>
                    <input type="password" name="new_passwd" required>
                </label>
            </p>
            <p>
                <label>
                    Confirm new password:<br>
                    <input type="password" name="new_passwd_red" required>
                </label>
            </p>
            <p>
            <button type="submit">Change</button>
            </p>
        </form>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>

<script>
    /** @type {HTMLFormElement} */
    var form = document.forms[0];

    /** @param ev {FocusEvent} */
    form.onsubmit = function (ev) {
        /** @type {HTMLInputElement} */
        var p0 = form.elements[0];
        /** @type {HTMLInputElement} */
        var p1 = form.elements[1];
        /** @type {HTMLInputElement} */
        var p2 = form.elements[2];

        p0.setCustomValidity();

        if(p1.value !== p2.value)
        {
            p1.setCustomValidity("New password does not match");
            p2.setCustomValidity("New password does not match");
            return false;
        }
        else
        {
            p1.setCustomValidity();
            p2.setCustomValidity();
            return true;
        }
    }
</script>
</body>
</html>
