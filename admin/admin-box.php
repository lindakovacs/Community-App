<?php

class PL_Admin_Box {
	protected $id;
	protected $title;
	protected $content;
	protected $style;

	public function __construct($id, $title, $style = null, $content = null) {
		$this->id = $id;
		$this->title = $title;
		$this->style = $style;
		$this->content = $content;
	}

	public function __toString() {
		ob_start();
		$this->open();
		$this->content();
		$this->close();
		return ob_get_clean();
	}

	public function open() {
	?>
		<div id="<?php echo $this->id; ?>" class="meta-box-sortables ui-sortable" style="<?php echo $this->style; ?>">
			<div id="div" class="postbox ">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle">
					<span><?php echo $this->title; ?></span>
				</h3>
				<div class="inside">
	<?php
	}

	public function content() {
		echo $this->content;
	}

	public function close() {
	?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	<?php
	}
}
