<!-- 
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
-->
 <div class="wrap">
    <h2>Stormpath Auth Plug-In Settings</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('sp_auth-group'); ?>
        <?php @do_settings_fields('sp_auth-group'); ?>

        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="sp_apikey_file_location">API Key Properties</label></th>
                <td><input type="text" style="width: 95%;" name="sp_apikey_file_location" id="sp_apikey_file_location" value="<?php echo get_option('sp_apikey_file_location', ''); ?>" />
                <br/>Only need to give 'apache' (or equivalent web server running user) read & execute permissions</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_application_href">Application HREF</label></th>
                <td><input type="text" style="width: 95%;" name="sp_application_href" id="sp_application_href" value="<?php echo get_option('sp_application_href', ''); ?>" />
                <br/>Should be a Stormpath URI for the Application resource used by this WordPress instance.
                <br/>e.g. https://api.stormpath.com/v1/applications/3jx7JDJDdjdkre7839</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_directory_href">Directory HREF</label></th>
                <td><input type="text" style="width: 95%;" name="sp_directory_href" id="sp_directory_href" value="<?php echo get_option('sp_directory_href', ''); ?>" />
                <br/>Should be a Stormpath URI for the Directory resource used by the above referenced Application.
                <br/>e.g. https://api.stormpath.com/v1/directories/AJufd3393XJDFqJ3E81230</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_authenticate_via_stormpath">Enable Authentication via Stormpath?</label></th>
				<td><input type="radio" name="sp_authenticate_via_stormpath" id="sp_authenticate_via_stormpath" <?php if(get_option('sp_authenticate_via_stormpath', 'false') === 'true') echo 'checked'; ?> value="true" />yes &nbsp;
				<input type="radio" name="sp_authenticate_via_stormpath" id="sp_authenticate_via_stormpath" <?php if(get_option('sp_authenticate_via_stormpath', 'false') === 'false') echo 'checked'; ?> value="false" />no</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_use_idsite_for_login">Use ID Site for Login?</label></th>
				<td><input type="radio" name="sp_use_idsite_for_login" id="sp_use_idsite_for_login" <?php if(get_option('sp_use_idsite_for_login', 'false') === 'true') echo 'checked'; ?> value="true" />yes &nbsp;
				<input type="radio" name="sp_use_idsite_for_login" id="sp_use_idsite_for_login" <?php if(get_option('sp_use_idsite_for_login', 'false') === 'false') echo 'checked'; ?> value="false" />no</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_idsite_login_uri">ID Site Login URI</label></th>
                <td><input type="text" style="width: 95%;" name="sp_idsite_login_uri" id="sp_idsite_login_uri" value="<?php echo get_option('sp_idsite_login_uri', ''); ?>" />
                <br/>Should be a URI relative to the current WordPress installation pointing to the sp-login.php file.
                <br/>e.g. http://www.mywordpress.com/wp/wp-content/plugins/sp-auth/sp-login.php</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_use_idsite_for_logout">Use ID Site for Logout?</label></th>
				<td><input type="radio" name="sp_use_idsite_for_logout" id="sp_use_idsite_for_logout" <?php if(get_option('sp_use_idsite_for_logout', 'false') === 'true') echo 'checked'; ?> value="true" />yes &nbsp;
				<input type="radio" name="sp_use_idsite_for_logout" id="sp_use_idsite_for_logout" <?php if(get_option('sp_use_idsite_for_logout', 'false') === 'false') echo 'checked'; ?> value="false" />no</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_idsite_logout_uri">ID Site Logout URI</label></th>
                <td><input type="text" style="width: 95%;" name="sp_idsite_logout_uri" id="sp_idsite_logout_uri" value="<?php echo get_option('sp_idsite_logout_uri', ''); ?>" />
                <br/>Should be a URI relative to the current WordPress installation pointing to the sp-login.php file.
                <br/>e.g. http://www.mywordpress.com/wp/wp-content/plugins/sp-auth/sp-logout.php</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_login_after_logout">Login after Logout?</label></th>
				<td><input type="radio" name="sp_login_after_logout" id="sp_login_after_logout" <?php if(get_option('sp_login_after_logout', 'false') === 'true') echo 'checked'; ?> value="true" />yes &nbsp;
				<input type="radio" name="sp_login_after_logout" id="sp_login_after_logout" <?php if(get_option('sp_login_after_logout', 'false') === 'false') echo 'checked'; ?> value="false" />no</td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>