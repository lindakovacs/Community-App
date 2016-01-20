// get current script
var scripts = document.getElementsByTagName( 'script' );
var thisScriptTag = scripts[ scripts.length - 1 ];

// read the WP related folders
var wp_index = thisScriptTag.src.indexOf('wp-content/');
var wp_folder = thisScriptTag.src.substring(0, wp_index);
var action_url = wp_folder + 'wp-admin/admin-ajax.php';

//get url vars
var url_script_vars = getUrlVars( thisScriptTag.src );
var url_json = JSON.stringify( url_script_vars );

var path_before_wpcontent = thisScriptTag.src.indexOf('/wp-content');
url_script_vars['widget_original_src'] = thisScriptTag.src.substring(0, path_before_wpcontent);
 
// since load is fired later, the load has to get the url vars
// for every script, and not repeatedly the last one.
// closure calling (module pattern)
(function( url_script_vars ) {
	var jsonp_handler = function () {
	   jsonp_event_listener( url_script_vars );
	};
	
	if( window.addEventListener ){
		window.addEventListener( 'load', jsonp_handler );
	} else if( window.attachEvent ) {
		window.attachEvent( 'onload', jsonp_handler );
	} else {
		window.onload = jsonp_handler;
	}
})( url_script_vars );

function jsonp_event_listener( url_vars ) {
	// JSONP approach for new elements creation
	var script = document.createElement('script');
	script.type = "text/javascript";
	script.src = action_url + '?action=handle_widget_script&callback=callback';
	
	// add all variables to the new URL for the remote call
	for( var argument in url_vars ) {
		script.src += '&' + argument + '=' + url_vars[argument];
	}

	document.documentElement.getElementsByTagName('head')[0].appendChild( script );
}

// Get response from the handle_script_insertion_cross_domain() PHP function and prepare the iframe
function callback( json ) {
	if( json.post_id !== undefined) {
		var script_id = 'plwidget-' + json.post_id;
		var script_element = document.getElementById( script_id );
		
		// create the iframe element
		var iframe = document.createElement('iframe');
		iframe.src = json.widget_url;

		iframe.width = json.width;
		iframe.height = json.height;
		
		var css =  document.createElement( 'style' );
		css.innerHTML = json.css || '';
		var widget =  document.createElement( 'div' );
		widget.className = 'pls_embedded_widget_wrapper ' + (json.widget_class || '');
		widget.appendChild(iframe);
		widget.innerHTML = (json.before_widget || '') + widget.innerHTML + (json.after_widget || '');
		widget.insertBefore(css, widget.childNodes[0]);
		
		// insert the iframe next to the script
		script_element.parentNode.insertBefore( widget, script_element );
		
		if( undefined !== json.before_widget ) {
			pl_regex_matcher( json.before_widget );
		}
		if( undefined !== json.after_widget ) {
			pl_regex_matcher( json.after_widget );
		}
	}
	
}

// After appending script elements, you need to evaluate them as well
function pl_regex_matcher( content ) {
	// question mark wildcard character is not rendered properly in all browsers
	// running 2 different regular expressions for script src="..." and <script>...</script>
	var re_src = /<script\b[^>]*src=['"]([^'"]*)['"][^>]*><\/script>/igm;

	var match_src;
	while (match_src = re_src.exec( content ) ) {
	  // full match is in match[0], whereas captured groups are in ...[1], ...[2], etc.
	  // eval( match_src[1] );
	  var scrpt = document.createElement('script');
	  scrpt.src=match_src[1];
	  document.head.appendChild(scrpt);
	}
	
	var re_in = /<script\b[^>]*>([\s\S]*?)<\/script>/igm;

	var match_in;
	while (match_in = re_in.exec( content ) ) {
	  // the content between the opening and the closing tag is match_in[1]
		if( undefined !== match_in[1] && "" != match_in[1] ) {
			eval( match_in[1] );
		}
	}
}

	
function getUrlVars( path ) { // Read a page's GET URL variables and return them as an associative array.
	if( path.indexOf('?') === -1 ) {
		return {};
	}
    var vars = {},
        hash;
    var hashes = path.slice(path.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars[hash[0]] = hash[1];
    }
    return vars;
}
