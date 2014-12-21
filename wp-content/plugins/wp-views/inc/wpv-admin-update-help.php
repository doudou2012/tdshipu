<?php

function views_update_help() {
	if ( isset( $_GET['help-subject'] ) && 'wpv-if' == $_GET['help-subject'] ) {
		views_update_help_wpv_if();
	} else {
	?>
		<div class="wrap">
			<h2><?php _e( 'Views update history', 'wpv-views' ); ?></h2>
			<p><?php _e( 'This list contains important information related to some Views updates that required extra information:', 'wpv-views' ); ?></p>
			<ul>
				<li><?php echo sprintf( __( '<strong>[wpv-if] shortcodes</strong> on Views 1.6.2 - <a href="%s">details</a>', 'wpv-views' ), admin_url('admin.php?page=views-update-help&help-subject=wpv-if') ); ?></li>
			</ul>
			<!--<p><?php _e( 'This is the complete changelog for Views:', 'wpv-views' ); ?></p>-->
		</div>
	<?php
	}
}

function views_update_help_wpv_if() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Update changes: [wpv-if] shortcodes', 'wpv-views' ); ?></h2>
		<?php wp_nonce_field( 'views_update_help_wpv_if_nonce', 'views_update_help_wpv_if_nonce' ); ?>
		<p><?php _e( 'We have changed the way that the <strong>[wpv-if]</strong> conditional shortcode works.', 'wpv-views' ); ?></p>
		<p><?php _e( 'On a recent update, we added support for functions as conditions. From now on, you will need to <strong>first register</strong> the functions that you want to use as conditionals.', 'wpv-views' ); ?></p>
		<p><?php _e( 'To do so, just head to the Views Settings page and look for the new <strong><em>Functions inside conditional evaluations</em></strong> section, and follow the instructions.', 'wpv-views' ); ?></p>
		
		<h3><?php _e( 'Backwards compatibility', 'wpv-views' ); ?></h3>
		<p><?php _e( 'You may be using functions inside your <strong>[wpv-if]</strong> conditions already, and you will need to register those functions for them to work again.', 'wpv-views' ); ?></p>
		<p><?php _e( 'Clicking the button below will scan all your content and provide a list of items that contain <strong>[wpv-if]</strong> shortcodes, so you can review them and register any function if needed:', 'wpv-views' ); ?></p>
		<p><button class="button-primary js-views-scan" data-action="wpv-if" data-nonce=""><?php _e( 'Scan content', 'wpv-views' ); ?></button></p>
		<div class="js-wpv-views-scan-results"></div>
	</div>
	<?php
}

// View Scan usage callback action

add_action('wp_ajax_wpv_scan_wpv_if', 'wpv_scan_wpv_if_callback');

function wpv_scan_wpv_if_callback() {
    global $wpdb, $sitepress, $WP_Views;

    $nonce = $_POST["wpnonce"];
    if (! wp_verify_nonce($nonce, 'views_update_help_wpv_if_nonce') ) die("Security check");

    $needle = '%[wpv-if%';
    
    $trans_join = '';
    $trans_where = '';
    $trans_meta_where = '';
    
    if (isset($sitepress) && function_exists('icl_object_id')) {
		$current_lang_code = $sitepress->get_current_language();
		$trans_join = " JOIN {$wpdb->prefix}icl_translations t ";
		$trans_where = " AND ID = t.element_id AND t.language_code =  '{$current_lang_code}' ";
		$trans_meta_where = " AND post_id = t.element_id AND t.language_code =  '{$current_lang_code}' ";
    }

    $q = "SELECT DISTINCT * FROM {$wpdb->posts} WHERE
	( ID in (SELECT DISTINCT ID FROM {$wpdb->posts} {$trans_join} WHERE post_content LIKE '{$needle}' {$trans_where})
	OR
	ID in (SELECT DISTINCT post_id FROM {$wpdb->postmeta} {$trans_join} WHERE meta_value LIKE '{$needle}' {$trans_meta_where}) )
	AND post_type NOT IN ('revision') AND post_status='publish'";
    $res = $wpdb->get_results( $q, OBJECT );

	$items = array();
	$slug_to_label = array();
	$wpa_label = __( 'WordPress Archives', 'wpv-views' );
	if ( !empty( $res ) ) {
        $items = array();
        foreach ( $res as $row ) {
			if ( isset( $slug_to_label[$row->post_type] ) ) {
				$type = $slug_to_label[$row->post_type];
			} else {
				$post_object = get_post_type_object( $row->post_type );
				$type = $post_object->labels->singular_name;
				$slug_to_label[$row->post_type] = $type;
			}
			if ( !isset( $items[$type] ) ) {
				$items[$type] = array();
			}
			$edit_link_item = '';
            if ( $row->post_type == 'view' ) {
                $settings = $WP_Views->get_view_settings( $row->ID );
				if ( $settings['view-query-mode'] == 'normal' ) {
					$edit_link_item = get_admin_url()."admin.php?page=views-editor&view_id=".$row->ID;
				} else if ( $settings['view-query-mode'] == 'archive' ) {
					if ( !isset( $items[$wpa_label] ) ) {
						$items[$wpa_label] = array();
					}
					$type = $wpa_label;
					$edit_link_item = get_admin_url()."admin.php?page=view-archives-editor&view_id=".$row->ID;
				}
            } else {
                $edit_link_item = get_admin_url()."post.php?post=".$row->ID."&action=edit";
			}
			if ( !empty( $edit_link_item ) ) {
				$edit_link = '<a target="_blank" href="';
				$edit_link .= $edit_link_item;
				$edit_link .= '" title="' . esc_attr( __( 'Edit this item', 'wpv-views' ) ) . '">';
				$edit_link .= $row->post_title;
				$edit_link .= '</a>';
				$items[$type][] = $edit_link;
			}
        }
        
    }
	echo json_encode( $items );

    die();
}