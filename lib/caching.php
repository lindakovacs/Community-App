<?php 

PL_Cache::init();

class PL_Cache {

	const TTL_LOW  = 1800; // 30 minutes
	const TTL_HOUR = 3600; // 1 hour
	const TTL_HOURS = 10800; // 3 hours
	const TTL_MID = 43200; // 12 hours
	const TTL_DAY = 86400; // 24 hours
	const TTL_HIGH = 172800; // 48 hours

	private static $key_prefix = 'pl_';

	private static $log_enabled = false;
	const LOG_PATH = "~/dev/wp_cache.log";
	
	private static $offset_key = 'pl_cache_offset';
	public static $offset = 0;

	public $group; // set by constructor...
	public $transient_id = false;
	private $always_cache = false;

	function __construct ($group = 'general', $always_cache = false) {
		self::$offset = get_option(self::$offset_key, 0);
		$this->group = preg_replace( "/\W/", "_", strtolower($group) );
		$this->always_cache = $always_cache;
	}

	public static function init() {
		// Allow cache to be cleared by going to url like http://example.com/?clear_cache
		if (isset($_GET['clear_cache']) || isset($_POST['clear_cache'])) {
			// style-util.php calls its PLS_Style::init() immediately so this can't be tied to a hook
			self::invalidate();
		}

		// This is VITAL for caching to work properly...
		// add_action( 'w3tc_register_fragment_groups', array(__CLASS__, 'register_fragment_groups') );

		// Invalidate cache when site's theme is changed...
		add_action('switch_theme', array(__CLASS__, 'invalidate'));
		
		// Flush cache when posts are trashed or untrashed -pek
		add_action('wp_trash_post', array(__CLASS__, 'invalidate'));
		add_action('untrash_post', array(__CLASS__, 'invalidate'));
	}

/*
 * Object functions (i.e., can only be called on individual instances of this class...)
 */

	public function get() {
		// Backdoor to ignore the cache completely...
		$cache_escape = ( isset($_GET['no_cache']) || isset($_POST['no_cache']) );

		// Allow for the "always_cache" breaker...
		$allow_caching = self::allow_caching() || $this->always_cache;
		// error_log($allow_caching ? 'true' : 'false');

		// Do not cache if it is not allowed OR if escape mechanism is set...
		if ( !$allow_caching || $cache_escape) {
			// error_log("Cache bypassed...");
			// error_log("group: {$this->group}");
			// error_log("Is AJAX: " . (defined('DOING_AJAX') ? "YES" : "NO"));
			// error_log("Is user logged in: " . (is_user_logged_in() ? "YES" : "NO"));
			// error_log("Is admin: " . (is_admin() ? "YES" : "NO") . "\n");
			// error_log("key/id/args: " . var_export(func_get_args(), true) . "\n");
			return false;
		}
		// error_log("CACHING!!!");

		// Create and store item's cache key...
		$args = func_get_args();
		$this->transient_id = self::build_cache_key($this->group, $args);

		// error_log(var_export($args, true));
		// error_log($this->transient_id);

        $transient = get_transient($this->transient_id);
        // Return as is -- if transient doesn't exist, it's up to the caller to check...
        return $transient;
	}

	public function save ($result, $duration = 172800) {
		// Make sure the transient_id was properly set in the "get" call, and that caching is permitted...
		if ( $this->transient_id && (self::allow_caching() || $this->always_cache)) {
			set_transient($this->transient_id, $result, $duration);
		}
	}

/*
 * Core class/static function library...
 */

	public static function allow_caching() {
		// Caching is off by default, but enabled within our hosting environment
		// For now, we disable caching for admins and other authenticated users
		return ( !is_user_logged_in() && defined('PL_ENABLE_CACHE') );
	}

	public static function build_cache_key ($group, $func_args = array()) {
		// Create a hash key 
		$arg_hash = rawToShortMD5( MD5_85_ALPHABET, md5(http_build_query($func_args), true) );
		$key = self::$key_prefix . $group . '_' . self::$offset . '_' . $arg_hash;

		return $key;
	}

	public static function clear() {
		// TODO: Allow user to clear by group...

		//manually flush a blog specific group.
		// w3tc_fragmentcache_flush_group('my_plugin_');

		//manually flush a network wide group
		// w3tc_fragmentcache_flush_group('my_plugin_global_', true);
	}

	public static function delete ($group, $func_args = array()) {
		$cache_key = build_cache_key($group, $func_args);
		$result = delete_transient($cache_key);
		return $result;
	}

	/* This call will delete ALL site transients (i.e., everything in the current site's cache)... */
	public static function invalidate() {
		// Retrieve the latest offset value 
		$new_offset = get_option(self::$offset_key, 0);
		$new_offset += 1;

		// Reset offset if value is high enough...
		if ($new_offset > 99) {
			$new_offset = 0;
		}

		// Update the option, then update the static variable
		update_option(self::$offset_key, $new_offset);
		self::$offset = $new_offset;
	}

/*
 * Cache logging functionality...
 */

	private static function cache_log ($msg) {
		if ( !empty($msg) && self::$log_enabled ) {
			$msg = '[' . date("M-d-Y g:i A T") . '] ' . $msg . "\n";
			error_log($msg, 3, self::LOG_PATH);
		}
	}

	private static function log_trace ($trace) {
		// Print the file, the function in that file, and the specific line where the given caching call 
		// is being made from to the cache log...
		if ( isset($trace[1]) ) {
			$file = str_replace('/Users/iantendick/Dev/wordpress/', '', @$trace[1]['file']);
			$caller = $file . ', ' . @$trace[2]['function'] . ', ' . @$trace[1]['line'];
			self::cache_log('Caller: ' . $caller);
		}
	}

/*
 * Mix-in W3TC fragment caching functionality...
 */	

	/* Register the groups we will use for caching... */
	// public static function register_fragment_groups() {
	// 	$blog_groups = array();
	// 	$network_groups = array();

	// 	// Blog specific group and an array of actions that will trigger a flush of the group
	// 	foreach ( $blog_groups as $group => $actions_arr ) {
	// 		w3tc_register_fragment_group('pl_{$group}_', $actions_arr);
	// 	}

	// 	//If using MultiSite Network/site wide specific group and an array of actions that will trigger a flush of the group
	// 	foreach ( $network_groups as $group => $actions_arr ) {
	// 		w3tc_register_fragment_group_global('{$group}_network_', $actions_arr);
	// 	}
	// }

//end class
}

// Flush our cache when admins save option pages or configure widgets
add_action('init', 'PL_Options_Save_Flush');
function PL_Options_Save_Flush() {
	// Check if options are being saved
	$doing_ajax = ( defined('DOING_AJAX') && DOING_AJAX );
	$editing_widgets = ( isset($_GET['savewidgets']) || isset($_POST['savewidgets']));
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_admin() && (!$doing_ajax || $editing_widgets)) {
		// Flush the entire blog/site's cache...
		PL_Cache::invalidate();
	}
}

/* Functions for converting between notations and short MD5 generation.
 * No license (public domain) but backlink is always welcome :)
 * By Proger_XP. http://proger.i-forge.net/Short_MD5
 * Usage: rawToShortMD5(MD5_85_ALPHABET, md5($str, true))
 * (passing true as the 2nd param to md5 returns raw binary, not a hex-encoded 32-char string)
 */
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