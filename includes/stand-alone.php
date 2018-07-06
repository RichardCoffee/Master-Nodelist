<?php

if ( ! function_exists( 'get_page_slug' ) ) {
	function get_page_slug() {
		$slug = '';
		if ( ( ! is_admin() ) && $wp_query->is_main_query() ) {
			if ( is_home() && empty( $wp_query->query_string ) ) {
				$slug = 'home';
			} else if ( get_option('page_on_front') && ( $wp_query->get('page_id') === get_option('page_on_front') ) ) {
				$slug = 'front';
			} else {
				$page = get_queried_object();  #  $wp_query->queried_object
				if ( is_object( $page ) ) {
					if ( isset( $page->post_type ) && ( $page->post_type === 'page' ) ) {
						$slug = $page->post_name;
					} else if ( isset( $page->post_name ) ) {
						$slug = $page->post_name;
					} else if ( $page instanceof WP_User ) {
						$slug = 'author';
					} else {
						$slug = $page->name;
					}
				}
			}
		}
		return $slug;
	}
}
