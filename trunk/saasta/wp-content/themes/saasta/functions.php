<?php

// Print share post buttons. Used in single post view.
function saasta_print_share_post_buttons() 
{
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    $excerpt = urlencode(get_the_excerpt());

    print '<p style="background-color:#dac8c7;padding:5px;border:1px solid #666666">';
    // digg
    print '<img style="border:0px;" src="images/digg.png" width="16" height="16" align="absmiddle" alt="Digg"/> <a href="http://digg.com/submit?url='.$url.'&title='.$title.'&bodytext='.$excerpt.'">Digg this</a>&nbsp;';
    // stumbleupon
    print '<img style="border:0px;" src="images/su.png" width="16" height="16" align="absmiddle" alt="stumbleupon"/> <a href="http://www.stumbleupon.com/submit?url='.$url.'&title='.$title.'">StumbleUpon</a>&nbsp;';
    // reddit
    print '<img style="border:0px;" src="images/reddit.gif" width="16" height="16" align="absmiddle" alt="reddit"/> <a href="http://reddit.com/submit?url='.$url.'&title='.$title.'">Reddit</a>&nbsp;';
    // facebook
    print '<script>function fbs_click() {u=location.href;t=document.title;window.open(\'http://www.facebook.com/sharer.php?u=\'+encodeURIComponent(u)+\'&t=\'+encodeURIComponent(t),\'sharer\',\'toolbar=0,status=0,width=626,height=436\');return false;}</script><style> html .fb_share_link { padding:2px 0 0 20px; height:16px; background:url(http://static.ak.fbcdn.net/images/share/facebook_share_icon.gif?0:26981) no-repeat top left; }</style><a href="http://www.facebook.com/share.php?u='.$url.'" onclick="return fbs_click()" target="_blank" class="fb_share_link">Facebook</a>';
    print '</p>';
}

// Print adsense ads based on subsite option.
function saasta_print_upper_adsense_link_unit()
{
    $subsite = get_option('saasta_subsite');
    if ($subsite == 'saasta')
    {
        print '
<script type="text/javascript"><!--
google_ad_client = "pub-7907497075456864";
/* 468x60, created 5/25/08 */
google_ad_slot = "6808140057";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
';
    } else
    {
        if ($subsite != 'posso')
            die("unknown subsite $subsite");
        print '
<script type="text/javascript"><!--
google_ad_client = "pub-7907497075456864";
/* posso.fi/468x60, created 8/10/08 */
google_ad_slot = "2096816017";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
';
    }

}


// Print adsense ads based on subsite option.
function saasta_print_middle_image_adsense_unit()
{
    $subsite = get_option('saasta_subsite');
    if ($subsite == 'saasta')
    {
        print '
<script type="text/javascript"><!--
google_ad_client = "pub-7907497075456864";
/* saasta.fi 468x60, created 8/10/08 */
google_ad_slot = "3278794639";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
';
    } else
    {
        if ($subsite != 'posso')
            die("unknown subsite $subsite");
    }
}

// Print adsense ads based on subsite option.
function saasta_print_right_skyscraper_adsense()
{
    $subsite = get_option('saasta_subsite');
    if ($subsite == 'saasta')
    {
        print '
<script type="text/javascript"><!--
google_ad_client = "pub-7907497075456864";
/* 160x600, created 4/4/08 */
google_ad_slot = "3125109427";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
';
    } else
    {
        if ($subsite != 'posso')
            die("unknown subsite $subsite");
        print '
<script type="text/javascript"><!--
google_ad_client = "pub-7907497075456864";
/* posso.fi - 160x600, created 8/10/08 */
google_ad_slot = "5985214010";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
';
    }
}

function saasta_print_add_fave_form() 
{
	$redirectURI = attribute_escape($_SERVER['REQUEST_URI']);
	$redirectURI .= "#saasta".get_the_ID();

    print '<form action="'.get_option('siteurl').'/saasta-handlefaves.php" method="post">';
    print '<input type="hidden" name="redirect_to" value="'.$redirectURI.'"/>';
    print '<input type="hidden" name="add_post_id" value="'.get_the_ID().'"/>';
    print '<input type="submit" style="border:0;font-size:smaller;background-color:#dac8c7;" value="add fave"/>';
    print '</form>';
}

