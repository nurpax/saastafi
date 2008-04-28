<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title>saasta.fi <?php if ( is_single() ) { ?> &raquo; Archive <?php } ?> <?php wp_title(); ?></title>

<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen"/>
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="favicon.ico"/>
<style type="text/css" media="screen">

<?php
// Checks to see whether it needs a sidebar or not
if ( !$withcomments && !is_single() ) {
?>
	#page { background: url("<?php bloginfo('stylesheet_directory'); ?>/images/saasta_bg.gif") repeat-y top; border: none; }
<?php } else { // No sidebar ?>
	#page { background: url("<?php bloginfo('stylesheet_directory'); ?>/images/saasta_bg.gif") repeat-y top; border: none; }
<?php } ?>
</style>

<?php wp_head(); ?>
</head>
<body>
<div id="page">
<div id="header">
	<div id="headerimg">
	<a href="<?php echo get_option('home'); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/transp.gif" style="width:100%;height:100%;" border="0" alt="saasta.fi"/></a>
	</div>
</div>
<hr />
