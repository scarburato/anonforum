<?php
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache"); // Il client non dovrebbe usare meccanismi di cache

define("_WEBROOT", "../");
define("SKIP_AUTH", 1);

include_once "../bootstrap.php";
include_once _FILEROOT . "lib/mysqli_wrapper/__init.hphp";

set_time_limit(0);
/**
 * Questa pagina usa lo standard SSE (https://www.w3.org/TR/eventsource/)
 * per inviare ad un cliente notifiche di eventuali nuove risposte ad uno dei suoi thread
 * ovvero uno dei suoi commenti.
 *
 * Il cliente passa tramite query GET la data, in formato ISO e nel fuso orario universale,
 * dal quale il programma che segue inzia a ricercare per nuove riposte; altrimenti, se il
 * cliente non passa nulla il programma usa la data attuale del DBMS.
 *
 * Il programma lavora come segue:
 * fintanto un cliente resta collegato al servente invia:
 *      - L'ora attuale del DMBS
 *      - Eventuali notifiche di nuove risposte ai commenti
 *
 * L'applicazione cliente dovrebbe memorizzare nella sua memoria l'ora delle ultime richieste in
 * modo da poterla riutilizzare in una conessione ex-nova per permettere a questo programma
 * di riprendere a notificare da dov'era rimasto.
 *
 * Per debug dello script eseguire in una riga di comando
 * curl -N http://127.0.0.1/notificationstream.php
 */

define("START_SSE_ID", "id: ");
define("STOP_SSE_ID", "\n");
define("START_SSE_EVENT", "event: ");
define("STOP_SSE_EVENT", "\n");
define("START_SSE_MESSAGE", "data: ");
define("END_SSE_MESSAGE", "\n\n");

/**
 * Invia un SSE al cliente collegato a questa pagina
 * @param $data mixed
 * @param $event String
 * @param $id int
 */
function send_sse($data, $event = null, $id = null)
{
    // Stampo eventuale ID
    if($id !== null)
        echo START_SSE_ID . (int)$id . STOP_SSE_ID;

    // Stampo eventuale EVENT
    if($event !== null)
        echo START_SSE_EVENT . (string)$event . STOP_SSE_EVENT;

    // Stampa corpo messaggio
    echo START_SSE_MESSAGE;
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    echo END_SSE_MESSAGE;

    // Forzo l'invio del buffer nel server WEB al client
    flush();
}

$server = new \mysqli_wrapper\mysqli();

/**
 * Da quando devo cominciare a notificare i nuovi commenti?
 * Dal paramatreto GET timestart altrimenti, se timestart non è
 * definito, uso l'ora attaule del DBMS
 */
$timestart = (! empty($_GET["timestart"])) ?
    $_GET["timestart"] :
    $server->dbms_timestamp();
$timestop = "1970-01-01 00:00:00Z";
$myip = CLIENT_INET_ADDRESS;

/**
 * Interrogazione per chiedere alla base dati quali sono i nuovi commenti apparsi
 * sotto i miei thread ovveo commenti
 */
$checkForNewNotificationsStatement = $server->prepare("
SELECT 
    RR.`thread` AS `threadId`,
       
    RR.`id` AS `fatherThreadComment`,
    RL.`id` AS `sonThreadComment`,

    RL.`timestamp` AS `replyTime`
FROM Poster P
    INNER JOIN Reply RR ON RR.`author` = P.`anon id` AND RR.`thread` = P.`thread`
    INNER JOIN Reply RL ON 
        RL.`replies` = RR.`id` AND -- Chi à risposto ai suoi commenti
        RL.`author` <> P.`anon id` -- Eccetto quelle che si è fatto da solo!
WHERE 
      P.`inet address` = inet6_aton(?) AND 
      RL.`timestamp` >= ? AND RL.`timestamp` < ? -- Intervallo temporale
ORDER BY RL.`timestamp` ASC
", false
);
$checkForNewNotificationsStatement->bind_param("sss", $myip, $timestart, $timestop);

if(!empty($_GET["watch"])) {
    $checkForNewCommentsForThread = $server->prepare("
    SELECT 
        S.`id` AS `comment id`,
        S.`content` AS `content`,
           
        S.`timestamp` AS `timestamp`,
           
        0 AS `number of childs`,

        S.`is locked` OR T.`is locked` AS `is locked`,
        T.`is locked` AS `thread is locked`,
           
        F.id AS `father`,
           
        -- Informazioni sull'autore
        S.`author` AS `author`,                             -- L'id dell'autore
        CONCAT('#', HEX(P.`anon color`)) AS `color hex`,    -- Il colore assegnato in HEX
        P.`is op` AS `is op`,                               -- È l'autore del thread? 
        P.`inet address` = inet6_aton(?) AS `is you`, -- Sei te?
        P.blocked OR EXISTS(
            SELECT 1 FROM `Banned poster in section` BPS WHERE BPS.`poster adress` = P.`inet address` AND BPS.section = T.id 
        ) OR EXISTS(
            SELECT 1 FROM `Banned poster in site` BPS WHERE BPS.`poster adress` = P.`inet address`
        ) AS `banned` -- È bandito ?
    FROM Reply S
        INNER JOIN Poster P ON P.`anon id` = S.author AND P.thread = S.thread
        INNER JOIN Thread T ON S.thread = T.id
        LEFT JOIN Reply F ON F.id = S.replies AND F.thread = S.thread
    WHERE 
          S.thread = ? AND -- Il thread da osservare
          S.timestamp >= ? AND S.timestamp < ? -- Intervallo
    ORDER BY S.timestamp ASC", false);

    $checkForNewCommentsForThread->bind_param("siss",$myip, $_GET["watch"], $timestart, $timestop);
}


/***********************************************
 *                 INZIO SSE
 ***********************************************/
// Disabilit il maccanismo di buffering del PHP
ob_end_flush();

// Finché c'è un client collegato invio notifiche
while(!connection_aborted())
{
    // Decido fino a dove verificare per nuovi messaggi
    $timestop = $server->dbms_timestamp();

    // Verifica nuove risposte
    $checkForNewNotificationsStatement->execute();
    $result = $checkForNewNotificationsStatement->get_result();

    while($notification = $result->fetch_assoc())
        send_sse($notification, "notification");

    // Verifica nuovi commenti al thread attualmente aperto
    if(!empty($_GET["watch"])) {
        $checkForNewCommentsForThread->execute();
        $result = $checkForNewCommentsForThread->get_result();

        while ($comment = $result->fetch_assoc())
            send_sse($comment, "comment");
    }

    // Invio il nuovo timestpo
    send_sse($timestop, "timestop");

    // Sposto il margine temporale avanti all'ultima interrogazione
    $timestart = $timestop;

    sleep(NOTIFICATION_STREAM_SLEEP_TIME);
}