function saasta_print_del_fave_form($post_id)
{
	$redirectURI = attribute_escape($_SERVER['REQUEST_URI']);
	$redirectURI .= "#saasta".get_the_ID();

    print '<form action="'.get_option('siteurl').'/saasta-handlefaves.php" method="post" onsubmit="return confirm(\"You sure?\");">';
    print '<input type="hidden" name="redirect_to" value="'.$redirectURI.'"/>';
    print '<input type="hidden" name="del_post_id" value="'. $post_id .'"/>';
    print '<input type="submit" style="border:0;font-size:smaller;background-color:#dac8c7;" value="unfave"/>';
    print '</form>';
}

/**
 * Print post header. Contains author image (avatar), post title,
 * author, date etc and fave button & count.
 *
 * muumi 080819: refactored the whole thing into a simple table
 */
function saasta_print_post_header() {
    global $user_ID;
    global $wpdb;

    $people_images_dir = "people/" . get_option('saasta_subsite') . '/';
    $icon = $people_images_dir . "unknown.png";
    $pic_name = $people_images_dir . get_the_author_login();
    if (file_exists($pic_name . ".png"))
        $icon = $pic_name . ".png";
    else if (file_exists($pic_name . ".gif"))
        $icon = $pic_name . ".gif";


	saasta_print_admin_notice(TRUE);

	print '<table class="postheader">';
	print '<tr>';
	// left column: author image
	print '<td width="40" align="center">';
	print '<img align="absmiddle" src="' . $icon . '" width="32" height="32" border="0" alt="'.get_the_author_login().'"/>';
	print '</td>';
	// middle column: post title, author etc
	print '<td width="420">';
	// post title
    print '<span style="font-weight:bold;font-size:1.2em;"><a name="saasta'.get_the_ID().'" href="';
    the_permalink();
    print '" rel="bookmark" title="Permanent Link to ';
    the_title();
    print '">';
    the_title();
    print '</a></span><br/><small>';
    the_time('F jS, Y');
    print ' by ';
    the_author();
    print '</small>';
	print '</td>';

	// right column: add fave/unfave buttons, # of faves
	print '<td valign="middle" align="center">';

	print '<div style="border:1px solid #666666;padding:0.2em;">';
	if ($user_ID == '') {
        saasta_print_add_fave_form ();
    }
	else {
		$foo = $wpdb->get_results("select post_id from ".$wpdb->prefix."faves where user_id=".$user_ID." and post_id=".get_the_ID());
		if (count($foo) == 0) 
			saasta_print_add_fave_form ();
		else 
			saasta_print_del_fave_form (get_the_ID());
	}
	$foo = $wpdb->get_row("select count(post_id) as numfaves from ".$wpdb->prefix."faves where post_id=".get_the_ID());
	if ($foo->numfaves > 0)
		print '<span style="font-size:larger;color:#666666;">'.$foo->numfaves.'</span>';
	print '</div>';
	print '</td>';

	// close post header table
	print '</tr></table>';

	// next/prev navigation for single post view
	if (is_single()) {
		print '<table class="singleprevnext">';
		print '<tr><td align="left" style="font-size:0.88em;">';
		next_post_link('&laquo; %link');
		print '</td><td align="right" style="font-size:0.88em;">';
		previous_post_link('%link &raquo;');
		print '</td></tr></table>';
	}
}

/**
 */
function saasta_print_admin_notice() {

	if (get_the_author_login() != 'admin')
		return;

    // Achtung baby only on saasta.fi, customize later for other subsites
    if (get_option('saasta_subsite') == 'saasta')
    {
        print '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:0.5em;margin-bottom:0.5em;">';
        print '<tr><td style="border:1px solid black;padding:0.5em;background-color:#cc0000;color:#ffffff;font-weight:bold;text-align:center;">ACHTUNG!</td></tr>';
        print '</table>';
    }
}

function saasta_print_permalink($id) 
{
    print '<a href="';
    print get_permalink($id);
    print '">';
    print get_the_title($id);
    print '</a>';
}

