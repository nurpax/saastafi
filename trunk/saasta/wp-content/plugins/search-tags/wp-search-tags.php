<?php
/*
Plugin Name:Search Tags
Plugin URI: http://projects.jesseheap.com/
Description: Ensure wordpress TAGS are searched.  Adapted from UTW
Version: 1.0
Author: Jesse Heap
Author URI: http://projects.jesseheap.com
*/

add_filter('posts_where', array('SearchActions','ultimate_search_where'));
add_filter('posts_join', array('SearchActions','ultimate_search_join'));
add_filter('posts_groupby', array('SearchActions','ultimate_search_groupby'));

class SearchActions {

	function ultimate_search_where($where) {
		if (is_search()) {
			global $table_prefix, $wpdb, $wp_query;
			$tabletags = $table_prefix . "terms";
			$tablepost2tag = $table_prefix . "term_relationships";
			$tabletaxonomy = $table_prefix . "term_taxonomy";
			
			$searchInput = $wp_query->query_vars['s'];
			
			$searchInput = str_replace('"', '', $searchInput);
			$searchInput = str_replace("'", '', $searchInput);
			$where .= " OR (tr.name like '%" . $searchInput . 
				"%' AND post_status = 'publish')";
		}
	
		return $where;
	}
		
	function ultimate_search_join($join) {
		if (is_search()) {
			global $table_prefix, $wpdb;
			
			$tabletags = $table_prefix . "terms";
			$tablepost2tag = $table_prefix . "term_relationships";
			$tabletaxonomy = $table_prefix . "term_taxonomy";
			
			$join .= " LEFT JOIN (select distinct tr.object_id, t.name from $tablepost2tag tr inner join $tabletaxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id inner join $tabletags t on t.term_id = tt.term_id where tt.taxonomy='post_tag') tr on $wpdb->posts.ID = tr.object_id ";
		}
		return $join;
	}

	function ultimate_search_groupby( $groupby )
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
