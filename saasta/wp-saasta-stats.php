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

//$n_posts = query_total_num_posts();

?>

<html>

<head>
<title>Saasta.fi statistics</title>
</head>

<body>

<h1>saasta.fi statistics page</h1>

<?php
for ($year = 2008; $year >= 2007; $year--)
	for ($q = 4; $q >= 1; $q--) {
?>
<p>
<table>
<tr>
<th colspan="2"><?php echo "Q".$q."/".$year; ?></th>
<tr>
<th>name</th>
<th>num posts</th>
</tr>
<?php

$foo = query_num_posts_in_quarter($q, $year);
$totalPosts = 0;
foreach ($foo as $f) {
	print "<tr>";
	print "<td>".$f->name."</td>";
	print "<td>".$f->num_posts."</td>";
	print "</tr>";

	$totalPosts += $f->num_posts;
}

print "<tr><td><b>total:</b></td><td><b>".$totalPosts."</b></td></tr>";

?>
</table>
</p>
<?php } ?>

</body>
</html>





