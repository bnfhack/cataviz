var GraphologyLayoutNoverlap;
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ([
/* 0 */
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

/**
 * Graphology Noverlap Layout
 * ===========================
 *
 * Library endpoint.
 */
var isGraph = __webpack_require__(1);
var iterate = __webpack_require__(2);
var helpers = __webpack_require__(3);
var DEFAULT_SETTINGS = __webpack_require__(4);
var DEFAULT_MAX_ITERATIONS = 500;

/**
 * Asbtract function used to run a certain number of iterations.
 *
 * @param  {boolean}       assign       - Whether to assign positions.
 * @param  {Graph}         graph        - Target graph.
 * @param  {object|number} params       - If number, params.maxIterations, else:
 * @param  {number}          maxIterations - Maximum number of iterations.
 * @param  {object}          [settings] - Settings.
 * @return {object|undefined}
 */
function abstractSynchronousLayout(assign, graph, params) {
  if (!isGraph(graph)) throw new Error('graphology-layout-noverlap: the given graph is not a valid graphology instance.');
  if (typeof params === 'number') params = {
    maxIterations: params
  };else params = params || {};
  var maxIterations = params.maxIterations || DEFAULT_MAX_ITERATIONS;
  if (typeof maxIterations !== 'number' || maxIterations <= 0) throw new Error('graphology-layout-force: you should provide a positive number of maximum iterations.');

  // Validating settings
  var settings = Object.assign({}, DEFAULT_SETTINGS, params.settings),
    validationError = helpers.validateSettings(settings);
  if (validationError) throw new Error('graphology-layout-noverlap: ' + validationError.message);

  // Building matrices
  var matrix = helpers.graphToByteArray(graph, params.inputReducer),
    converged = false,
    i;

  // Iterating
  for (i = 0; i < maxIterations && !converged; i++) converged = iterate(settings, matrix).converged;

  // Applying
  if (assign) {
    helpers.assignLayoutChanges(graph, matrix, params.outputReducer);
    return;
  }
  return helpers.collectLayoutChanges(graph, matrix, params.outputReducer);
}

/**
 * Exporting.
 */
var synchronousLayout = abstractSynchronousLayout.bind(null, false);
synchronousLayout.assign = abstractSynchronousLayout.bind(null, true);
module.exports = synchronousLayout;

/***/ }),
/* 1 */
/***/ ((module) => {

/**
 * Graphology isGraph
 * ===================
 *
 * Very simple function aiming at ensuring the given variable is a
 * graphology instance.
 */

/**
 * Checking the value is a graphology instance.
 *
 * @param  {any}     value - Target value.
 * @return {boolean}
 */
module.exports = function isGraph(value) {
  return value !== null && typeof value === 'object' && typeof value.addUndirectedEdgeWithKey === 'function' && typeof value.dropNode === 'function' && typeof value.multi === 'boolean';
};

/***/ }),
/* 2 */
/***/ ((module) => {

/**
 * Graphology Noverlap Iteration
 * ==============================
 *
 * Function used to perform a single iteration of the algorithm.
 */

/**
 * Matrices properties accessors.
 */
var NODE_X = 0,
  NODE_Y = 1,
  NODE_SIZE = 2;

/**
 * Constants.
 */
var PPN = 3;

/**
 * Helpers.
 */
function hashPair(a, b) {
  return a + '§' + b;
}
function jitter() {
  return 0.01 * (0.5 - Math.random());
}

/**
 * Function used to perform a single interation of the algorithm.
 *
 * @param  {object}       options    - Layout options.
 * @param  {Float32Array} NodeMatrix - Node data.
 * @return {object}                  - Some metadata.
 */
module.exports = function iterate(options, NodeMatrix) {
  // Caching options
  var margin = options.margin;
  var ratio = options.ratio;
  var expansion = options.expansion;
  var gridSize = options.gridSize; // TODO: decrease grid size when few nodes?
  var speed = options.speed;

  // Generic iteration variables
  var i, j, x, y, l, size;
  var converged = true;
  var length = NodeMatrix.length;
  var order = length / PPN | 0;
  var deltaX = new Float32Array(order);
  var deltaY = new Float32Array(order);

  // Finding the extents of our space
  var xMin = Infinity;
  var yMin = Infinity;
  var xMax = -Infinity;
  var yMax = -Infinity;
  for (i = 0; i < length; i += PPN) {
    x = NodeMatrix[i + NODE_X];
    y = NodeMatrix[i + NODE_Y];
    size = NodeMatrix[i + NODE_SIZE] * ratio + margin;
    xMin = Math.min(xMin, x - size);
    xMax = Math.max(xMax, x + size);
    yMin = Math.min(yMin, y - size);
    yMax = Math.max(yMax, y + size);
  }
  var width = xMax - xMin;
  var height = yMax - yMin;
  var xCenter = (xMin + xMax) / 2;
  var yCenter = (yMin + yMax) / 2;
  xMin = xCenter - expansion * width / 2;
  xMax = xCenter + expansion * width / 2;
  yMin = yCenter - expansion * height / 2;
  yMax = yCenter + expansion * height / 2;

  // Building grid
  var grid = new Array(gridSize * gridSize),
    gridLength = grid.length,
    c;
  for (c = 0; c < gridLength; c++) grid[c] = [];
  var nxMin, nxMax, nyMin, nyMax;
  var xMinBox, xMaxBox, yMinBox, yMaxBox;
  var col, row;
  for (i = 0; i < length; i += PPN) {
    x = NodeMatrix[i + NODE_X];
    y = NodeMatrix[i + NODE_Y];
    size = NodeMatrix[i + NODE_SIZE] * ratio + margin;
    nxMin = x - size;
    nxMax = x + size;
    nyMin = y - size;
    nyMax = y + size;
    xMinBox = Math.floor(gridSize * (nxMin - xMin) / (xMax - xMin));
    xMaxBox = Math.floor(gridSize * (nxMax - xMin) / (xMax - xMin));
    yMinBox = Math.floor(gridSize * (nyMin - yMin) / (yMax - yMin));
    yMaxBox = Math.floor(gridSize * (nyMax - yMin) / (yMax - yMin));
    for (col = xMinBox; col <= xMaxBox; col++) {
      for (row = yMinBox; row <= yMaxBox; row++) {
        grid[col * gridSize + row].push(i);
      }
    }
  }

  // Computing collisions
  var cell;
  var collisions = new Set();
  var n1, n2, x1, x2, y1, y2, s1, s2, h;
  var xDist, yDist, dist, collision;
  for (c = 0; c < gridLength; c++) {
    cell = grid[c];
    for (i = 0, l = cell.length; i < l; i++) {
      n1 = cell[i];
      x1 = NodeMatrix[n1 + NODE_X];
      y1 = NodeMatrix[n1 + NODE_Y];
      s1 = NodeMatrix[n1 + NODE_SIZE];
      for (j = i + 1; j < l; j++) {
        n2 = cell[j];
        h = hashPair(n1, n2);
        if (gridLength > 1 && collisions.has(h)) continue;
        if (gridLength > 1) collisions.add(h);
        x2 = NodeMatrix[n2 + NODE_X];
        y2 = NodeMatrix[n2 + NODE_Y];
        s2 = NodeMatrix[n2 + NODE_SIZE];
        xDist = x2 - x1;
        yDist = y2 - y1;
        dist = Math.sqrt(xDist * xDist + yDist * yDist);
        collision = dist < s1 * ratio + margin + (s2 * ratio + margin);
        if (collision) {
          converged = false;
          n2 = n2 / PPN | 0;
          if (dist > 0) {
            deltaX[n2] += xDist / dist * (1 + s1);
            deltaY[n2] += yDist / dist * (1 + s1);
          } else {
            // Nodes are on the exact same spot, we need to jitter a bit
            deltaX[n2] += width * jitter();
            deltaY[n2] += height * jitter();
          }
        }
      }
    }
  }
  for (i = 0, j = 0; i < length; i += PPN, j++) {
    NodeMatrix[i + NODE_X] += deltaX[j] * 0.1 * speed;
    NodeMatrix[i + NODE_Y] += deltaY[j] * 0.1 * speed;
  }
  return {
    converged: converged
  };
};

/***/ }),
/* 3 */
/***/ ((__unused_webpack_module, exports) => {

/**
 * Graphology Noverlap Helpers
 * ============================
 *
 * Miscellaneous helper functions.
 */

/**
 * Constants.
 */
var PPN = 3;

/**
 * Function used to validate the given settings.
 *
 * @param  {object}      settings - Settings to validate.
 * @return {object|null}
 */
exports.validateSettings = function (settings) {
  if ('gridSize' in settings && typeof settings.gridSize !== 'number' || settings.gridSize <= 0) return {
    message: 'the `gridSize` setting should be a positive number.'
  };
  if ('margin' in settings && typeof settings.margin !== 'number' || settings.margin < 0) return {
    message: 'the `margin` setting should be 0 or a positive number.'
  };
  if ('expansion' in settings && typeof settings.expansion !== 'number' || settings.expansion <= 0) return {
    message: 'the `expansion` setting should be a positive number.'
  };
  if ('ratio' in settings && typeof settings.ratio !== 'number' || settings.ratio <= 0) return {
    message: 'the `ratio` setting should be a positive number.'
  };
  if ('speed' in settings && typeof settings.speed !== 'number' || settings.speed <= 0) return {
    message: 'the `speed` setting should be a positive number.'
  };
  return null;
};

/**
 * Function generating a flat matrix for the given graph's nodes.
 *
 * @param  {Graph}        graph   - Target graph.
 * @param  {function}     reducer - Node reducer function.
 * @return {Float32Array}         - The node matrix.
 */
exports.graphToByteArray = function (graph, reducer) {
  var order = graph.order;
  var matrix = new Float32Array(order * PPN);
  var j = 0;
  graph.forEachNode(function (node, attr) {
    if (typeof reducer === 'function') attr = reducer(node, attr);
    matrix[j] = attr.x;
    matrix[j + 1] = attr.y;
    matrix[j + 2] = attr.size || 1;
    j += PPN;
  });
  return matrix;
};

/**
 * Function applying the layout back to the graph.
 *
 * @param {Graph}        graph      - Target graph.
 * @param {Float32Array} NodeMatrix - Node matrix.
 * @param {function}     reducer    - Reducing function.
 */
exports.assignLayoutChanges = function (graph, NodeMatrix, reducer) {
  var i = 0;
  graph.forEachNode(function (node) {
    var pos = {
      x: NodeMatrix[i],
      y: NodeMatrix[i + 1]
    };
    if (typeof reducer === 'function') pos = reducer(node, pos);
    graph.mergeNodeAttributes(node, pos);
    i += PPN;
  });
};

/**
 * Function collecting the layout positions.
 *
 * @param  {Graph}        graph      - Target graph.
 * @param  {Float32Array} NodeMatrix - Node matrix.
 * @param  {function}     reducer    - Reducing function.
 * @return {object}                  - Map to node positions.
 */
exports.collectLayoutChanges = function (graph, NodeMatrix, reducer) {
  var positions = {};
  var i = 0;
  graph.forEachNode(function (node) {
    var pos = {
      x: NodeMatrix[i],
      y: NodeMatrix[i + 1]
    };
    if (typeof reducer === 'function') pos = reducer(node, pos);
    positions[node] = pos;
    i += PPN;
  });
  return positions;
};

/**
 * Function returning a web worker from the given function.
 *
 * @param  {function}  fn - Function for the worker.
 * @return {DOMString}
 */
exports.createWorker = function createWorker(fn) {
  var xURL = window.URL || window.webkitURL;
  var code = fn.toString();
  var objectUrl = xURL.createObjectURL(new Blob(['(' + code + ').call(this);'], {
    type: 'text/javascript'
  }));
  var worker = new Worker(objectUrl);
  xURL.revokeObjectURL(objectUrl);
  return worker;
};

/***/ }),
/* 4 */
/***/ ((module) => {

/**
 * Graphology Noverlap Layout Default Settings
 * ============================================
 */
module.exports = {
  gridSize: 20,
  margin: 5,
  expansion: 1.1,
  ratio: 1.0,
  speed: 3
};

/***/ })
/******/ 	]);
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__(0);
/******/ 	GraphologyLayoutNoverlap = __webpack_exports__;
/******/ 	
/******/ })()
;
//# sourceMappingURL=graphology.layout.noverlap.js.map