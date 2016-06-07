<?php 

PL_Cache::init();
class PL_Cache {

	const TTL_LOW  = 1800; // 30 minutes
	const TTL_HOUR = 3600; // 1 hour
	const TTL_HOURS = 10800; // 3 hours
	const TTL_MID = 43200; // 12 hours
	const TTL_DAY = 86400; // 24 hours
	const TTL_HIGH = 172800; // 48 hours

	private static $cache_offset;

	protected $group;
	protected $group_offset;
	protected $always_cache;

	protected $transient_id = false;

	public static function init() {
		if(isset($_REQUEST['clear_cache']))
			self::invalidate();

		else if(isset($_REQUEST['clear_cache_group']))
			self::invalidate($_REQUEST['clear_cache_group']);

		add_action('switch_theme', array(__CLASS__, 'invalidate'));
		add_action('wp_trash_post', array(__CLASS__, 'invalidate'));
		add_action('untrash_post', array(__CLASS__, 'invalidate'));
	}

	function __construct ($group = 'general', $always_cache = false) {
		if(!isset(self::$cache_offset)) {
			self::$cache_offset = get_option('pl_cache_offset', null);
			if(!self::$cache_offset) update_option('pl_cache_offset', self::$cache_offset = rand());
		}

		$this->group = $group = preg_replace("/\W/", "_", strtolower($group));
		$this->always_cache = $always_cache;

		$this->group_offset = get_option('pl_' . $group . '_cache_offset', null);
		if(!$this->group_offset) update_option('pl_' . $group . '_cache_offset', $this->group_offset = rand());
	}

	public function get() {
		$cache_escape = (isset($_GET['no_cache']) || isset($_POST['no_cache']));
		$allow_caching = self::allow_caching() || $this->always_cache;
		if (!$allow_caching || $cache_escape)
			return false;

		$args = func_get_args();
		$this->transient_id = self::build_cache_key($this->group, $args);

		$transient = get_transient($this->transient_id);
		if(is_array($transient)
			&& isset($transient['cache_offset']) && $transient['cache_offset'] == self::$cache_offset
			&& isset($transient['group_offset']) && $transient['group_offset'] == $this->group_offset)
			return $transient['value'];

        return false;
	}

	public function save ($result, $duration = 172800) {
		if($this->transient_id && (self::allow_caching() || $this->always_cache)) {
			$transient = array('value' => $result,
				'cache_offset' => self::$cache_offset, 'group_offset' => $this->group_offset);
			return set_transient($this->transient_id, $transient, $duration);
		}
		return false;
	}

	public function delete() {
		$args = func_get_args();
		$cache_key = self::build_cache_key($this->group, $args);
		return delete_transient($cache_key);
	}

	public function flush() {
		if(++$this->group_offset == PHP_INT_MAX)
			$this->group_offset = 1;

		update_option('pl_' . $this->group . '_cache_offset', $this->group_offset);
	}

	public function flush_all() {
		if(++self::$cache_offset == PHP_INT_MAX)
			self::$cache_offset = 1;

		update_option('pl_cache_offset', self::$cache_offset);
	}


	public static function allow_caching() {
		return (!is_user_logged_in() && defined('PL_ENABLE_CACHE'));
	}

	public static function build_cache_key ($group, $func_args = array()) {
		$key = RawToShortMD5(MD5_85_ALPHABET, md5(http_build_query($func_args), true));
		$key = 'pl_' . $group . '_' . $key;
		return $key;
	}

	public static function invalidate($group = null) {
		if($group) {
			$instance = new self($group);
			$instance->flush();
		}
		else {
			$instance = new self(); // to force a read of the cache offsets
			$instance->flush_all();
		}
	}

}


// flush our cache when admins save option pages or configure widgets
add_action('init', 'pl_options_save_flush');
function pl_options_save_flush() {
	$doing_ajax = (defined('DOING_AJAX'));
	$editing_widgets = (isset($_POST['savewidgets']));
	if($_SERVER['REQUEST_METHOD'] == 'POST' && is_admin() && (!$doing_ajax || $editing_widgets)) {
		PL_Cache::invalidate();
	}
}


define('MD5_24_ALPHABET', '0123456789abcdefghijklmnopqrstuvwxyzABCDE');
define('MD5_85_ALPHABET', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()"|;:?\/\'[]<>');

function RawToShortMD5($alphabet, $raw) {
  $result = '';
  $length = strlen(DecToBase($alphabet, 2147483647));

  foreach (str_split($raw, 4) as $dword) {
    $dword = ord($dword[0]) + ord($dword[1]) * 256 + ord($dword[2]) * 65536 + ord($dword[3]) * 16777216;
    $result .= str_pad(DecToBase($alphabet, $dword), $length, $alphabet[0], STR_PAD_LEFT);
  }

  return $result;
}

function DecToBase($alphabet, $dword) {
  $rem = fmod($dword, strlen($alphabet));
  if ($dword < strlen($alphabet)) {
    return $alphabet[(int) $rem];
  } else {
    return DecToBase($alphabet, ($dword - $rem) / strlen($alphabet)).$alphabet[(int) $rem];
  }
}