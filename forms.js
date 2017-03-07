;(function() {
  var name = document.getElementById( "name" );
  var persark = document.getElementById( "persark" );
  var perslist = document.getElementById( "perslist" );
  if ( !name || !persark || !perslist ) return;
  name.onclick = function()
  {
    this.setSelectionRange(0, this.value.length);
  };
  name.setAttribute( "autocomplete", "off" );
  name.onkeyup = function( event )
  {
    keycode = event.which | event.keyCode;
    // down arrow, go in list
    if( keycode == 40 ) {
      perslist.style.visibility = "visible";
      perslist.focus();
      perslist.options[0].selected= true;
      perslist.onchange();
      return;
    }
    if( keycode == 13 || keycode == 27 ) {
      perslist.style.visibility = "hidden";
      return;
    }
    var value = this.value;
    persark.value = "";
    if ( value.length < 2 ) return;
    perslist.style.visibility = "visible";
    if ( value == this.oldValue ) return;
    this.oldValue = value;
    var src = "perslist.php?callback=perspop&q="+value;
    var js = document.createElement('script');
    js.src = src;
    var head = document.getElementsByTagName('head')[0];
    head.appendChild(js);
  };
  perslist.onchange = function()
  {
    opt = this.options[this.selectedIndex];
    name.value = opt.text;
    persark.value = opt.value;
  };
  perslist.onclick = function()
  {
    this.style.visibility = "hidden";
    this.blur();
  }
  perslist.onkeypress = function( event )
  {
    keycode = event.which | event.keyCode;
    if( keycode == 13 || keycode == 27 ) {
      this.style.visibility = "hidden";
      name.focus();
      name.select();
      return;
    }
  }
  perslist.onblur = function( event ) {
    perslist.style.visibility = "hidden";
  }
  window.perspop = function ( data )
  {
    perslist.style.visibility = "visible";
    // remove all options
    while ( perslist.length ) perslist.remove( 0 );
    for ( var i=0, max=data.length; i < max; i++ ) {
      var opt = document.createElement("option");
      opt.value = data[i][1];
      opt.text = data[i][2];
      perslist.add( opt );
    }
  }
})();
