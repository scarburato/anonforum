<?php
define("_WEBROOT", "./");

include_once "./bootstrap.php";
include_once "./lib/mysqli_wrapper/__init.hphp";
include_once "./lib/html_helper/__init.hphp";

$server = new mysqli_wrapper\mysqli();
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <?php include _FILEROOT . "lib/pages/common/head.php"; ?>
    <title>Forum</title>
</head>
<body>
<?php include "lib/pages/common/header.php" ?>
<main id="content">
    <h2>Trending sections</h2>
    <ol id="trend_section" class="section_list">
        <?php
        $sections = $server->query("
SELECT S.name, S.`full name`, SUM(R.timestamp >= CURRENT_TIMESTAMP - INTERVAL 12 HOUR ) AS `growth`
FROM Section S 
    INNER JOIN Thread T ON S.name = T.section
    INNER JOIN Reply R ON T.id = R.thread
GROUP BY S.name HAVING growth <> 0
ORDER BY `growth` DESC LIMIT 5
");
        if($sections->num_rows !== 0) {
            while ($section = $sections->fetch_assoc()) {
                ?>
                <li>
                    <span class="trending-counter" title="new comments in the last 12 hours">
                        <span class="trending-counter-icon">📈</span>
                        <span><?= $section["growth"] ?></span>
                    </span>
                    <a href="<?= urlbuild("./section/index.php", ["name" => $section["name"]]) ?>">
                        <?= toHTMLText($section["full name"]) ?>
                    </a>
                </li>
                <?php
            }
        }
        else
        {
            ?>
            <p id="no-trend">Nothing is trending at the moment :(</p>
        <?php
        }
        ?>
    </ol>
    <h2>Main sections</h2>
    <ul id="main_sections" class="section_list">
        <?php
        $sections = $server->query("SELECT S.`name`, S.`full name` FROM `Section` S ORDER BY S.name");

        while ($section = $sections->fetch_assoc()) {
            ?>
            <li>
                <a href="<?= urlbuild("./section/index.php", ["name" => $section["name"]]) ?>">
                    <?= toHTMLText($section["full name"]) ?>
                </a>
            </li>
            <?php
        }
        ?>
    </ul>
</main>
<?php include "lib/pages/common/footer.php" ?>

</body>
</html>