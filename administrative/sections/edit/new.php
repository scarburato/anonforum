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
$fail = false;
if(isset($_GET["section"]) && isset($_GET["full_section_name"]))
{
    $server = new \mysqli_wrapper\mysqli();
    $stm = $server->prepare("INSERT INTO Section(name, `full name`) VALUES (?, ?)");
    $stm->bind_param("ss", $_GET["section"], $_GET["full_section_name"]);
    try {
        $stm->execute();
        header("Location: ./index.php?section=" . toQueryParameter($_GET["section"]));
    }
    catch (\mysqli_wrapper\sql_exception $e)
    {
        $fail = $e->getMessage();
    }
}
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>
    <style>
        #main{
            padding-top: 20px;
        }
        label {
            display: block;
        }
    </style>

    <title>Control panel - Manage section</title>
</head>
<body>
<!-- Testata -->
<?php include _FILEROOT . "lib/pages/common/header.php" ?>
<!-- Parte laterale -->
<div id="content">
    <?php include "../../menu.php" ?>
    <main id="main" >
        <?php
        if($fail !== false)
        {
            ?>
            <p class="error">
                Qualcosa è andato storto...<br>
                <code><?= toHTMLText($fail) ?></code>
            </p>
        <?php
        }
        ?>
        <form method="get">
            <label>
                Section name:
                <input name="section" type="text" maxlength="64" required>
            </label>
            <label>
                Full section name:
                <input name="full_section_name" maxlength="256" type="text" required>
            </label>
            <button>Create</button>
        </form>
    </main>
</div>
<?php include _FILEROOT . "lib/pages/common/footer.php" ?>
</body>
</html>

