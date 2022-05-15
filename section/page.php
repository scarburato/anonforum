<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";
include_once _FILEROOT . "lib/html_helper/__init.hphp";

// Se non è definito esco
if(empty($_GET["section"]))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

if(empty($_GET["page"]))
    $_GET["page"] = 0;

$offset = $_GET["page"] * SECTION_THREADS_PER_PAGE;

$server = new mysqli_wrapper\mysqli();
$threads = $server->prepare("
SELECT 
    T.`id`,
    T.`is pinned`,
    T.`timestamp`,
    T.`title`,
    substr(T.`content`,0, 100) AS `content preview`
FROM Thread T
WHERE T.`section` = ?
-- Ordine cronologico, prima quelli in evidenza
ORDER BY T.`is pinned` IS TRUE DESC, T.`timestamp` ASC
LIMIT " . (SECTION_THREADS_PER_PAGE + 1) ." OFFSET ?
");
$threads->bind_param("si", $_GET["section"], $offset);
$threads->bind_result($id, $pinned, $timestamp, $title, $content);
$threads->execute();

/**
 * Scorro n - 1 elementi. L'esistenza dell'elemento ennesimo lo uso per sapere se devo
 * generare il pulsante AVANTI per proseguire avanti di una pagina.
 */
for($i = 0; $i < SECTION_THREADS_PER_PAGE && $threads->fetch(); $i++)
{
    ?>
    <article class="card">
        <header class="card-header">
            <h2 style="display: block; width: calc(100% - 50px)"><?= toHTMLText($title) ?></h2>
        </header>
        <div class="card-content">
            <p>
                <?= toHTMLText($content) ?>
            </p>
        </div>
        <footer class="card-footer">
            <a class="item is-link" href="../thread/index.php?id=<?= $id ?>#replies-master">Comments</a>
            <a class="item is-link" href="../thread/index.php?id=<?= $id ?>">Read</a>
        </footer>
    </article>
<?php
}

$has_next_page = $threads->fetch();
$page = (int)($_GET["page"]);
?>

<nav class="pagination" role="navigation" aria-label="pagination">
    <a
        class="pagination-previous button"
        data-goto="<?= $page - 1 ?>"
        <?= $offset == 0 ? "disabled" : "" ?>>
        Previous
    </a>
    <a
        class="pagination-next button"
        data-goto="<?= $page + 1 ?>"
        <?= !$has_next_page ? "disabled" : "" ?>>
        Next page
    </a>
    <!--<ul class="pagination-list">
         <li>
            <a class="pagination-link" aria-label="Goto page 1">1</a>
        </li>
        <li>
            <span class="pagination-ellipsis">&hellip;</span>
        </li>
        <li>
            <a class="pagination-link" aria-label="Goto page 45">45</a>
        </li>
        <li>
            <a class="pagination-link is-current" aria-label="Page 46" aria-current="page">46</a>
        </li>
        <li>
            <a class="pagination-link" aria-label="Goto page 47">47</a>
        </li>
        <li>
            <span class="pagination-ellipsis">&hellip;</span>
        </li>
        <li>
            <a class="pagination-link" aria-label="Goto page 86">86</a>
        </li>
    </ul>-->
</nav>