function saasta_print_if_logged_in($s)
{
    if (is_user_logged_in())
        print $s;
}

// under construction, do not touch -muumi
function saasta_print_votebar() {
    global $wpdb;
    global $user_ID;

    get_currentuserinfo();
    // no user
    if ('' == $user_ID)
        return;

    
    
    $query = "select count(*) as numvotes from saasta_votes where post_id=".get_the_ID()." and user_id=".$user_ID;
    $foo = $wpdb->get_results($query);
    // has not voted yet?
    if ($foo[0]->numvotes == 0) {
        print '<p>';
        print '<a href="'.$_SERVER['REQUEST_URI'].'&vote=res&pid='.get_the_ID().'">res</a>';
        print ' or ';
        print '<a href="'.$_SERVER['REQUEST_URI'].'&vote=dis&pid='.get_the_ID().'">dis</a>';
        print '</p>';
    }
    else {
        // vote cast now?
        if (isset($_REQUEST['vote']) && isset($_REQUEST['pid'])) {
            $vote = $_REQUEST['vote'];
            $pid = $_REQUEST['pid'];
            $v = 0;
            if ('res' == $vote) { $v = 1; }
            if ('dis' == $vote) { $v = -1; }
            if ($v != 0) {
                $wpdb->query("delete from saasta_votes where post_id=".$pid." and user_id=".$user_ID);
                $wpdb->query("insert into saasta_votes (post_id,user_id,vote) values (".$pid.",".$user_ID.",".$v.")");
            }
        }

        $query = "select count(vote) as num_votes,SUM(vote) as tally from saasta_votes where post_id=".get_the_ID();
        $foo = $wpdb->get_results($query);
        print '<p>';
        print $foo[0]->num_votes." votes cast, tally = ".$foo[0]->tally;
        print '</p>';
    }
}

/*
 * list users favourites
 * under construction
 */
function saasta_list_faves() {
    global $wpdb;
    global $user_ID;

    get_currentuserinfo();
	if ('' == $user_ID)
		return;

    $foo = $wpdb->get_results("select p.post_title as title,f.post_id as post_id from ".$wpdb->posts." p,".$wpdb->prefix."faves f where f.post_id=p.ID and f.user_id=".$user_ID);
    if (count($foo) > 0) {
        print '<li><h2>Your favorites</h2><ul>';
        foreach ($foo as $f) {
            print '<form action="'.get_option('siteurl').'/saasta-handlefaves.php" method="post">';
                    print '<input type="hidden" name="redirect_to" value="'.attribute_escape($_SERVER['REQUEST_URI']).'"/>';
                    print '<input type="hidden" name="del_post_id" value="'.$f->post_id.'"/>';
            print '<li><a href="'.get_permalink($f->post_id).'" title="'.$f->title.'">'.$f->title.'</a>';
                    print ' <input type="submit" style="border:1px solid black;font-size:smaller;background-color:#ddd391" value="del"/>';
                    print '</form>';

            //print ' <a href="'.$url.'delfave='.$f->post_id.'" title="delete favorite">[x]</a>';
        }
        print '</ul></li>';
    }
}

/**
 * List n recent faved posts. Orders by fave date (in saasta_faves)
 * and then by post id, both in descending order.
 *
 * @param limit how many faves to show (defaults to 5)
 * @return a list of associative arrays {url,title,author,num_faves}
 */
function saasta_list_recent_faves($limit=5) {	
	global $wpdb;
    $tbl_faves = $wpdb->prefix."faves";

	$query = "
SELECT 
  post_id
FROM 
  $tbl_faves AS sf
INNER JOIN 
  (SELECT MAX(fave_date) AS fd FROM $tbl_faves GROUP by post_id) foo 
ON 
  sf.fave_date=foo.fd
ORDER BY 
  fave_date DESC
LIMIT 5";

	return $wpdb->get_results($query);
}

