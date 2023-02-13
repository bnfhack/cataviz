        <div id="sugg_col">
            <header>
                <input id="sugg_filter" placeholder="Filtrer par lettres"/>
                <div>Né
                après <input id="sugg_after" size="4"/>
                avant <input id="sugg_before" size="4"/>
                </div>
            </header>
            <nav id="sugg_terms">

            </nav>
        </div>
        <script>
const suggUrl = "data/suggest_pers.php";
const suggParam = "pers";

const filter = document.getElementById('sugg_filter');
const after = document.getElementById('sugg_after');
const before = document.getElementById('sugg_before');

const suggUp = function() {
    let url = new URL(suggUrl, document.location);
    let pars = Suggest.pars(form, 'start', 'end');
    if (filter && filter.value) pars.set('q', filter.value);
    if (after && after.value) pars.set('after', after.value);
    if (before && before.value) pars.set('before', before.value);
    url.search = pars;

    const suggTerms = document.getElementById('sugg_terms');
    suggTerms.innerText = '';
    Suggest.loadJson(url, function(json) {
        if (!json || !json.data || !json.data.length) return;
        for (let i=0, len = json.data.length; i < len; i++) {
            let line = Suggest.line(suggParam, json.data[i], Suggest.addInput);
            suggTerms.appendChild(line);
        }
    });
}
filter.addEventListener('input', suggUp);
after.addEventListener('change', suggUp);
before.addEventListener('change', suggUp);


// create form inputs from http params
const submit = document.getElementById('submit');
let el = null;
<?php
$terms = http::pars('pers');
$sql = "SELECT * FROM pers WHERE id = ?";
$stmt = Cataviz::prepare($sql);
foreach($terms as $pers_id) {
    $stmt->execute([$pers_id]);
    $pers_row = $stmt->fetch();
    if (!$pers_row) continue;
    $label = addslashes(Cataviz::pers_label($pers_row));
    echo "el = Suggest.input(suggParam, '$pers_id', '$label', chartUp);\n";
    echo "submit.parentNode.insertBefore(el, submit);\n";
}

?>
        </script>

