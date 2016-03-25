<style id="pl_infobar" type="text/css">
	#infobar {
		position: fixed;
		overflow: hidden;
		top: 0px;
		z-index: 100001;
		width: 100%;
		background: #9E1616;
		box-shadow: black 0 1px 3px;
	}

	#infobar .msg {
		vertical-align: middle;
		text-align: center;
		font-family: sans-serif;
		font-style: normal;
		font-size: 14px;
		font-weight: bold;
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

<script>
	jQuery(document).ready(function($) {

		$('#toggle_demo').live('click', function(event) {
			event.preventDefault();
			$.post('<?php echo admin_url('admin-ajax.php'); ?>', { action: 'demo_data_off' }, function(response) {
				window.location.reload(true);
			}, 'json');
		});

		$('#infobar .close').live('click', function() {
			$('#infobar, #infobar-buffer').css('display', 'none');
		});

	});
</script>