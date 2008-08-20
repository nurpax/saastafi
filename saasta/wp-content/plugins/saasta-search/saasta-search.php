<?php
/*
Plugin Name:Saasta Search
Plugin URI: http://saasta.fi/
Description: Better search for saasta.fi. Based on Jesse Heap's Search Tags plugin.
Version: 2008.08.21
Author: Mikko Uromo
Author URI: http://saasta.fi/
*/

add_filter('posts_where', array('SearchActions','saasta_search_where'));
add_filter('posts_join', array('SearchActions','saasta_search_join'));
add_filter('posts_groupby', array('SearchActions','saasta_search_groupby'));

class SearchActions {

	function saasta_search_where($where) {

		if (is_search()) {
			global $table_prefix, $wpdb, $wp_query;
			
			$searchInput = $wp_query->query_vars['s'];
			
			$searchInput = str_replace('"', '', $searchInput);
			$searchInput = str_replace("'", '', $searchInput);
			
			// changed plugin's default search to allow multiple tags -muumi
			$tags = explode(" ",$searchInput);

			// wp search query starts with "where 1=1", so let's wrap
			// our stuff inside AND(...)

			// ensure we get only published posts
			$where = " AND ($wpdb->posts.post_status='publish')";
			$where .= " AND (";
			// title & content
			$where .= "($wpdb->posts.post_title LIKE '%{$searchInput}%' OR $wpdb->posts.post_content LIKE '%{$searchInput}%')";
			// tags
			$where .= " OR (";
			for ($i = 0; $i < count($tags); $i++) {
				if ($i > 0) $where .= " OR ";
			    $where .= "(tr.name LIKE '%".trim($tags[$i])."%')";
			}
			$where .= ")";

			// author
			$where .= " OR ($wpdb->users.display_name LIKE '%".$searchInput."%')";

			$where .= ")";
		}
		return $where;
	}
		
	function saasta_search_join($join) {
		if (is_search()) {
			global $table_prefix, $wpdb;
			
			$tabletags = $table_prefix . "terms";
			$tablepost2tag = $table_prefix . "term_relationships";
			$tabletaxonomy = $table_prefix . "term_taxonomy";
			
			$join .= " LEFT JOIN (select distinct tr.object_id, t.name from $tablepost2tag tr inner join $tabletaxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id inner join $tabletags t on t.term_id = tt.term_id where tt.taxonomy='post_tag') tr on $wpdb->posts.ID = tr.object_id ";

			/* another change to search by author as well */
			$join .= " LEFT JOIN $wpdb->users ON ($wpdb->posts.post_author = $wpdb->users.ID)";
		}

		return $join;
	}

	function saasta_search_groupby( $groupby )
	{
		global $wpdb;
		
		if( !is_search() ) {
			return $groupby;
		}
	
		// we need to group on post ID
		
		$mygroupby = "{$wpdb->posts}.ID";
		
		if( preg_match( "/$mygroupby/", $groupby )) {
			// grouping we need is already there
			return $groupby;
		}
		
		if( !strlen(trim($groupby))) {
			// groupby was empty, use ours
			return $mygroupby;
		}
		
		// wasn't empty, append ours
		return $groupby . ", " . $mygroupby;
	}	
}
?>
