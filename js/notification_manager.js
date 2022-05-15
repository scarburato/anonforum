var stream = new EventSource(WebRoot + "/notifications/stream.php" + (function ()
{
    var threadId = (new URLSearchParams(window.location.search)).get("id");

    if(threadId !== null)
        return "?watch=" + parseInt(threadId);
    else
        return "";
})());


//var NOTIFICATIONS = (function ()
//{
//    var self = {};
//
//    /**
//     * Tutti le notifiche mandate giù dal servente in attesa di essere lette
//     * @type {Object[]} */
//    self.pending = [];
//
//    /**
//     * Suono da riprodurre alla notifica
//     * @type {HTMLAudioElement}
//     */
//    self.audio = new Audio(WebRoot + "/asset/horn.wav");
//
//    /** @type {HTMLButtonElement} */
//    self.button = undefined;
//
//    /**
//     * La finestra POP-OUT
//     * @type {WindowProxy|null}*/
//    self.window = null;
//    self.windowOptions = "width=300,menubar=no,scrollbars=yes,status=no,location=no";
//    self.windowURL = WebRoot + "notifications/index.html";
//
//    /**
//     * Aggiunge una notifica alla finestra che si suppone aperta
////     * @param notification {Object}
//     */
//    self.addNotification = function(notification) {
//        var template = self.window.document.getElementById("notTemplate");
//        var table = self.window.document.getElementById("notification");
//
//        table.append(n.getDomReference());
//    };
//
//    // Al carimaneto del documento registro il pulsante
//    window.addEventListener("load", function (ev) {
//        self.button = document.getElementById("notificationButton");
//
//        // Alla pressione del pulsante
//        self.button.onclick = function (ev1) {
//            self.button.classList.remove("ding");
//
//            // La finestra è già presente
//            if(self.window !== null && !self.window.closed)
//                return;
//
//            // Apertura della finestra
//            self.window = window.open(self.windowURL, "", self.windowOptions);
//            if(self.window === null)
//            {
//                alert("Allow for popOut windows!");
//                return;
//            }
//
//            self.pending.forEach(function (value) {
//                self.addNotification(value);
//            });
//            self.pending = [];
//        }
//    });
//
//    // Registrazione dell'evento di nuova notifica
//    stream.addEventListener("notification", function (evt) {
//        var notification = JSON.parse(evt.data);
//        if(self.window !== null && !self.window.closed)
//        {
//            self.addNotification(notification)
//        }
//        else
//            self.pending.push(notification);
//
//        self.button.classList.add("ding");
//        self.audio.play();
//    });
//
//    return self;
//})();