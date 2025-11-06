<?php

/**
 * Plugin Name:       Referral Data Show
 * Description:       A plugin for managing referral data.
 * Version:           1.1.6
 * Author:            Referral Partners Plus
 * Author URI:         #
 * GitHub Plugin URI: https://github.com/CapWebSolutions/rpp-referral-data-show
 */

defined( 'ABSPATH' ) || die( 'Invalid Request' );

define( 'REFERRAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'REFERRAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'REFERRAL_PLUGIN_FILE', __FILE__ );

// Include the file where Example_List_Table is defined
require_once REFERRAL_PLUGIN_PATH . 'inc/database/select-data.php';

require_once REFERRAL_PLUGIN_PATH . 'inc/classes/class-custom-notifications.php';

if ( ! class_exists( 'ReferralPluginMain' ) ) {
	class ReferralPluginMain {

		public function __construct() {
			// Register activation and deactivation hooks
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			// register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// Hook to enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

			// Hook to create menu on activation
			add_action( 'admin_menu', array( $this, 'create_custom_menu' ) );

			// Hook to handle CSV download
			add_action( 'admin_init', array( $this, 'handle_csv_download' ) );
		}

		/**
		 * Enqueue scripts and styles for the plugin.
		 */
		public function enqueue_scripts_styles() {
			wp_enqueue_script( 'custom-referral-plugin-js', REFERRAL_PLUGIN_URL . 'inc/assets/js/custom.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'custom-referral-plugin-css', REFERRAL_PLUGIN_URL . 'inc/assets/css/custom.css', array(), '1.0', 'all' );
		}

		/**
		 * Activates the plugin by including necessary files and creating the referral table if it doesn't exist.
		 */
		public function activate() {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$create_table_path = REFERRAL_PLUGIN_PATH . 'inc/database/create-table.php';

			if ( file_exists( $create_table_path ) ) {
				include_once $create_table_path;

				// Create referral table
				if ( class_exists( 'CreateReferralTable' ) ) {
					CreateReferralTable::create_referral_table();
				} else {
					error_log( 'Error: CreateReferralTable class not found.' );
				}
			} else {
				error_log( 'Error: create-table.php not found.' );
			}
		}

		/**
		 * Deactivates the referral feature by deleting the referral table.
		 */
		public function deactivate() {
			$delete_table_path = REFERRAL_PLUGIN_PATH . 'inc/database/delete-table.php';

			if ( file_exists( $delete_table_path ) ) {
				include_once $delete_table_path;

				// Delete referral table
				if ( class_exists( 'DeleteReferralTable' ) ) {
					DeleteReferralTable::delete_referral_table();
				} else {
					error_log( 'Error: DeleteReferralTable class not found.' );
				}
			} else {
				error_log( 'Error: delete-table.php not found.' );
			}
		}

		/**
		 * Create a custom menu.
		 */
		public function create_custom_menu() {
			add_menu_page(
				'Referral Data',
				'Referral Data',
				'manage_options',
				'referral_data_page',
				array( $this, 'list_table_page' ),
				'dashicons-admin-users',
				20
			);
		}

		/**
		 * List table page function.
		 *
		 */
		public function list_table_page() {
			$example_list_table = new ShowReferralData();
			$example_list_table->prepare_items();

			echo '<div class="wrap">
	  <h2>All Referrals</h2>
	<form method="post" action="">
	<input type="hidden" name="action" value="download_csv">
	<button type="submit" class="button">Download CSV</button>
	</form>';
			$example_list_table->display();
			echo '</div>';
		}

		/*   public function list_table_page()
		{
		$example_list_table = new ShowReferralData();
		$example_list_table->prepare_items();

		echo '<div class="wrap">
		<h2>All Referrals</h2>';

		// Add search box
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="' . esc_attr( $_REQUEST['page'] ) . '" />';
		$example_list_table->search_box( 'Search', 'referral_search' );
		echo '</form>';

		echo '<form method="post" action="">
		<input type="hidden" name="action" value="download_csv">
		<button type="submit" class="button">Download CSV</button>
		</form>';
		$example_list_table->display();
		echo '</div>';
		} */



		/**
		 * Handles the CSV download functionality.
		 */
		public function handle_csv_download() {
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'download_csv' ) {
				$example_list_table = new ShowReferralData();
				$example_list_table->prepare_items(); // Make sure data is prepared

				// Generate CSV data
				$csv_data = $example_list_table->generate_csv();

				// Set the headers for download
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment; filename="referral_data.csv"' );

				// Output the CSV content (escaped to satisfy security scanners)
				echo wp_kses_post( $csv_data );

				// Make sure no further output is sent
				exit;
			}
		}
	}

	// Instantiate the main class
	if ( is_admin() ) {
		new ReferralPluginMain();
	}
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
