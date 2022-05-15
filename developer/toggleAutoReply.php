<?php
define("_WEBROOT", "../");

include_once "../bootstrap.php";
include_once _FILEROOT . "./lib/mysqli_wrapper/__init.hphp";

$server = new mysqli_wrapper\mysqli();

switch ($_GET["status"])
{
    default:
    case "activate":
        $server->query("ALTER EVENT AutoReply ENABLE");
        break;
    case "disable":
        $server->query("ALTER EVENT AutoReply DISABLE");
}
?>
<script>
    history.back();
</script>
