<?php
/*
Template Name: KPI2
*/
?>

<?php get_header(); ?>

<?php

function query_quarterly_stats ()
{
    global $wpdb;
    $q_stats = $wpdb->get_results("
SELECT 
    su.user_login as user,
    concat(year(post_date),'_',ceil(month(sp.post_date)/3)) as quarter,
    concat('Q', ceil(month(sp.post_date)/3),', ', year(post_date)) as quarter_pretty,
    count(su.user_login) as num_posts
FROM
$wpdb->posts as sp,$wpdb->users as su 
WHERE 
    post_date>0 and 
    post_status='publish' and
    su.ID=sp.post_author and
    su.user_login <> 'admin'
GROUP BY
    sp.post_author,quarter
ORDER BY
    quarter desc, num_posts desc, su.user_login");
    return $q_stats;
}

function saasta_trends()
{
    global $wpdb;

    print "<p>Number of saasta posts per day during the last 30 days</p>\n";

    print "<table style=\"vertical-align:bottom\">";
    print "<tr>";

    // Last 31 days
    for ($d = 30; $d >= 0; $d--) 
    {
        $q = $wpdb->get_results("
SELECT DATE_SUB(CURDATE(), INTERVAL $d DAY) as day, count(*) as num_posts
  FROM $wpdb->posts 
  WHERE date_format(post_date, '%Y-%m-%d') = date_format(DATE_SUB(CURDATE(), INTERVAL $d DAY),'%Y-%m-%d')");
        
        $day = $q[0]->day;
        $num_posts = $q[0]->num_posts;
        $h = $num_posts * 3;

        print "<td style=\"vertical-align:bottom;\"><img title=\"{$day}: {$num_posts} posts\" src=\"bargraph.png\" style=\"height:{$h}px;width:0.5em; border:1px solid black;\"/></td>\n";
    }

    print "</tr>";
    print "</table>";
}

?>

<div id="content" class="narrowcolumn">

<h2>Key Performance Indicators</h2>

<?php 
$q_stats = query_quarterly_stats();

// &trends=1 in URL -> launch Saasta Trends (TM)
if ($_GET{'trends'})
{
    saasta_trends();
}
?>

<br/>
Amount of filth posted by each registered user.
<?php 

$q_stats = query_quarterly_stats();

print "<table>\n";

$quarter = "";
$qcnt = 0;
$quarterData = array();
$totalPosts = 0;

function kpi_render_bargraph($quarterData, $totalPosts) {
    foreach ($quarterData as $qd) {
        print "<tr><td>{$qd[0]}</td><td width=\"200\">";
        $w = round($qd[1]*200/$totalPosts);
        $pct = round($qd[1]*100/$totalPosts);
        print "<img src=\"bargraph.png\" style=\"width:{$w}px;height:1.0em;border:1px solid black;\"/>";
        print "</td><td>{$qd[1]}</td><td><small>({$pct}%)</small></td></tr>";
    }
}

foreach ($q_stats as $q) {
    if ($qcnt > 1) {
        kpi_render_bargraph($quarterData, $totalPosts);
        break;
    }
    
    if ($quarter == "") {
        print "<tr><th colspan=\"4\" style=\"padding-top:1.0em;border-bottom:1px solid black;\">Current quarter</th></tr>\n";
    }
    else if ($quarter != $q->quarter_pretty) {
        // new quarter, output previous quarter results as bar graph
        kpi_render_bargraph($quarterData, $totalPosts);

	// print quarter total
        print "<tr><th colspan=\"4\" style=\"border-top:1px solid black;height:1px;\"></th></tr>\n";
        print '<tr><th colspan="2" align="right"><b>Total</b></th><th colspan="2">'.$totalPosts.'</th></tr>';

        // reset quarter results
        unset($quarterData);
        $quarterData = array();
        $qdCount = 0;
        $totalPosts = 0;
        // advance to next quarter
        $qcnt++;
        print "<tr><th colspan=\"4\" style=\"padding-top:1.0em;border-bottom:1px solid black;\"><strong>$q->quarter_pretty</strong></th></tr>\n";
    }
  
    $quarter = $q->quarter_pretty;
    
    $quarterData[] = array($q->user, $q->num_posts);
    $totalPosts += $q->num_posts;
}

print ("</table>\n");

?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
