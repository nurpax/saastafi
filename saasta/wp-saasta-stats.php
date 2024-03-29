<?php
header ('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
if (empty($wp)) {
	require_once('./wp-config.php');
}
require_once('./saasta-common.php');

get_currentuserinfo();
// no user
if ('' == $user_ID)
	return;

/**
 * Retrieve number of posts in a specific quarter
 *
 * @param q Quarter (1..4)
 * @param y Year
 * @return list of associated arrays {name,num_posts}
 */
function query_num_posts_in_quarter($q, $y) {
	global $wpdb;

	$query = "SELECT su.display_name as name, count(sp.ID) as num_posts
FROM saasta_posts sp, 
     saasta_users su 
WHERE sp.post_status='publish' AND 
      su.ID=sp.post_author AND
      QUARTER(sp.post_date)=".$q." AND
      YEAR(sp.post_date)=".$y."
GROUP BY 
      su.ID 
ORDER BY 
      num_posts desc, 
      name";

    return $wpdb->get_results($query);
}

/**
 * Retrieve list of most popular tags used in posts
 *
 * @return list of associated arrays {tag, num_posts}
 */
function query_top_tags() {
	global $wpdb;

	$query = "SELECT
        term.name as tag,
        tax.count as num_posts
FROM 
     saasta_terms term,
     saasta_term_taxonomy tax
WHERE
        tax.term_id = term.term_id AND
        tax.taxonomy = 'post_tag' AND
        tax.count > 0
ORDER BY
      num_posts DESC,
      tag";

	return $wpdb->get_results($query);
}

/**
 * Retrieve list of users with most comments in a specific quarter.
 *
 * @param q Quarter (1..4)
 * @param y Year
 * @return list of associated arrays {name, num_comments}
 */
function query_top_commenters_in_quarter($q, $y) {
	global $wpdb;

	$query = "
SELECT
    su.display_name AS name,
    COUNT(sc.comment_ID) AS num_comments 
FROM
    saasta_comments sc, saasta_users su 
WHERE 
    QUARTER(comment_date)=".$q." AND 
    YEAR(comment_date)=".$y." AND
    sc.comment_author=su.display_name 
GROUP BY
    name 
ORDER BY
    num_comments desc,name";
	return $wpdb->get_results($query);
}

/**
 * Retrieve list of users who've added most faves in a specific
 * quarter.
 *
 * @param q Quarter (1..4)
 * @param y Year
 * @return list of associated arrays {name, num_faves}
 */
function query_top_favers_in_quarter($q, $y) {
	global $wpdb;

	$query = "
SELECT 
    su.display_name AS name,
    COUNT(sf.user_id) AS num_faves
FROM
    saasta_posts sp, saasta_faves sf, saasta_users su
WHERE
    QUARTER(sp.post_date)=".$q." AND 
    YEAR(sp.post_date)=".$y." AND
    sf.user_id=su.ID AND
    sp.ID = sf.post_id
GROUP BY
    su.ID
ORDER BY
    num_faves DESC,
    name
";

	return $wpdb->get_results($query);
}

/**
 * Retrieve list of best posters per quarter.
 *
 * UNDER CONSTRUCTION - FEEL FREE TO MODIFY
 *
 * Currently sorts by user's faveweight which is calculated as:
 *
 *       faveWeight = num_faves / num_posts * sqrt(num_posts)
 *
 * @param q Quarter (1..4)
 * @param y Year
 * @return list of associated arrays {name, num_faves, num_posts,
 * favesPerPost, postsPerFave, faveWeight}
 */
function query_best_posters_in_quarter($q, $y) {
	global $wpdb;

	// first fetch some basic stuff: user id, username and num faves
	$query = "
SELECT
    su.ID AS id, 
    su.display_name AS name, 
    COUNT(sf.post_id) AS num_faves 
FROM 
    saasta_posts sp, saasta_users su, saasta_faves sf
WHERE 
    QUARTER(sp.post_date)=".$q." AND 
    YEAR(sp.post_date)=".$y." AND 
    sp.post_author=su.ID AND 
    sf.post_id=sp.ID AND
    sp.post_status='publish'
GROUP BY
    su.ID
ORDER BY
    num_faves DESC
";
	$ret = $wpdb->get_results($query);

	// then fetch total number of posts per quarter
	$tmp = $wpdb->get_row("SELECT COUNT(ID) as num_posts FROM saasta_posts WHERE QUARTER(post_date)=".$q." AND YEAR(post_date)=".$y." AND post_status='publish'");
	$totalPosts = $tmp->num_posts;

	// then the total number of faves per quarter
	$tmp = $wpdb->get_row("SELECT COUNT(sf.post_id) as num_faves FROM saasta_faves sf, saasta_posts sp WHERE QUARTER(sp.post_date)=".$q." AND YEAR(sp.post_date)=".$y." and sf.post_id=sp.ID AND sp.post_status='publish'");
	$totalFaves = $tmp->num_faves;

	// finally, go through each user and fetch
	foreach ($ret as $user) {
		$tmp = $wpdb->get_row("SELECT COUNT(ID) as num_posts FROM saasta_posts WHERE post_author=".$user->id." AND QUARTER(post_date)=".$q." AND YEAR(post_date)=".$y." and post_status='publish'");
		$user->num_posts = $tmp->num_posts;
		$user->favesPerPost = $user->num_faves / $user->num_posts;
		$user->postsPerFave = $user->num_posts / $user->num_faves;
		$user->faveWeight = $user->favesPerPost * sqrt($user->num_posts);
	}

	// and before we go, sort our list based on something custom
	usort($ret, "best_posters_cmp");

	return $ret;
}

/**
 * Simple user-defined comparison function for
 * query_best_posters_in_quarter().
 *
 * UNDER CONSTRUCTION!
 */
function best_posters_cmp($a, $b) {
	// lame comparison
	//if ($a->favesPerPost <= $b->favesPerPost)
	if ($a->faveWeight <= $b->faveWeight)
		return 1;
	else
		return -1;
}

/**
 */
function query_most_commented_posts($q, $y) {
	global $wpdb;

	$query = "
SELECT
  COUNT(sc.comment_post_ID) AS num_comments,
  sp.post_title AS title,
  CONCAT('".get_bloginfo('wpurl')."/?p=',sp.ID) AS url,
  su.display_name AS author
FROM
  saasta_posts sp, saasta_users su, saasta_comments sc
WHERE
  QUARTER(sp.post_date)={$q} AND
  YEAR(sp.post_date)={$y} AND
  su.ID=sp.post_author AND
  sc.comment_post_ID=sp.ID  
GROUP BY
  sc.comment_post_ID
ORDER BY
  num_comments DESC
";

	return $wpdb->get_results($query);
}

function query_user_activity() {
	global $wpdb;

	$query = "
SELECT
	su.display_name AS jope,
	COUNT(sp.ID)/(TO_DAYS(NOW())-TO_DAYS(su.user_registered)) AS posts_per_day,
	COUNT(sp.ID) AS num_posts,
	(TO_DAYS(NOW())-TO_DAYS(su.user_registered)) AS days_active
FROM
	saasta_users su, saasta_posts sp
WHERE
	sp.post_author = su.ID AND
	sp.post_status = 'publish'
GROUP BY
	su.ID
ORDER BY
	posts_per_day, jope
";

	return $wpdb->get_results($query);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>Saasta.fi statistics</title>
<style type="text/css">
body,td,th { font: 1.0em 'Courier New', Courier, Fixed; }
th { background-color: #cccccc; font-weight: bold; border-bottom: 2px solid black; }
td { border-bottom: 1px dashed #999999; }
</style>
</head>

<body>

<h1>saasta.fi top sekret statistics page</h1>

<p style="border:2px black solid; padding: 1.0em; background-color: #caccaa;">
<b>LE MOTHERFUCKING MENU SILVUPLEE</b><br>
<br>
<a href="wp-saasta-stats.php">posts per quarter</a><br>
<a href="wp-saasta-stats.php?m=faves">top faved posts per quarter</a><br>
<a href="wp-saasta-stats.php?m=tags">top tags</a><br>
<a href="wp-saasta-stats.php?m=commenters">top commenters</a><br>
<a href="wp-saasta-stats.php?m=bestposters">best posters per quarter (UNDER CONSTRUCTION)</a><br>
<a href="wp-saasta-stats.php?m=topfavers">top favers per quarter</a><br>
<a href="wp-saasta-stats.php?m=mostcommented">most commented posts per quarter</a><br>
<a href="wp-saasta-stats.php?m=useractivity">user activity</a><br>
</p>

<?php

$mode = $_REQUEST['m'];

if ($mode == 'tags') {
	$foo = query_top_tags();
	print '<table cellspacing="2"><tr><th>tag</th><th>num posts</th></tr>';
	foreach ($foo as $f) {
		print '<tr><td><a href="/saasta/?tag='.$f->tag.'">'.$f->tag.'</a></td><td>'.$f->num_posts.'</td></tr>';
	}
	print '</table>';
}
else if ($mode == 'useractivity') {
	$foo = query_user_activity();
	print '<table cellspacing="2"><tr><th>user</th><th>posts per day</th><th>num posts</th><th>days active</th></tr>';
	foreach ($foo as $f) {
		print '<tr><td>'.$f->jope.'</td><td>'.$f->posts_per_day.'</td><td>'.$f->num_posts.'</td><td>'.$f->days_active.'</td></tr>';
	}
	print '</table>';
}
else 
for ($year = 2008; $year >= 2007; $year--)
	for ($q = 4; $q >= 1; $q--) {
?>
<p>
<table border="0" cellspacing="0" cellpadding="4">
<?php

if ($mode == 'mostcommented') {
	print '<tr><th colspan="4">Q'.$q.'/'.$year.'</th></tr>';
	print '<tr><th>post</th><th>num comments</th><th>author</th></tr>';
	$foo = query_most_commented_posts($q, $year);
	foreach ($foo as $f) {
		print "<tr>";
		print "<td><a href=\"{$f->url}\">{$f->title}</a></td>";
		print "<td>{$f->num_comments}</td>";
		print "<td>{$f->author}</td>";
		print "</tr>";
	}
}
else if ($mode == 'bestposters') {
	print '<tr><th colspan="5">Q'.$q.'/'.$year.'</th></tr>';
	print '<tr><th>name</th><th>num faves</th><th>num posts</th><th>faves per post</th><th>fave weight</th></tr>';
	$foo = query_best_posters_in_quarter($q, $year);
	foreach ($foo as $f) {
		print "<tr>";
		print "<td>".$f->name."</td>";
		print "<td>".$f->num_faves."</td>";
		print "<td>".$f->num_posts."</td>";
		printf("<td>%.3f</td>", $f->favesPerPost);
		printf("<td>%.3f</td>", $f->faveWeight);
		print "</tr>";	   
	}	
}
else if ($mode == 'faves') {
	print '<tr><th colspan="6">Q'.$q.'/'.$year.'</th></tr>';

	print '<tr><th>title</th><th>author</th><th>num faves</th><th>post id</th><th>date posted</th></tr>';

	$foo = saasta_query_top_faved_posts($q,$year);
	foreach ($foo as $f) {
		print '<tr>';
		print '<td><a href="'.$f->url.'">'.$f->title.'</a></td>';
		print '<td>'.$f->author.'</td>';
		print '<td>'.$f->fave_count.'</td>';
		print '<td>'.$f->post_id.'</td>';
		print '<td>'.$f->date_posted.'</td>';
		print '</tr>';
	}
}
else if ($mode == 'topfavers') {
	print '<tr><th colspan="2">Q'.$q.'/'.$year.'</th></tr>';
	print '<tr><th>user</th><th>num posts faved</th></tr>';

	$foo = query_top_favers_in_quarter($q,$year);
	foreach ($foo as $f) {
		print '<tr>';
		print '<td>'.$f->name.'</td>';
		print '<td>'.$f->num_faves.'</td>';
		print '</tr>';
	}
}
else if ($mode == 'commenters') {
	print '<tr><th colspan="2">Q'.$q.'/'.$year.'</th></tr>';
	$foo = query_top_commenters_in_quarter($q, $year);
	print '<tr><th>name</th><th>num comments</th></tr>';
	foreach ($foo as $f) {
		print "<tr>";
		print "<td>".$f->name."</td>";
		print "<td>".$f->num_comments."</td>";
		print "</tr>";	   
	}	
 } 
 else {
	print '<tr><th colspan="2">Q'.$q.'/'.$year.'</th></tr>';

	$foo = query_num_posts_in_quarter($q, $year);

	print '<tr><th>name</th><th>num posts</th></tr>';

	$totalPosts = 0;
	foreach ($foo as $f) {
		print "<tr>";
		print "<td>".$f->name."</td>";
		print "<td>".$f->num_posts."</td>";
		print "</tr>";
		
		$totalPosts += $f->num_posts;
	}
	
	print "<tr><td><b>total:</b></td><td><b>".$totalPosts."</b></td></tr>";
 }

?>
</table>
</p>
<?php } ?>

</body>
</html>





