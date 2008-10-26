<?php
/*
Template Name: FAVORITES
*/
?>

<?php get_header(); 

require_once(ABSPATH . '/saasta-common.php');
?>

<div id="content" class="narrowcolumn">

<?php

function saasta_query_user_faves($user) 
{
    global $wpdb;

    $foo = $wpdb->get_results("select p.post_title as title,f.post_id as post_id from ".$wpdb->posts." p,".$wpdb->prefix."faves f where f.post_id=p.ID and f.user_id=".$user->ID." ORDER BY f.fave_date desc, title");

    return $foo;
}

$user = wp_get_current_user();
?>

<h2>All Faved Posts in the Previous Four Quarters</h2>

<?php

if (isset($_REQUEST['all_faves'])) 
{
    echo '<br/><a href="' . get_permalink(922) . '">Click here to hide..</a></br>';

    $quarters = saasta_prev_quarters(4);

    foreach ($quarters as $q)
    {
        $qrtr = $q["q"];
        $year = $q["y"];
        print "<h3>Q".$qrtr."/".$year."</h3>";

        echo '<table><tr><th></th><th>Author</th><th>Faves</th><th>Title</th></tr>';
        $r = saasta_query_top_faved_posts($qrtr,$year);
        $cnt = 1;
        foreach ($r as $f)
        {
            $url = get_permalink($f->post_id);
            echo '<tr><td style="padding-right:1.0em;">' . $cnt . '</td>';
            echo '<td>' . $f->author . '</td>';
            echo '<td>' . $f->fave_count . '</td>';
            echo '<td><a href="' . $url . '">';
            echo $f->title == "" ? $url : $f->title;
            print "</a>";
            echo "</tr>\n";
            $cnt++;
        }
        echo '</table>';
    }

} else
{
    echo '<br/><a href="' . get_permalink(922) . '&all_faves=1">Click here to expand..</a></br>';
}

if ( $user->ID ) {
?>

<h2>Your favorites</h2>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 

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
