<?php
/**
 * WordPress Logout from Stormpath
 *
 * Handles responding to logout request by explicitly logging user
 * out from Wordpress by calling wp_logout and clearing auth cookies.
 *
 * Then sets up and redirects to Stormpath to perform the actual logout.
 *
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

Util::debug('msg', 'sp-logout',  'ENTERED');

// Dereference original arguments
// $logout_url = isset($_REQUEST['logout_url']) ? $_REQUEST['logout_url'] : '';
// Util::debug('pr','sp-logout::logout_url', $logout_url);
$redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '';
Util::debug('pr','sp-logout::redirect', $redirect);

// Connect to the stormpath API and get the application object
$spApiKeyFileLocation = get_option('sp_apikey_file_location');
Util::debug('pr', 'sp-logout::spApiKeyFileLocation', $spApiKeyFileLocation);
\Stormpath\Client::$apiKeyFileLocation = $spApiKeyFileLocation;
$spApplicationHref = get_option('sp_application_href');
Util::debug('pr', 'sp-logout::spApplicationHref', $spApplicationHref);
$spApplication = \Stormpath\Resource\Application::get($spApplicationHref);
$spIDSiteLoginURI = get_option('sp_idsite_login_uri');
Util::debug('pr', 'sp-logout::spIDSiteURI', $spIDSiteLoginURI);

// Create callback link use with Stormpath ID Site
$args = array(
	'callbackUri' => $spIDSiteLoginURI,
	'logout' => true,
	'state' => json_encode(array( 'redirect' => $redirect,
					'from' => 'logout'
					), JSON_UNESCAPED_SLASHES)
);
Util::debug('pr','sp-logout::$args',$args);

$idSiteURI = $spApplication->createIdSiteUrl( $args );
Util::debug('pr','sp-logout::Generated idSiteURI',$idSiteURI);

// Force the user to logout
wp_logout();
wp_clear_auth_cookie();

// Redirect to Stormpath ID Site
wp_redirect( $idSiteURI );
exit();

?>

