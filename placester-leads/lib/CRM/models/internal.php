<?php


PL_CRM_Controller::registerCRM(array(
	'id' => 'internal',
	'class' => 'PL_CRM_Internal',
	'display_name' => 'Placester'
));


class PL_CRM_Internal extends PL_CRM_Base{
	protected function getAPIOptionKey() { return 'placester_api_key'; }
	protected function setCredentials (&$handle, &$args) {}
	protected function constructURL ($endpoint) { return ''; }

	public function getAPIKey () { return placester_get_api_key() ? '(authorized)' : '(disconnected)'; }
	public function setAPIKey ($api_key) { return false; }
	public function resetAPIKey () { return false; }

	public function callAPI ($endpoint, $method, $args = array()) { return null; }
	public function createContact ($args) {}
	public function pushEvent ($event_args) {}

	public function contactFieldMeta () {
		static $contactFieldMeta;
		if(empty($contactFieldMeta))
			$contactFieldMeta = array(
				"id" => array(
					"label" => "User",
					"data_format" => "id-lookup",
					"searchable" => false
				),
				"name" => array(
					"label" => "Name",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"company" => array(
					"label" => "Company",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"email" => array(
					"label" => "E-mail",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"phone" => array(
					"label" => "Phone",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"address" => array(
					"label" => "Address",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"locality" => array(
					"label" => "City",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"region" => array(
					"label" => "State",
					"data_format" => "string",
					"searchable" => true,
					"group" => "Search",
					"type" => "text"
				),
				"updated_at" => array(
					"label" => "Last Updated",
					"data_format" => "datetime",
					"searchable" => false
				)
			);

		return $contactFieldMeta;
	}

	public function contactFieldLabels () {
		$fields = $this->contactFieldMeta();
		$labels = array();

		foreach ($fields as $key => $value) {
			$labels[] = $value["label"];
		}

		return $labels;
	}

	private function getIdMap() {
		global $wpdb;
		$map = array();
		$meta_list = $wpdb->get_results( "SELECT user_id, meta_value from wp_usermeta WHERE meta_key = 'placester_api_id'", ARRAY_A );

		if(is_array($meta_list)) foreach($meta_list as $item) {
			if($item['meta_value'])
				$map[$item['meta_value']] = $item['user_id'];
		}

		return $map;
	}

	public function formatContactData ($value, $format) {
		if($format == 'datetime') {
			if($value) {
				$date = new DateTime($value);
				return $date->format("m/d/Y");
			}
			return '';
		}
		else if($format == 'id-lookup') {
			static $id_map;
			if(!isset($id_map)) $id_map = $this->getIdMap();

			if($id = $id_map[$value]) {
				$user = get_userdata($id);
				return $user->user_login;
			}
			return '';
		}
		else {
			return trim($value);
		}
	}

	public function getContacts ($filters = array()) {
		$api_filters = array('sort_by' => 'updated_at', 'sort_type' => 'desc');
		foreach($filters as $name => $value) {
			if(in_array($name, array('name', 'company', 'email', 'phone'))) {
				if(!isset($api_filters['metadata'])) $api_filters['metadata'] = array();
				$api_filters['metadata'][$name . '_match'] = 'like';
				$api_filters['metadata'][$name] = $value;
			}
			else if(in_array($name, array('address', 'locality', 'region'))) {
				if(!isset($api_filters['location'])) $api_filters['location'] = array();
				$api_filters['location'][$name . '_match'] = ($name == 'region' ? 'eq' : 'like');
				$api_filters['location'][$name] = $value;
			}
			else {
				$api_filters[$name] = $value;
			}
		}

		$response = PL_People::details($api_filters);
		$contacts = array('total' => (is_array($response) && $response['total'] ? $response['total'] : 0), 'contacts' => array());
		if(is_array($response) && is_array($response['people'])) {
			foreach ($response['people'] as $lead)
				$contacts['contacts'][] = array(
					'id' => $lead['id'],
					'name' => (is_array($lead['cur_data']) ? $lead['cur_data']['name'] : ''),
					'company' => (is_array($lead['cur_data']) ? $lead['cur_data']['company'] : ''),
					'email' => (is_array($lead['cur_data']) ? $lead['cur_data']['email'] : ''),
					'phone' => (is_array($lead['cur_data']) ? $lead['cur_data']['phone'] : ''),
					'address' => (is_array($lead['location']) ? $lead['location']['address'] : ''),
					'locality' => (is_array($lead['location']) ? $lead['location']['locality'] : ''),
					'region' => (is_array($lead['location']) ? $lead['location']['region'] : ''),
					'updated_at' => $lead['updated_at']
				);
		}
		return $contacts;
	}

	public function getContact ($id) {
		$listings = $searches = null;
		$listing_details = $search_details = array();

		if($id && ($user = get_user_by('login', $id))) {
			$listings = get_user_option('pl_member_listings', $user->ID);
			$searches = get_user_option('pl_member_searches', $user->ID);

			if(is_array($listings) && $listing_data = PL_Listing_Helper::details(array('property_ids' => $listings))) {
				if(is_array($listing_data) && $listing_data['listings'])
					foreach($listing_data['listings'] as $listing)
						$listing_details[$listing['location']['full_address']] = ($listing['cur_data']['status'] ?: '&nbsp;');
			}

			if(is_array($searches)) foreach($searches as $search)
				if($search_data = PL_Permalink_Search::display_saved_search_filters('/' . $search['hash'])) {
					$title = get_the_title(url_to_postid($search['url'])); if(!$title) $title = 'Search';
					$search_details[$title . $search_data] = ($search['timestamp'] ? 'Subscribed' : '&nbsp;');
				}
		}

		$response = array(
			'name' => $id,
			'details' => array(
				'Favorite Listings' . ($listings && is_array($listings) ? (' (' . count($listings) . ')') : '') => $listing_details ? '&nbsp;' : null
			));

		if($listing_details)
			$response['details'] = array_merge($response['details'], $listing_details);

		if($search_details) {
			$response['details']['&nbsp;'] = '&nbsp';
			$response['details']['Favorite Searches (' . count($search_details) . ')'] = '&nbsp';
			$response['details'] = array_merge($response['details'], $search_details);
		}

		return $response;
	}
}
