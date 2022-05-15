<?php
/** Questo file deve essere incluso come unico HTML nella pagina */
header("HTTP/1.0 404 Not Found");

if(defined("JSON_MODE"))
    if(defined("JSON_MODE"))
    {
        die(json_encode([
            "error" => 404,
            "what" => "Not Found",
        ], JSON_PRETTY_PRINT));
    }

?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error 404</title>
</head>
<body>
<h1>404 Not found</h1>
<p>The requested resource <em>does not</em> exists. Check if the URI is spelled correctly....</p>
</body>
