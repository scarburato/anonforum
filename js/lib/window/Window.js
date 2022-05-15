/**
 * Classe per gesitre un dive di position abosolute come se fosse una finestra
 * @param window {String}       L'identificato del DIV da usare come finestra
 * @param headbar {String, undefined} L'identificato del DIV da usare come barra del titolo.
 *                                    Se non fornito usa l'intero corpo della finestra come barra del titolo
 * @constructor
 */
function Window(window, headbar)
{
    /**
     * L'oggetto DOM che contiene la finestra da gestire
     * @private
     * @type HTMLElement */
    this.window = document.getElementById(window);

    if(this.window === null)
        throw new Error("No element with id \"" + window + "\"!");

    /**
     * L'oggetto DOM da usare come barra da spostare
     * @private
     * @type HTMLElement */
    this.headbar = (headbar === undefined) ? this.window : document.getElementById(headbar);

    if(this.headbar === null)
        throw new Error("No element with id \"" + headbar + "\"!");

    /** Funzione da chiamare quando la finestra viene chiusa
     * @type {function() | undefined}
     * @public */
    this.onclose = undefined;
    /** Funzione da chiamare quando la finestra viene apera
     * @type {function() | undefined}
     * @public*/
    this.onclose = undefined;

    /**
     * Oggetto per tracciare il movimento della finestra sul video
     * @type {{enable: boolean, deltaX: number, deltaY: number}}
     */
    this.eventMoving = {
        enable: false,

        relativeX: 0,
        relativeY: 0
    };

    // Sposto in primo piano la finestra alla pressione su di essa
    this.window.addEventListener("mousedown", this.goForeground.bind(this));

    // Attivo gli eventi alla pressione del mouse sulla barra del titolo
    this.headbar.addEventListener("mousedown", function (ev) {
        var position = this.headbar.getBoundingClientRect();

        // Memorizzo il punto di dove il cursore si è poggiato origarimaente relativamente al bordo NORD-OVEST del div
        // che funge da headbar
        this.eventMoving.relativeX = ev.clientX - position.x;
        this.eventMoving.relativeY = ev.clientY - position.y;

        this.eventMoving.enable = true;
    }.bind(this), false);

    // Disabilito l'evento all'ulsta del mouse
    document.addEventListener("mouseup", function (ev) {
        if(this.window.style.zIndex > 0)
            this.window.style.zIndex--;

        this.eventMoving.enable = false;
    }.bind(this), false);

    // Registro l'handler che viene chiamato ad ogni movimento del cursore sul video
    document.body.addEventListener("mousemove", this.listenerFollowMouse.bind(this), false);

    // Porto la finestra ad un livello rispetto alle altre
    this.window.style.zIndex = Window.CURRENT_LEVEL;
    Window.CURRENT_LEVEL ++;
}

Window.CURRENT_LEVEL = 0;

Window.prototype.goForeground = function() {
    this.window.style.zIndex = 16777271;
};

/**
 * Muove la finestra nella posizione specificata
 * @param x
 * @param y
 */
Window.prototype.move = function (x, y) {
    this.window.style.top = y.toString() + "px";
    this.window.style.left = x.toString() + "px";
};

Window.prototype.close = function () {
    if(typeof this.onclose === "function")
        this.onclose();

    this.window.hidden = true;
};

Window.prototype.open = function () {
    if(typeof  this.onopen === "function")
        this.onopen();
    this.window.hidden = false;
    this.goForeground();
};

/**
 * @private
 * @param ev {MouseEvent}
 */
Window.prototype.listenerFollowMouse = function (ev) {
    // Se non tengo premuto non faccio nulla.
    if(!this.eventMoving.enable)
        return;

    this.move(ev.clientX - this.eventMoving.relativeX, ev.clientY - this.eventMoving.relativeY);

    if(this.eventMoving.oldX !== undefined)
    {
        this.eventMoving.deltaX = ev.clientX - this.eventMoving.oldX;
        this.eventMoving.deltaY = ev.clientY - this.eventMoving.oldY;
    }

    this.eventMoving.oldX = ev.clientX;
    this.eventMoving.oldY = ev.clientY;
};