<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "./lib/html_helper/__init.hphp";


if(! $auth->has_privilege(Auth\LEVEL_ADMIN))
{
    include _FILEROOT . "/lib/pages/error/unauthorized.php";
    die();
}

$server = new mysqli_wrapper\mysqli();

// Aggiornamento livello privilegi
$auth_check = $server->prepare("SELECT A.`privilege level` FROM Administrator A WHERE A.`username` = ?");
$auth_check->bind_param("s", $auth->get_user_id());
$auth_check->bind_result($level);
$auth_check->execute();
$auth_check->fetch();
$auth_check->close();

$auth->sync_privileges_from_db($level);

?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>

    <title>Control panel</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "menu.php" ?>
    <main id="main">
        <h1>Main panel</h1>
        <p>
            Hello <strong><?= $auth->get_user_id() ?></strong>, your privilege level is
            <code>0x<?= strtoupper(dechex((int)$_SESSION["auth_level"])) ?></code>
        </p>
        <?php
        if($auth->has_privilege(auth\LEVEL_EDIT_SECTION)) {
            ?>
            <p>
                You can moderate all the communities on the website!
            </p>
            <?php
        } ?>
        <h2>Moderator of those communities</h2>
        <?php
        $sections = $server->prepare(
            "SELECT section, `full name` FROM Moderator INNER JOIN Section S ON section = S.name WHERE administrator = ?");
        $sections->bind_param("s", $auth->get_user_id());
        $sections->bind_result($section_name, $section_fullname);
        $sections->execute();

        if($sections->num_rows === 0)
        {
            ?><p>∅ empty set!</p><?php
        }
        else
        {
            ?><ul><?php
            while($sections->fetch())
            {
                ?>
                <li>
                    <?php if($auth->has_privilege(\auth\LEVEL_EDIT_SECTION))
                        {
                            ?>
                            <a href="<?= urlbuild("sections/edit.php", ["section" => $section_name]) ?>">
                                <?= toHTMLText($section_fullname) ?>
                            </a>
                    <?php
                        }
                        else
                            echo toHTMLText($section_fullname)
                            ?>

                </li>
            <?php
            }
                ?></ul>
        <?php } ?>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>

