<?php

/**
 * Add a filter to add the query by post parent to the $query
 *
 */

add_filter('wpv_filter_query', 'wpv_filter_post_parent', 10, 2);
function wpv_filter_post_parent($query, $view_settings) {
    
    global $WP_Views, $wpdb, $sitepress;
    
    if (isset($view_settings['parent_mode'][0])) {
        if ($view_settings['parent_mode'][0] == 'current_page') {
            $query['post_parent'] = $WP_Views->get_current_page()->ID;
        }
        if ($view_settings['parent_mode'][0] == 'this_page') {
            if (isset($view_settings['parent_id']) && $view_settings['parent_id'] > 0) {
                $query['post_parent'] = $view_settings['parent_id'];
                if (isset($sitepress) && function_exists('icl_object_id')) {
                    $post_type = $wpdb->get_var( $wpdb->prepare( "SELECT post_type FROM {$wpdb->posts} WHERE ID = %d", $query['post_parent'] ) );
                    if ($post_type) {
                        $query['post_parent'] = icl_object_id($query['post_parent'], $post_type, true);
                    }
                }
            } else {
                // filter for items with no parents
                $query['post_parent'] = 0;
            }
        }
    }
    
    return $query;
}

add_filter('wpv_filter_requires_current_page', 'wpv_filter_parent_requires_current_page', 10, 2);
function wpv_filter_parent_requires_current_page($state, $view_settings) {
	if ($state) {
		return $state; // Already set
	}

    if (isset($view_settings['parent_mode'][0])) {
        if ($view_settings['parent_mode'][0] == 'current_page') {
            $state = true;
        }
    }
    
    return $state;
}

    


