/**
 * Classe per gestire le card HTML da un template
 * @constructor
 */
function CardTemplate(template)
{
    if(! (template instanceof HTMLTemplateElement ))
        throw new TypeError("Cannot work without a template!");

    /** Preparo una copia del nostro template
     * @type {HTMLDivElement} */
    this.dom = document.importNode(template.content, true).children[0];

    /** Se esiste rappresenta la testata della Card
     * @type {HTMLDivElement} **/
    this.cardHeader = this.dom.querySelector(".card-header");

    /** Se esiste rappresenta il contentuo della Card
     * @type {HTMLDivElement} **/
    this.cardContent = this.dom.querySelector(".card-content");

    /** Se esiste rappresenta il piè di Card
     *  @type {HTMLDivElement}*/
    this.cardFooter = this.dom.querySelector(".card-footer");
}

/**
 * Ritorna una copia della card generata finora
 * @return {HTMLElement}
 */
CardTemplate.prototype.getDomCopy = function ()
{
    return this.dom.cloneNode(true);
};

/**
 * Ritorna il Nodo interno su qui l'oggetto sta lavorando.
 * Le modifiche fatte attraverso i metodi di questo oggetto hanno
 * un effetto immediato sul Nodo ritornato da questo metodo.
 * @return {HTMLElement}
 */
CardTemplate.prototype.getDomReference = function ()
{
    return this.dom;
};
