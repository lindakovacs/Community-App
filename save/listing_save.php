<?php

require_once('connection.php');

class _PL_Listing_Base {
	protected $listing;

	public function get_id() { return $this->data('id'); }
	public function get_listing_type() { return $this->data('compound_type'); }
	public function get_property_type() { return $this->data('property_type'); }

	public function get_price() { return $this->data('price', 'metadata'); }
	public function get_status() { return $this->data('status', 'metadata'); }
	public function get_available_date() { return $this->data('avail_on', 'metadata'); }

	public function get_sqft() { return $this->data('sqft', 'metadata'); }
	public function get_beds() { return $this->data('beds', 'metadata'); }
	public function get_baths() { return $this->data('baths', 'metadata'); }
	public function get_half_baths() { return $this->data('half_baths', 'metadata'); }

	public function get_description() { return $this->data('desc', 'metadata'); }

	public function get_unit() { return $this->data('unit', 'location'); }
	public function get_address() { return $this->data('address', 'location'); }
	public function get_neighborhood() { return $this->data('neighborhood', 'location'); }
	public function get_locality() { return $this->data('locality', 'location'); }
	public function get_county() { return $this->data('county', 'location'); }
	public function get_region() { return $this->data('region', 'location'); }
	public function get_postal() { return $this->data('postal', 'location'); }
	public function get_country() { return $this->data('country', 'location'); }

	public function get_latitude() { return $this->data('coords', 'location', 0); }
	public function get_longitude() { return $this->data('coords', 'location', 1); }

	public function get_contact_phone() { return $this->data('phone', 'contact'); }

	public function __construct($json = null) {
		if(is_object($json))
			$this->listing = $json;
		else if(is_string($json))
			$this->listing = json_decode($json);
	}

	// accessor for raw(ish) listing data from the server
	public function data($attribute, $group = '', $index = null) {
		if($this->listing == null)
			return null;

		$element = null;
		$array_element = null;
		$group_element = null;
		$element_index = ((is_numeric($index) && $index == intval($index)) ? $index : 0);

		switch($group) {
			case '':
				// look at the listing data's top level, and try plural
				if($element = $this->listing->{$attribute}) break;
				if($array_element = $this->listing->{$attribute . 's'}) break;

			// fall through
			case 'metadata':
			case 'cur_data':
			case 'uncur_data':
				if(($group == 'metadata') && ($this->listing->metadata) && ($element = $this->listing->metadata->{$attribute})) break;
				if(($group != 'uncur_data') && ($this->listing->cur_data) && ($element = $this->listing->cur_data->{$attribute})) break;
				if(($group != 'cur_data') && ($this->listing->uncur_data) && ($element = $this->listing->uncur_data->{$attribute})) break;
				break;

			case 'rets':
			case 'contact':
			case 'location':
			default:
				if(($group_element = $this->listing->{$group}) && ($element = $group_element->{$attribute})) break;
				if($group == 'location' && $attribute == 'coord') {
					$array_element = $group_element->coords;
				}
				break;
		}

		if(is_array($element) && $index !== null) {
			$element = $element[$element_index];
		}
		elseif(is_array($array_element)) {
			$element = $array_element[$element_index];
		}

		if(is_array($element)) {
			$element = sizeof($element);
		}
		elseif(is_object($element)) {
			if(is_scalar($element->id)) {
				$element = $element->id;	// when id exists, give it back to the caller as the attribute value
			}
			elseif($group != '') {
				$element = "($attribute)";	// attribute exists but is a structure (no such attributes currently exist)
			}
			else {
				$element = null;	// don't return top-level groups as if they were structured data attributes
			}
		}

		return $element;
	}

	private static function image_compare($a, $b) {
		return $a->order - $b->order;
	}

	public function image($index = 0) {
		if($this->data('images') && !$this->data('images', '_modified')) {
			if(is_array($this->listing->images)) {
				$this->set('images', '_modified', $this->listing->images);
				usort($this->listing->_modified->images, array(__CLASS__, 'image_compare'));
			}
		}
		if($index >= 0 && $index < $this->data('images', '_modified'))
			return $this->listing->_modified->images[$index]->url;
		return null;
	}

	protected function set($attribute, $group, $value) {
		if($this->listing == null)
			$this->listing = new stdClass();
		if($group && $this->listing->{$group} == null)
			$this->listing->{$group} = new stdClass();

		$group_element = ($group ? $this->listing->{$group} : $this->listing);
		$group_element->{$attribute} = $value;
	}
}

// this is the basic class for data access to property listings on the Placester API
class PL_Property_Listing extends _PL_Listing_Base {

	public function get_purchase_type() { return $this->data('purchase_type', '', 0); }
	public function get_zoning_type() { return $this->data('zoning_type', '', 0); }

	public function get_baths() { return $this->data('baths') . ($this->data('half_baths') ? '+' : ''); }
	public function get_full_baths() { return $this->data('baths'); }

	public function get_address() {
		// use the stashed copy of the concatenated address if it's already available
		if($address = $this->data('address', '_modified'))
			return $address;

		// we want the unit number appended as part of the address
		$address = $this->data('address', 'location');
		if($unit = $this->data('unit', 'location')) {

			// check (imperfectly) to see if unit already appears in the address
			$pos = strpos($address, $unit);
			if($pos === 0) {
				$next = substr($address, strlen($unit), 1);
				if($next != ',' && $next != ' ') $pos = false;	// at the beginning, followed by a comma or space
			}
			elseif($pos === (strlen($address) - strlen($unit))) {
				$prev = substr($address, $pos - 1, 1);
				if($prev != '#' && $prev != ' ') $pos = false;	// or at the end, preceded by a pound sign or space
			}
			else {
				$pos = false;
			}

			// if not, append it...
			if($pos === false) {
				// ...but check for a space to catch values such as 'Apt 6' or 'Suite 100'
				$address .= ' ' . (strpos($unit, ' ') != false || strpos($unit, '#') != false ? '' : '#') . $unit;
			}
		}

		// stash the concatenated address for later, in the listing itself so that we will know if a new listing is read
		$this->set('address', '_modified', $address);
		return $address;
	}

