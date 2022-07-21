(function() {
"use strict";

var Dygraph;
if (window.Dygraph) {
  Dygraph = window.Dygraph;
} else if (typeof(module) !== 'undefined') {
  Dygraph = require('../dygraph');
}


function plotHistory(e) {

  var ctx = e.drawingContext;
  var points = e.points;
  ctx.fillStyle = e.color;
  let past = 1;
  let future = 2;

  // Do the actual plotting.
  ctx.globalAlpha = 0.15
  for (var i = 0; i < points.length; i++) {
    var p = points[i];
    ctx.beginPath();
    ctx.arc(p.canvasx, p.canvasy, 5, 0, 2 * Math.PI, false);
    ctx.fill();
  }
  ctx.globalAlpha = 1

  // verify points
  for (var i = 0; i < points.length; i++) {
    let p = points[i];
    if (!p || p.canvasy === undefined || isNaN(p.canvasy)) points[i] = null;
  }
  // draw a smoothed line
  ctx.beginPath();
  let max = points.length - 1;
  for (var i = 0; i <= max; i++) {
    let p = points[i];
    if (!p) continue;
    let sum = 0;
    let count = 0;
    let pos = i;
    let from = Math.max(0, i-past);
    while(--pos >= from) {
      let p2 = points[pos];
      if(!p2) break;
      sum += p2.canvasy;
      count++;
    }
    pos = i;
    let to = Math.min(max, i+future);
    while(pos <= to) {
      let p2 = points[pos];
      if(!p2) break;
      sum += p2.canvasy;
      count++;
      pos++;
    }
    let y = sum / count;
    if(i && !points[i-1]) {
      ctx.moveTo(p.canvasx, y);
    }
    else {
      ctx.lineTo(p.canvasx, y);
    }
  }
  ctx.stroke();

}

Dygraph.plotHistory = plotHistory;

})();
