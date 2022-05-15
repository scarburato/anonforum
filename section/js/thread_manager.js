/**scaricato
 * Gestione dello scaricamento dei thread.
 * Una richiesta un nuovo thread viene creata quando l'utente arriva a fine pagina
 */
var threadLoader = new (function ()
{
    this.threadBox = document.getElementById("page_content");
    this.keepLoading = document.getElementById("keep-loading");
    this.nomoreLoading = document.getElementById("stop-loading");
    this.currentSection = (new URLSearchParams(window.location.search)).get("name");

    /** Vale true se e solo se una richiesta è già pedente sul servente
     * @type {boolean}*/
    this.waitForRequest = false;
    /** La pagina che andrò scaricare alla prossima chiamata.
     * @type {number}
     */
    this.currentPage = 0;

    /** Per evitare l'accavallmento di threads in caso di modifiche alla base di dati **/
    this.timestamp = undefined;

    /**
     * Chiude tutto e ricomncia da zero
     */
    this.restart = function ()
    {
        while(this.waitForRequest)
            continue;

        clearInterval(this.timer);

        this.currentPage = 0;
        this.timestamp = undefined;
        this.keepLoading.hidden = false;
        this.nomoreLoading.hidden = true;

        // Elimino i nodi
        while(this.threadBox.firstChild)
            this.threadBox.removeChild(this.threadBox.firstChild);


        // Via!
        this.timer = window.setInterval(this.chkndownload.bind(this),800);
    };

    /**
     * Metodo per scaricare un nuovo blocco di thread e aggiungerli alla lista
     */
    this.downloadChunk = function ()
    {
        this.waitForRequest = true;
        var self = this;

        ajaxManager.get(
            "fetch_threads.php",
            {section: self.currentSection, page: self.currentPage++, timestamp: self.timestamp},
            function (status, response) {
                if(! (response.threads instanceof Array))
                    throw new TypeError("Must be an array!");

                this.timestamp = response.timestamp;

                /** Se non ho ricevuto nessun thread allora dovrei essere arrivato
                 * alla fine della lista. Altrimenti li scorro tutti e comincio a
                 * stamparli
                 */
                if(response.threads.length === 0)
                {
                    this.keepLoading.hidden = true;
                    this.nomoreLoading.hidden = false;

                    clearInterval(this.timer);
                }
                else
                    response.threads.forEach(function (thread) {
                        // Creazione di un thead
                        var threadDOM = new Thread();

                        threadDOM.setId(thread["id"]);
                        threadDOM.pinned(thread["is pinned"]);
                        threadDOM.setPublishTime(new Date(thread["timestamp"] + 'Z'));

                        threadDOM.setTitle(thread["title"]);
                        threadDOM.setContent(thread["content preview"]);

                        threadDOM.setCommentNumber(thread["comment counter"]);

                        this.threadBox.appendChild(threadDOM.getDomReference());
                    }.bind(this));
            }.bind(this),
            function ()
            {
                this.waitForRequest = false;
            }.bind(this)
            )
    };

    /**
     * Un timeer da far girare ogni tanto. Controlla se la DIV in basso, quella con
     * scritto "LOADING MORE", è visibile sul video; se sì allora procede a scaricare un
     * blocco di thread
     */
    this.chkndownload = function ()
    {
        if(this.waitForRequest)
            return;

        position = this.keepLoading.getBoundingClientRect();

        // Se il DIV non è visibile sul video del navigatore allora esco...
        if(position.y >= window.innerHeight)
            return;

        this.downloadChunk();
    };

    this.restart();
});

// Bind sul tasto
document.getElementById("button_reload").onclick = threadLoader.restart.bind(threadLoader);