<?php

if (!class_exists('DeleteReferralTable')) {
    class DeleteReferralTable
    {
        public static function delete_referral_table()
        {
            global $wpdb;

            // Your table name
            $table_name = $wpdb->prefix . 'show_referrals';

            // Delete all data from the table
//             $wpdb->query("TRUNCATE TABLE $table_name");

            // Delete the table itself
//             $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }
    }
}

// Register deactivation hook
// register_deactivation_hook(__FILE__, 'deactivate_referral_plugin');
function deactivate_referral_plugin()
{
    DeleteReferralTable::delete_referral_table();
}
