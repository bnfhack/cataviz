'use strict';

/**
 * Toolkit for some ajax hacks
 */
const Ajix = function() {
    const EOF = '\u000A';
    /**
     * Get URL and send line by line to a callback function.
     * “Line” separator could be configured with any string,
     * this allow to load multiline html chunks 
     * 
     * @param {String} url 
     * @param {function} callback 
     * @returns 
     */
    function loadLines(url, callback, sep = '\n') {
        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            var start = 0;
            xhr.onprogress = function() {
                // loop on separator
                var end;
                while ((end = xhr.response.indexOf(sep, start)) >= 0) {
                    callback(xhr.response.slice(start, end));
                    start = end + sep.length;
                }
            };
            xhr.onload = function() {
                let part = xhr.response.slice(start);
                if (part.trim()) callback(part);
                // last, send a message to callback
                callback(EOF);
                resolve();
            };
            xhr.onerror = function() {
                reject(Error('Connection failed'));
            };
            xhr.responseType = 'text';
            xhr.open('GET', url);
            xhr.send();
        });
    }

    /**
     * 
     * @param {*} url 
     * @param {*} callback 
     */
    function loadJson(url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'json';
        xhr.onload = function() {
            var status = xhr.status;
            if (status === 200) {
                callback(xhr.response, null);
            } else { // in case of error ?
                callback(xhr.response, status);
            }
        };
        xhr.send();
    }

    /**
     * Append a record to a div
     * @param {*} html 
     * @returns 
     */
    function insLine(div, html) {
        if (!div) { // what ?
            return false;
        }
        // last line, liberate div for next load
        if (html == EOF) {
            div.loading = false;
            return;
        }
        div.insertAdjacentHTML('beforeend', html);
    }

    /**
     * Send query to populate concordance
     * @param {*} id 
     * @param {*} form 
     * @param {*} url 
     * @param {*} append 
     * @returns 
     */
    function divLoad(id, form, url = null, append = false) {
        const div = document.getElementById(id);
        if (!div) { // no pb, it’s another kind of page
            return;
        }
        if (div.loading) return; // still loading
        if (!url && !div.dataset.url) {
            console.log('[Elicom] @data-url required <div id="' + id + '" data-url="data/conc">');
        }
        if (!url) url = div.dataset.url;
        if (form) url += "?" + pars(form);
        div.loading = true;
        if (!append) {
            div.innerText = '';
        }
        Ajix.loadLines(url, function(html) {
            insLine(div, html);
        }, '&#10;');
    }

    function blob2form(thing) {
        if (typeof thing === 'string') {
            let form = document.forms[thing];
            if (!form) form = document.getElementById(thing);
            // check if it is form ?
            return form;
        }
        return thing;
    }

    /**
     * Get form values as url pars
     */
    function pars(form, ...include) {
        form = blob2form(form);
        if (!form) return "";
        const formData = new FormData(form);
        // delete empty values, be careful, deletion will modify iterator
        const keys = Array.from(formData.keys());
        for (const key of keys) {
            if (include.length > 0 && !include.find(k => k === key)) {
                formData.delete(key);
            }
            if (!formData.get(key)) {
                formData.delete(key);
            }
        }
        return new URLSearchParams(formData);
    }

    /**
     * Check if at least on par is not empty
     * @param {*} id 
     * @param  {...any} include 
     * @returns 
     */
    function hasPar(form, ...include) {
        form = blob2form(form);
        if (include.length < 1) return null;
        const formData = new FormData(form);
        for (const name of include) {
            for (const value of formData.getAll(name)) {
                if (value) return true;
            }
        }
        return false;
    }
    /**
     * For event.target, get first element of name
     * @param {*} el 
     * @param {*} name 
     * @returns 
     */
    function selfOrAncestor(el, name) {
        while (el.tagName.toLowerCase() != name) {
            el = el.parentNode;
            if (!el) return false;
            let tag = el.tagName.toLowerCase();
            if (tag == 'div' || tag == 'nav' || tag == 'body') return false;
        }
        return el;
    }

    return {
        divLoad: divLoad,
        blob2form: blob2form,
        hasPar: hasPar,
        insLine: insLine,
        loadLines: loadLines,
        loadJson: loadJson,
        pars: pars,
        selfOrAncestor: selfOrAncestor,
    }

}();