function kubrick_head() {
	$head = "<style type='text/css'>\n<!--";
	$output = '';
	if ( kubrick_header_image() ) {
		$url =  kubrick_header_image_url() ;
		$output .= "#header { background: url('$url') no-repeat bottom center; }\n";
	}
	if ( false !== ( $color = kubrick_header_color() ) ) {
		$output .= "#headerimg h1 a, #headerimg h1 a:visited, #headerimg .description { color: $color; }\n";
	}
	if ( false !== ( $display = kubrick_header_display() ) ) {
		$output .= "#headerimg { display: $display }\n";
	}
	$foot = "--></style>\n";
	if ( '' != $output )
		echo $head . $output . $foot;
}

add_action('wp_head', 'kubrick_head');

function kubrick_header_image() {
	return apply_filters('kubrick_header_image', get_option('kubrick_header_image'));
}

function kubrick_upper_color() {
	if ( strstr( $url = kubrick_header_image_url(), 'header-img.php?' ) ) {
		parse_str(substr($url, strpos($url, '?') + 1), $q);
		return $q['upper'];
	} else
		return '69aee7';
}

function kubrick_lower_color() {
	if ( strstr( $url = kubrick_header_image_url(), 'header-img.php?' ) ) {
		parse_str(substr($url, strpos($url, '?') + 1), $q);
		return $q['lower'];
	} else
		return '4180b6';
}

function kubrick_header_image_url() {
	if ( $image = kubrick_header_image() )
		$url = get_template_directory_uri() . '/images/' . $image;
	else
		$url = get_template_directory_uri() . '/images/kubrickheader.jpg';

	return $url;
}

function kubrick_header_color() {
	return apply_filters('kubrick_header_color', get_option('kubrick_header_color'));
}

function kubrick_header_color_string() {
	$color = kubrick_header_color();
	if ( false === $color )
		return 'white';

	return $color;
}

function kubrick_header_display() {
	return apply_filters('kubrick_header_display', get_option('kubrick_header_display'));
}

function kubrick_header_display_string() {
	$display = kubrick_header_display();
	return $display ? $display : 'inline';
}

add_action('admin_menu', 'kubrick_add_theme_page');

function kubrick_add_theme_page() {
	if ( $_GET['page'] == basename(__FILE__) ) {
		if ( 'save' == $_REQUEST['action'] ) {
			if ( isset($_REQUEST['njform']) ) {
				if ( isset($_REQUEST['defaults']) ) {
					delete_option('kubrick_header_image');
					delete_option('kubrick_header_color');
					delete_option('kubrick_header_display');
				} else {
					if ( '' == $_REQUEST['njfontcolor'] )
						delete_option('kubrick_header_color');
					else
						update_option('kubrick_header_color', $_REQUEST['njfontcolor']);

					if ( preg_match('/[0-9A-F]{6}|[0-9A-F]{3}/i', $_REQUEST['njuppercolor'], $uc) && preg_match('/[0-9A-F]{6}|[0-9A-F]{3}/i', $_REQUEST['njlowercolor'], $lc) ) {
						$uc = ( strlen($uc[0]) == 3 ) ? $uc[0]{0}.$uc[0]{0}.$uc[0]{1}.$uc[0]{1}.$uc[0]{2}.$uc[0]{2} : $uc[0];
						$lc = ( strlen($lc[0]) == 3 ) ? $lc[0]{0}.$lc[0]{0}.$lc[0]{1}.$lc[0]{1}.$lc[0]{2}.$lc[0]{2} : $lc[0];
						update_option('kubrick_header_image', "header-img.php?upper=$uc&amp;lower=$lc");
					}

					if ( isset($_REQUEST['toggledisplay']) ) {
						if ( false === get_option('kubrick_header_display') )
							update_option('kubrick_header_display', 'none');
						else
							delete_option('kubrick_header_display');
					}
				}
			} else {

				if ( isset($_REQUEST['headerimage']) ) {
					if ( '' == $_REQUEST['headerimage'] )
						delete_option('kubrick_header_image');
					else
						update_option('kubrick_header_image', $_REQUEST['headerimage']);
				}

				if ( isset($_REQUEST['fontcolor']) ) {
					if ( '' == $_REQUEST['fontcolor'] )
						delete_option('kubrick_header_color');
					else
						update_option('kubrick_header_color', $_REQUEST['fontcolor']);
				}

				if ( isset($_REQUEST['fontdisplay']) ) {
					if ( '' == $_REQUEST['fontdisplay'] || 'inline' == $_REQUEST['fontdisplay'] )
						delete_option('kubrick_header_display');
					else
						update_option('kubrick_header_display', 'none');
				}
			}
			//print_r($_REQUEST);
			wp_redirect("themes.php?page=functions.php&saved=true");
			die;
		}
		add_action('admin_head', 'kubrick_theme_page_head');
	}
	add_theme_page('Customize Header', 'Header Image and Color', 'edit_themes', basename(__FILE__), 'kubrick_theme_page');
}

