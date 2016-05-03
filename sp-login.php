<?php
/**
 * WordPress Login from Stormpath
 *
 * Handles responding to login request directly,
 * or indirectly if from a logout request.
 *
 * @package sp-auth
 */
/*
Copyright 2016  Jordan Olin and OLINSolutions, Inc.  (email : jordan@olinsolutions.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/** Make sure that the WordPress bootstrap has run before continuing. */
require( '../../../wp-load.php' );
require_once( 'classes/util.php' );
require_once( 'classes/vendor/autoload.php');

/**
 * Generates a redirectable URI to the Stormpath system to perform the login
 *
 * @param \Stormpath\Resource\Application $spApplication - An initialized Stormpath Application resource
 * @param string $redirect - The URI to capture and send the user to after the login has been performed
 *
 * @return string URI to redirect for login and authentication
 *
 */
function create_stormpath_uri($spApplication, $redirect) 
{
	// Get the destination URI
	$spIDSiteLoginURI = get_option('sp_idsite_login_uri');
	Util::debug('pr', 'sp-login::create_stormpath_uri - spIDSiteLoginURI', $spIDSiteLoginURI);
	$args = array(
		'callbackUri' => $spIDSiteLoginURI,
		'state' => json_encode(array( 
						'redirect' => $redirect,
						'from' => 'login'
						), JSON_UNESCAPED_SLASHES)
	);
	Util::debug('pr','sp-login::create_stormpath_uri - $args', $args);
	$idSiteURI = $spApplication->createIdSiteUrl( $args );
	Util::debug('pr','sp-login::create_stormpath_uri - Generated idSiteURI', $idSiteURI);
	return $idSiteURI;
}

/**
 * Fixes up the URI to send the user to upon successful completion of a login.  Includes adding the
 * WordPress site fully qualified URL if needed.
 *
 * @param string $redirect - The URI to fixup, if empty, then the site_url() is returned.
 *
 * @return string URI to use in redirecting after successful login
 *
 */
function clean_redirect_uri($redirect) 
{
	$redirect_to = $redirect;
	if ($redirect_to === '')
		$redirect_to = site_url();
	else {
		// If the requeseted redirect does not start with a protocol, then add the site_url
		$pos = strpos($redirect_to, 'http');
		if ( (false === $pos) || ($pos > 0) )
			$redirect_to = site_url($redirect_to);
	}
	return $redirect_to;
}

//
// Main
//

Util::debug('msg', 'sp-login',  'ENTERED');

// Redirect to https login if forced to use SSL
if ( force_ssl_admin() && ! is_ssl() ) {
	if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
		Util::debug('msg', 'sp-login',  'REDIRECTING TO FORCE SSL');
		wp_redirect( set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
		exit();
	} else {
		Util::debug('msg', 'sp-login',  'REDIRECTING TO FORCE SSL');
		wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		exit();
	}
}

// Get the formal request URI
$requestURI = $_SERVER['REQUEST_URI'] ;
Util::debug('pr','sp-login::requestURI',$requestURI);

// Initialize Stormpath API
$spApiKeyFileLocation = get_option('sp_apikey_file_location');
Util::debug('pr', 'sp-login::spApiKeyFileLocation', $spApiKeyFileLocation);
\Stormpath\Client::$apiKeyFileLocation = $spApiKeyFileLocation;
$spApplicationHref = get_option('sp_application_href');
Util::debug('pr', 'sp-login::spApplicationHref', $spApplicationHref);
$spApplication = \Stormpath\Resource\Application::get($spApplicationHref);

// If a user is currently logged-in, force them out
if (is_user_logged_in()) {
	$user = wp_get_current_user();
	wp_logout();
	wp_clear_auth_cookie();
	Util::debug('pr', 'sp-login - Logged out User with ID', $user->ID);
} else {
	Util::debug('msg', 'sp-login', 'No user currently logged in');
}

// Look for presence of the 'jwtResponse' variable to know
// whether we are getting a callback from Stormpath
$hasjwt = isset($_REQUEST['jwtResponse']);
Util::debug('pr', 'sp-login::$hasjwt', $hasjwt);

// If no jwt, we are in the first phase of login processing.
if (!$hasjwt) {

	// Redirect is part of the request URI
	$redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '';
	Util::debug('pr','sp-login::processing pre-login call - redirect', $redirect);
	
	// Setup and create Stormpath URI
	$stormpath_uri = create_stormpath_uri( $spApplication, $redirect );
	
	// Redirect back to stormpath to do the actual login and then return here
	wp_redirect( $stormpath_uri );
	exit();
	
}

/**
*	If we are here, we know that we were called by Stormpath
*	Need to determine whether we are here as a result of a login, or from a successful logout
*	In order to do that we need to begin to parse the JWT and evaluate the contents of the 'state' variable
*	which contains the 'from' flag to identify whether we are responding to a login or logout redirect
*/

// Parse the JWT that is encoded as part of the request URI
try {
	$response = $spApplication->handleIdSiteCallback($requestURI);
} catch (\Exception $re)
{
	$err = new WP_Error( 'denied', __("Stormpath Authenticator::ERROR:Code=".$re->getCode().", Message=".$re->getMessage().", Exception=".$re) );
	Util::debug('pr', 'sp-login::ResourceError calling $spApplication->handleIdSiteCallback($requestURI)', $err);
	wp_redirect( site_url() );
}

