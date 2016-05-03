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
<div class="wrap">
    <h2>Stormpath Auth Plug-In Settings</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('sp_auth-group'); ?>
        <?php @do_settings_fields('sp_auth-group'); ?>

        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="sp_apikey_file_location">API Key Properties (only give apache read & execute)</label></th>
                <td><input type="text" style="width: 95%;" name="sp_apikey_file_location" id="sp_apikey_file_location" value="<?php echo get_option('sp_apikey_file_location'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_directory_href">Directory HREF</label></th>
                <td><input type="text" style="width: 95%;" name="sp_directory_href" id="sp_directory_href" value="<?php echo get_option('sp_directory_href'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_application_href">Application HREF</label></th>
                <td><input type="text" style="width: 95%;" name="sp_application_href" id="sp_application_href" value="<?php echo get_option('sp_application_href'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_idsite_login_uri">ID Site Login URI</label></th>
                <td><input type="text" style="width: 95%;" name="sp_idsite_login_uri" id="sp_idsite_login_uri" value="<?php echo get_option('sp_idsite_login_uri'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_idsite_logout_uri">ID Site Logout URI</label></th>
                <td><input type="text" style="width: 95%;" name="sp_idsite_logout_uri" id="sp_idsite_logout_uri" value="<?php echo get_option('sp_idsite_logout_uri'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="sp_login_after_logout">Login after Logout?</label></th>
				<td><input type="radio" name="sp_login_after_logout" id="sp_login_after_logout" <?php if(get_option('sp_login_after_logout') === 'true') echo 'checked'; ?> value="true" />yes &nbsp;
				<input type="radio" name="sp_login_after_logout" id="sp_login_after_logout" <?php if(get_option('sp_login_after_logout') === 'false') echo 'checked'; ?> value="false" />no</td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>