/**
 * Partendo da un elemento html risale i suoi genitori fino ad arrivare
 * al primo elemento article. Se arriva in cima senza incontrarne uno
 * torna null.
 * @param element {HTMLElement}
 * @return {HTMLElement|null}
 */
function gotoParentArticle(element) {
    // Risalgo i genitori fino ad arrivare all'article
    while (element.tagName !== null && element.tagName !== "ARTICLE")
        element = element.parentElement;

    return element;
}