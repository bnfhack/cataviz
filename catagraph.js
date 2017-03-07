// sigma autoloader FAILED, synchronism problem, script tags in html file is easier than everything
// get URI of this script
var scripts = document.getElementsByTagName("script");
var src = scripts[scripts.length-1].src;
;(function() {
  'use strict';



  sigma.utils.pkg('sigma.canvas.labels');
  /**
   * Ne pas afficher les titres des documents
   *
   * @param  {object}                   node     The node object.
   * @param  {CanvasRenderingContext2D} context  The canvas context.
   * @param  {configurable}             settings The settings function.
   */
  sigma.canvas.labels.document = function(node, context, settings) {
  };

  window.Cataviz = function ( canvas, data ) {
    var maxNodeSize = 30; // relatif à la taille du graphe ?
    this.src = src; // store the global
    this.workerUrl = this.src.substr( 0, this.src.lastIndexOf("/")+1 )+"sigma/worker.js";
    this.canvas = document.getElementById(canvas);


    this.odata = data;
    //
    var height = this.canvas.offsetHeight;
    var width = this.canvas.offsetWidth;
    var scale = Math.max( Math.min(height, width), 200) / 500;
    this.sigma = new sigma({
      graph: data,
      renderer: {
        container: this.canvas,
        type: 'canvas'
      },
      settings: {
        borderSize: 1,
        defaultEdgeColor: 'rgba(128, 128, 128, 0.1)',
        defaultLabelSize: 14,
        defaultLabelColor: "rgba(0, 0, 0, 0.7)",
        defaultNodeBorderColor: '#000',
        defaultNodeColor: "rgba(230, 230, 230, 0.7)",
        defaultNodeOuterBorderColor: 'rgb(236, 81, 72)', // stroke color of active nodes
        doubleClickEnabled: false, // utilisé pour les liens
        drawLabels: true,
        edgeColor: "default",
        edgeHoverExtremities: true,
        edgeHoverSizeRatio: 1,
        // enableEdgeHovering: true, // bad for memory
        font: ' Tahoma, Geneva, sans-serif', // after fontSize
        fontStyle: '', // before fontSize
        // labelAlignment: 'center', // linkurous only and not compatible with drag node
        // labelColor:"node",
        labelSize:"fixed",
        labelThreshold: 0,
        minEdgeSize: 1,
        minNodeSize: 3,
        minArrowSize: 15,
        maxArrowSize: 20,
        maxEdgeSize: maxNodeSize,
        maxNodeSize: maxNodeSize,
        mouseWheelEnabled: false,
        outerBorderSize: 3, // stroke size of active nodes
        sideMargin: 1,
        zoomingRatio: 1.3,
        // propriétés locales
        height: height,
        width: width,
        scale : scale, // effect of global size on graph objects
      }
    });
    var els = this.canvas.getElementsByClassName('restore');
    if (els.length) {
      this.gravBut = els[0];
      els[0].net = this;
      els[0].onclick = function() {
        this.net.stop(); // stop force and restore button
        this.net.sigma.graph.clear();
        this.net.sigma.graph.read(this.net.odata);
        this.net.sigma.refresh();
      }
    }
    var els = this.canvas.getElementsByClassName('grav');
    if (els.length) {
      this.gravBut = els[0];
      this.gravBut.net = this;
      this.gravBut.onclick = this.grav;
    }
    var els = this.canvas.getElementsByClassName('colors');
    if (els.length) {
      els[0].net = this;
      els[0].onclick = function() {
        var bw = this.net.sigma.settings( 'bw' );
        if (!bw) {
          this.innerHTML = '🌈';
          this.net.sigma.settings( 'bw', true );
        }
        else {
          this.innerHTML = '◐';
          this.net.sigma.settings( 'bw', false );
        }
        this.net.sigma.refresh();
      };
    }
    var els = this.canvas.getElementsByClassName( 'zoomin' );
    if (els.length) {
      els[0].net = this;
      els[0].onclick = function() {
        var c = this.net.sigma.camera; c.goTo({ratio: c.ratio / c.settings('zoomingRatio')});
      };
    }
    var els = this.canvas.getElementsByClassName( 'zoomout' );
    if (els.length) {
      els[0].net = this;
      els[0].onclick = function() {
        var c = this.net.sigma.camera; c.goTo({ratio: c.ratio * c.settings('zoomingRatio')});
      };
    }


    var els = this.canvas.getElementsByClassName( 'mix' );
    if (els.length) {
      this.mixBut = els[0];
      this.mixBut.net = this;
      this.mixBut.onclick = this.mix;
    }
    var els = this.canvas.getElementsByClassName( 'shot' );
    if (els.length) {
      els[0].net = this;
      els[0].onclick = function() {
        this.net.stop(); // stop force
        this.net.sigma.refresh();
        var s =  this.net.sigma;
        var size = prompt("Largeur de l’image (en px)", window.innerWidth);
        sigma.plugins.image(s, s.renderers[0], {
          download: true,
          margin: 50,
          size: size,
          clip: true,
          zoomRatio: 1,
          labels: false
        });
      };
    }

    // resizer
    var els = this.canvas.getElementsByClassName( 'resize' );
    if (els.length) {
      els[0].net = this;
      els[0].onmousedown = function(e) {
        this.net.stop();
        var html = document.documentElement;
        html.sigma = this.net.sigma; // give an handle to the sigma instance
        html.dragO = this.net.canvas;
        html.dragX = e.clientX;
        html.dragY = e.clientY;
        html.dragWidth = parseInt( document.defaultView.getComputedStyle( html.dragO ).width, 10 );
        html.dragHeight = parseInt( document.defaultView.getComputedStyle( html.dragO ).height, 10 );
        html.addEventListener( 'mousemove', Cataviz.doDrag, false );
        html.addEventListener( 'mouseup', Cataviz.stopDrag, false );
      };
    }

    this.sigma.bind( 'rightClickNode', function( e ) {
      e.data.renderer.graph.dropNode(e.data.node.id);
      e.target.refresh();
    });
    this.sigma.bind( 'doubleClickNode', function( e ) {
      if ( e.data.node.type=="person" ) {
        // window.open("http://catalogue.bnf.fr/ark:/12148/"+e.data.node.id);
        window.top.location.href="auteur.php?person="+e.data.node.id;
      }
      else if ( e.data.node.type=="document" || e.data.node.type=="work" ) {
        window.open("http://catalogue.bnf.fr/ark:/12148/"+e.data.node.id);
      }
    });
    // Initialize the dragNodes plugin:
    sigma.plugins.dragNodes( this.sigma, this.sigma.renderers[0] );
    this.start();
  }
  Cataviz.prototype.start = function() {
    if (this.gravBut) this.gravBut.innerHTML = '◼';
    var pars = {
      // slowDown: 1,
      adjustSizes: true, // avec iterationsPerRender, resserre trop le réseau
      // linLogMode: true, // long, avec gravité > 1
      gravity: 1.5, // <1 pour le Tartuffe
      // edgeWeightInfluence: 0.1, // demande iterationsPerRender, désorganise
      // outboundAttractionDistribution: true, // ?, même avec iterationsPerRender
      // barnesHutOptimize: true, // trop lent
      // barnesHutTheta: 0.5,  // pas d’effet apparent sur si petit graphe
      // scalingRatio: 2, // non, pas compris
      // outboundAttractionDistribution: true, // pas avec beaucoup de petits rôles
      // strongGravityMode: true, // instable, nécessaire avec outboundAttractionDistribution
      iterationsPerRender : 20, // important
    };
    pars.worker = true;
    pars.workerUrl = this.workerUrl;
    this.sigma.startForceAtlas2( pars );
    var dramanet = this;
    setTimeout(function() { dramanet.stop();}, 3000)
  };
  Cataviz.prototype.stop = function() {
    this.sigma.killForceAtlas2();
    if (this.gravBut) this.gravBut.innerHTML = '►';
  };
  Cataviz.prototype.grav = function() {
    if ((this.net.sigma.supervisor || {}).running) {
      this.net.sigma.killForceAtlas2();
      this.innerHTML = '►';
    }
    else {
      this.innerHTML = '◼';
      this.net.start();
    }
    return false;
  };
  Cataviz.prototype.mix = function() {
    this.net.sigma.killForceAtlas2();
    if (this.net.gravBut) this.net.gravBut.innerHTML = '►';
    for (var i=0; i < this.net.sigma.graph.nodes().length; i++) {
      this.net.sigma.graph.nodes()[i].x = Math.random()*10;
      this.net.sigma.graph.nodes()[i].y = Math.random()*10;
    }
    this.net.sigma.refresh();
    // this.net.start();
    return false;
  };
  // global static
  Cataviz.doDrag = function( e ) {
    this.dragO.style.width = ( this.dragWidth + e.clientX - this.dragX ) + 'px';
    this.dragO.style.height = ( this.dragHeight + e.clientY - this.dragY ) + 'px';
  };
  Cataviz.stopDrag = function( e ) {
    var height = this.dragO.offsetHeight;
    var width = this.dragO.offsetWidth;
    var scale = Math.max( Math.min(height, width), 200) / 500;

    this.removeEventListener( 'mousemove', Cataviz.doDrag, false );
    this.removeEventListener( 'mouseup', Cataviz.stopDrag, false );
    this.sigma.settings( 'height', height );
    this.sigma.settings( 'width', width );
    this.sigma.settings( 'scale', scale );
    this.sigma.refresh();
  };

})();
