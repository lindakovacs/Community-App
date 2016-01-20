<?php

/* Start session and load library. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

/* Build TwitterOAuth object with client credentials. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
 
/* Get temporary credentials. */
$request_token = $connection->getRequestToken(OAUTH_CALLBACK);

pls_log_socials( 'logins.txt', CONSUMER_KEY  );
pls_log_socials( 'logins.txt', CONSUMER_SECRET );
pls_log_socials( 'logins.txt', var_export( $connection, true )  );
pls_log_socials( 'logins.txt', var_export( $request_token, true )  );

/* Save temporary credentials to session. */
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

$current_user_id = get_current_user_id();
// SESSION might be destroyed for step 3
// set_transient('pls_twitter_oauth_transient_' . $current_user_id, $request_token, time() + 3600 );

pls_log_socials( 'logins.txt', var_export( $_SESSION, true )  );

/* If last connection failed don't display authorization link. */
switch ($connection->http_code) {
  case 200:
    /* Build authorize URL and redirect user to Twitter. */
    $url = $connection->getAuthorizeURL($token);
    wp_redirect( $url );
    exit;
    // header('Location: ' . $url); 
    break;
  default:
    /* Show notification if something went wrong. */
    echo 'Could not connect to Twitter. Refresh the page or try again later.';
}
