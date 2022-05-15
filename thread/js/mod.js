/**
 * Gestione degli strumenti di moderzione
 */
var modWindow = new (function () {
    // Se non trovo la finestra probabilmente non è un admin. Esco...
    if(document.getElementById("mod-window") === null)
        return;

    this.window = new Window("mod-window", "mod-window-headbar");

    /** @type {HTMLFormElement} */
    this.form = document.getElementById("mod-window-form");
    this.inputId = this.form.elements[1];
    this.lockMode = this.form.elements[2];

    // La uso per conferma rimozione
    this.alertUser = false;

    /**
     * Cosa devo moderare?
     * @param button {HTMLButtonElement}
     */
    this.moderateComment = function (button) {
        this.inputId.value = gotoParentArticle(button).dataset.messageid;
        this.lockMode.value = "";

        this.window.open();
    };

    /**
     * Funzione da registrare alla validazione della form per moderare
     * un commento
     * @param ev {FocusEvent}
     */
    this.validateForm = function (ev) {
        if(this.alertUser)
        {
            this.alertUser = false;
            return confirm("Do you really want to delete this comment and all his children?\nThe operation may be irreversible");
        }

        return true;
    }.bind(this);

    this.form.onsubmit = this.validateForm;

    // Registrazione su tutti i pulsanti
    document.getElementById("mod-window-close").onclick = function (ev) {
        // Se premo col primario allora esco
        if (ev.button === 0)
            this.close();
    }.bind(this.window);

    // Init
    this.window.close();
    this.window.move(0,0);
})();

var modThreadWindow = new (function () {
    // Se non trovo la finestra probabilmente non è un admin. Esco...
    if(document.getElementById("mod-thread-window") === null)
        return;

    this.window = new Window("mod-thread-window", "mod-thread-window-headbar");

    /** @type {HTMLFormElement} */
    this.form = document.getElementById("mod-thread-window-form");
    this.inputId = this.form.elements[1];
    this.lockMode = this.form.elements[2];

    // La uso per conferma rimozione
    this.alertUser = false;

    /**
     * Cosa devo moderare?
     * @param button {HTMLButtonElement}
     */
    this.moderateComment = function (button) {
        this.inputId.value = gotoParentArticle(button).dataset.messageid;
        this.lockMode.value = "";

        this.window.open();
    };

    /**
     * Funzione da registrare alla validazione della form per moderare
     * un commento
     * @param ev {FocusEvent}
     */
    this.validateForm = function (ev) {
        if(this.alertUser)
        {
            this.alertUser = false;
            return confirm("Do you really want to delete this comment and all his children?\nThe operation may be irreversible");
        }

        return true;
    }.bind(this);

    this.form.onsubmit = this.validateForm;

    // Registrazione su tutti i pulsanti
    document.getElementById("mod-thread-window-close").onclick = function (ev) {
        // Se premo col primario allora esco
        if (ev.button === 0)
            this.close();
    }.bind(this.window);

    // Init
    this.window.close();
    this.window.move(0,0);
})();

var modUserWindow = new (function () {
    // Se non trovo la finestra probabilmente non è un admin. Esco...
    if(document.getElementById("mod-user-window") === null)
        return;

    this.window = new Window("mod-user-window", "mod-user-window-headbar");

    /** @type {HTMLFormElement} */
    this.form = document.getElementById("mod-user-window-form");
    this.inputId = this.form.elements[1];
    this.inputAddress = document.getElementById("ip_user_sel");

    /**
     * Cosa devo moderare?
     * @param button {HTMLButtonElement}
     */
    this.moderateUser = function (button) {
        this.inputId.value = gotoParentArticle(button).dataset.author;

        if(this.inputAddress !== null)
        {
            this.inputAddress.value = "Fetching....";
            ajaxManager.get("mod_tools/get_user_address.php", {
                id: this.inputId.value,
                thread: new URLSearchParams(window.location.search).get("id")
            }, function (status, response) {
                if(status !== 200)
                    return;
                this.inputAddress.value = response.address;
            }.bind(this))
        }

        this.window.open();
    };

    // Registrazione su tutti i pulsanti
    document.getElementById("mod-user-window-close").onclick = function (ev) {
        // Se premo col primario allora esco
        if (ev.button === 0)
            this.close();
    }.bind(this.window);

    // Init
    this.window.close();
    this.window.move(0,0);
})();