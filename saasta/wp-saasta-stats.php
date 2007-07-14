<?php
require(dirname(__FILE__) . '/wp-blog-header.php');

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


		    $n_posts = query_total_num_posts();
?>

<html>

<head>
  <title>Saasta.fi statistics</title>
</head>

<body>
<h1>saasta.fi statistics page</h1>

<p>Page created on: <?php print date("m/d/Y"); ?></p>
<table>
  <tr><th>Descr</th><th>Value</th></tr>
  <tr><td>Total # of posts</td><td><?php print $n_posts[0]->num_posts; ?></td></tr>
</table>

</body>

</html>

