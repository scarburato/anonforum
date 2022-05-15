<?php
/**
 * Presa dall'esempio 1 da questa pagina. Converte un errore PHP in eccezzione
 * @author https://www.php.net/manual/en/class.errorexception.php
 */
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
} );

// Modalità JSON ?
if(defined("JSON_MODE"))
{
    header('Content-Type: application/json');

    set_exception_handler(function ($e){
        // Se è ancora possibile modifico l'header in errore
        header("HTTP/1.0 500 Internal Server Error");

        die(json_encode([
            "class" => get_class($e),
            "error" => $e->getCode(),
            "what" => $e->getMessage(),
            "trace" => $e->getTrace()
        ], JSON_PRETTY_PRINT));
    });
}
else {
    error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
    set_exception_handler(function ($e) {
        include __DIR__ . "/lib/pages/error/error.php";
        die();
    });
}

// Attivare UTF8!
mb_internal_encoding("UTF-8");

// Posizione su file system della root del progetto
define("_FILEROOT", __DIR__ . "/");

// Collegamento al servente MySQL/MariaDB
define("DBMS_HOST", null);
define("DBMS_USER", "root");
define("DBMS_PASSWD", "");
define("DBMS_DATABASE", "Pagani_585281");
define("DBMS_PORT", null);
define("DBMS_SOCKET", null);

/************************************************************
 * COSTANTI LA GESTIONE DELL'INVIO DI NOTIFICHE
 */
define("NOTIFICATION_STREAM_SLEEP_TIME", 5);


/************************************************************
 * COSTANTI PER LA GENERAZIONE DEI COMMENTI SOTTO UN THREAD
 */
define("COMMENTS_MAX_DEPTH", 10);
define("COMMENTS_DEF_DEPTH", 6);

define("COMMENTS_CARDINALITY_ROOT", PHP_INT_MAX);
define("COMMENTS_CARDINALITY_NESTED", 4);

define("SECTION_THREADS_PER_PAGE", 4);

/************************************************************
 * ALTRO
 */
define("CLIENT_INET_ADDRESS", $_SERVER['REMOTE_ADDR']);
/** @var Auth\Auth|null $auth */
$auth = null;
// Attivazione autenticazione
if(!defined("SKIP_AUTH")) {
    session_start();
    require_once _FILEROOT . "lib/auth/__init.hphp";
    $auth = new Auth\Auth();
}