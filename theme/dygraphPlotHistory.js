(function() {
    "use strict";

    var Dygraph;
    if (window.Dygraph) {
        Dygraph = window.Dygraph;
    } else if (typeof(module) !== 'undefined') {
        Dygraph = require('../dygraph');
    }

    function plotHistory(e) {
        const pointSize = e.dygraph.getOption('pointSize', e.setName);
        var ctx = e.drawingContext;
        var points = e.points;
        ctx.fillStyle = e.color;

        // define the smooth level by year
        const smoothYear = [
            [-1, 3],
            [1648, 0],
            [1654, 2],
            [1788, 0],
            [1802, 2],
            [1829, 0],
            [1832, 2],
            [1846, 0],
            [1851, 2],
            [1868, 0],
            [1874, 2],
            [1913, 0],
            [1919, 2],
            [1938, 0],
            [1946, 2],
            [2030, 0],
        ];

        // Do the actual plotting.
        ctx.globalAlpha = 1;
        if (pointSize > 0) {
            for (var i = 0; i < points.length; i++) {
                var p = points[i];
                ctx.beginPath();
                ctx.arc(p.canvasx, p.canvasy, pointSize, 0, 2 * Math.PI, false);
                ctx.fill();
            }
        }
        ctx.globalAlpha = 0.15;

        // verify points
        for (var i = 0; i < points.length; i++) {
            let p = points[i];
            if (!p || p.canvasy === undefined || isNaN(p.canvasy)) points[i] = null;
        }
        // draw a smoothed line

        ctx.beginPath();
        let max = points.length - 1;
        let smoothCursor = 0;
        let smooth = smoothYear[smoothCursor][1];
        smoothCursor++
        for (var i = 0; i <= max; i++) {
            let p = points[i];
            if (!p) continue;
            // console.log(p);
            const year = p.xval;
            if (year >= smoothYear[smoothCursor][0]) {
                smooth = smoothYear[smoothCursor][1];
                smoothCursor++;
            }
            let pos = i;
            let from = Math.max(0, i - smooth);
            let to = Math.min(max, i + smooth);
            let count = 0;
            let sum = 0;
            for (pos = from; pos <= to; pos++) {
                let apoint = points[pos];
                if (!apoint) break;
                sum += apoint.canvasy;
                count++;
            }
            let y = sum / count;
            if (i && !points[i - 1]) {
                // first, move ?
                ctx.moveTo(p.canvasx, y);
            } else {
                ctx.lineTo(p.canvasx, y);
            }
        }
        ctx.stroke();

    }

    Dygraph.plotHistory = plotHistory;

})();