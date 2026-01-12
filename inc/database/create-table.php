<?php

if ( ! class_exists( 'CreateReferralTable' ) ) {
	class CreateReferralTable {

		/**
		 * Create a referral table if it doesn't already exist in the WordPress database.
		 */
		public static function create_referral_table() {
			global $wpdb;

			// Your table name
			$table_name = $wpdb->prefix . 'show_referrals';

			$sql = "CREATE TABLE $table_name (
				ref_id mediumint(9) NOT NULL AUTO_INCREMENT,
				ref_name varchar(255) NOT NULL,
				ref_email varchar(255) NOT NULL,
				ref_phoneno varchar(20) NOT NULL,
				ref_message text NOT NULL,
				referral_type varchar(100) NOT NULL DEFAULT '',
				referral_subtype varchar(100) NOT NULL DEFAULT '',
				sender_id mediumint(9) NOT NULL,
				recipient_id mediumint(9) NOT NULL,
				sent_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				received_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				is_seen tinyint(1) DEFAULT 0,
				PRIMARY KEY  (ref_id)
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
        }

		/**
		 * Function to activate the referral plugin.
		 *
		 */
		public static function activate_referral_plugin() {
			self::create_referral_table();
		}
	}
}
register_activation_hook( __FILE__, 'activate_referral_plugin' );
