<?php
define("JSON_MODE", 1);
define("SKIP_AUTH", 1);

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";

// Se non è definito esco
if(empty($_GET["section"]))
{
    include _FILEROOT . "lib/pages/error/bad_request.php";
    die();
}

if(empty($_GET["page"]))
    $_GET["page"] = 0;

$server = new mysqli_wrapper\mysqli();

if(empty($_GET["timestamp"]))
    $_GET["timestamp"] = $server->dbms_timestamp();

$offset = $_GET["page"] * SECTION_THREADS_PER_PAGE;

$threads_stm = $server->prepare("
SELECT 
    T.`id`,
    T.`is pinned`,
    T.`timestamp`,
    T.`title`,
    CAST(content AS char(150)) AS `content preview`,
    (
        SELECT COUNT(*)
        FROM Reply R WHERE R.thread = T.id
    ) AS `comment counter`
FROM Thread T
WHERE T.`section` = ? AND T.timestamp <= ?
-- Ordine cronologico, prima quelli in evidenza
ORDER BY T.`is pinned` IS TRUE DESC, T.`timestamp` DESC
LIMIT " . (SECTION_THREADS_PER_PAGE) ." OFFSET ?
", false);
$threads_stm->bind_param("ssi", $_GET["section"],$_GET["timestamp"], $offset);
$threads_stm->bind_result($id, $pinned, $timestamp, $title, $content, $count);
$threads_stm->execute();
$threads = $threads_stm->get_result();

$threads_arr = [];

while($thread = $threads->fetch_assoc())
    array_push($threads_arr, $thread);

echo json_encode([
    "timestamp" => $_GET["timestamp"],
    "threads" => $threads_arr,
    "comment counter" => $count
]);

