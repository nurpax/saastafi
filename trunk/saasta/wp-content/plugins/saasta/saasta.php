<?php
/*
Plugin Name: saasta.fi
Plugin URI: http://code.google.com/p/saastafi/#
Description: This plugin is a configuration tab for the saasta.fi site.  This plugin needs to be used together with the saasta theme.
Author: Janne Hellsten
Version: 1.0
*/

// Print sidebar links for various ad/poll/gala campaigns
function saasta_sidebar_links() {
	echo "saasta.fi plugin hello world!";
}

// Now we set that function up to execute when the admin_footer action is called
add_action('saasta_sidebar_links', 'saasta_sidebar_links');

?>