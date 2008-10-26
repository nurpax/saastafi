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

<h2>Faved Posts in Previous Quarters</h2>

<?php

$quarters = saasta_prev_quarters(4);

function print_faved_posts($user_id)
{
    global $quarters;

    $title_prefix = "Most faved posts in";
    if ($user_id)
        $title_prefix = "My faves in";

    foreach ($quarters as $q)
    {
        $qrtr = $q["q"];
        $year = $q["y"];

        // TODO misleading title for own faves
        print "<h3>$title_prefix Q".$qrtr."/".$year."</h3>";

        if (!$user_id)
        {
            $r = saasta_query_top_faved_posts($qrtr,$year);

            echo '<table><tr><th>Author</th><th>Faves</th><th>Title</th></tr>';
        }
        else
        {
            $r = saasta_query_top_faved_posts_for_user($user_id, $qrtr,$year);

            echo '<table><tr><th>Author</th><th>Title</th></tr>';
        }
            
        foreach ($r as $f)
        {
            $url = get_permalink($f->post_id);
            echo '<tr><td>' . $f->author . '</td>';

            if (!$user_id)
                echo '<td>' . $f->fave_count . '</td>';

            echo '<td><a href="' . $url . '">';
            echo $f->title == "" ? $url : $f->title;
            print "</a>";
            echo "</tr>\n";
        }
        echo '</table>';
    }
}

if (isset($_REQUEST['all_faves'])) 
{
    echo '<br/><a href="' . get_permalink(922) . '">Click here to hide..</a></br>';
    
    // user_id == 0 -- all users
    print_faved_posts(0);

} else
{
    echo '<br/><a href="' . get_permalink(922) . '&all_faves=1">Click here to expand..</a></br>';
}

if ( $user->ID ) {
    print "<h2>Your favorites</h2>";
    print_faved_posts($user->ID);
} 

?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
