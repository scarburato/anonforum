<?php
/** Questo file deve essere incluso come unico HTML nella pagina */
header("HTTP/1.1 403 Forbidden");

if(defined("JSON_MODE"))
    if(defined("JSON_MODE"))
    {
        die(json_encode([
            "error" => 403,
            "what" => "Forbidden",
        ], JSON_PRETTY_PRINT));
    }
?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error 403</title>
</head>
<body>
<h1>403 Forbidden</h1>
<p>You are <strong>not</strong> authorized to view this page!</p>
</body>