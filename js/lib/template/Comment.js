/**
 * Questa classe gesitisce un albero di Comment
 * @constructor
 */
function CommentNode(id) {
    /** @type {CommentNode[]} */
    this.replies = [];

    this.id = id;
}

/**
 * @param id
 */
CommentNode.prototype.setId = function (id)
{
    this.id = id;
};

/**
 * Aggiunge un nodo
 * @param node {CommentNode}
 */
CommentNode.prototype.addReply = function(node)
{
    this.replies.push(node);
};

/**
 * Trova un riposta partendo da un ID
 * @param id
 * @return {CommentNode|null}
 */
CommentNode.prototype.findReply = function (id)
{
    if(id === this.id)
        return this;

    for(var i = 0; i < this.replies.length; i++)
    {
        var res = this.replies[i].findReply(id);
        if(res !== null) return res;
    }

    return null;
};

/**
 * Questa classe si occupa di aiutare alla costruzione di un commento e le
 * sue risposte partendo da un templete messo in calce al documento HTML
 * che contiene i commenti
 * @constructor
 * @extends {CardTemplate, CommentNode}
 */
function Comment() {
    CardTemplate.call(this, Comment.template);
    CommentNode.call(this);

    /** @type {HTMLAnchorElement} **/
    this.commentURLElement = this.cardHeader.querySelector(".reply-id");

    /** @type {HTMLButtonElement}*/
    this.replyButton = this.cardFooter.children[0];

    /** @type {HTMLDivElement} */
    this.comments = this.dom.querySelector(".comments");

    /** @type {HTMLDivElement} L'ancora per mostrare i figli in eccesso **/
    this.moreRepliesDiv = this.dom.querySelector(".more");
}

/**
 * Contiene l'elemento DOM da usare come prototipo del commento
 * @type {undefined|HTMLTemplateElement}
 */
Comment.template = undefined;

Comment.LOCK_BUTTON_TITLE = "This comment is locked, you can't reply anymore";
Comment.CURRENT_THREAD_ID = (new URLSearchParams(window.location.search)).get("id");
Comment.THREAD_URL = window.location.pathname;

// Estensione
Comment.prototype = Object.create(CardTemplate.prototype);
Comment.prototype.constructor = Comment;

/**
 * Imposta il corpo della risposta
 * @param text {String}
 * @param parseBBCode {boolean} Se devo tradduree il bbcodes
 */
Comment.prototype.setContent = function(text, parseBBCode) {
    // Pulisco la card
    while(this.cardContent.firstChild)
        this.cardContent.removeChild(this.cardContent.firstChild);

    // Devo tradurre i bbcodes ?
    if(parseBBCode)
    {
        var bbcode = XBBCODE.process({
            text: text,
            removeMisalignedTags: false,
            addInLineBreaks: false
        });
        this.cardContent.innerHTML = bbcode.html;
    }
    else
        this.cardContent.textContent = text;
};

/**
 * Imposta l'ID della reply
 * @param id {String|Number}
 */
Comment.prototype.setId = function (id) {
    CommentNode.prototype.setId.call(this, id);

    // Imposto l'identificativo dell'elemento
    this.dom.id = "comment_" + id;

    // Imposto l'identifcatore per il pulsante
    this.dom.dataset.messageid = id;

    // Imposto l'identifciatrore nella testata
    this.commentURLElement.children[1].textContent = id;

    // Creo l'url e aggiungo l'url all'ancora
    var urlQuery = new URLSearchParams();
    urlQuery.set("id", Comment.CURRENT_THREAD_ID);
    urlQuery.set("root", id);

    this.commentURLElement.href = Comment.THREAD_URL + '?' + urlQuery.toString() + '#replies-master';
    this.moreRepliesDiv.children[0].href = this.commentURLElement.href;
};

/**
 * Imposta il nome dell'autore e il suo colore
 * @param id {String}
 * @param is_op {boolean}
 * @param is_you {boolean}
 * @param color {String}
 */
Comment.prototype.setAuthor = function(id, is_op , is_you, color) {
    // dataset
    this.dom.dataset.author = id;

    // Contentu div autore
    this.cardHeader.children[0].children[0].textContent = id;
    this.cardHeader.children[0].children[1].hidden = !is_op;

    // Colore div autore
    this.cardHeader.children[0].style.backgroundColor = color;

    // Sei te?
    this.cardHeader.children[1].children[2].hidden = !is_you;
};

/**
 * Imposta l'ora di pubblicazione
 * @param time {Date}
 */
Comment.prototype.setPublishTime = function (time) {
    this.cardHeader.children[1].children[0].children[0].dateTime = time.toISOString();
    this.cardHeader.children[1].children[0].children[0].title = time.toUTCString();
    this.cardHeader.children[1].children[0].children[0].textContent = time.toLocaleString(DEFAULT_TIME_LOCALE, DEFAULT_TIME_FORMAT);
};

/**
 * Imposta se il commento risulta bloccato o meno sul server
 * @param lock {boolean}
 */
Comment.prototype.lock = function (lock) {
    // Imposto il pulsante
    this.replyButton.disabled = lock;
    this.replyButton.title = lock ? Comment.LOCK_BUTTON_TITLE : "";

    // Faccio apparire il lucchetto ?
    this.cardHeader.children[1].children[3].hidden = !lock;
    this.cardHeader.children[1].children[3].title = lock ? Comment.LOCK_BUTTON_TITLE : "";
};

/**
 * Imposta se l'autore del commento è stato bandito
 * @param banned {boolean}
 */
Comment.prototype.authorBanned = function (banned) {
    this.cardHeader.children[1].children[4].hidden = !banned;
};

/**
 * Fa apparire il pulsante mostra più commenti
 * @param show {boolean}
 */
Comment.prototype.moreReplies = function (show) {
    this.moreRepliesDiv.hidden = !show;
};

/**
 * Appende nella sezione delle risposte una nuova rispsota
 * @param comment {Comment}
 */
Comment.prototype.addReply = function (comment) {
    CommentNode.prototype.addReply.call(this, comment);
    this.comments.appendChild(comment.dom);
};

Comment.prototype.findReply = CommentNode.prototype.findReply;