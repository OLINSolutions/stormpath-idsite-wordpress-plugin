<?php
/**
 * WordPress Authentication with Stormpath
 *
 * Handles calling Stormpath API in order to validate user principal.
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

// 1. Require the Stormpath PHP SDK via the composer auto loader
require  'vendor/autoload.php';

if(!class_exists('SP_Authenticator'))
{

	class SP_Authenticator
	{
		/**
		* @var apiKeyFileLocation - File that contains the API Key ID and Secret
		* Should only be accesible by root and apache
		* Based on whatever came from settings
		*/
		private $spApiKeyFileLocation;
		 
		/**
		* @var spApplicationHref - Contains the href pointing to the required Application resource in Stormpath
		*/
		private $spApplicationHref;

		/**
		* @var spDirectoryHref - Contains the href pointing to the required Directory resource in Stormpath
		*/
		private $spDirectoryHref;

		/**
		* @var spApplication - The required Application resource in Stormpath
		*/
		private $spApplication;

		/**
		* @var spDirectory - The required Application resource in Stormpath
		*/
		private $spDirectory;

		/**
		* @var spIDSiteLoginURI - The ID Site Login Callback URI for Stormpath
		*/
		private $spIDSiteLoginURI;

		/**
		* @var spIDSiteLogoutURI - The ID Site Logout URI for Stormpath
		*/
		private $spIDSiteLogoutURI;

		/**
		* Initialize the Stormpath specific instance variables from the settings
		*/
		private function init_vars() 
		{
			// Pull the operational settings from the options store
			$this->spApiKeyFileLocation = get_option('sp_apikey_file_location', '');
			Util::debug('pr', 'SP_Authenticator::apiKeyFileLocation', $this->spApiKeyFileLocation);
			$this->spApplicationHref = get_option('sp_application_href', '');
			Util::debug('pr', 'SP_Authenticator::spApplicationHref', $this->spApplicationHref);
			$this->spDirectoryHref = get_option('sp_directory_href', '');
			Util::debug('pr', 'SP_Authenticator::spDirectoryHref', $this->spDirectoryHref);
			$this->spIDSiteLoginURI = get_option('sp_idsite_login_uri', '');
			Util::debug('pr', 'SP_Authenticator::spIDSiteLoginURI', $this->spIDSiteLoginURI);
			$this->spIDSiteLogoutURI = get_option('sp_idsite_logout_uri', '');
			Util::debug('pr', 'SP_Authenticator::spIDSiteLogoutURI', $this->spIDSiteLogoutURI);
			$this->spApplication = '';
			$this->spDirectory = '';
		}
		
		/**
		 * Returns true if the settings have been properly configured and initialized
		 */
		public function stormpathInitialized()
		{
			if (empty(($this->spApiKeyFileLocation)) ||
				empty(($this->spApplicationHref)) ||
				empty(($this->spDirectoryHref)) ||
				empty(($this->spIDSiteLoginURI)) ||
				empty(($this->spIDSiteLogoutURI)))
				return false;
			return true;
		}

		/**
		* Configures and creates the Stormpath Application and Directory resource locators
		*/
		private function configure_client()
		{
			// If the key file location has been set
			if ($this->stormpathInitialized()) {
				\Stormpath\Client::$apiKeyFileLocation = $this->spApiKeyFileLocation;
				$this->spApplication = \Stormpath\Resource\Application::get($this->spApplicationHref);
				$this->spDirectory = \Stormpath\Resource\Directory::get($this->spDirectoryHref);
			}
		}
		
		/**
		* login_url Hook to cause the login to be processed by Stormpath via sp-login.php
		*
		* @param string $login_url - The original login URL (ignored)
		* @param string $redirect - The URL to send the user to upon a successful login
		*
		* @return string Replacement login URL
		*
		*/
		public function login_url_hook_get_id_site_login_uri( $login_url, $redirect ) 
		{
			$loginURI = $this->spIDSiteLoginURI . '?redirect=' . $redirect;
			Util::debug('pr','login_url_hook_get_id_site_login_uri::Generated loginURI',$loginURI);
			return $loginURI;
		}
		
		/**
		* Registers the login_url hook
		*/
		private function hookLoginURLforIDSite()
		{
			//Make sure the required options have been properly initialized
			if ($this->stormpathInitialized()) {
				add_filter( 'login_url', array(&$this, 'login_url_hook_get_id_site_login_uri'), 10, 3 );
				Util::debug('msg', 'SP_Authenticator::hookLoginURLforIDSite', 'After add_filter');
			} else {
				Util::debug('msg', 'SP_Authenticator::hookLoginURLforIDSite', 'Unable to add filter, settings not configured yet.');
			}
		}
			
		/**
		* logout_url Hook to cause the logout to be processed by Stormpath via sp-logout.php
		*
		* @param string $logout_url - The original logout URL (ignored)
		* @param string $redirect - The URL to send the user to upon a successful logout
		*
		* @return string Replacement logout URL
		*
		*/
		public function logout_url_hook_get_id_site_logout_url( $logout_url, $redirect ) 
		{
			$logoutURL = $this->spIDSiteLogoutURI . '?redirect=' . $redirect;
			Util::debug('pr','logout_url_hook_get_id_site_logout_url::Generated logoutURL',$logoutURL);
			return $logoutURL;
		}
		
		/**
		* Registers the logout_url hook
		*/
		private function hookLogoutUrlforIDSite()
		{
			//Make sure the required options have been properly initialized
			if ($this->stormpathInitialized()) {
				add_filter( 'logout_url', array(&$this, 'logout_url_hook_get_id_site_logout_url'), 10, 2 );
				Util::debug('msg', 'SP_Authenticator::hookLogoutUrlforIDSite', 'After add_filter');
			} else {
				Util::debug('msg', 'SP_Authenticator::hookLogoutURLforIDSite', 'Unable to add filter, settings not configured yet.');
			}
		}

		/**
		* allowed_redirect_hosts Hook so stormpath.com URLs can be called without issue
		*
		* @param string[] $allowed - Other allowed resources
		*
		* @return string[] Array with stormpath.com included
		*
		*/
		function allow_stormpath_redirect($allowed)
		{
			$allowed[] = 'stormpath.com';
			return $allowed;
		}
		
        /**
         * Construct the Authenticator
         */
        public function __construct()
        {
        	$msg = Util::getCallingFunction();
        	Util::debug('msg', 'SP_Authenticator::__construct - ENTERED', $msg);

        	// Initialize the instance variables
        	$this->init_vars();
        	
        	// Configure the client connection to Stormpath
        	$this->configure_client();
        	
			// Allow stormpath.com to be redirected to
			add_filter('allowed_redirect_hosts', array(&$this, 'allow_stormpath_redirect'), 10, 1);

        	// Set the login_url filter
        	$this->hookLoginURLforIDSite();
        	
        	// Set the logout_url filter
        	$this->hookLogoutUrlforIDSite();
        	
        	Util::debug('msg', 'SP_Authenticator::__construct', 'LEAVING');

        } // END public function __construct

		/**
		*	Returns a Stormpath Account instance for the specific user, if found
		*
		*	@param string $username [MANDATORY] - email address os user name
		*	@param string $password [MANDATORY] - value to submit
		*	
		*	If user is found, and authenticated, then a valid account is returned
		* 
		*	@return \Stormpath\Resource\Account instance
		*	
		*/    
		private function lookupAccount($username, $password)
		{
			// If the settings have not been initialized, return a WP_Error object
			if (!$this->stormpathInitialized())
				return new WP_Error( 'denied', __("Stormpath Authenticator::ERROR: Stormpath plugin settings are not configured") );

			// Lookup the account within Stormpath
			$result = $this->spApplication->authenticate($username, $password);
			$account = $result->account;
			Util::debug('pr', 'account', $account);
			return $account;
		}
		
		/**
		*	Authenticate - Validates the user identity and password in the stormpath system
		*
		*	@param WP_User $user - Object to create
		*	@param string $username [MANDATORY] - email address os user name
		*	@param string $password [MANDATORY] - value to submit
		*	
		*	If user is found in remote system then a WP user object is searched for, if found, returned, 
		*	if not found, created and returned.
		*   If user is not found in remote system, then  an instance of WP_Error is returned
		
		*	@return WP_User object or WP_Error
		*	
		*/    
		function authenticate( $user, $username = '', $password = '')
		{
			// Make sure a username and password are present for us to work with
			if($username == '' || $password == '')  return new WP_Error( 'denied', __("Stormpath Authenticator::ERROR: User/pass missing") );

			try
			{

				// Lookup the account within Stormpath
				$account = $this->lookupAccount($username, $password);
				// If an error was returned, forward on to caller
				if ($account instanceof WP_Error) return $account;
				
				// External user exists, try to load the user info from the WordPress user table
				$user = Util::lookupWPUser($account);

				// Does the user exist in WordPress?
				if( $user->ID == 0 ) {
				 
				 	// Create a new proxy user in the WP user system
					$user = Util::createWPUser($account);
				} 

				// Comment this line if you wish to fall back on WordPress authentication
				// Useful for times when the external service is offline
				remove_action('authenticate', 'wp_authenticate_username_password', 20);

			} catch (\Stormpath\Resource\ResourceError $re)
			{
				Util::debug('pr','ResourceError',$re);
				$user = new WP_Error( 'denied', __("Stormpath Authenticator::ERROR:Status=".$re->getStatus().", ErrorCode=".$re->getErrorCode().", Message=".$re->getMessage().", DeveloperMessage=".$re->getDeveloperMessage().", MoreInfo=".$re->getMoreInfo()) );
			}
			
			if (isset($account))
			{
				Util::debug('msg', 'SP_Authenticator', 'After try-catch for authenticate. $account');
			}

			return $user;
		}

    } // END class SP_Authenticator

} // END if(!class_exists('SP_Authenticator'))

?>

