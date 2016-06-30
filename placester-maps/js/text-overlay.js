// fixed to map viewport
function TxtControl(pos, txt, cls, map) {
  var div = this.div_ = document.createElement('div');
  div.className = cls;
  div.innerHTML = txt;
  div.index = 1;

  this.map_ = map;
  this.pos = pos;
  this.index = map.controls[pos].push(div) - 1;
}

// fixed to map content
function TxtOverlay(pos, txt, cls, map) {
  this.pos = pos; // google.maps.LatLng
  this.txt_ = txt;
  this.cls_ = cls;
  this.map_ = map;
  this.div_ = null;

  this.setMap(map);
}

jQuery(document).ready(function($) {
  TxtControl.prototype.toggleDOM = function() {
    if(this.index >= 0) {
      this.map_.controls[this.pos].removeAt(this.index);
      this.index = -1;
    }
    else {
      this.index = this.map_.controls[this.pos].push(this.div_);
    }
  };


  TxtOverlay.prototype = new google.maps.OverlayView();

  TxtOverlay.prototype.onAdd = function() {
    var div = this.div_ = document.createElement('div');
    div.style.position = 'absolute';
    div.className = this.cls_;
    div.innerHTML = this.txt_;

    var panes = this.getPanes();
    panes.floatPane.appendChild(div);
  };

  TxtOverlay.prototype.draw = function() {
    var div = this.div_;

    var overlayProjection = this.getProjection();
    var position = overlayProjection.fromLatLngToDivPixel(this.pos);
    div.style.left = (position.x - (div.clientWidth / 2)) + 'px';
    div.style.top = (position.y - (div.clientHeight / 2)) + 'px';
  };

  TxtOverlay.prototype.onRemove = function() {
    this.div_.parentNode.removeChild(this.div_);
    this.div_ = null;
  };

  TxtOverlay.prototype.hide = function() {
    if (this.div_)
      this.div_.style.visibility = "hidden";
  };

  TxtOverlay.prototype.show = function() {
    if (this.div_)
      this.div_.style.visibility = "visible";
  };

  TxtOverlay.prototype.toggle = function() {
    if (this.div_)
      if (this.div_.style.visibility == "hidden")
        this.show();
      else
        this.hide();
  };

  TxtOverlay.prototype.toggleDOM = function() {
    if (this.getMap())
      this.setMap(null);
    else
      this.setMap(this.map_);
  }
});
