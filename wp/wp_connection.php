<?php


require_once(BUILDER . 'api/connection.php');


class PL_WP_API_Connection extends PL_API_Connection {
	protected $connection_cpt;

	static public function get_default_connection() {
		$api_key = get_option('placester_api_key') ?:
			// Demo API Key
			'7e63514ebfad7608bbe7b4469ab470ecef4dc651099ae06fc1df6807717f0deacd38809e3c314ca09c085125f773a4c7';

		$connection = new self($api_key);
		$connection->enable_attribute(array_keys($connection->get_standard_attributes()));
		$connection->enable_attribute(array_keys($connection->get_custom_attributes()));
		return $connection;
	}

	static public function get_connection($id = null) {
		$connection_search = array('post_type' => 'pdx_connection', 'post_status' => 'publish');
		$connection_cpt = null;

		if($posts = get_posts($connection_search)) {
			$connection_cpt = $posts[0];
			if($connection_cpt->post_content && ($connection = unserialize($connection_cpt->post_content))) {
				$connection->connection_cpt = $connection_cpt;
				return $connection;
			}

			if($api_key = get_post_meta($connection_cpt->ID, 'pl_api_key', true)) {
				$connection = new self($api_key);
				$connection->connection_cpt = $connection_cpt;
				$connection->connection_cpt->post_content = null;

				$connection->read_options();
				$connection->read_attributes();
				return $connection;
			}
		}

		return self::get_default_connection();
	}

	protected function read_options() {
	}

	protected function read_attributes() {
		$this->enable_attribute(array_keys($this->get_standard_attributes()));
		$this->enable_attribute(array_keys($this->get_custom_attributes()));
	}

	protected function update_connection() {
		$result = false;

		if($connection_cpt = $this->connection_cpt) {
			$this->connection_cpt = null;
			$connection_cpt->post_content = serialize($this);
			$this->connection_cpt = $connection_cpt;

			if($id = $connection_cpt->ID)
				$result = wp_update_post(array('ID' => $id, 'post_content' => $connection_cpt->post_content));
		}

		return $result;
	}

	public function __sleep() {
	}

	public function __wakeup() {
	}
}