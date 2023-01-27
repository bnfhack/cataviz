<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sigma example: Use node and edge reducers</title>
    <script src="../lib/sigma.min.js"></script>
    <script src="../lib/graphology.umd.min.js"></script>
    <script src="../lib/graphology.layout.min.js"></script>
    <script src="../lib/graphology.layout.noverlap.js"></script>
    <script src="../lib/graphology.layout.forceatlas2.js"></script>
  </head>
  <body>
    <style>
      html,
      body,
      #sigma-container {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
      }
      #search {
        position: absolute;
        right: 1em;
        top: 1em;
      }
    </style>
    <div id="sigma-container"></div>
    <div id="search">
      <input type="search" id="search-input" list="suggestions" placeholder="Try searching for a node..." />
      <datalist id="suggestions"></datalist>
    </div>
    <script type="module" src="./miserables.js"></script>
  </body>
</html>