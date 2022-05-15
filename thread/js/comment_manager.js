Comment.template = document.getElementById("reply-prototype");

/**
 * Gestore della radice del thread.
 * @param root {HTMLDivElement}
 * @constructor
 * @extends {CommentNode}
 */
function CommentRoot (root) {
    CommentNode.call(this);
    this.root = root !== undefined ? root : document.getElementById("replies-master");

    // Pulisco la radice
    while(this.root.firstChild)
        this.root.removeChild(this.root.firstChild);
}

CommentRoot.prototype = new CommentNode;
CommentRoot.prototype.constructor = CommentRoot;

/**
 * Inserisce un elemento nella root
 * @param comment {Comment}
 */
CommentRoot.prototype.addReply = function (comment) {
    CommentNode.prototype.addReply.call(this, comment);
    this.root.appendChild(comment.getDomReference());
};

var banned = document.getElementById("user_locked") !== null;
var threadLocked = document.getElementById("thread_locked") !== null;

/**
 * Qua risiede la logica per la gestione dei commenti sotto un thread.
 * All'avvio scarico una prima volta i commenti dal servente e li stampo nel DOM.
 *
 * Inoltre attivo anche i listener(s) per scaricare nuovi commenti a determinati eventi:
 *  - Decisione dell'utente di cambiare radice
 *  - Il navigatore si sposta avanti e indietro tra le radici senza abbandonare la pagina
 *  - L'utente ha inviato con successo un nuovo commento
 *  - Il servente riporta una nuova versione dei commenti
 */
