<?php
/**
 * WordPress Authentication with Stormpath Utility Functions
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

if(!class_exists('Util'))
{

	class Util {

		/**
		* Returns a string with the name of the calling function and class if available.
		*
		* @return string with caller info
		*
		*/
		static function getCallingFunction()
		{
			$trace = debug_backtrace(); 
			$caller = $trace[2]; 
			$msg = "Called by ";
			if (isset($caller['class'])) $msg .= "{$caller['class']}->"; 
			$msg .= "{$caller['function']}"; 
			return $msg;
		}
		
		/**
		* Debug messages
		*
		* @param string $type - What type of debugging (msg = simple string, pr = print_r of variable)
		* @param string $header - the header
		* @param string $message - what you want to say
		* @param string $file - file of the call (__FILE__)
		* @param int $line - line number of the call (__LINE__)
		*
		* @return null
		*
		*/
		static function debug($type='msg', $header='sp-auth DMP', $message='', $file=null, $line=null)
		{
			// Ignore if not in debug mode
			if (null === WP_DEBUG) return;
		
			$output = $header;
			switch (strtolower($type)):
				case 'pr':
					$output .=  ': ' . print_r($message, true);
					break;
				default:
					$output .= ' - ' . $message;
			endswitch;
			$output .= ($file != null) ? ' - File: ' . $file : '';
			$output .= ($line != null)?' - Line# ' . $line : '';
			error_log($output);
		}
	
		/**
		* Lookup WordPress user by email address and then ID
		*
		* @param \Stormpath\Resource\Account $account - A logged in Stormpath Account object
		*
		* @return WP_User - The WordPress User Object related to this account
		*
		*/
		static function lookupWPUser($account)
		{
			// External user exists, try to load the user info from the WordPress user table
			$userobj = new WP_User();
			$user = $userobj->get_data_by( 'email', $account->getEmail() ); // Does not return a WP_User object
			Util::debug('pr','lookupWpUser - user from get_data_by('.$account->getEmail().')',$user->user_login);
			$user = new WP_User($user->ID); // Attempt to load up the user with that ID
			Util::debug('pr','lookupWpUser - user from new WP_User('.$user->ID.')',$user->data->user_login);
			return $user;
		}
		
		/**
		* Creates a new WordPress user object based on the passed in Stormpath account object
		*
		* @param \Stormpath\Resource\Account $account - A logged in Stormpath Account object
		*
		* @return WP_User - The WordPress User Object created for this account
		*
		*/
		static function createWPUser($account)
		{
			// The user does not currently exist in the WordPress user table.
			// You have arrived at a fork in the road, choose your destiny wisely

			// If you do not want to add new users to WordPress if they do not
			// already exist uncomment the following line and remove the user creation code
			//$user = new WP_Error( 'denied', __("ERROR: Not a valid user for this system") );

			// Setup the minimum required user information for this example
			$userdata = array( 'user_email' => $account->getEmail(),
								'user_login' => $account->getUsername(),
								'first_name' => $account->getGivenName(),
								'last_name' => $account->getSurname()
								);
			Util::debug('pr','createWPUser - $userdata',$userdata);
			
			// A new user has been created
			$new_user_id = wp_insert_user( $userdata );
			Util::debug('pr','createWPUser - $new_user_id after wp_insert_user',$new_user_id);

			// Load the new user info
			$user = new WP_User ($new_user_id);
			return $user;
		}
		
	}
	
}

?>