<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class ShowReferralData extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'referral',
            'plural'   => 'referrals',
            'ajax'     => false,
        ));
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'show_referrals';

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        // Handle date filtering
        $this->process_date_filtering();

        // Handle search
        $this->process_search();

        // Fetch data from the table
        $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        usort($data, array(&$this, 'sort_data'));

        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'ref_name'     => 'Referral Name',
            'ref_email'    => 'Referral Email',
            'ref_phoneno'  => 'Referral Phone Number',
            'ref_message'  => 'Referral Message',
            'sender_id'    => 'Sender ID',
            'recipient_id' => 'Recipient ID',
            'sent_date'    => 'Sent Date',
            'received_date'    => 'Received Date',
			'referral_type' => 'Referral Type',
			'referral_subtype' => 'Referral Subtype',
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array(
            'ref_name'     => array('ref_name', false),
            'ref_email'    => array('ref_email', false),
            'ref_phoneno'  => array('ref_phoneno', false),
            'sender_id'    => array('sender_id', false),
            'recipient_id' => array('recipient_id', false),
            'sent_date'    => array('sent_date', false),
            'received_date'=> array('received_date', false),
            'referral_type'=> array('referral_type', false),
        );
    }

    /**
     * Process date filtering to modify the SQL query and filter by date.
     * 
     * @return void
     */
    private function process_date_filtering()
    {
        // Get the selected date filter
        $date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';

        if ($date_filter !== 'all') {
            // Modify the SQL query to filter by date
            $start_date = '';
            $end_date = '';

            // Adjust $start_date and $end_date based on $date_filter
            switch ($date_filter) {
                case 'today':
                    $start_date = date('Y-m-d') . ' 00:00:00';
                    $end_date = date('Y-m-d') . ' 23:59:59';
                    break;
                case 'yesterday':
                    $start_date = date('Y-m-d', strtotime('yesterday')) . ' 00:00:00';
                    $end_date = date('Y-m-d', strtotime('yesterday')) . ' 23:59:59';
                    break;
                case 'this_month':
                    $start_date = date('Y-m-01') . ' 00:00:00';
                    $end_date = date('Y-m-t') . ' 23:59:59';
                    break;
                case 'this_year':
                    $start_date = date('Y-01-01') . ' 00:00:00';
                    $end_date = date('Y-12-31') . ' 23:59:59';
                    break;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'show_referrals';

            $this->items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE DATE(sent_date) >= %s AND DATE(sent_date) <= %s",
                    $start_date,
                    $end_date
                ),
                ARRAY_A
            );
        }
    }

    /**
     * Process the search query and modify the SQL query to include the search condition.
     */
    private function process_search()
    {
        // Get the search query
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        if (!empty($search)) {
            // Modify the SQL query to include the search condition
            global $wpdb;
            $table_name = $wpdb->prefix . 'show_referrals';

            $this->items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE ref_name LIKE '%%%s%%' OR ref_email LIKE '%%%s%%'",
                    $search,
                    $search
                ),
                ARRAY_A
            );
        }
    }

    /**
     * Display the search box.
     *
     * @param string $text
     * @param string $input_id
     */
    /*
    public function search_box($text, $input_id)
    {
        $input_id = $input_id . '-search-input';
        $search = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '';
?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php echo $search; ?>" />
            <?php submit_button($text, 'button', false, false, array('id' => 'search-submit')); ?>
        </p>
<?php
    }*/


    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        global $wpdb;

        switch ($column_name) {
            case 'ref_name':
            case 'ref_email':
            case 'ref_phoneno':
            case 'ref_message':
                return $item[$column_name];

            case 'sender_id':
                // Fetch sender name based on sender_id
                $sender_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM {$wpdb->prefix}users WHERE ID = %d", $item['sender_id']));
                return $sender_name;

            case 'recipient_id':
                // Fetch recipient name based on recipient_id
                $recipient_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM {$wpdb->prefix}users WHERE ID = %d", $item['recipient_id']));
                return $recipient_name;

            case 'sent_date':
            case 'received_date':
                return date('F j, Y g:i a', strtotime($item[$column_name]));
   
            case 'referral_type':
                return $item['referral_type'];

            case 'referral_subtype':
                return $item['referral_subtype'];
                
            default:
                return print_r($item, true);
        }
    }

    /**
     * Fetch the "Chapter Member" data from BuddyBoss member profile page
     *
     * @param int $user_id - User ID
     * @return string - Chapter Member data
     */
    private function get_buddyboss_profile_data($user_id)
    {
        if (function_exists('xprofile_get_field_data')) {
            // Adjust the field_id based on your BuddyBoss setup
            //    11 - Chapter
            //    40 - Chapter Role (self-assigned) Custom order
            $field_id = '11'; // Replace with the actual field ID for the chapter

            // Get the field data for the specified user and field ID
            $field_data = xprofile_get_field_data($field_id, $user_id);

            return $field_data;
        }

        return '';
    }


    /**
     * column_cb function
     *
     * @param datatype $item description
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="referral[]" value="%s" />',
            $item['ref_id']
        );
    }

    /**
     * Get the bulk actions available.
     *
     * @return array List of available bulk actions
     */
    protected function get_bulk_actions()
    {
        return array(
            'delete' => 'Delete',
        );
    }

    /**
     * Process the bulk action.
     *
     * @return void
     */
    protected function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $referral_ids = isset($_REQUEST['referral']) ? $_REQUEST['referral'] : [];

            if (!empty($referral_ids)) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'show_referrals';

                foreach ($referral_ids as $referral_id) {
                    $wpdb->delete($table_name, ['ref_id' => $referral_id]);
                }
            }
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'ref_id';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }

    /**
     * Generate CSV file content
     *
     * @return string
     */
    public function generate_csv()
    {
        ob_start();

        $output = fopen('php://output', 'w');

        // Debug statement to check data before generating CSV
        error_log('Data for CSV: ' . print_r($this->items, true));

        // Output CSV column headers excluding unwanted columns
        $columns = array_diff_key($this->get_columns(), array_flip(['ref_id', 'is_seen', 'cb']));
        fputcsv($output, $columns);

        // Fetch data from the table
        $data = $this->items;

        // Output each row excluding unwanted columns
        foreach ($data as $row) {
            unset($row['ref_id']);
            unset($row['is_seen']);
            unset($row['cb']);
            fputcsv($output, $row);
        }

        fclose($output);

        return ob_get_clean();
    }

    /**
     * Resend Referral Notices
     *
     * @return boolean
     */
    public function resend_referral_notice()
    {
        $notice_sent = true;

        return $notice_sent;
    }
}
