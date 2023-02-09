const Suggest = function() {
    const EOF = '\u000A';
    /**
     * Get URL to a callback function.
     * 
     * @param {String} url 
     * @param {function} callback 
     * @returns 
     */
    function loadJson(url, callback) {
        return new Promise(function(resolve, reject) {
            let xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onprogress = function() {
                // do something ? is it send by chunks ?
                if (xhr.response) console.log("progress " + xhr.response.length);
            };
            xhr.onload = function() {
                var status = xhr.status;
                if (status !== 200) {
                    // error ? do what ?
                    callback(xhr.response);
                    reject(Error(status + " " + url));
                }
                callback(xhr.response);
                resolve();
            };
            xhr.onerror = function() {
                reject(Error('Connection failed'));
            };
            xhr.open('GET', url);
            xhr.send();
        });
    }
    
    /**
     * Attached to a dropdown pannel, show
     */
    function show() {
        const dropdown = this;
        if (window.dropdown && window.dropdown != dropdown) {
            window.dropdown.hide();
        }
        window.dropdown = dropdown;
        dropdown.style.display = 'block';
    }

    /**
     * Attached to a dropdown pannel, hide
     */
    function hide() {
        const dropdown = this;
        dropdown.blur();
        dropdown.style.display = 'none';
        dropdown.input.value = '';
        window.dropdown = null;
    }
    
    /**
     * Intitialize an input with dropdown
     * @param {HTMLInputElement} input 
     * @returns 
     */
    function init(input, callback) {
        if (!input) {
            console.log("[Suggest] No <input> to equip");
            return;
        }
        if (input.list) { // create a list
            console.log("[Suggest] <datalist> will no be used\n" + input);
        }
        if (!input.dataset.url) {
            console.log("[Suggest] No @data-url to get data from\n" + input);
            return;
        }
        if (!input.id) {
            console.log("[Suggest] No @id, required to create params\n" + input);
            return;
        }
        input.autocomplete = 'off';
        // create dropdown
        const dropdown = document.createElement("div");
        dropdown.className = "suggest dropdown " + input.id;
        input.parentNode.insertBefore(dropdown, input.nextSibling);
        input.dropdown = dropdown;
        dropdown.input = input;
        dropdown.hide = hide;
        dropdown.show = show;
        // global click hide current dropdown
        window.addEventListener('click', (e) => {
            if (window.dropdown) window.dropdown.hide();
        });
        // click in dropdown, avoid hide effect at body level
        input.parentNode.addEventListener('click', (e) => {
            e.stopPropagation();
        });
        // control dropdowns, 
        input.addEventListener('click', function(e) {
            if (dropdown.style.display != 'block') {
                dropdown.show();
            } else {
                dropdown.hide();
            }
        });

        input.addEventListener('click', callback);
        input.addEventListener('input', callback);
        input.addEventListener('input', function(e) { dropdown.show(); });

        dropdown.addEventListener("touchstart", function(e) {
            // si on défile la liste de résultats sur du tactile, désafficher le clavier
            input.blur();
        });
        input.addEventListener('keyup', function(e) {
            e = e || window.event;
            if (e.key == 'Esc' || e.key == 'Escape') {
                dropdown.hide();
            } else if (e.key == 'Backspace') {
                if (input.value) return;
                dropdown.hide();
            } else if (e.key == 'ArrowDown') {
                if (input.value) return;
                dropdown.show();
            } else if (e.key == 'ArrowUp') {
                // focus ?
            }
        });

    }

    /**
     * Get form values as url pars
     */
    function pars(form, ...include) {
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

    return {
        init: init,
        loadJson: loadJson,
        pars: pars,
    }
}();

Suggest.addInput = function(e) {
    const line = e.currentTarget;
    const name = line.dataset.name;
    const value = line.dataset.value;
    const label = line.textContent;
    // point from where insert before the field
    const beforeId = 'submit';
    const before = document.getElementById(beforeId);
    if (!before) {
        console.log('Suggest, insert point not found, before id="' + beforeId +'"');
        return;
    }

    let el = Suggest.input(name, value, label, chartUp);
    before.parentNode.insertBefore(el, before);
    // quite hard coded
    chartUp();
    if (line.input) {
        line.input.focus();
        line.input.dropdown.hide();
    }
}

Suggest.input = function(name, value, text, callback)
{
    const el = document.createElement("label");
    el.addEventListener('click', function(e){
        const el = e.currentTarget;
        el.parentNode.removeChild(el);
        callback(e);
    });
    el.className = 'sugg';
    el.title = text;
    const a = document.createElement("a");
    a.innerText = '🞭';
    a.className = 'inputDel';
    el.appendChild(a);
    const input = document.createElement("input");
    input.name = name;
    input.type = 'hidden';
    input.value = value;
    el.appendChild(input);
    el.appendChild(document.createTextNode(text));
    return el;
}

Suggest.line = function(name, record, callback) {
    let line = document.createElement('div');
    line.className = "sugg";
    line.dataset.name = name;
    line.dataset.value = record.value;
    let html = '';
    if (record.n) html += "<small>" + record.n + ".</small> " 
    html += record.label;
    if (record.count) {
        html += ' (' + record.count + ')';
    }
    line.innerHTML = html;
    line.addEventListener('click', callback);
    return line;
}

/**
 * Append 
 * @param {Event} e 
 */
Suggest.load = function (e) {
    const input = e.currentTarget;
    const dropdown = input.dropdown;
    // get forms params ? dates ?
    const formData = new FormData(input.form);
    const pars = new URLSearchParams(formData);
    pars.set("q", input.value); // add the suggest query
    const url = input.dataset.url + "?" + pars;
    dropdown.innerText = ''; // clean
    Suggest.loadJson(url, function(json) {
        if (!json) return;
        if (!json.data) return;
        if (!json.data.length) return;
        for (let i=0, len = json.data.length; i < len; i++) {
            let line = Suggest.line(input.id, json.data[i], Suggest.addInput);
            line.input = input;
            dropdown.appendChild(line);
        }
    });
}




// 
const els = document.querySelectorAll('input.suggest');
for (let i = 0, len = els.length; i < len; i++) {
    Suggest.init(els[i], Suggest.load);
}

/**
 * Dygraph parameters
 */

let attrs = {
    legend: "always",
    // labelsSeparateLines: true,
    showRoller: false,
    titleHeight: 75,
    pointSize: 2,
};

attrs.colors = [
    'hsla(0, 0%, 50%, 1)', // grey
    'hsla(0, 50%, 50%, 1)', // red
    'hsla(225, 50%, 50%, 1)', // blue
    'hsla(45, 80%, 50%, 1)', // yellow
    'hsla(90, 60%, 30%, 1)', // green
    'hsla(180, 50%, 40%, 1)', // 5
    'hsla(270, 50%, 50%, 1)', // 6
    'hsla(135, 70%, 50%, 1)',
    'hsla(215, 90%, 50%, 1)',
    'hsla(0, 30%, 50%, 1)',
];



attrs.underlayCallback = function(canvas, area, g) {
    canvas.fillStyle = "rgba(192, 192, 192, 0.2)";
    var periods = [
        [1562, 1598],
        [1648, 1653],
        [1789, 1795],
        [1814, 1815],
        [1830, 1831],
        [1848, 1849],
        [1870, 1871],
        [1914, 1918],
        [1939, 1945]
    ];
    var lim = periods.length;
    for (var i = 0; i < lim; i++) {
        var bottom_left = g.toDomCoords(periods[i][0], -20);
        var top_right = g.toDomCoords(periods[i][1], +20);
        var left = bottom_left[0];
        var right = top_right[0];
        canvas.fillRect(left, area.y, right - left, area.h);
    }
};



attrs.axes = {
    x: {
        gridLineWidth: 1,
        gridLineColor: "rgba(192, 192, 192, 0.3)",
        drawGrid: true,
        independentTicks: true,
        /*
        ticker: function(a, b, pixels, opts, dygraph, vals) {
            return [
                { "v": 1648, "label": 1648 },
                { "v": 1685, "label": 1685 },
                { "v": 1715, "label": 1715 },
                { "v": 1756, "label": "1756        " },
                { "v": 1763, "label": "      1763" },
                { "v": 1789, "label": "1789        " },
                { "v": 1795, "label": "        1795" },
                { "v": 1815, "label": 1815 },
                { "v": 1830, "label": 1830 },
                { "v": 1848, "label": 1848 },
                { "v": 1870, "label": 1870 },
                { "v": 1900, "label": 1900 },
                { "v": 1914, "label": "1914        " },
                { "v": 1918, "label": "        1918" },
                { "v": 1939, "label": "1939        " },
                { "v": 1945, "label": "        1945" },
                { "v": 1968, "label": 1968 },
                { "v": 1989, "label": 1989 },
                { "v": 2005, "label": 2005 },
                { "v": 2018, "label": 2019 },
            ];
        }
        */
    },
    y: {
        independentTicks: true,
        drawGrid: true,
        gridLinePattern: [1, 2],
        gridLineColor: "rgba(128, 128, 128, 0.7)",
        gridLineWidth: 0.5,
    },
    y2: {
        drawGrid: false,
        independentTicks: true,
        gridLineColor: "rgba(192, 192, 192, 0.4)",
        gridLineWidth: 4,
        gridLinePattern: [6, 6],
    },
};



attrs.annotations = function(aseries) {
    return [
        { series: aseries, x: "1562", shortText: "Guerres de Religion", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1789", shortText: "1789", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1815", shortText: "1815", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1830", shortText: "1830", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1848", shortText: "1848", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1870", shortText: "1870", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1914", shortText: "1914", width: "", height: "", cssClass: "annv" },
        { series: aseries, x: "1939", shortText: "1939", width: "", height: "", cssClass: "annv" },
    ];
}
/*
var linear = document.getElementById("linear");
var log = document.getElementById("log");
var setLog = function(val) {
    g.updateOptions({ logscale: val });
    linear.disabled = !val;
    log.disabled = val;
};
if (linear) linear.onclick = function() { setLog(false); };
if (log) log.onclick = function() { setLog(true); };
*/