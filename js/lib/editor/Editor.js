/**
 * Classe per gestire una textarea con BBCode
 * @param textarea {String} L'id dell'elemento textarea
 * @param controls {String|undefined} L'id della DIV che contiene i pulsanti che comandano l'editor
 * @constructor
 */
function Editor(textarea, controls)
{
    /**
     * @type {HTMLTextAreaElement|null}
     */
    this.textarea = document.getElementById(textarea);
    this.controls = document.getElementById(controls);

    if(this.textarea === null || !(this.textarea instanceof HTMLTextAreaElement))
        throw new Error("No element with id \"" + textarea + "\"!");

    var buttons = this.controls.getElementsByClassName("editor_control");

    for(var i = 0; i < buttons.length; i++)
        buttons[i].onclick = this.buttonCallback.bind(this);
}

/**
 * Funzione che viene chiamata alla pressione di un pulsante nella barra
 * dei controlli dell'editore di testo
 * @param ev {MouseEvent}
 */
Editor.prototype.buttonCallback = function (ev) {
    this.applyStyle(ev.target.dataset.action);
};

Editor.prototype.clear = function () {
    this.textarea.value = ""
};

/**
 * Inserisce il bbcode richiesto
 * @param mode {String}
 */
Editor.prototype.applyStyle = function (mode) {
    var oldLenght = this.textarea.value.length;

    // Ottengo la posizione del cursore (o della selezione)
    var left  = this.textarea.selectionStart;
    var right = this.textarea.selectionEnd;

    // Creo il tag (o lo applico alla selezione)
    this.textarea.value =
        this.textarea.value.slice(0, right) +
        "[/" + Editor.BBCODES[mode].close + "]" +
        this.textarea.value.slice(right);
    this.textarea.value =
        this.textarea.value.slice(0, left) +
        "[" + Editor.BBCODES[mode].open + "]" +
        ((Editor.BBCODES[mode].default !== undefined && left === right) ? Editor.BBCODES[mode].default : "") +
        this.textarea.value.slice(left);

    // Sposto il cursore dentro
    this.textarea.selectionStart = right + Editor.BBCODES[mode].open.length + 2;
    this.textarea.selectionEnd = this.textarea.selectionStart;

    // Dò il focus
    this.textarea.focus();
};

Editor.BBCODES = {
    "bold": {open: "b", close: "b"},
    "italic": {open: "i", close: "i"},
    "underline": {open: "u", close: "u"},
    "font-size": {open: "size=12", close: "size"},
    "url": {open: "url=http://example.org", close: "url"},
    "picture": {open: "img", close: "img", default: "http://example.org/favicon.ico"}
};