/**
 * Classe da usare in caso di errore AJAX
 * @param httpState
 * @param errno
 * @param errwhat
 * @param raw_payload
 * @constructor
 */
function AjaxError(httpState, errno, errwhat, raw_payload)
{
    this.name = 'AjaxError';
    this.raw_payload = raw_payload;

    this.message = "Server responded with http state = " + httpState + ".";
    if(errno !== undefined)
        this.message += "\nThe serverside scripts have generated an error!\nerrno = " + errno +
                        "\nwhat =\"" + errwhat + "\".";
}
AjaxError.prototype = new Error;

/**
 * Funzioni di utilità per la gestione di richieste asincrone al servente
 */
var ajaxManager = {
    /**
     * Creo una query string
     * @param url {String}      Un url base come ..\index.php
     * @param query {Object}    Un oggetto rappresentate i parametri da passare
     * @returns String
     */
    buildQuery: function(url, query)
    {
        if(!(query instanceof Object))
            query = {};
        else
        {
            // Copio l'oggetto
            query = Object.assign(query);

            // Non invio i campi undefined
            for (var propName in query)
                if (query[propName] === undefined)
                    delete query[propName];
        }

        // Creazione della query e la concateno all'URL
        var queryString = new URLSearchParams(query).toString();
        if(queryString.length > 0)
            url += '?' + queryString;

        return url;
    },

    /**
     * Apre una XMLHttpRequest e torna indietro l'oggetto
     * @param url {String}
     * @param query {Object}
     * @param handler {function(status: Number, response: Object)}
     * @param always {function}
     * @param method {String} Può essere POST o GET
     * @return {XMLHttpRequest}
     *
     * @private
     */
    openRequest: function (url, query, handler, always, method)
    {
        var request = new XMLHttpRequest();

        // Preparazione della richiesta
        request.open(method, ajaxManager.buildQuery(url, query));

        // Bind dell'handler
        request.onreadystatechange = function () {
            if(this.readyState !== XMLHttpRequest.DONE)
                return;

            var has_throw = true;
            try
            {
                // Conversione della risposta in JSON
                var response = JSON.parse(this.response);

                // Ho avuto errori ?
                if (this.status !== 200 || (response.error !== undefined && response.error !== 0))
                    throw new AjaxError(this.status, response.error, response.what, this.response);
                else
                    handler(this.status, response);
                has_throw = false;
            }
            finally
            {
                if(typeof always === "function") always(has_throw);
            }
        };

        request.onerror = function (ev) {
            // TODO
        };

        return request;
    },

    /**
     * Effettua una richiesta GET
     * @param url {String} L'indirizzo a cui inviare la richiesta
     * @param query {Object} Un oggetto che verrà usato per creare la query string
     * @param handler {function(status: Number, response: Object | null)} Una funzione da eseguire al successo
     * @param always {function} Una funzione da eseguire sempre; ammesso che il server riesca ad sodisfarre la richiesta, cioè ad inviare un documento di sorta indietro e mandare lo stato di XMLHttpRequest a DONE
     */
    get: function(url, query, handler, always)
    {
        var request = this.openRequest(url, query, handler, always,"GET");
        // Via!
        request.send();
    },

    /**
     * Effettua una richiesta POST
     * @param url {String}
     * @param query {Object}
     * @param handler {function(status: Number, response: Object | null)}
     * @param data {Document}
     * @param always {function} Una funzione da eseguire sempre
     */
    post: function (url, query,data, handler, always) {
        var request = this.openRequest(url, query, handler, always, "POST");

        // Via! Invia anche i dati
        request.send(data);
    }
};