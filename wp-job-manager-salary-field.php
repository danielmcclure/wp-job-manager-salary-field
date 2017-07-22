<?php
/*
Plugin Name: WP Job Manager - GBP Salary Add-On
Plugin URI: https://wpjobmanager.com/
Description: Adds GBP Salary field to the frontend, backend & search for WP Job Manager.
Version: 1.0
Author: danielmcclure
Author https://github.com/danielmcclure/wp-job-manager-salary-field
Requires at least: 4.1
Tested up to: 4.8
Text Domain: wp-job-manager
Domain Path: /languages

	Copyright: 2015 Mike Jolley & 2017 Daniel McClure
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Add your own function to filter the fields in admin
add_filter( 'job_manager_job_listing_data_fields', 'admin_add_salary_field' );

    // Here we create the function for this custom field in admin  
    function admin_add_salary_field( $fields ) {
  $fields['_job_salary'] = array(
    'label'       => __( 'Salary (£)', 'job_manager' ),
    'type'        => 'text',
    'placeholder' => 'e.g. 20000',
    'description' => ''
  );
  return $fields;
}

// Add your own function to filter the fields in frontend
add_filter( 'submit_job_form_fields', 'frontend_add_salary_field' );

    // Here we create the function for this custom field in frontend
function frontend_add_salary_field( $fields ) {
  $fields['job']['job_salary'] = array(
    'label'       => __( 'Salary (£)', 'job_manager' ),
    'type'        => 'text',
    'required'    => true,
    'placeholder' => 'e.g. 20000',
    'priority'    => 7
  );
  return $fields;
}

// Add your salary field to display on single job page
add_action( 'single_job_listing_meta_end', 'display_job_salary_data' );

    // Here we create the function for this custom field to display on single job page 
   function display_job_salary_data() {
  global $post;

  $salary = get_post_meta( $post->ID, '_job_salary', true );

  if ( $salary ) {
    echo '<li>' . __( 'Salary:' ) . ' £' . esc_html( $salary ) . '</li>';
  }
}

/**
 * This can either be done with a filter (below) or the field can be added directly to the job-filters.php template file!
 *
 * job-manager-filter class handling was added in v1.23.6
 */
add_action( 'job_manager_job_filters_search_jobs_end', 'filter_by_salary_field' );

function filter_by_salary_field() {
	?>
	<div class="search_categories">
		<label for="search_categories"><?php _e( 'Salary', 'wp-job-manager' ); ?></label>
		<select name="filter_by_salary" class="job-manager-filter">
			<option value=""><?php _e( 'Any Salary', 'wp-job-manager' ); ?></option>
			<option value="upto20"><?php _e( 'Up to £20,000', 'wp-job-manager' ); ?></option>
			<option value="20000-40000"><?php _e( '£20,000 to £40,000', 'wp-job-manager' ); ?></option>
			<option value="40000-60000"><?php _e( '£40,000 to £60,000', 'wp-job-manager' ); ?></option>
			<option value="over60"><?php _e( '£60,000+', 'wp-job-manager' ); ?></option>
		</select>
	</div>
	<?php
}

/**
 * Adds Salary Search Functionality as per https://wpjobmanager.com/document/tutorial-adding-a-salary-field-for-jobs/
 */
add_filter( 'job_manager_get_listings', 'filter_by_salary_field_query_args', 10, 2 );
function filter_by_salary_field_query_args( $query_args, $args ) {
	if ( isset( $_POST['form_data'] ) ) {
		parse_str( $_POST['form_data'], $form_data );

		// If this is set, we are filtering by salary
		if ( ! empty( $form_data['filter_by_salary'] ) ) {
			$selected_range = sanitize_text_field( $form_data['filter_by_salary'] );
			switch ( $selected_range ) {
				case 'upto20' :
					$query_args['meta_query'][] = array(
						'key'     => '_job_salary',
						'value'   => '20000',
						'compare' => '<',
						'type'    => 'NUMERIC'
					);
				break;
				case 'over60' :
					$query_args['meta_query'][] = array(
						'key'     => '_job_salary',
						'value'   => '60000',
						'compare' => '>=',
						'type'    => 'NUMERIC'
					);
				break;
				default :
					$query_args['meta_query'][] = array(
						'key'     => '_job_salary',
						'value'   => array_map( 'absint', explode( '-', $selected_range ) ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC'
					);
				break;
			}

			// This will show the 'reset' link
			add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
		}
	}
	return $query_args;
}
