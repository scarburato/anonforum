<?php
/** Questo file deve essere incluso come unico HTML nella pagina */
header("HTTP/1.0 400 Bad Request");

if(defined("JSON_MODE"))
{
    die(json_encode([
        "error" => 400,
        "what" => "Bad Request",
    ], JSON_PRETTY_PRINT));
}

?>
<!DOCTYPE HTML>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error 400</title>
</head>
<body>
<h1>400 Bad request</h1>
<p>The request is <em>not</em> correct! Check if the URI is spelled correctly....</p>
</body>