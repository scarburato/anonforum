/***********************************************************************
 GESTIONE PULSANTE COMPONI
 ********************************************************************/

/**
 * Qua risiede la logica per la composizione di una risposta ad un commento.
 */
var composeWindow = new (function () {
    this.setResposeId = function (responseId) {
        this.replies.setAttribute("value", responseId);
    };

    /**
     * Metodo che è registrato su ogni pulsate rispondi di ogni commento sotto al thread, oltre che al
     * thread stesso. Quando viene chiamato sposta la finestra nella posizione del pulsante che lo ha invocato
     * e prepara il campo responseId in accordo all'ID del commento a cui si vuole rispondere ovvero NULL se si
     * vuole rispondere nella radice del thread.
     * @param button {HTMLButtonElement}
     */
    this.onclick = function (button) {
        var h;
        if(button.dataset.messageid !== undefined)
            h = button.dataset.messageid;
        else
            h = gotoParentArticle(button).dataset.messageid;

        this.setResposeId(h);

        var pos = button.getBoundingClientRect();

        this.window.move(pos.x, pos.y);
        this.window.open();
    };

    // La finestra
    this.window = new Window("compose-window", "compose-window-headbar");

    this.window.onopen = function () {
        // Ripristino la textarea a \0
        this.content.value = "";
    }.bind(this);

    /** @type {HTMLFormElement} */
    this.form = document.getElementById("compose-window-form");

    this.thread = this.form.elements[0];
    this.replies = this.form.elements[1];
    this.content = document.getElementById("compose_textarea");

    this.loading = document.getElementById("compose-window-loading");

    // Disattivo la modifica manuale di replies
    this.replies.readOnly = true;

    /** Gestione della form dinamica con AJAX
     * @param ev {Event} */
    this.form.onsubmit = function (ev) {
        ev.preventDefault();

        // Nascondo la form e mostro il wait....
        this.loading.hidden = false;
        this.form.hidden = true;

        var dati = new FormData(this.form);
        ajaxManager.post("reply.php", {}, dati,
            function (status, data)
            {
                commentManager.switchRoot(data.replies, data.insertId);
            }.bind(this),
            function (hasThrow)
            {
                if(hasThrow)
                    alert("Something went wrong :(. Check your JS console");

                // 0k, Ora posso chiudere la finestra e nascondere il loading
                this.loading.hidden = true;
                this.form.hidden = false;
                this.window.close();
            }.bind(this));
    }.bind(this);

    // Inizializzazione
    this.window.close();

    // Registrazione su tutti i pulsanti
    document.getElementById("compose-window-close").onclick = function (ev) {
        // Se premo col primario allora esco
        if (ev.button === 0)
            this.close();
    }.bind(this.window);
})();