function kubrick_theme_page_head() {
?>
<script type="text/javascript" src="../wp-includes/js/colorpicker.js"></script>
<script type='text/javascript'>
// <![CDATA[
	function pickColor(color) {
		ColorPicker_targetInput.value = color;
		kUpdate(ColorPicker_targetInput.id);
	}
	function PopupWindow_populate(contents) {
		contents += '<br /><p style="text-align:center;margin-top:0px;"><input type="button" value="Close Color Picker" onclick="cp.hidePopup(\'prettyplease\')"></input></p>';
		this.contents = contents;
		this.populated = false;
	}
	function PopupWindow_hidePopup(magicword) {
		if ( magicword != 'prettyplease' )
			return false;
		if (this.divName != null) {
			if (this.use_gebi) {
				document.getElementById(this.divName).style.visibility = "hidden";
			}
			else if (this.use_css) {
				document.all[this.divName].style.visibility = "hidden";
			}
			else if (this.use_layers) {
				document.layers[this.divName].visibility = "hidden";
			}
		}
		else {
			if (this.popupWindow && !this.popupWindow.closed) {
				this.popupWindow.close();
				this.popupWindow = null;
			}
		}
		return false;
	}
	function colorSelect(t,p) {
		if ( cp.p == p && document.getElementById(cp.divName).style.visibility != "hidden" )
			cp.hidePopup('prettyplease');
		else {
			cp.p = p;
			cp.select(t,p);
		}
	}
	function PopupWindow_setSize(width,height) {
		this.width = 162;
		this.height = 210;
	}

	var cp = new ColorPicker();
	function advUpdate(val, obj) {
		document.getElementById(obj).value = val;
		kUpdate(obj);
	}
	function kUpdate(oid) {
		if ( 'uppercolor' == oid || 'lowercolor' == oid ) {
			uc = document.getElementById('uppercolor').value.replace('#', '');
			lc = document.getElementById('lowercolor').value.replace('#', '');
			hi = document.getElementById('headerimage');
			hi.value = 'header-img.php?upper='+uc+'&lower='+lc;
			document.getElementById('header').style.background = 'url("<?php echo get_template_directory_uri(); ?>/images/'+hi.value+'") center no-repeat';
			document.getElementById('advuppercolor').value = '#'+uc;
			document.getElementById('advlowercolor').value = '#'+lc;
		}
		if ( 'fontcolor' == oid ) {
			document.getElementById('header').style.color = document.getElementById('fontcolor').value;
			document.getElementById('advfontcolor').value = document.getElementById('fontcolor').value;
		}
		if ( 'fontdisplay' == oid ) {
			document.getElementById('headerimg').style.display = document.getElementById('fontdisplay').value;
		}
	}
	function toggleDisplay() {
		td = document.getElementById('fontdisplay');
		td.value = ( td.value == 'none' ) ? 'inline' : 'none';
		kUpdate('fontdisplay');
	}
	function toggleAdvanced() {
		a = document.getElementById('jsAdvanced');
		if ( a.style.display == 'none' )
			a.style.display = 'block';
		else
			a.style.display = 'none';
	}
	function kDefaults() {
		document.getElementById('headerimage').value = '';
		document.getElementById('advuppercolor').value = document.getElementById('uppercolor').value = '#69aee7';
		document.getElementById('advlowercolor').value = document.getElementById('lowercolor').value = '#4180b6';
		document.getElementById('header').style.background = 'url("<?php echo get_template_directory_uri(); ?>/images/kubrickheader.jpg") center no-repeat';
		document.getElementById('header').style.color = '#FFFFFF';
		document.getElementById('advfontcolor').value = document.getElementById('fontcolor').value = '';
		document.getElementById('fontdisplay').value = 'inline';
		document.getElementById('headerimg').style.display = document.getElementById('fontdisplay').value;
	}
	function kRevert() {
		document.getElementById('headerimage').value = '<?php echo kubrick_header_image(); ?>';
		document.getElementById('advuppercolor').value = document.getElementById('uppercolor').value = '#<?php echo kubrick_upper_color(); ?>';
		document.getElementById('advlowercolor').value = document.getElementById('lowercolor').value = '#<?php echo kubrick_lower_color(); ?>';
		document.getElementById('header').style.background = 'url("<?php echo kubrick_header_image_url(); ?>") center no-repeat';
		document.getElementById('header').style.color = '';
		document.getElementById('advfontcolor').value = document.getElementById('fontcolor').value = '<?php echo kubrick_header_color_string(); ?>';
		document.getElementById('fontdisplay').value = '<?php echo kubrick_header_display_string(); ?>';
		document.getElementById('headerimg').style.display = document.getElementById('fontdisplay').value;
	}
	function kInit() {
		document.getElementById('jsForm').style.display = 'block';
		document.getElementById('nonJsForm').style.display = 'none';
	}
	addLoadEvent(kInit);
// ]]>
</script>
<style type='text/css'>
	#headwrap {
		text-align: center;
	}
	#kubrick-header {
		font-size: 80%;
	}
	#kubrick-header .hibrowser {
		width: 780px;
		height: 260px;
		overflow: scroll;
	}
	#kubrick-header #hitarget {
		display: none;
	}
	#kubrick-header #header h1 {
		font-family: 'Trebuchet MS', 'Lucida Grande', Verdana, Arial, Sans-Serif;
		font-weight: bold;
		font-size: 4em;
		text-align: center;
		padding-top: 70px;
		margin: 0;
	}

	#kubrick-header #header .description {
		font-family: 'Lucida Grande', Verdana, Arial, Sans-Serif;
		font-size: 1.2em;
		text-align: center;
	}
	#kubrick-header #header {
		text-decoration: none;
		color: <?php echo kubrick_header_color_string(); ?>;
		padding: 0;
		margin: 0;
		height: 200px;
		text-align: center;
		background: url('<?php echo kubrick_header_image_url(); ?>') center no-repeat;
	}
	#kubrick-header #headerimg {
		margin: 0;
		height: 200px;
		width: 100%;
		display: <?php echo kubrick_header_display_string(); ?>;
	}
	#jsForm {
		display: none;
		text-align: center;
	}
	#jsForm input.submit, #jsForm input.button, #jsAdvanced input.button {
		padding: 0px;
		margin: 0px;
	}
	#advanced {
		text-align: center;
		width: 620px;
	}
	html>body #advanced {
		text-align: center;
		position: relative;
		left: 50%;
		margin-left: -380px;
	}
	#jsAdvanced {
		text-align: right;
	}
	#nonJsForm {
		position: relative;
		text-align: left;
		margin-left: -370px;
		left: 50%;
	}
	#nonJsForm label {
		padding-top: 6px;
		padding-right: 5px;
		float: left;
		width: 100px;
		text-align: right;
	}
	.defbutton {
		font-weight: bold;
	}
	.zerosize {
		width: 0px;
		height: 0px;
		overflow: hidden;
	}
	#colorPickerDiv a, #colorPickerDiv a:hover {
		padding: 1px;
		text-decoration: none;
		border-bottom: 0px;
	}