var commentManager = new (function () {
    this.query = new URLSearchParams(window.location.search);
    /** @type {CommentRoot} */
    this.rootComments = new CommentRoot();

    /**
     * Partendo da un Oggetto che rappresenta un commento scaricato dal servente ne creo un elemento DOM partendo
     * dal modello dichiarato nell'oggetto Comment. Itero anche per i suoi figli
     * @param raw_comment {Object}
     * @returns {Comment}
     * @private
     */
    this.buildComments = function (raw_comment) {
        var comment = new Comment();

        // Dati del commento
        comment.setId(raw_comment["comment id"]);
        comment.setAuthor(raw_comment["author"], raw_comment["is op"], raw_comment["is you"], raw_comment["color hex"]);
        comment.setPublishTime(new Date(raw_comment["timestamp"] + 'Z'));
        comment.lock(/*threadLocked ||*/ banned || raw_comment["is locked"]);
        comment.authorBanned(raw_comment["banned"]);

        // Contentuto del commento
        comment.setContent(raw_comment["content"], true);

        // Se ha figli allora itero
        if (raw_comment.sons instanceof Array) {
            raw_comment.sons.forEach(function (son) {
                comment.addReply(this.buildComments(son));
            }.bind(this));

            //debugger;

            // Se ci sono figli nascosti mostro il pulsante
            if(raw_comment.sons.length !== raw_comment["number of childs"])
                comment.moreReplies(true);
        }

        return comment;
    };

    /**
     * Questa funzione aggiorna il contentuto dei commenti partendo da una root.
     * Scarica i commenti dal servente, li mette nel DOM grazie alla buildComments, e li scrive nel documento.
     * @param root {String|null} Che commento usare come radice. Usare NULL per passare nella radice principale
     * @param onupdateend {function (Object)} Funziona da chiamare alla fine
     */
    this.updateRootContent = function (root, onupdateend) {
        // Pulisco la radice
        this.rootComments = new CommentRoot();

        // Se sono sono nella radice principe lo imposto
        this.rootComments.setId(root === null ? null : parseInt(root));

        // Interrogo il servente, che mi dia i commenti
        ajaxManager.get("fetch_replies.php", {
            thread: this.query.get("id"),
            root: root
        }, function (status, comments) {
            // Per ogni commento nella root avvio la procedura per creare i commenti
            comments.forEach(function (raw_comment) {
                this.rootComments.addReply(this.buildComments(raw_comment));
            }.bind(this));

            // Se non c'è nulla da mostrare
            document.getElementById("no_comments").hidden = comments.length !== 0;

            // 0k. Chiamo l'handler
            if(onupdateend !== undefined)
                onupdateend(this);
        }.bind(this));

        // I pulsanti in cima: se sono nella root principale allora appare il pulsante per rispondere al Thread
        // altrimenti appare il "pulsante" per tornare alla root principale
        document.getElementById("replies-ansew").hidden = root !== null;
        document.getElementById("replies-orignal-root-button").hidden = root === null;

        this.rootComments.root.scrollIntoView(true);
    };

    /**
    * Funzione che viene chiamata quando si preme col tasto sull'URL per cambiare root
    * Se non viene passato un parametro usa come nuova root il primo commento che trova
    * salendo dall'elemento e.target ad arrivare in cima. Non dovrebbe mai arrivare fino in
    * cima a document
    * @param root {String}
    * @param commentFocus {String} L'ID del commento da mettere a fuoco
    */
    this.switchRoot = function (root, commentFocus)
    {
        // Aggiorno la query e la spingo nella cronologia
        if(root === null)
            this.query.delete("root");
        else
            this.query.set("root", root);

        // Aggiungo un elemento alla cronologia del navigatore
        window.history.pushState({}, "", window.location.pathname + '?' + this.query.toString() + '#replies-master');

        //e chiamo la funzione per aggiornare la pagina
        this.updateRootContent(root, function () {
            if(commentFocus !== undefined)
                this.rootComments.findReply(commentFocus).getDomReference().scrollIntoView();
        }.bind(this));
    };

    /**
     * @param e {Event}
     * @param root {String}
     * @param commentFocus {String} L'ID del commento da mettere a fuoco
     */
    this.switchRootLink = function (e, root, commentFocus) {
        // Se non è il pulsante primario esco
        if (e.button !== 0)
            return;

        // Ottengo l'ID.
        if(root === undefined)
            root = gotoParentArticle(e.target).dataset.messageid;

        e.preventDefault();
        this.switchRoot(root, commentFocus)
    };

    /***********************************************************
     *             INIT INT INIT INIT INIT INIT
     ***********************************************************/

    // Chiamata iniziale al caricamento della pagina
    this.updateRootContent(this.query.get("root"));

    /** Questo evento permette di andare avanti ovvero indietro coi pulsanti del navigatore tra le varie root
     * senza che la pagina venga ricarita da zero. Ovviamente se si entra in un altra pagina WEB questo evento
     * viene ignorato e il navigatore riparte da zero.
     */
    window.onpopstate = function (ev) {
        this.query = new URLSearchParams(window.location.search);
        this.updateRootContent(this.query.get("root") );
    }.bind(this);

    if(stream === undefined)
        return;

    /** Questo evento permette di appendere dinamicamente nuovi commenti sotto un figlio
     */
    this.ding = new Audio(WebRoot + "/asset/morse.wav");
    stream.addEventListener("comment", function (evt) {
        var raw_comment = JSON.parse(evt.data);

        /**
         * Il commento ricevuto, di quale altro commento è figlio?
         * Un valore nullo indica che è nella radice altrimenti cerco il riferimento al padre
         * @type {Comment, CommentRoot}
         * */
        var father = this.rootComments.findReply(raw_comment.father);

        // Se il padre non c'è è probabile che non sia nella schermata attuale...
        if(father === null)
            return;

        // Se i commenti già presenti superano COMMENTS_CARDINALITY_NESTED, desisto e mostro il pulsante MORE
        if(father instanceof Comment && father.replies.length >= 4)
            father.moreReplies(true);
        else if(father.findReply(raw_comment["comment id"]) === null)
        {
            father.addReply(this.buildComments(raw_comment));
            this.ding.play();
        }
        // TODO Scrolla se sto sotto con il video
    }.bind(this))
})();