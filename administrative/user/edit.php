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

$server = new \mysqli_wrapper\mysqli();
$privileges = $server->enum_values("Administrator", "privilege level");
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>
    <style>
        #main form {
            margin: 2em;
        }
    </style>

    <title>Control panel - User list</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../menu.php" ?>
    <main id="main">
        <form action="replace_user.php" method="get">
            <label style="display: block">
                username
                <input id="user" name="user" type="text" required>
            </label>
            <label style="display: block">
                privilege level
                <select id="prlev" name="privilege_level">
                    <?php foreach ($privileges as $privilege) {
                        ?>
                        <option value="<?= $privilege ?>"><?= $privilege ?></option>
                        <?php
                    } ?>
                </select>
            </label>
            <div>
                <button type="submit">Save</button>
            </div>
        </form>
        <p>
            New users will have the password set to <code>password</code>
        </p>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>

<script>
    /** @type HTMLInputElement */
    var user = document.getElementById("user");
    /** @type HTMLSelectElement */
    var privileges = document.getElementById("prlev");
    function chk()
    {
        Array.prototype.forEach.call(privileges.options,/** @param o {HTMLOptionElement}*/ function (o, i)
        {
            if(o.value !== "root")
                o.disabled = this.value === "root";
            else
                privileges.selectedIndex = i;
        }.bind(this));
    }
    user.oninput = chk;
    chk.call(user);
</script>
</body>
</html>
