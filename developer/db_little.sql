SET NAMES utf8;

DROP DATABASE IF EXISTS `Pagani_585281`;

CREATE DATABASE `Pagani_585281` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `Pagani_585281`;

/**********************************************************************
 *
 *                      DATI DI UN SINGOLO THREAD
 *
 **********************************************************************/
CREATE TABLE `Thread` (
    `id`            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `section`       CHAR(64) NOT NULL,

    `title`         VARCHAR(64) NOT NULL,
    `content`       TEXT NOT NULL,

    `timestamp`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `time to live`  INTEGER UNSIGNED DEFAULT 259200, -- = 72 ore = 3 giorni

    -- Flags
    `is pinned`     BOOLEAN NOT NULL DEFAULT FALSE,
    `is locked`     BOOLEAN NOT NULL DEFAULT FALSE
);

-- Gli utenti che prendono parte ad una discussione (incluso l'autore)
CREATE TABLE `Poster` (
    -- Chiave usata internamente (POST ID + NET ADDRESS)
    `inet address`      VARBINARY(16) NOT NULL COMMENT 'IPv6 (or IPv4) network address of the poster',
    `thread`            BIGINT UNSIGNED NOT NULL,

    -- Chiave anonima usata esternamente (POST ID + USER ID)
    `anon id`           CHAR(7) NOT NULL DEFAULT 'NOP' COMMENT 'ID used to identify a poster in a thread',
    `anon color`        MEDIUMINT UNSIGNED COMMENT 'A colour assigned to a poster (3 bytes RGB)',

    -- Stato dello utente
    `blocked`           BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'TRUE when the poster is no more allowed to reply in a thread',
    `is op`             BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Is this the author of the thread?',

    -- Dichiarazione delle chiavi
    PRIMARY KEY (`inet address`, `thread`),
    UNIQUE KEY (`anon id`, `thread`)
);

CREATE TABLE `Reply` (
    `id`                INT UNSIGNED AUTO_INCREMENT,
    `thread`            BIGINT UNSIGNED,

    `content`           TEXT NOT NULL CHECK (`content` <> ''),

    `timestamp`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `author`            CHAR(7) NOT NULL,

    -- `clone thread`      BIGINT UNSIGNED GENERATED ALWAYS AS (`thread`) VIRTUAL,
    `replies`           INT UNSIGNED DEFAULT NULL COMMENT 'Is this reply a reply to another reply?',

    `is locked`         BOOLEAN NOT NULL DEFAULT FALSE,

    -- Chiave primaria
    PRIMARY KEY (`id`, `thread`),

    -- Chiave esternera
    FOREIGN KEY (`thread`) REFERENCES `Thread` (`id`) ON DELETE CASCADE ,

    -- Su MySQL da 5.6.12 in su
    -- FOREIGN KEY (`clone thread`, `replies`) REFERENCES Reply(`thread`, `id`) ON DELETE CASCADE,
    -- Su MySQL antichi (attivare il trigger)
    FOREIGN KEY (`replies`) REFERENCES Reply(`id`)
);
/**********************************************************************
 *
 *                      SEZIONE DI UN SITO E I SUOI
 *                            AMMINISTRATORI
 **********************************************************************/

CREATE TABLE `Section` (
    `name`              CHAR(64) PRIMARY KEY,
    `full name`         VARCHAR(256) NOT NULL,

    `description`       TEXT,
    `rules`             TEXT
);

ALTER TABLE Thread ADD FOREIGN KEY (`section`) REFERENCES  Section(name);

CREATE TABLE `Administrator` (
    `username`          CHAR(32) PRIMARY KEY,
    `password`          VARCHAR(256) NOT NULL,

    `privilege level`   ENUM('root', 'section_administrator', 'section_moderator')
);

INSERT INTO Administrator(`username`, `password`, `privilege level`) VALUES
('root', 'toor', 'root'),
('pluto', 'pluto', 'root'),
('pippo', 'pippo', 'section_administrator'),
('paperino', 'paperino', 'section_moderator');

CREATE TABLE `Moderator` (
    `section`           CHAR(64),
    `administrator`     CHAR(32),

    PRIMARY KEY (`section`, `administrator`),
    FOREIGN KEY (`section`) REFERENCES  Section(`name`),
    FOREIGN KEY (`administrator`) REFERENCES Administrator(`username`)
);

/****************************************************************************
  Utenti banditi
 */
CREATE TABLE `Banned poster in section` (
    `poster adress`     VARBINARY(16),
    `section`           CHAR(64),

    PRIMARY KEY (`section`, `poster adress`),
    FOREIGN KEY (`section`) REFERENCES Section(`name`)
);

CREATE TABLE `Banned poster in site` (
    `poster adress`     VARBINARY(16) PRIMARY KEY
);

/*****************************************************************************
 *                  PROCEDURE
 */
DELIMITER ;;

-- Sopperisce all'assenza di chiave esterna sul DB su MariaDB 5.6
CREATE PROCEDURE `delete_cascade_Reply`(IN `threadD` BIGINT UNSIGNED, IN `comment` INT UNSIGNED)
BEGIN
    DECLARE terminato BOOLEAN DEFAULT FALSE;
    DECLARE replyID INT UNSIGNED;

    DECLARE cur CURSOR FOR (
        SELECT R.id FROM Reply R
        WHERE R.thread = `threadD` AND R.replies = `comment`
    );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET terminato = TRUE;
    OPEN cur;

    SET max_sp_recursion_depth=255;

    -- Loop tra le risposte. In modo da cancellare ogni figlio
    loop_replies: LOOP
        FETCH cur INTO replyID;

        IF terminato IS TRUE THEN
            LEAVE loop_replies;
        END IF ;

        CALL delete_cascade_Reply(threadD, replyID);
    END LOOP ;

    -- Cancello il padre
    DELETE FROM Reply WHERE thread = threadD AND id = `comment`;
END ;;

CREATE TRIGGER `check permission` BEFORE INSERT ON `Reply` FOR EACH ROW
BEGIN
    -- Il thread è bloccato ?
    IF (SELECT T.`is locked` FROM Thread T WHERE T.`id` = NEW.`thread`) IS TRUE
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'This thread is locked! You can\'t reply anymore.';
    END IF;

    -- Il commento è bloccato ??
    IF NEW.replies IS NOT NULL AND (SELECT F.`is locked` FROM Reply F WHERE F.`id` = NEW.`replies` AND F.thread = NEW.thread) IS TRUE
    THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'This comment is locked! You can\'t reply anymore.';
    END IF;

    -- Il Thread è bloccato ???
    IF (
        SELECT `blocked` OR EXISTS(
            SELECT 1 FROM `Banned poster in section` BPS
                INNER JOIN Thread T2 ON BPS.section = T2.section
            WHERE BPS.`poster adress` = P.`inet address` AND T2.id = NEW.thread
        ) OR EXISTS(
            SELECT 1 FROM `Banned poster in site` BPS WHERE BPS.`poster adress` = P.`inet address`
        ) FROM Poster P WHERE NEW.author = P.`anon id` AND NEW.thread = P.thread
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'YOU ARE BANNED!';
    END IF ;
END ;;

CREATE TRIGGER `generate poster` BEFORE INSERT ON `Poster` FOR EACH ROW
m: BEGIN
    DECLARE `charSet` CHAR(66) DEFAULT 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM091234567890!?';
    DECLARE `newId` CHAR(7) DEFAULT '';
    DECLARE i TINYINT UNSIGNED DEFAULT 0;

    -- Genero il color
    IF NEW.`anon color` IS NULL THEN
        SET NEW.`anon color` = FLOOR(RAND() * 0xFFFFFF);
    END IF;

    -- Se mi ànno passato un ID manualmente esco.
    IF NEW.`anon id` <> 'NOP' THEN
        LEAVE m;
    END IF ;

    ancora: LOOP
        -- Genero i 5 caratteri dello ID
        SET i = 0;
        SET `newId` = '';

        WHILE i < 7 DO
                SET `newId` = CONCAT(
                        newId,
                        SUBSTRING(charSet, RAND() * 66 + 1, 1) -- Prendo un carattere a caso nel charSet
                    );

                SET i = i + 1;
            END WHILE ;

        -- Se esiste un altro col medesimo ID nel thread esco
        IF `newId` <> 'autoREp' AND NOT EXISTS (
                SELECT 1
                FROM `Poster` AU
                WHERE AU.thread = NEW.thread AND AU.`anon id` = newId
            ) THEN
            LEAVE ancora;
        END IF ;
    END LOOP ;

    SET NEW.`anon id` = newId;
END;;

DELIMITER ;

/*****************************************************************************
 *                  DATI DI ESEMPIO
 */
INSERT INTO Section(`name`, `full name`, description, rules) VALUES
    ('pol', 'International politics', 'Perferendis nihil officiis accusamus porro omnis. Placeat veniam voluptatem itaque cumque. Unde voluptas quisquam et deserunt quo unde nulla. Dolores nam veniam velit expedita. Est quibusdam voluptatem eius voluptas et sequi maiores. Consectetur eligendi reprehenderit omnis autem.', '[list][*]Do not be rude\n[*]Do not post illegal content\n[/list]'),
    ('news', 'News from the World', 'Voluptatibus sapiente aliquam neque ipsam. Delectus at rem magni recusandae consequatur et quae. Quaerat nobis sed et. Iure optio sint et ut nemo.', '[list][*]Add at least one reaiable source when posting a news article\n[*]Test\n[/list]'),
    ('bicycles', 'Bicycles', 'Voluptatibus sapiente aliquam neque ipsam. Delectus at rem magni recusandae consequatur et quae. Quaerat nobis sed et. Iure optio sint et ut nemo.', '[list][*]Add at least one reaiable source when posting a news article\n[*]Test\n[/list]'),
    ('thinkpad', 'IBM/Lenovo ThinkPad', 'Voluptatibus sapiente aliquam neque ipsam. Delectus at rem magni recusandae consequatur et quae. Quaerat nobis sed et. Iure optio sint et ut nemo.', '[list][*]Add at least one reaiable source when posting a news article\n[*]Test\n[/list]'),
    ('announcement', 'Forum Announcement', 'Voluptatibus sapiente aliquam neque ipsam. Delectus at rem magni recusandae consequatur et quae. Quaerat nobis sed et. Iure optio sint et ut nemo.', '[list][*]Add at least one reaiable source when posting a news article\n[*]Test\n[/list]'),
    ('cuisine', 'Recipes and food', 'Cupiditate sit voluptas exercitationem earum eligendi autem voluptates aut. Dolore velit aliquam fugiat reiciendis. Iste rem dolores incidunt possimus qui beatae. Veniam unde enim explicabo qui dignissimos vel. Enim ex minima quae. Laborum praesentium repellat delectus tenetur.', '[ul][li][b]NEVER[/b] post frogs\n[/li][/ul]');

INSERT INTO Thread(section, title, `is pinned`, content) VALUES
    ('pol', 'Prova di funzionamento!', TRUE,
     'Questa è una semplice [b]prova[/b] nulla di che, ecco alcuni caratteri utf8
ÀÒ Ð¼³¬¼³²¹㏣£"%)"!¼¬¼³ÐΩŁ®.\nEcco del [u]BBCode[/u]
[list][*][color=red]Testo rosso[/color]
[*][quote]testo citato[/quote]
[*][code]testo monospazio[/code]
[*][url=http://it.wikipedia.org]Wikipedia[/url]
[*][img]https://upload.wikimedia.org/wikipedia/commons/9/90/JustAnExample.JPG[/img]
[*][i]Italic[/i]
[*]შინაური
[/list]
[table]
[tr]
  [td]table cell 1[/td]
  [td]table cell 2[/td]
[/tr]
[tr]
  [td]table cell 3[/td]
  [td]table cell 4[/td]
[/tr]
[/table]
Funzionerà tutto?');

INSERT INTO Moderator(section, administrator) VALUES
('news', 'pippo'),
('pol', 'paperino'),
('announcement', 'paperino'),
('bicycles', 'paperino');


INSERT INTO Poster(`inet address`, `thread`, `anon id`, `is op`)
VALUES (inet6_aton('127.0.0.10'), 1, 'xXxXxXx', FALSE),
       (inet6_aton('127.0.0.11'), 1, 'yYyYyYy', TRUE),
       (inet6_aton('127.0.0.12'), 1, 'cCcCcCc', FALSE),
       (inet6_aton('127.0.0.13'), 1, 'bBbBbBb', FALSE),
       (inet6_aton('127.0.0.14'), 1, '89CdeEf', FALSE),
       (inet6_aton('::2'), 1, 'dDdDdDd', FALSE);

INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (1, 1, 'Sapientia quarundam instituti rom sap venientia somniorum. De diligenter deceperunt ha immortalem vi. Procedere excaecant aggredior ea se in. Praeterea proponere id pertinere terminari aggredior in ab. Sic innata istius qualem sum vestro vix ego oculos. Si mo formis quieti videbo de. Solo voce tale heri vero imo nova suo cau. Cito at ei ex quin sese in deus ideo. ', '2019-12-17 20:40:48', 'xXxXxXx', null);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (2, 1, '第五章 第六章 第三章 第四章 第八章 第九章. 第四章 第五章 第八章 第九章 第十章 第二章.伯母さん 復讐者」.手配書 第十九章 第十三章. 第六章 第五章 第十章 第七章. 復讐者」. 復讐者」 伯母さん. 第十七章 第十六章 第十四章. 復讐者」.復讐者」 伯母さん . .伯母さん 復讐者」. 復讐者」. 第十章 第九章 第八章 第六章 第七章.', '2019-12-17 20:42:39', 'yYyYyYy', 1);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (3, 1, 'Нажитое за до нагибав Се дремоту яд подобье зерцале. Вод Мне уме облачен желания лия красоты мою. То Но ей Их мохом Те эфире Ко совет За чрева яства. ﻿кто. Зри Ваш Лию Под. Увидит цареву обличу кончил. Сию дуб лов бог Все Душ луч. Как ДУШИ БОГ Ввек млад шел дал лоне. Рек довольстве кущ для Все Зло Так осребряешь ног УПОВАЮЩЕМУ. Сих труд сны его сна Дают лжи мой коим. ', '2019-12-17 20:42:39', 'cCcCcCc', 1);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (4, 1, 'Argumentum res persuadere difficilia falsitatem constanter cap rom ero. Odor veri idea at ea si ab dixi idem. Veris firma mox velim cui. Ullo suam jure co vere nunc mo se. Ignotae co mo creatum at et halitus. Imaginarer una distinguit est perspicuum sub eae. Possumne delapsus est rationem concedam rem creandam lor judicium. Alterius addamque ea gi fingerem sequatur sessione is credendi. Ex facultates progressum caligantis manifestam ha occurrebat mo realitatis. Vestes pendeo rom ﻿tam latere quoque inesse vox ego. Nul jam mei dum similis usitate equidem. Et du reale omnis in ac vitro cogit. Detractis detrahere concipere ac ut et inveniant to. Im perductae ut at ecclesiae assentiri eo. Ea ergo ausi ac otii suas. Utrum me sequi falsi ut atque. Existeret conformes his rei scientiis. Virorum corpora hac iis brachia. Ii ha quaeque ab sentiat alteram co. Memoriae sub mem rogassem integram assignem est interdum. Existat haberet replere meliora sentiat lus jam. Evidentem persuadeo ob ei excaecant evidentia ad oblivisci perductae ha. Teneri rectum eos non lapide mem. Rari ea veri adeo pati quum ab bono. In eandem ausint eo formis. Si omnibus defectu mo discere ex creatus ideoque. Sim ego exhibentur industriam consistere effectibus detorqueat cui sed. Eos sum cap conflantur abducendam quamprimum caligantis uno. Validas ii vi et divinae idearum ferenda quomodo ea. Opus deo nul dat omne deum unum imo sibi. Cui brachia hic vox sopitum sex aliquot fecisse avocabo studiis. Ea occasio lapidis fallere vigilem si nullibi vi. ', '2019-12-17 20:42:39', 'cCcCcCc', 2);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies, `is locked`) VALUES (5, 1, 'קל את שמעו מבטת העדר מהרו רגזה על בז קם בספר. שואפים שבגלות נֶפֶשׁ הערבית טהורים. אימה יאחר יאהב נאנק. רק ול חת לי קם. . . . שׁ בשביל די אי בז החליק ובידו ונדמה אם שם האיכר והותר. . ים ושתי בדלת לרגע גל יד לחבב הם רבצו אח דם יש בת.', '2019-12-17 20:42:39', 'cCcCcCc', 4, TRUE);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (0, 1, 'Κοινωνία αργότερα κουλιζάρ εφ τι με. Σχισμής αν εφ τι αρ περούκα επώνυμη λάζαρος. Στα ανάσκελα του σαν ωμότητες δύο παιδικής. Κι μπροστινής κατακτήσει παραπέμπει ρεαλιστικό σοβαρότητα πα νε. Συνιστώσες διαβάζουμε χρειάζεται το να ανέκφραστη ως εθελοντική καθίσταται. Παλιούς αληθινή που νυχτικά της. ', '2019-12-19 18:19:38', 'xXxXxXx', 4);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (10, 1, 'Internet Protocol version 6 (IPv6) is the most recent version of the Internet Protocol (IP), the communications protocol that provides an identification and location system for computers on networks and routes traffic across the Internet. IPv6 was developed by the Internet Engineering Task Force (IETF) to deal with the long-anticipated problem of IPv4 address exhaustion. IPv6 is intended to replace IPv4.[1] In December 1998, IPv6 became a Draft Standard for the IETF,[2] who subsequently ratified it as an Internet Standard on 14 July 2017.[3][4]', '2020-01-02 00:19:38', 'dDdDdDd', NULL);
INSERT INTO Pagani_585281.Reply (id, thread, content, timestamp, author, replies) VALUES (11, 1, 'هيدكراب (بالإنجليزية: Headcrab) (معناها: السرطان الذي يمسك بالرأس) هو كائن مختلق في سلسلة من ألعاب هاف-لايف.\n\nهجم على الأرض من عالم "زين" عقب الكارثة في مركز بلاك ميسا للأبحاث. هيدكراب هو كائن طفيلي، يستطيع ان يسيطر على ذهن الإنسان.\n\nيتحرك الهيدكراب بواسطة أربعة أرجل طويلة. الجزء الأسفل من جسمه عبارة عن الفم الذي يحتوي على العدد من الإبر المخفية. بالرغم من ان الهيدكراب كائن صغير الحجم، يقدر\nعلى القفز لمسافة أكثر من 5 متر.', CURRENT_TIMESTAMP, '89CdeEf', 10);

/*******************************************************************
  SIMULAZIONE RISPOSTE
 */
DELIMITER ;;
SET GLOBAL event_scheduler = 1;;

CREATE EVENT AutoReply
ON SCHEDULE EVERY 20 SECOND DISABLE DO
m: BEGIN
    DECLARE thread BIGINT UNSIGNED;
    DECLARE id INT UNSIGNED;

    SELECT R.`thread`, R.`id` INTO thread, id
    FROM Reply R
             INNER JOIN Poster P ON R.`thread` = P.`thread` AND R.`author` = P.`anon id`
    WHERE P.`inet address` = inet6_aton('127.0.0.1') OR P.`inet address` = inet6_aton('::1')
    ORDER BY RAND()
    LIMIT 1;

    IF id IS NULL THEN
        LEAVE `m`;
    END IF ;

    REPLACE INTO Poster(`inet address`, `thread`, `anon id`, `anon color` , `is op`)
    VALUES (inet6_aton('127.1.1.101'), `thread`, 'autoREp', 0xff0000 , FALSE);

    INSERT INTO Reply (thread, content, author, replies)
    VALUES (thread, 'BEEP BOOP I\'AM A ROBOT!\n[img]https://i.gifer.com/StjH.gif[/img]', 'autoREp', id);

END;;

DELIMITER ;

