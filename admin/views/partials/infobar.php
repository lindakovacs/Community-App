<style id="pl_infobar" type="text/css">
  #infobar {
	overflow: hidden;
	/*position: absolute;*/
	top: 0px;
	z-index: 100001;
	width: 100%;
	background: #9E1616;
	position: fixed;
	/*behavior: url(//dhiodphkum9p1.cloudfront.net/assets/misc/PIE-9b1859b5f8f5e290b0b121fddfecef36.htc);*/
	-moz-box-shadow: #000 0 1px 3px;
	-webkit-box-shadow: black 0 1px 3px;
	-o-box-shadow: #000 0 1px 3px;
	box-shadow: black 0 1px 3px;
  }

  #infobar .msg {
	vertical-align: middle;
	text-align: center;
	font-weight: bold;
	font-size: 14px;
	color: white;
	padding: 12px 0 12px 0;
  }

  #infobar .msg button {
  	margin-left: 9px;
  	font-weight: bold;
  }

  #infobar .close {
	float: right;
	background: url(<?php echo PL_IMG_URL; ?>icons/x_white_icon.png) no-repeat;
	height: 17px;
	width: 14px;
	cursor: pointer;
	margin-right: 12px;
	position: relative;
	top: -1px;
  }

  #infobar-buffer {
  	width: 100%;
  	height: 50px;
  }
</style>
<div id="infobar-buffer"></div>
<div class="alert" id="infobar">
  <div class="msg">
    You are test-driving your site with demo data
    <button id="toggle_demo">turn off</button>
    <div class="close"></div>
  </div>
</div>