Util::debug('msg','sp-login - Stormpath $response object found?', isset($response)?'Yes':'No');
$status = $response->status;
Util::debug('pr','sp-login - Stormpath status',$status);
$state =  json_decode($response->state, true);
Util::debug('pr','sp-login - Stormpath $state', $state);
$from = $state['from'];
Util::debug('pr','sp-login - $from', $from);
$redirect = $state['redirect'];
Util::debug('pr','sp-login - $redirect', $redirect);
$redirect = clean_redirect_uri($redirect);
Util::debug('pr','sp-login - Cleaned $redirect', $redirect);

/**
*	If the call was from a 'logout' request, we need to
*	check to see if the behavior is to immediately prompt to
*	login again and call back into Stormpath to continue the login process.
*	If not, we simply re-direct back to the requested page
*/
if ($from === 'logout') {

	// The redirect address is already setup
	// Now , should we login right away now that the logout is complete?
	if (get_option('sp_login_after_logout') === 'true')
		// Setup and create Stormpath URI
		$redirect_to = create_stormpath_uri( $spApplication, $redirect );
	else
		// Nope, just go back to original page.
		$redirect_to = $redirect;
	
	// Redirect to either the caller, or stormpath to do the actual login and then return
	Util::debug('pr', 'sp-login - From logout, redirecting to', $redirect_to);
	wp_redirect( $redirect_to );
	exit();
	
}

// If we are here, the user has made it through Stormpath login process, 
// so complete the Wordpress side of the equation

$action = 'login';
$errors = new WP_Error();

if ( defined( 'RELOCATE' ) && RELOCATE ) { // Move flag is set
	if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
		$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );

	$url = dirname( set_url_scheme( 'http://' .  $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ) );
	if ( $url != get_option( 'siteurl' ) )
		update_option( 'siteurl', $url );
}

//Set a cookie now to see if they are supported by the browser.
$secure = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, $secure );
if ( SITECOOKIEPATH != COOKIEPATH )
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
$interim_login = isset($_REQUEST['interim-login']);

// Dereference the account and other interesting things from the JWT
$account = $response->account;
Util::debug('pr','sp-login::account->href',$account->href);
$isNew = $response->isNew;
Util::debug('pr','sp-login::isNew',$isNew);

// External user exists, try to load the user info from the WordPress user table
$user = Util::lookupWPUser($account);

// Does the user exist in WordPress?
if( $user->ID == 0 ) {
	// Create a new proxy user in the WP user system
	$user = Util::createWPUser($account);
} 

// If the user wants ssl but the session is not ssl, force a secure cookie.
$secure_cookie = false;
if ( !force_ssl_admin() ) {
	if ( get_user_option('use_ssl', $user->ID) ) {
		$secure_cookie = true;
		force_ssl_admin(true);
		Util::debug('msg','sp_login', 'Forcing SSL and secure cookie based on use_ssl present in user options.');
	}
}

// Redirect to https if going to admin and user wants ssl
if ( $secure_cookie && false !== strpos($redirect, 'wp-admin') )
	$redirect_to = preg_replace('|^http://|', 'https://', $redirect);
else
	$redirect_to = $redirect;
Util::debug('pr','sp_login - After initially setting $redirect_to', $redirect_to);

$reauth = empty($_REQUEST['reauth']) ? false : true;

/** The following two statements were pulled from wp_signon **/
wp_set_auth_cookie($user->ID, true /*$credentials['remember']*/, $secure_cookie);

/**
 * Fires after the user has successfully logged in.
 *
 * @since 1.5.0
 *
 * @param string  $user_login Username.
 * @param WP_User $user       WP_User object of the logged-in user.
 */
do_action( 'wp_login', $user->user_login, $user );

if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
	if ( headers_sent() ) {
		$err = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
			__( 'https://codex.wordpress.org/Cookies' ), __( 'https://wordpress.org/support/' ) ) );
		Util::debug('pr','sp_login - cookie error', $err);
	} elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
		// If cookies are disabled we can't log in even with a valid user+pass
		$err = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
			__( 'https://codex.wordpress.org/Cookies' ) ) );
		Util::debug('pr','sp_login - cookie error', $err);
	}
	Util::debug('pr','sp_login - After wp_set_auth_cookie, $_COOKIE[ LOGGED_IN_COOKIE ] is empty, $user', $user);
} else {
	Util::debug('pr','sp_login - After wp_set_auth_cookie, $_COOKIE: ', $_COOKIE);
}

if ( !is_wp_error($user) && !$reauth ) {
	if ( $interim_login ) {
		$message = '<p class="message">' . __('You have logged in successfully.') . '</p>';
		$interim_login = 'success';
		Util::debug('msg', 'sp-login',  'LEAVING - Was in interim_login, $message='.$message);
		exit;
	}

	if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
		// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
		if ( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin( $user->ID ) )
			$redirect_to = user_admin_url();
		elseif ( is_multisite() && !$user->has_cap('read') )
			$redirect_to = get_dashboard_url( $user->ID );
		elseif ( !$user->has_cap('edit_posts') )
			$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();

		Util::debug('msg', 'sp-login',  'LEAVING - Before call to wp_redirect('.$redirect_to.') and exit()');
		wp_redirect( $redirect_to );
		exit();
	}
	
	// Perform the redirect
	Util::debug('msg', 'sp-login',  'LEAVING - Before call to wp_safe_redirect('.$redirect_to.') and exit()');
	wp_safe_redirect($redirect_to);
	exit();
}

// Clear any stale cookies.
if ( $reauth )
	wp_clear_auth_cookie();

Util::debug('msg', 'sp-login',  'LEAVING - ERRORS FOUND - Before call to wp_safe_redirect('.$redirect_to.') and exit()');
wp_safe_redirect($redirect_to);
exit();
?>

