<?php
if( !class_exists( 'WP_Http' ) )
	include_once( ABSPATH . WPINC. '/class-http.php' );

/**
 * Base on WP_Http so we can extend cUrl to support our certs.
 *
 */
Class PL_HTTP extends WP_Http {

	private static $timeout = 60;
	private static $http = null;

	private static function _get_object () {
		// Enforces a singleton paradigm...
		if ( is_null(self::$http) )
			self::$http = new PL_Http();

		return self::$http;
	}

	public static function add_amp ($str) {
		return ( strlen($str) > 0 ? '&' : '' );
	}

	public static function build_request ($request, $allow_empty_values = false) {
		// What is returned...
		$str = '';

		foreach ($request as $key => $value) {
			/* Value is an array... */
	        if (is_array($value)) {
	        	/* Value-array has is empty... */
	            if (empty($value) && $allow_empty_values) {
	                $str .= self::add_amp($str) . urlencode($key) . '[]=';
	            }
	            
	            /* Value-array HAS values... */
	            foreach ($value as $k => $v) {
	            	// Check if key is an int, set $k_show accordingly...
	            	$k_show = ( is_int($k) ? '' : $k );

	            	/* $v is an array */
					if (is_array($v)) {
						// Different logic for single & multi-value cases...
						$multi = ( count($v) > 1 && count($v) != 0 );

						foreach ($v as $i => $j) {
							$i_show = ( is_int($i) ? '' : $i );
							$dim2 = ( $multi || !empty($i_show) ? '[' . $i_show . ']' : '' );

							$str .= self::add_amp($str) . urlencode($key) . '[' . $k_show . ']' . $dim2 . '=' . urlencode($j);
						}
					}
					/* $v is NOT an array... */
					else {
						$str .= self::add_amp($str) . urlencode($key) . '[' . $k_show . ']=' . urlencode($v);
					}
	            }
	        }
	        /* Value is NOT an array... (i.e., is a scalar) */
	        else {
                $str .= self::add_amp($str) . urlencode($key) . '=' . urlencode($value);
	        }
	    }

	    return $str;
	}

	/*
	 * Sends HTTP request and parses generic elements of API response
	 *
	 * @param string $url
	 * @param array $request
	 * @param string $method
	 * @return array
	 */
	public static function send_request ($url, $request, $method = 'GET', $allow_cache = true, $allow_empty_values = false, $force_return = false, $use_encoding = true) {

		$request_string = self::build_request($request, $allow_empty_values);

		// error_log($url);
		// error_log($request_string);

		if (!$use_encoding) {
			$request_string = urldecode($request_string);
		}

		// error_log(var_export(debug_backtrace(), true));
		// error_log("Endpoint logged as: {$method} {$url}?{$request_string}");

		$wp_http = self::_get_object();

		switch ($method) {
			case 'POST':
			case 'PUT':
				$response = $wp_http->post($url, array('body' => $request_string, 'timeout' => self::$timeout, 'method' => $method));
				if ( is_array($response) && isset($response['body']) ) {
					return json_decode($response['body'], true);
				}
				return false;

			case 'DELETE':
				$response = $wp_http->delete($url, array('body' => $request_string, 'timeout' => self::$timeout));
				if ( is_array($response) && isset($response['body']) ) {
					return json_decode($response['body'], true);
				}
				return false;

			case 'GET':
				$cache = new PL_Cache('http');
				if ($allow_cache && $transient = $cache->get($url . $request_string)) {
					// error_log('From cache:  ' . $url . $request_string);
					return $transient;
				}

				else {
					// NGINX is currently configured with an 8k limit, so 8000 leaves some space for the domain and document
					// -- the Rails Data API v2.1/listings endpoint can handle either GET or POST
					if (strlen($request_string) > 8000) {
						$response = $wp_http->post($url, array('body' => $request_string, 'timeout' => self::$timeout));
					}
					else {
						$response = $wp_http->get($url . '?' . $request_string, array('timeout' => self::$timeout));
					}

					// error_log($url . "?" . $request_string);
					// error_log(var_export($response, true));

					if (is_array($response) && isset($response['response']) && isset($response['response']['code'])) {
						if (($response['response']['code'] == 200 || $force_return) && !empty($response['body'])) {
							$body = json_decode($response['body'], true);
							$cache->save($body, PL_Cache::TTL_HOURS);
							return $body;

						} else {
							// error_log("Server returned: {$response['headers']['status']} {$response['body']}.");
						}
					}
					else {
						// error_log("Error processing response.");
					}
				}
				return false;

			default:
				return false;
		}
	}

	/*
	 * Sends multipart HTTP request and parses generic elements of API response.
	 * Used to upload file
	 * NOTE: will only work if cUrl is installed
	 *
	 * @param string $url
	 * @param array $request
	 * @param string $file_name
	 * @param string $file_mime_type
	 * @param string $file_tmpname
	 * @return array
	 */
	public static function send_request_multipart ($url, $request, $file_name, $file_mime_type, $file_tmpname) {
		
		if (! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
			return array('message' => "You cannot upload pictures because cURL is not installed on your server.\n\nPlease ask your hosting provider to install the PHP cURL module.");
		}

		unset($request['action']);
		if ( !(($wp_upload_dir = wp_upload_dir()) && false===$wp_upload_dir['error']) ) {
			return array('message' => $wp_upload_dir['error']);
		}
		$file_location = trailingslashit($wp_upload_dir['path']) . $file_name;
		// pls_dump($file_location);
		if (!move_uploaded_file($file_tmpname, $file_location)) {
			return array('message' => "Unable to upload the file to $file_location.\n\nIs its parent directory writable by the server?");
		}
		$ssl_verify = apply_filters('https_ssl_verify', true);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ( $ssl_verify === true ) ? 2 : false );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verify );
		
		// Use a local cert to make sure we have a valid one when not on the hosted network...
		if (!defined("HOSTED_PLUGIN_KEY")) {
			curl_setopt($ch, CURLOPT_CAINFO, trailingslashit(PLACESTER_PLUGIN_DIR) . "cacert.pem");
		}

		// Most importantly, cURL assumes @field as file field...
		$post_array = array(
			"file"=>"@".$file_location
		);
		$post_array = array_merge($post_array, $request);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
		$response = curl_exec($ch);
		curl_close($ch);
		if ($response === false) {
			// error_log(var_export(curl_error($ch), true));
			return false;
		}
		
		$o = json_decode($response, true);
		return $o;
	}

	/**
	 * Override the WP_Http version so we can insert our cURL class
	 * @see WP_Http::_get_first_available_transport()
	 */
	public function _get_first_available_transport ($args, $url = null) {
		$request_order = array( 'pl_curl', 'streams', 'fsockopen' );

		// Loop over each transport on each HTTP request looking for one which will serve this request's needs
		foreach ( $request_order as $transport ) {
			$class = 'WP_HTTP_' . $transport;

			// Check to see if this transport is a possibility, calls the transport statically
			if ( !call_user_func( array( $class, 'test' ), $args, $url ) )
				continue;

			return $class;
		}

		return false;
	}

	private function delete ($url, $args = array()) {
		$defaults = array('method' => 'DELETE');
		$r = wp_parse_args( $args, $defaults );
		return $this->request($url, $r);
	}
}


