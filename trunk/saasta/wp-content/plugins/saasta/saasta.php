<?php
/*
Plugin Name: saasta.fi
Plugin URI: http://code.google.com/p/saastafi/#
Description: This plugin is a configuration tab for the saasta.fi site.  This plugin needs to be used together with the saasta theme.  The purpose of making all of this configurable is to be able to serve two slightly different sites using the same source code.  This is useful as we have written our own extensions to WordPress.
Author: Janne Hellsten
Version: 1.0
*/

// Options
add_option('saasta_sidebar_survey_enabled', 0, null);

add_option('saasta_sidebar_survey_link_text', "Take the Saasta Survey #1", null);
add_option('saasta_sidebar_survey_caption', "you have until July 15 to respond", null);

add_option('saasta_subsite', "saasta", null);

add_option('saasta_middle_adunit_enabled', 0, null);

function bool_to_checked($i)
{
    if ($i) 
        return 'checked="checked"';
    else
        return "";
}

function print_selected($a, $b)
{
    if ($a === $b) 
        echo "value=\"$a\" selected";
    else
        echo "value=\"$a\"";
}

/* Auto-create saasta SQL tables.  This function should be the ONLY
 place that creates or modifies our custom tables.  If tables are
 upgraded directly via console manipulation, all sorts of
 incompatibilities will arise.  Please ensure that this function is
 the only place that modifies our saasta.fi schema. */
function saasta_sql_setup()
{
    global $wpdb;

    $table_name = $wpdb->prefix . "faves";

    /* Check whether or not we have a faves table? */
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "
CREATE TABLE `$table_name` (
  `user_id` int(11) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  `fave_date` datetime NOT NULL,
  PRIMARY KEY  (`user_id`,`post_id`)
)";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);  
    }
}

function saasta_config_submenu() 
{
?>
    <div style="margin:1.0em;">
<?php
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

        $opt_val = $_POST['saasta_subsite'];
        update_option('saasta_subsite', $opt_val);

        $opt_val = $_POST['saasta_middle_adunit_enabled'];
        update_option('saasta_middle_adunit_enabled', $opt_val);

        $saved_settings = true;
    }

    $subsite = get_option('saasta_subsite');

    $survey_link_enabled = get_option('saasta_sidebar_survey_enabled');
    $survey_url = get_option('saasta_sidebar_survey_url');
    $survey_link_text = get_option('saasta_sidebar_survey_link_text');
    $survey_caption = get_option('saasta_sidebar_survey_caption');
    $middle_adunit_enabled = get_option('saasta_middle_adunit_enabled');

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

    <h4>General</h4>

    <p>
         Choose subsite theme: 
      <select value="<?php echo $subsite; ?>" name="saasta_subsite">
         <option <?php print_selected("saasta", $subsite);?>>saasta.fi</option>
         <option <?php print_selected("posso", $subsite);?>>posso.fi</option>
      </select>
    </p>

    <h4>Adsense</h4>
    <p>
     <input type="checkbox" name="saasta_middle_adunit_enabled" <?php echo bool_to_checked($middle_adunit_enabled); ?>> Check to show AdSense ad unit after first post on index page?</input>
    </p>

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

function saasta_sidebar_meta_links()
{
    $user = wp_get_current_user();
?>
<?php if (is_user_logged_in()) { print "<li>"; saasta_print_permalink(140); print "</li>"; } ?>
    <li><?php saasta_print_permalink(2624); ?></li>
    <?php if (is_user_logged_in()) { print "<li>"; saasta_print_permalink(922); print "</li>"; } ?>
    <li><?php saasta_print_permalink(2598); ?></li>
<?php
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

$subsite = get_option('saasta_subsite');
if ($subsite == 'saasta')
{
    add_action('saasta_sidebar_meta_links', 'saasta_sidebar_meta_links');
} else
{
    assert ($subsite == 'posso');
}

add_action('admin_menu', 'saasta_add_menus');

/* Ensure all SQL tables are properly setup upon plugin activation, */
register_activation_hook(__FILE__,'saasta_sql_setup');

?>
