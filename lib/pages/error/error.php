<?php
/** Questo file deve essere incluso come unico HTML nella pagina */
header("HTTP/1.0 500 Internal Server Error");
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error 500</title>
</head>
<body>
<h1>500 Script Error</h1>
<p>Something went wrong during the execution of the scripts on the server:</p>
<pre><?= json_encode([
        "class" => get_class($e),
        "error" => $e->getCode(),
        "what" => $e->getMessage(),
        "trace" => $e->getTrace()
    ], JSON_PRETTY_PRINT) ?></pre>
</body>