/**
 * Modified from WP_Http_Curl to support local certificate and delete
 *
 * Requires the cURL extension to be installed.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7
 */
class WP_Http_PL_Curl {

	/**
	 * Temporary header storage for use with streaming to a file.
	 *
	 * @since 3.2.0
	 * @access private
	 * @var string
	 */
	private $headers = '';

	/**
	 * Send a HTTP request to a URI using cURL extension.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url
	 * @param str|array $args Optional. Override the defaults.
	 * @return array 'headers', 'body', 'response', 'cookies' and 'filename' keys.
	 */
	public function request ($url, $args = array()) {

		$defaults = array(
			'method' => 'GET', 'timeout' => 60,
			'redirection' => 5, 'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(), 'body' => null, 'cookies' => array()
		);

		$r = wp_parse_args( $args, $defaults );

		if ( isset($r['headers']['User-Agent']) ) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset($r['headers']['User-Agent']);
		} else if ( isset($r['headers']['user-agent']) ) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset($r['headers']['user-agent']);
		}

		// Construct Cookie: header if any cookies are set.
		WP_Http::buildCookieHeader( $r );

		$handle = curl_init();

		// cURL offers really easy proxy support.
		$proxy = new WP_HTTP_Proxy();

		if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {

			curl_setopt( $handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
			curl_setopt( $handle, CURLOPT_PROXY, $proxy->host() );
			curl_setopt( $handle, CURLOPT_PROXYPORT, $proxy->port() );

			if ( $proxy->use_authentication() ) {
				curl_setopt( $handle, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
				curl_setopt( $handle, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}

		$is_local = isset($r['local']) && $r['local'];
		$ssl_verify = isset($r['sslverify']) && $r['sslverify'];
		if ( $is_local )
			$ssl_verify = apply_filters('https_local_ssl_verify', $ssl_verify);
		elseif ( ! $is_local )
			$ssl_verify = apply_filters('https_ssl_verify', $ssl_verify);

		// CURLOPT_TIMEOUT and CURLOPT_CONNECTTIMEOUT expect integers. Have to use ceil since
		// a value of 0 will allow an unlimited timeout.
		$timeout = (int) ceil( $r['timeout'] );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $handle, CURLOPT_URL, $url);
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, ( $ssl_verify === true ) ? 2 : false );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, $ssl_verify );
		curl_setopt( $handle, CURLOPT_USERAGENT, $r['user-agent'] );
		// use a local cert to make sure we have a valid one
		curl_setopt( $handle, CURLOPT_CAINFO, trailingslashit(PLACESTER_PLUGIN_DIR) . 'cacert.pem');
		// The option doesn't work with safe mode or when open_basedir is set, and there's a
		// bug #17490 with redirected POST requests, so handle redirections outside cURL.
		curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, false );

		switch ( $r['method'] ) {
			case 'HEAD':
				curl_setopt( $handle, CURLOPT_NOBODY, true );
				break;
			case 'POST':
				curl_setopt( $handle, CURLOPT_POST, true );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
			case 'PUT':
				curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, 'PUT' );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
			case 'DELETE':
	            curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body']);
	            curl_setopt( $handle, CURLOPT_HEADER, 0);
				break;
			default:
				curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, $r['method'] );
				if ( ! is_null( $r['body'] ) )
					curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
		}

		if ( true === $r['blocking'] )
			curl_setopt( $handle, CURLOPT_HEADERFUNCTION, array( $this, 'stream_headers' ) );

		curl_setopt( $handle, CURLOPT_HEADER, false );

		// If streaming to a file open a file handle, and setup our cURL streaming handler
		if ( $r['stream'] ) {
			if ( ! WP_DEBUG )
				$stream_handle = @fopen( $r['filename'], 'w+' );
			else
				$stream_handle = fopen( $r['filename'], 'w+' );
			if ( ! $stream_handle )
				return new WP_Error( 'http_request_failed', sprintf( __( 'Could not open handle for fopen() to %s' ), $r['filename'] ) );
			curl_setopt( $handle, CURLOPT_FILE, $stream_handle );
		}

		if ( !empty( $r['headers'] ) ) {
			// cURL expects full header strings in each element
			$headers = array();
			foreach ( $r['headers'] as $name => $value ) {
				$headers[] = "{$name}: $value";
			}
			curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
		}

		if ( $r['httpversion'] == '1.0' )
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		else
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		// Cookies are not handled by the HTTP API currently. Allow for plugin authors to handle it
		// themselves... Although, it is somewhat pointless without some reference.
		do_action_ref_array( 'http_api_curl', array(&$handle) );

		// We don't need to return the body, so don't. Just execute request and return.
		if ( ! $r['blocking'] ) {
			curl_exec( $handle );
			curl_close( $handle );
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		$theResponse = curl_exec( $handle );

		$theBody = '';
		$theHeaders = WP_Http::processHeaders( $this->headers );

		if ( strlen($theResponse) > 0 && ! is_bool( $theResponse ) ) // is_bool: when using $args['stream'], curl_exec will return (bool)true
			$theBody = $theResponse;

		// If no response
		if ( 0 == strlen( $theResponse ) && empty( $theHeaders['headers'] ) ) {
			if ( $curl_error = curl_error( $handle ) )
				return new WP_Error( 'http_request_failed', $curl_error );
			if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array( 301, 302 ) ) )
				return new WP_Error( 'http_request_failed', __( 'Too many redirects.' ) );
		}

		$this->headers = '';

		$response = array();
		$response['code'] = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		$response['message'] = get_status_header_desc($response['code']);

		curl_close( $handle );

		if ( $r['stream'] )
			fclose( $stream_handle );

		// See #11305 - When running under safe mode, redirection is disabled above. Handle it manually.
		if ( ! empty( $theHeaders['headers']['location'] ) && 0 !== $r['_redirection'] ) { // _redirection: The requested number of redirections
			if ( $r['redirection']-- > 0 ) {
				return $this->request( WP_HTTP::make_absolute_url( $theHeaders['headers']['location'], $url ), $r );
			} else {
				return new WP_Error( 'http_request_failed', __( 'Too many redirects.' ) );
			}
		}

		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($theHeaders['headers']) )
			$theBody = WP_Http_Encoding::decompress( $theBody );

		return array( 'headers' => $theHeaders['headers'], 'body' => $theBody, 'response' => $response, 'cookies' => $theHeaders['cookies'], 'filename' => $r['filename'] );
	}

	/**
	 * Grab the headers of the cURL request
	 *
	 * Each header is sent individually to this callback, so we append to the $header property for temporary storage
	 *
	 * @since 3.2.0
	 * @access private
	 * @return int
	 */
	private function stream_headers ($handle, $headers) {
		$this->headers .= $headers;
		return strlen( $headers );
	}

	/**
	 * Whether this class can be used for retrieving an URL.
	 *
	 * @static
	 * @since 2.7.0
	 *
	 * @return boolean False means this class can not be used, true means it can.
	 */
	public static function test ($args = array()) {
		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) )
			return false;

		$is_ssl = isset( $args['ssl'] ) && $args['ssl'];

		if ( $is_ssl ) {
			$curl_version = curl_version();
			if ( ! (CURL_VERSION_SSL & $curl_version['features']) ) // Does this cURL version support SSL requests?
				return false;
		}

		return apply_filters( 'use_curl_transport', true, $args );
	}
}
