/**
 * Toolkit for ajax forms
 */
const Formajax = function() {
    /** Message send to a callback loader to say end of file */
    const EOF = '\u000A';
    /** Used as a separator between mutiline <div> */
    const LF = '&#10;';
    /**
     * Load Json complete, not in slices
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

    return {
        LF: LF,
        loadJson: loadJson,
    }

}();