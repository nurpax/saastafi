<?php
/*
Plugin Name: saasta.fi
Plugin URI: http://code.google.com/p/saastafi/#
Description: This plugin is a configuration tab for the saasta.fi site.  This plugin needs to be used together with the saasta theme.
Author: Janne Hellsten
Version: 1.0
*/

// Options
add_option('saasta_sidebar_survey_enabled', 0, null);

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

    // Read user inputs and update DB for new values:
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        $opt_val = $_POST['saasta_sidebar_survey_enabled'];
        update_option('saasta_sidebar_survey_enabled', $opt_val);

        $opt_val = $_POST['saasta_sidebar_survey_url'];
        update_option('saasta_sidebar_survey_url', $opt_val);
    }

    $survey_link_enabled = get_option('saasta_sidebar_survey_enabled');
    $survey_url = get_option('saasta_sidebar_survey_url');


?>
    <h3>Configure saasta.fi</h3>
    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
    <p>
    <input type="checkbox" name="saasta_sidebar_survey_enabled" <?php echo bool_to_checked($survey_link_enabled); ?>> Survey no. 1 link enabled in sidebar?</input>
    </p>
    <p>
    Link to the survey post: <input type="text" size="40" name="saasta_sidebar_survey_url" value="<?php echo $survey_url ?>"/>
    </p>
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Update Options', 'saasta_config' ) ?>" />
    </p>
    </form>

</p><hr />

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
		$imgurl = get_template_directory_uri() . '/images/' . "poll_obama.png";
        $url = get_option('saasta_sidebar_survey_url');
        echo "<p>";
        echo "<span style=\"font-size:1.4em; font-weight:bold;\"><a href=\"$url\"><img src=\"$imgurl\" alt=\"poll\" /> Take the Saasta Survey #1!</a></span><br/>";
        echo "<span style=\"color:#444;\">[you have until July 15 to respond]</span></p>";
    }
}

// Now we set that function up to execute when the admin_footer action is called
add_action('saasta_sidebar_links', 'saasta_sidebar_links');

add_action('admin_menu', 'saasta_add_menus');

?>