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
        const smooth = e.dygraph.getOption('historySmooth', 0);

        // Do the actual plotting.
        if (pointSize >= 6) ctx.globalAlpha = 0.3;
        else if (pointSize >= 3) ctx.globalAlpha = 0.5;
        else ctx.globalAlpha = 1;
        if (pointSize > 0) {
            for (var i = 0; i < points.length; i++) {
                var p = points[i];
                ctx.beginPath();
                ctx.arc(p.canvasx, p.canvasy, pointSize, 0, 2 * Math.PI, false);
                ctx.fill();
            }
        }

        // change alpha according to line width
        // console.log(ctx.lineWidth);

        // if (ctx.lineWidth >= 2) ctx.globalAlpha = 0.5;
        if (ctx.lineWidth >= 10) ctx.globalAlpha = 0.2;
        else if (ctx.lineWidth >= 5) ctx.globalAlpha = 0.3;
        else if (ctx.lineWidth <= 2) {
            ctx.shadowColor = "#fff";
            ctx.shadowBlur = 1;
            ctx.lineJoin = "round";
            ctx.globalAlpha = 1;
        }

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
            const year = p.xval;
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