</style>
<?php
}

function kubrick_theme_page() {
	if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';
?>
<div class='wrap'>
	<div id="kubrick-header">
		<h2>Header Image and Color</h2>
		<div id="headwrap">
			<div id="header">
				<div id="headerimg">
					<h1><?php bloginfo('name'); ?></h1>
					<div class="description"><?php bloginfo('description'); ?></div>
				</div>
			</div>
		</div>
		<br />
		<div id="nonJsForm">
			<form method="post" action="">
				<div class="zerosize"><input type="submit" name="defaultsubmit" value="Save" /></div>
				<label for="njfontcolor">Font Color:</label><input type="text" name="njfontcolor" id="njfontcolor" value="<?php echo kubrick_header_color(); ?>" /> Any CSS color (<code>red</code> or <code>#FF0000</code> or <code>rgb(255, 0, 0)</code>)<br />
				<label for="njuppercolor">Upper Color:</label><input type="text" name="njuppercolor" id="njuppercolor" value="#<?php echo kubrick_upper_color(); ?>" /> HEX only (<code>#FF0000</code> or <code>#F00</code>)<br />
				<label for="njlowercolor">Lower Color:</label><input type="text" name="njlowercolor" id="njlowercolor" value="#<?php echo kubrick_lower_color(); ?>" /> HEX only (<code>#FF0000</code> or <code>#F00</code>)<br />
				<input type="hidden" name="hi" id="hi" value="<?php echo kubrick_header_image(); ?>" />
				<input type="submit" name="toggledisplay" id="toggledisplay" value="Toggle Text" />
				<input type="submit" name="defaults" value="Use Defaults" />
				<input type="submit" class="defbutton" name="submitform" value="&nbsp;&nbsp;Save&nbsp;&nbsp;" />
				<input type="hidden" name="action" value="save" />
				<input type="hidden" name="njform" value="true" />
			</form>
		</div>
		<div id="jsForm">
			<form style="display:inline;" method="post" name="hicolor" id="hicolor" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
				<input type="button" onclick="tgt=document.getElementById('fontcolor');colorSelect(tgt,'pick1');return false;" name="pick1" id="pick1" value="Font Color"></input>
				<input type="button" onclick="tgt=document.getElementById('uppercolor');colorSelect(tgt,'pick2');return false;" name="pick2" id="pick2" value="Upper Color"></input>
				<input type="button" onclick="tgt=document.getElementById('lowercolor');colorSelect(tgt,'pick3');return false;" name="pick3" id="pick3" value="Lower Color"></input>
				<input type="button" name="revert" value="Revert" onclick="kRevert()" />
				<input type="button" value="Advanced" onclick="toggleAdvanced()" />
				<input type="hidden" name="action" value="save" />
				<input type="hidden" name="fontdisplay" id="fontdisplay" value="<?php echo kubrick_header_display(); ?>" />
				<input type="hidden" name="fontcolor" id="fontcolor" value="<?php echo kubrick_header_color(); ?>" />
				<input type="hidden" name="uppercolor" id="uppercolor" value="<?php echo kubrick_upper_color(); ?>" />
				<input type="hidden" name="lowercolor" id="lowercolor" value="<?php echo kubrick_lower_color(); ?>" />
				<input type="hidden" name="headerimage" id="headerimage" value="<?php echo kubrick_header_image(); ?>" />
				<p class="submit"><input type="submit" name="submitform" class="defbutton" value="<?php _e('Update Header &raquo;'); ?>" onclick="cp.hidePopup('prettyplease')" /></p>
			</form>
			<div id="colorPickerDiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;visibility:hidden;"> </div>
			<div id="advanced">
				<form id="jsAdvanced" style="display:none;" action="">
					<label for="advfontcolor">Font Color (CSS): </label><input type="text" id="advfontcolor" onchange="advUpdate(this.value, 'fontcolor')" value="<?php echo kubrick_header_color(); ?>" /><br />
					<label for="advuppercolor">Upper Color (HEX): </label><input type="text" id="advuppercolor" onchange="advUpdate(this.value, 'uppercolor')" value="#<?php echo kubrick_upper_color(); ?>" /><br />
					<label for="advlowercolor">Lower Color (HEX): </label><input type="text" id="advlowercolor" onchange="advUpdate(this.value, 'lowercolor')" value="#<?php echo kubrick_lower_color(); ?>" /><br />
					<input type="button" name="default" value="Select Default Colors" onclick="kDefaults()" /><br />
					<input type="button" onclick="toggleDisplay();return false;" name="pick" id="pick" value="Toggle Text Display"></input><br />
				</form>
			</div>
		</div>
	</div>
</div>
<?php } ?>
