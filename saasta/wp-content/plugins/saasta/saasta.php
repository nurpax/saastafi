<?php
/*
Plugin Name: saasta.fi
Plugin URI: http://code.google.com/p/saastafi/#
Description: This plugin is a configuration tab for the saasta.fi site.  This plugin needs to be used together with the saasta theme.
Author: Janne Hellsten
Version: 1.0
*/

?>
    <div style="margin:1.0em;">
<?php

// Options
add_option('saasta_sidebar_survey_enabled', 0, null);

add_option('saasta_sidebar_survey_link_text', "Take the Saasta Survey #1", null);
add_option('saasta_sidebar_survey_caption', "you have until July 15 to respond", null);

function bool_to_checked($i)
{
    if ($i) 
        return 'checked="checked"';
    else
        return "";
}

function saasta_config_submenu() 
{
    $hidden_field_name = 'saasta_submit_hidden';

    $saved_settings = false;

    // Read user inputs and update DB for new values:
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        $opt_val = $_POST['saasta_sidebar_survey_enabled'];
        update_option('saasta_sidebar_survey_enabled', $opt_val);

        $opt_val = $_POST['saasta_sidebar_survey_url'];
        update_option('saasta_sidebar_survey_url', $opt_val);

        $opt_val = $_POST['saasta_sidebar_survey_link_text'];
        update_option('saasta_sidebar_survey_link_text', $opt_val);

        $opt_val = $_POST['saasta_sidebar_survey_caption'];
        update_option('saasta_sidebar_survey_caption', $opt_val);
        $saved_settings = true;
    }

    $survey_link_enabled = get_option('saasta_sidebar_survey_enabled');
    $survey_url = get_option('saasta_sidebar_survey_url');
    $survey_link_text = get_option('saasta_sidebar_survey_link_text');
    $survey_caption = get_option('saasta_sidebar_survey_caption');

    if ($saved_settings)
    {
        ?><div id="message" class="updated fade">
        <p>Options saved.</p>
        </div><?php
    }

?>
    <h3>Configure saasta.fi</h3>
    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

    <h4>Sidebar links</h4>
    <table>
    <tr>
     <td>
         URL: <input type="text" size="35" name="saasta_sidebar_survey_url" value="<?php echo $survey_url ?>"/>
     </td>

     <td>
         Link text: <input type="text" size="35" name="saasta_sidebar_survey_link_text" value="<?php echo $survey_link_text ?>"/>
     </td>

     <td>
         Link text: <input type="text" size="35" name="saasta_sidebar_survey_caption" value="<?php echo $survey_caption ?>"/>
     </td>

     <td><input type="checkbox" name="saasta_sidebar_survey_enabled" <?php echo bool_to_checked($survey_link_enabled); ?>> Enabled?</input>
     </td>
    </tr>
    </table>
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Update Options', 'saasta_config' ) ?>" />
    </p>
    </form>

</p><hr />
</div>

<?php
}

function saasta_add_menus()
{
    add_submenu_page('plugins.php', 'saasta.fi config', 'saasta.fi', 7, __FILE__, 'saasta_config_submenu');
}

// Print sidebar links for various ad/poll/gala campaigns
function saasta_sidebar_links() 
{
    if (get_option('saasta_sidebar_survey_enabled')) {
        $link_text = get_option('saasta_sidebar_survey_link_text');
        $caption = get_option('saasta_sidebar_survey_caption');
		$imgurl = get_template_directory_uri() . '/images/' . "poll_obama.png";
        $url = get_option('saasta_sidebar_survey_url');
        echo "<p>";
        echo "<span style=\"font-size:1.4em; font-weight:bold;\"><a href=\"$url\"><img src=\"$imgurl\" alt=\"poll\" /> $link_text</a></span><br/>";
        echo "<span style=\"color:#444;\">[$caption]</span></p>";
    }
}

// Now we set that function up to execute when the admin_footer action is called
add_action('saasta_sidebar_links', 'saasta_sidebar_links');

add_action('admin_menu', 'saasta_add_menus');

?>