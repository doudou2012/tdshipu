<?php

/*
  Modify the query to include filtering by custom_field.
  
*/

add_filter('wpv_filter_query', 'wpv_filter_post_custom_field', 10, 2);  
function wpv_filter_post_custom_field($query, $view_settings) {

	global $WP_Views, $no_parameter_found;
	
	$meta_keys = array();

	foreach (array_keys($view_settings) as $key) {
		if (strpos($key, 'custom-field-') === 0 && strpos($key, '_compare') === strlen($key) - strlen('_compare')) {
			
			if ( empty( $meta_keys ) ) $meta_keys = $WP_Views->get_meta_keys();
			
			$name = substr($key, 0, strlen($key) - strlen('_compare'));
			$name = substr($name, strlen('custom-field-'));
			
			$meta_name = $name;
			if (!in_array($meta_name, $meta_keys)) { // this is needed for fields with keys containing spaces - we map those spaces to underscores when creating the filter
				$meta_name = str_replace('_', ' ', $meta_name);
			}
			
			// TODO add filter here: what happens when a meta_name contains a space AND an underscore?
			// We need a final solution, I prefer to use a %%SPACE%% placeholder and avoid the above mapping (which we should keepfor backwards compatibility)

			$value = $view_settings['custom-field-' . $name . '_value'];
			$type = $view_settings['custom-field-' . $name . '_type'];
			
			$view_id = $WP_Views->get_current_view();
			
			/**
			* Filter wpv_filter_custom_field_filter_original_value
			*
			* @param $value the value coming from the View settings filter after passing through the check for URL params, shortcode attributes and date functions comparison
			* @param $meta_name the key of the custom field being used to filter by
			* @param $view_id the ID of the View being displayed
			*
			* $value comes from the View settings. It's a string containing a single-value or a comma-separated list of single-values if the filter needs more than one value (for IN, NOT IN, BETWEEN and NOT BETWEEN comparisons)
			* Each individual single-value element in the list can use any of the following formats:
			* (string|numeric) if the single-value item is fixed
			* (string) URL_PARAM(parameter) if the filter is done via a URL param "parameter"
			* (string) VIEW_PARAM(parameter) if the filter is done via a [wpv-view] shortcode attribute "parameter"
			* (string) NOW() | TODAY() | FUTURE_DAY() | PAST_DAY() | THIS_MONTH() | FUTURE_MONTH() | PAST_MONTH() | THIS_YEAR() | FUTURE_YEAR() | PAST_YEAR() | SECONDS_FROM_NOW() | MONTHS_FROM_NOW() | YEARS_FROM_NOW() | DATE()
			*
			* @return $value
			*
			* @since 1.4.0
			*/
			
			$value = apply_filters('wpv_filter_custom_field_filter_original_value', $value, $meta_name, $view_id);
			
			$value = wpv_apply_user_functions($value);

			/**
			* Filter wpv_filter_custom_field_filter_processed_value
			*
			* @param $value the value coming from the View settings filter after passing through the check for URL params, shortcode attributes and date functions comparison
			* @param $meta_name the key of the custom field being used to filter by
			* @param $view_id the ID of the View being displayed
			*
			* @return $value
			*
			* @since 1.4.0
			*/
			
			$value = apply_filters('wpv_filter_custom_field_filter_processed_value', $value, $meta_name, $view_id);
			
			/**
			* Filter wpv_filter_custom_field_filter_type
			*
			* @param $type the type coming from the View settings filter: <CHAR>, <NUMERIC>, <BINARY>, <DATE>, <DATETIME>, <DECIMAL>, <SIGNED>, <TIME>, <UNSIGNED>
			* @param $meta_name the key of the custom field being used to filter by
			* @param $view_id the ID of the View being displayed
			*
			* @return $type
			*
			* @since 1.6.0
			*/
			
			$type = apply_filters('wpv_filter_custom_field_filter_type', $type, $meta_name, $view_id);
			
			if ($value !== $no_parameter_found) { // Only add if we have found a parameter
			
				$compare_mode = $view_settings['custom-field-' . $name . '_compare'];
				
				if ($compare_mode == 'BETWEEN' || $compare_mode == 'NOT BETWEEN') {
					// We need to make sure we have values for min and max.
					// If any of the values is missing we will transform into lower-than or greater-than filters
					// TODO: Note that we are not covering the case where min or max is an empty constant value, we might want to review that
					
					$values = explode(',', $value);
					$values = array_map('trim',$values);
					
					if (count($values) == 0) {
						continue;
					}
					
					if (count($values) == 1) {
						
						if ($values[0] == $no_parameter_found) {
							// nothing to compare to so ignore
							continue;
						}
						
						// assume this is the smaller value
						
						if ($compare_mode == 'BETWEEN') {
							$compare_mode =  '>=';
						} else {
							$compare_mode =  '<=';
						}
						$value = $values[0];
					} else {
						if ($values[0] == $no_parameter_found && $values[1] == $no_parameter_found) {
							// nothing to compare so ignore
							continue;
						}
						if ($values[0] == $no_parameter_found) {
							// minimum value is missing so use less than compare.
							if ($compare_mode == 'BETWEEN') {
								$compare_mode = '<=';
							} else {
								$compare_mode = '>=';
							}
							$value = $values[1];
						} elseif ($values[1] == $no_parameter_found) {
							// maximum value is missing so use greater than compare.
							if ($compare_mode == 'BETWEEN') {
								$compare_mode = '>=';
							} else {
								$compare_mode = '<=';
							}
							$value = $values[0];
						}  
						
						
					}
					
				}
				
				// Now, check any other option against a $no_parameter_found value
				// If $value still contains a $no_parameter_found value, no filter should be applied
				// Because it means there is a non-existing or empty URL parameter
				
				// TODO: on shortcode attributes, an empty value as two commas will pass this test
				// Maybe this is OK, as we might want to filter by an empty value too
				// Which is not possible on filters by URL parameter
				
				if ( strpos( $value, $no_parameter_found ) !== false ) {
					continue;
				}
				
				// Now that we are sure that the filter should be applied, even for empty values, let's do it
				
				if ( $compare_mode == 'IN' || $compare_mode == 'NOT IN' ) { // WordPress query expects an array in this case
					$value = explode(',', $value); // make it an array
				}
				
				if (!isset($query['meta_query']) && isset($view_settings['custom_fields_relationship'])) {
					$query['meta_query'] = array('relation' => $view_settings['custom_fields_relationship']);
				}
				
				if ( is_array($value) ) {
					foreach ( $value as $key => $val ) {
						$value[$key] = stripslashes( urldecode( sanitize_text_field( trim( $val ) ) ) );
					}
				} else {
					$value = stripslashes( urldecode( sanitize_text_field( trim( $value ) ) ) );
				}
				
				if ( ( empty( $value ) && !is_numeric( $value ) ) && ( $compare_mode == '>=' || $compare_mode == '<=' || $compare_mode == '>' || $compare_mode == '<' ) ) {
					// do nothing as we are comparing greater than / lower than to an empty value
				} else {
					$query['meta_query'][] = array('key' => $meta_name,
												  'value' => $value,
												  'type' => $type,
												  'compare' => $compare_mode);
				}
				
				/*
				$value = str_replace($no_parameter_found, '', $value); // just in case we have more than one parameter
				
				
				
				if ($compare_mode == 'IN' || $compare_mode == 'NOT IN') { // WordPress query expects an array in this case
					if ( !empty( $value ) || is_numeric( $value ) ) {
						$value = explode(',', $value); // make it an array and separate values, for multiple values in shortcode mode, but avoid an array with just an empty value
					}
				}

				if (!isset($query['meta_query']) && isset($view_settings['custom_fields_relationship'])) {
					$query['meta_query'] = array('relation' => $view_settings['custom_fields_relationship']);
				}
				
				// Flag: should we add this filer anyway?
				// If $values is an array, the comparison is expected to be IN, but could also be NOT IN; for all the other comparison types, it is a string
				// If $values is an array and the comparison is IN, it may contain an empty value
				// Then we have a default "all items" value selected => no filter
				
				$filter_needed = true;
				
				// Sanitize $value so they can contain quotes
				
				if ( is_array($value) ) {
					foreach ( $value as $key => $val ) {
						if ( empty( $val ) && !is_numeric( $val ) && $compare_mode == 'IN' ) {
							$filter_needed = false;
						} else {				
							$value[$key] = stripslashes( urldecode( sanitize_text_field( trim( $val ) ) ) );
						}
					}
				} else {
					$value = stripslashes( urldecode( sanitize_text_field( trim( $value ) ) ) );
				}
				
				if( ( empty( $value ) && !is_numeric( $value ) ) && ( $compare_mode == '>=' || $compare_mode == '<=' || $compare_mode == '>' || $compare_mode == '<' ) ) {
					// do nothing as we are comparing greater than / lower than to an empty value
				}
				else if ( $filter_needed && ( !empty( $value ) || is_numeric( $value ) ) )
				{
					$query['meta_query'][] = array('key' => $meta_name,
												  'value' => $value,
												  'type' => $type,
												  'compare' => $compare_mode);
				}
				*/
			}
			
		}
	}

    return $query;
}

function wpv_get_custom_field_view_params($view_settings) {
    $pattern = '/VIEW_PARAM\(([^(]*?)\)/siU';

	$results = array();
	
	foreach (array_keys($view_settings) as $key) {
		if (strpos($key, 'custom-field-') === 0 && strpos($key, '_compare') === strlen($key) - strlen('_compare')) {
			$name = substr($key, 0, strlen($key) - strlen('_compare'));
			$name = substr($name, strlen('custom-field-'));
			
			$value = $view_settings['custom-field-' . $name . '_value'];
			
    
		    if(preg_match_all($pattern, $value, $matches, PREG_SET_ORDER)) {
		        foreach($matches as $match) {
					$results[] = $match[1];
				}
			}
			
		}
	}
	
	return $results;
}

