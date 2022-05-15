/**
 *
 * @constructor
 */
function Thread()
{
    CardTemplate.call(this, Thread.template);
}

Thread.template = undefined;

Thread.prototype = Object.create(CardTemplate.prototype);
Thread.prototype.constructor = Thread;

/**
 * Imposta l'ID del thread
 * @param id {String|Number}
 */
Thread.prototype.setId = function(id)
{
    // Imposto l'identificativo dell'elemento
    this.dom.id = "thread_" + id;

    // Imposto l'identifcatore per il pulsante
    this.dom.dataset.threadid = id;

    var param = new URLSearchParams({id: id}).toString();

    // Modifico l'ancora
    this.cardFooter.children[1].href = "../thread/index.php?" + param + "#replies-master";
    this.cardFooter.children[0].href = "../thread/index.php?" + param;
};

/**
 * Imposta il titolo del thread
 * @param title {String}
 */
Thread.prototype.setTitle = function (title)
{
    this.cardHeader.children[0].textContent = title;
};

Thread.prototype.setCommentNumber = function (number) {
    this.cardFooter.getElementsByClassName("comment-counter").item(0).textContent = number;
};

/**
 * Imposta l'anteprima del thread
 * @param text {String}
 */
Thread.prototype.setContent = function (text)
{
    this.cardContent.children[0].textContent = text;
};

/**
 * Imposta se il commento è fissato in alto
 * @param pinned {boolean}
 */
Thread.prototype.pinned = function (pinned) {
    this.dom.classList.toggle("pinned-card", pinned);
    this.cardHeader.children[1].children[1].hidden = !pinned;
};

/**
 * Imposta se il commento risulta bloccato o meno sul server
 * @param lock {boolean}
 */
Thread.prototype.lock = function (lock) {
    this.cardHeader.children[1].children[2].hidden = !lock;
};

/**
 * Imposta l'ora di pubblicazione
 * @param time {Date}
 */
Thread.prototype.setPublishTime = function (time) {
    this.cardHeader.children[1].children[0].children[0].dateTime = time.toISOString();
    this.cardHeader.children[1].children[0].children[0].title = time.toUTCString();
    this.cardHeader.children[1].children[0].children[0].textContent = time.toLocaleString(DEFAULT_TIME_LOCALE, DEFAULT_TIME_FORMAT);
};