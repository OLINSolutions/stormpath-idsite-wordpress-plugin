# stormpath-idsite-wordpress-plugin
A basic Wordpress Plugin to integrate with Stormpath's (stormpath.com) ID Site and Authentication functionality.

## sp-auth
Contributors: olinsolutions
Tags: stormpath, auth
Requires at least: 4.0
Tested up to: 4.5.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stormpath IDM integration plugin.  Supports IDSite and authentication.

### Description
Intended to integrate with the Stormpath IDM PaaS (www.stormpath.com) in order to allow replacement of the authentication mechanism used at WordPress login.  In addition, integration for IDSite is configured based on the settings.
For now, only the Login and Logout flows are integrated.  Registration and Password Change/reset are coming.


### Installation
1. Unzip the plugin into a directory under Plugins (e.g. sp-auth).
2. Create a hidden and protected stompath api key file somewhere in the servers file system where it can\'t be served up to end-users.
* e.g. /etc/.stormpath/apikey.properties with 640 and owner = root, group = apache
3. Configure the settings with your Stormpath information.
4. Activate the plugin.


### Changelog
* 0.3 - 20160504 - Made sure even if activated, login/logout urls are not impacted until settings are configured
* 0.2 - 20160503 - Fixed width of input boxes on settings tab
* 0.1 - 20160502 - Still in development

