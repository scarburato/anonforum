<?php
if(!isset($server) || !($server instanceof \mysqli_wrapper\mysqli))
    throw new RuntimeException("\$server must be defined AND an instace of mysqli!");

$sections = $server->query("SELECT `full name`, `name` FROM Section ORDER BY `full name` LIMIT 25");
?>
<aside id="sections_list">
    <p id="sections_list_title">
        Other sections
    </p>
    <ul aria-labelledby="sections_list_title">
    <?php
    while($section = $sections->fetch_assoc()) {
        ?>
        <li>
            <a
                    title="<?= toHTMLText($section["full name"]) ?>"
                    class="<?= (isset($_GET["section" ]) && $_GET["section"] === $section["name"]) || (isset($_GET["name" ]) && $_GET["name"] === $section["name"]) || (isset($section_name) && $section_name === $section["name"]) ? "selected" : " "  ?>"
                    href="<?= urlbuild(_WEBROOT . "./section/index.php", ["name" => $section["name"]]) ?>">
                <?= toHTMLText($section["full name"]) ?>
            </a>
        </li>
        <?php
    }
    $sections->close();
    ?>
    </ul>
</aside>