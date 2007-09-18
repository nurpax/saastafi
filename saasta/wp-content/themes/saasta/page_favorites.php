<?php
/*
Template Name: FAVORITES
*/
?>

<?php get_header(); ?>

<div id="content" class="narrowcolumn">

<?php

function saasta_query_user_faves($user) 
{
    global $wpdb;

    $foo = $wpdb->get_results("select p.post_title as title,f.post_id as post_id from saasta_posts p,saasta_faves f where f.post_id=p.ID and f.user_id=".$user->ID." ORDER BY title");

    return $foo;
}

function saasta_query_q3_2007_faves($user) 
{
    global $wpdb;

    $foo = $wpdb->get_results("select DISTINCT p.post_title as title,f.post_id as post_id,dATE_FORMAT(p.post_date, '%b %d, %Y') as date_posted from saasta_posts p,saasta_faves f where f.post_id=p.ID AND DATE(p.post_date) >= '2007-07-01' AND DATE(p.post_date) < '2007-10-01' ORDER BY p.post_date");

    return $foo;
}

$user = wp_get_current_user();
if ( $user->ID ) {
?>

<h2>All Faved Posts of Q3/2007</h2>

<?php

if (isset($_REQUEST['all_faves'])) 
{
    echo '<a href="' . get_permalink(922) . '">Click here to hide..</a>';

    echo '<table>';
    $r = saasta_query_q3_2007_faves($user);
    $cnt = 1;
    foreach ($r as $f)
    {
        echo '<tr><td style="padding:0.2em;">' . $cnt . '</td>';
        echo '<td> </td><td>' . $f->date_posted . '</td>';
        echo '<td><a href="' . get_permalink($f->post_id) . '">';
        echo $f->title . "</a>";
        print "</tr>\n";
        $cnt++;
    }
    echo '</table>';

} else
{
    echo '<a href="' . get_permalink(922) . '&all_faves=1">Click here to expand..</a>';
}

?>

<h2>Your favorites</h2>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 

        //    $foo = saasta_query_user_faves($user);
    $foo = saasta_query_user_faves($user);

    if (count($foo) > 0) {
        foreach ($foo as $f) {
            print '<tr><td width="100%" style="padding-top:0.5em;"><a href="'.get_permalink($f->post_id).'" title="'.$f->title.'">'.$f->title.'</a></td>';
            print '<td valign="middle" align="right" style="padding-top:0.5em;">';

            saasta_print_del_fave_form($f->post_id);

            print '</td></tr>';
            print '<tr><td colspan="2" style="height:0.5empx;border-top:1px dashed #666666;"></td></tr>';
        }
    }

?>
</table>
<?php } ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