	public function get_full_address() {
		return $this->get_address() . ' ' . $this->get_locality() . ' ' . $this->get_region();
	}

	public function get_listing_date() { return $this->data('lst_dte', 'metadata'); }
	public function get_days_on_market() { return $this->data('dom', 'metadata'); }

	public function get_mls_id() { return $this->data('mls_id', 'rets'); }
	public function get_office_id() { return $this->data('oid', 'rets'); }
	public function get_office_name() { return $this->data('oname', 'rets'); }
	public function get_office_phone() { return $this->data('ophone', 'rets'); }
	public function get_agent_id() { return $this->data('aid', 'rets'); }
	public function get_agent_name() { return $this->data('aname', 'rets'); }
	public function get_agent_phone() { return $this->data('aphone', 'rets'); }

	public function get_contact_phone() {
		$phone = $this->data('phone', 'contact');
		if(empty($phone)) $phone = $this->data('aphone', 'rets');
		if(empty($phone)) $phone = $this->data('ophone', 'rets');
		return $phone;
	}

	public function listing_read($connection, $id) {
		if($result = $connection->GET_LISTING($id)) {
			$this->listing = json_decode($result);
		}
	}
}

// this class is for read/write access to manually entered listings
class PL_Placester_Listing extends PL_Property_Listing {

	public function set_listing_type($type) { $this->set('compound_type', '', $type); }
	public function set_property_type($type) { $this->set('property_type', '', $type); }

	public function set_price($price) { $this->set('price', 'metadata', $price); }
	public function set_status($status) { $this->set('status', 'metadata', $status); }
	public function set_date_available($date) { $this->data('avail_on', 'metadata', $date); }

	public function set_sqft($sqft) { $this->set('sqft', 'metadata', $sqft); }
	public function set_beds($beds) { $this->set('beds', 'metadata', $beds); }
	public function set_baths($baths) { $this->set('baths', 'metadata', $baths); ; }
	public function set_half_baths($half_baths) { $this->set('half_baths', 'metadata', $half_baths); }

	public function set_description($desc) { $this->set('desc', 'metadata', $desc); }

	public function set_unit($unit) { $this->set('unit', 'location', $unit); }
	public function set_address($address) { $this->set('address', 'location', $address); }
	public function set_neighborhood($neighborhood) { $this->set('neighborhood', 'location', $neighborhood); }
	public function set_locality($locality) { $this->set('locality', 'location', $locality); }
	public function set_county($county) { $this->set('county', 'location', $county); }
	public function set_region($region) { $this->set('region', 'location', $region); }
	public function set_postal($postal) { $this->set('postal', 'location', $postal); }
	public function set_country($country) { $this->set('country', 'location', $country); }

	public function set_coords($latitude, $longitude) { $this->set('coords', 'location', array($latitude, $longitude)); }
	public function set_latitude($latitude) { $this->set('coords', 'location', array($latitude, $this->get_longitude())); }
	public function set_longitude($longitude) { $this->set('coords', 'location', array($this->get_latitude(), $longitude)); }

	public function set_contact_phone($phone) { $this->set('phone', 'contact', $phone); }

	protected function listing_encode() {
		$form_data = "compound_type=" . urlencode($this->listing->compound_type);
		$form_data .= "&property_type=" . urlencode($this->listing->property_type);

		if($this->listing && $this->listing->cur_data)
			foreach($this->listing->cur_data as $attribute => $value) {
				$form_data .= "&metadata[$attribute]=" . urlencode($value);
			}

		if($this->listing && $this->listing->uncur_data)
			foreach($this->listing->uncur_data as $attribute => $value) {
				$form_data .= "&metadata[$attribute]=" . urlencode($value);
			}

		if($this->listing && $this->listing->location)
			foreach($this->listing->location as $attribute => $value) {
				if($attribute != "coords")
					$form_data .= "&location[$attribute]=" . urlencode($value);
			}

		return $form_data;
	}

	public function listing_create($connection) {
		if($result = $connection->CREATE_LISTING($this->listing_encode())) {
			$this->listing_read($connection, $result->id);
		}
	}

	public function listing_update($connection) {
		if($result = $connection->UPDATE_LISTING($this->get_id(), $this->listing_encode())) {
			$this->listing_read($connection, $result->id);
		}
	}

//	public static function update ($args = array()) {
//		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('create', 'args')));
//		$update_url = trailingslashit( PL_Config::PL_API_LISTINGS('update', 'request', 'url') ) . $args['id'];
//		$response = PL_HTTP::send_request($update_url, $request, PL_Config::PL_API_LISTINGS('update', 'request', 'type'));
//
//		return $response;
//	}

//	public static function delete ($args = array()) {
//		$config = PL_Config::PL_API_LISTINGS('delete');
//		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, $config['args']));
//		$delete_url = trailingslashit($config['request']['url']) . $request['id'];
//		$response = PL_HTTP::send_request($delete_url, $request, $config['request']['type']);
//		$response = PL_Validate::attributes($response, $config['returns']);
//
//		return $response;
//	}

}