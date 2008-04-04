<?php

if (empty($wp)) {
	require_once('./wp-config.php');
 }

get_currentuserinfo();
// no user
if ('' == $user_ID)
	return;

/*
function query_total_num_posts ()
{
    global $wpdb;
    $q_stats = $wpdb->get_results("
SELECT 
    COUNT(p.ID) as num_posts
FROM    
    $wpdb->posts as p 
WHERE        
    post_date>0 and 
    post_status='publish'");

    return $q_stats;
}
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

function query_top_faved_posts($q,$y) {
	global $wpdb;

	$query = "SELECT DISTINCT 
       p.post_title        AS title,   
       u.display_name      AS author,
       COUNT(p.post_title) AS fave_count,
       f.post_id           AS post_id,
       CONCAT('http://saasta.fi/saasta/?p=',f.post_id) AS url,
       DATE_FORMAT(p.post_date, '%b %d, %Y')           AS date_posted
FROM 
     saasta_posts p,
     saasta_faves f,
     saasta_users u
WHERE 
      f.post_id=p.ID AND 
      p.post_author <> f.user_id AND
      p.post_author=u.ID AND      
      QUARTER(p.post_date)=".$q." AND
      YEAR(p.post_date)=".$y."
GROUP BY 
      p.post_title 
ORDER BY 
      fave_count DESC";

	return $wpdb->get_results($query);
}

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

//$n_posts = query_total_num_posts();

?>

<html>

<head>
<title>Saasta.fi statistics</title>
</head>

<body>

<p style="border:2px black solid; padding: 1.0em; background-color: #caccaa;">
<b>LÉ MOTHERFUCKING MENU SILVUPLEE</b><br>
<br>
<a href="wp-saasta-stats.php">posts per quarter</a><br>
<a href="wp-saasta-stats.php?m=faves">top faved posts per quarter</a><br>
<a href="wp-saasta-stats.php?m=tags">top tags</a><br>
</p>

<h1>saasta.fi statistics page</h1>

<?php

$mode = $_REQUEST['m'];

if ($mode == 'tags') {
	$foo = query_top_tags();
	print '<table cellspacing="2"><tr><td><b>tag</b></td><td><b>num posts</b></td></tr>';
	foreach ($foo as $f) {
		print '<tr><td>'.$f->tag.'</td><td>'.$f->num_posts.'</td></tr>';
	}
	print '</table>';
 }
 else 

for ($year = 2008; $year >= 2007; $year--)
	for ($q = 4; $q >= 1; $q--) {
?>
<p>
<table>

<?php


if ($mode == 'faves') {
	print '<tr><th style="background-color:#cccccc;" colspan="6">Q'.$q.'/'.$year.'</th></tr>';

	print '<tr>
<th>title</th>
<th>author</th>
<th>num faves</th>
<th>post id</th>
<th>url</th>
<th>date posted</th>
</tr>';

	$foo = query_top_faved_posts($q,$year);
	foreach ($foo as $f) {
		print '<tr>';
		print '<td>'.$f->title.'</td>';
		print '<td>'.$f->author.'</td>';
		print '<td>'.$f->fave_count.'</td>';
		print '<td>'.$f->post_id.'</td>';
		print '<td><a href="'.$f->url.'">view saasta</a></td>';
		print '<td>'.$f->date_posted.'</td>';
		print '</tr>';
	}
}
else {
	print '<tr><th style="background-color:#cccccc;" colspan="2">Q'.$q.'/'.$year.'</th></tr>';

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





