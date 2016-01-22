<style id="pl_infobar" type="text/css">
  #infobar {
	position: fixed;
	overflow: hidden;
	top: 0px;
	z-index: 100001;
	width: 100%;
	background: #9E1616;
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
	background: url('<?php echo PL_ADMIN_CSS_URL; ?>white-x.png') no-repeat;
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