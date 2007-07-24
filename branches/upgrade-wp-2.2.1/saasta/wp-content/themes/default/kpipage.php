<?php
/*
Template Name: KPI
*/
?>

<?php get_header(); ?>

<div id="content" class="widecolumn">

<h2>Key Performance Indicators</h2>

<br/>
Amount of filth posted by each registered user.

<?php

function query_quarterly_stats ()
{
    $q_stats = $wpdb->get_results("select 
    su.user_login,
    concat(year(post_date),'_',ceil(month(sp.post_date)/3)) as quarter,
    count(su.user_login) as num_posts
from 
    $wpdb->posts as sp,$wpdb->users as su 
where 
    post_date>0 and 
    su.ID=sp.post_author
group by
    sp.post_author,quarter
order by
    quarter desc, num_posts desc, su.user_login");

}

function find_user_name($users, $id)
{
    foreach ($users as $u)
    {
        if ($u->ID == $id)
            return $u->display_name;
    }
    return "unknown";
}

   $users = $wpdb->get_results("SELECT ID,display_name FROM $wpdb->users");
?>

<?php if ($users): ?>
<?php 
$n_posts_per_uid=array();

foreach ($users as $u) {
    $n_posts = $wpdb->get_results("SELECT COUNT(*) as nposts FROM $wpdb->posts WHERE post_author=$u->ID");
    $n_posts_per_uid[$u->ID] = $n_posts[0]->nposts;
}

printf("  Number of posts by admin (%d posts) is omitted for historical reasons.<br><br>", $n_posts_per_uid[1]);

arsort($n_posts_per_uid);
?>

<table>
<tr><th>User</th><th># of posts</th>
<?php
foreach ($n_posts_per_uid as $uid => $nposts) {
    if($uid != 1) {
        printf("<tr><td>%s</td><td>$nposts</td></tr>\n", find_user_name($users, $uid));
    }
}?>
</table>

<?php endif; ?>

</div>

<?php get_footer(); ?>
