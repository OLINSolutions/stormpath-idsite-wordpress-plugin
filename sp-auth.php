<?php
/**
 * @package sp-auth
 * @version 0.2
 */
/**
Plugin Name: SP Auth
Plugin URI: https://github.com/OLINSolutions/stormpath-idsite-wordpress-plugin
Description: Stormpath authorization plugin
Version: 0.2
Author: Olinsolutions
Author URI: http://olinsolutions.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: sp-auth
*/
/**
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


// Require the Stormpath authenticator, utility functions
require_once 'classes/util.php';
require_once 'classes/sp-authenticator.php';


if(!class_exists('SP_Auth')) {

	class SP_Auth
	{
	
		/**
		* @var spAuthenticator - An instance of the SP_Authenticator class
		*/
		private $spAuthenticator;
		
        /**
         * Construct the SP_Auth plugin object
         */
        public function __construct()
        {
        	$msg = Util::getCallingFunction();
        	Util::debug('msg', 'SP_Auth::__construct - ENTERED', $msg);
        	
            // register the administration init and menu actions
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));

			// Create an SP_Authenticator			
			if(class_exists('SP_Authenticator')) {
				$this->spAuthenticator = new SP_Authenticator();
	        	Util::debug('msg', 'SP_Auth::__construct', 'Created SP_Authenticator instance');
			}

			// Register the authenticate filter
			add_filter( 'authenticate', array(&$this, 'authenticate'), 10, 3 );

        	Util::debug('msg', 'SP_Auth::__construct', 'LEAVING');
        } // END public function __construct
    
		/**
		 * Initialize the Stormpath related custom settings
		 */     
		public function init_settings()
		{
			// register the settings for this plugin
			register_setting('sp_auth-group', 'sp_apikey_file_location');
			register_setting('sp_auth-group', 'sp_directory_href');
			register_setting('sp_auth-group', 'sp_application_href');
			register_setting('sp_auth-group', 'sp_idsite_login_uri');
			register_setting('sp_auth-group', 'sp_idsite_logout_uri');
			register_setting('sp_auth-group', 'sp_login_after_logout');
			
		} // END public function init_custom_settings()

		/**
 		* Called when WP's admin_init action hook is fired
 		*/
		public function admin_init()
		{
		    // Set up the settings for this plugin
    		$this->init_settings();

		} // END public function admin_init

		/**
		 * Menu Callback for displaying the Stormpath auth plugin settings page
		 */     
		public function plugin_settings_page()
		{
			if(!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Render the settings template
			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
			
		} // END public function plugin_settings_page()
		
		/**
		 * Add a menu when called by WP's admin_menu hook is fired
		 */     
		public function add_menu()
		{
			add_options_page('Stormpath Auth Settings', 'Stormpath Auth', 'manage_options', 'sp_auth', array(&$this, 'plugin_settings_page'));
		} // END public function add_menu()

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        } // END public static function activate
    
        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
            // Do nothing
        } // END public static function deactivate

		/**
		* Authenticates a user against the Stormpath system
		*
		* @param WP_User $user - An existing, possibly already logged in user object (ignored)
		* @param string $username - The user principal identifier to authenticate
		* @param string $password - The user principal's password to authenticate
		*
		* @return WP_User instance representing the logged in user.
		*				WP_Error if an error occured
		*
		*/
		function authenticate( $user, $username, $password )
		{
			// Make sure a username and password are present for us to work with
			if($username == '' || $password == '') return new WP_Error( 'denied', __("ERROR: User/pass missing") );

		    // If the Stormpath authenticator was instantiated, then use it
		    if(isset($this->spAuthenticator)) {
		    	
				$user =  $this->spAuthenticator->authenticate( $user, $username, $password );
			
				// Comment this line if you wish to fall back on WordPress authentication
				// Useful for times when the external service is offline
				remove_action('authenticate', 'wp_authenticate_username_password', 20);

			// Otherwise, return an error
			} else {

				$user = new WP_Error( 'denied', __("ERROR: Stormpath plugin settings were not initialized.") );

			}
			
			return $user;
		}

    } // END class SP_Auth

	if(class_exists('SP_Auth')) {
		// Installation and uninstallation hooks
		register_activation_hook(__FILE__, array('SP_Auth', 'activate'));
		register_deactivation_hook(__FILE__, array('SP_Auth', 'deactivate'));

		// instantiate the plugin class
		$sp_auth = new SP_Auth();
	
		// Add a link to the settings page onto the plugin page
		if(isset($sp_auth)) {
			// Add the settings link to the plugins page
			function plugin_settings_link($links) { 
				$settings_link = '<a href="options-general.php?page=sp_auth">Settings</a>'; 
				array_unshift($links, $settings_link); 
				return $links; 
			}

			$plugin = plugin_basename(__FILE__); 
			add_filter("plugin_action_links_$plugin", 'plugin_settings_link');

		}

	}

} // END if(!class_exists('SP_Auth'))


?>

