<?php

/*
* wpv-admin-notices.php
*
* Handle admin notices
*
* @since 1.6.2/August 13, 2014
*/

if ( defined( 'WPT_ADMIN_NOTICES' ) ) {
    return; 
}

define( 'WPT_ADMIN_NOTICES', true );

/*
* WPToolset_Admin_Notices
*
* Methods for handling admin notices
*
*/

class WPToolset_Admin_Notices {

	/*
	* @param $textdomain (string) the textdomain to use
	* @param $path (string) the path to the folder containing the mo files
	* @param $mo_file (string) the .mo file name, using %s as a placeholder for the locale - do not add the .mo extension!
	*/
	
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
		add_action( 'admin_init', array( $this, 'wpv_admin_notice_ignore' ) );
	}
	
	function init() {
		add_filter( 'wptoolset_filter_admin_notices', array( $this, 'wpv_if_changes' ) );
	}
	
	function wpv_if_changes( $notices ) {
		if ( isset($_GET['page'] ) && $_GET['page']=='views-update-help' ) {
			return $notices;
		} else if ( current_user_can( 'activate_plugins' ) ) {
			global $current_user;
			$user_id = $current_user->ID;
			if ( ! get_user_meta( $user_id, 'wpv_wpv_if_changes_ignore_notice' ) ) {
				$notice_text = sprintf(
					__( 'The last Views update fixed one important security issue which may require some extra action | <a href="%1s">More details</a> | <a class="js-wpv-dismiss" href="%2s">Dismiss</a>.', 'wpv-views' ),
					admin_url('admin.php?page=views-update-help&help-subject=wpv-if'),
					add_query_arg( array( 'wpv_wpv_if_changes_ignore' => 'yes' ) )
				);
				$args = array(
					'notice_class' => 'error',
					'notice_text' => $notice_text
				);
				$notices['wpv_if_changes'] = $args;
			}
		}
		return $notices;
	}
	
	/*
	* display_admin_notices
	*
	* Displays admin notices based on some criteria
	*
	* @since 1.6.2/August 13, 2014
	*/
	
	function display_admin_notices() {
		
		$notices = array();
		/*
		* wptoolset_filter_admin_notices
		*
		* Filter to pass admin notices
		*
		* $notices is an array with the format:
		*	'notice_id' => $notice_data = array()
		*
		* $notice_data is an array with the format:
		*	'notice_class' => 'update'|'error'|custom (string) (defaults to 'update')
		*	'notice_text' => (string) (mandatory) (localized on origin)
		*/
		$notices = apply_filters( 'wptoolset_filter_admin_notices', $notices );
		
		if ( is_array( $notices ) ) {
			foreach ( $notices as $notice_id => $notice_data ) {
				if ( is_array( $notice_data ) ) {
					$notice_data_defaults = array(
						'notice_class' => 'updated',
						'notice_text' => '',
					);
					$notice_data = wp_parse_args( $notice_data, $notice_data_defaults );
					
					echo '<div id="' . $notice_id . '" class="message ' . esc_attr( $notice_data['notice_class'] ) . '"><p>';
					echo $notice_data['notice_text'];
					echo "</p></div>";
				}
			}
		}
	}
	
	function wpv_admin_notice_ignore() {
		global $current_user;
		$user_id = $current_user->ID;
		
		if ( isset($_GET['wpv_wpv_if_changes_ignore']) && 'yes' == $_GET['wpv_wpv_if_changes_ignore'] ) {
			 add_user_meta($user_id, 'wpv_wpv_if_changes_ignore_notice', 'true', true);
		}
		
	}

}

new WPToolset_Admin_Notices();