<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class ShowReferralData extends WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'referral',
				'plural'   => 'referrals',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'show_referrals';

		$this->process_bulk_action();

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		// Handle date filtering
		$this->process_date_filtering();

		// Handle search
		$this->process_search();

		// Fetch data from the table
		if ( $this->is_self_referrals_view() ) {
			$data = $wpdb->get_results( "SELECT * FROM $table_name WHERE sender_id = recipient_id", ARRAY_A );
		} else {
			$data = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
		}

		usort( $data, array( &$this, 'sort_data' ) );

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'ref_name'         => 'Referral Name',
			'ref_email'        => 'Referral Email',
			'ref_phoneno'      => 'Referral Phone Number',
			'ref_message'      => 'Referral Message',
			'sender_id'        => 'Sender ID',
			'recipient_id'     => 'Recipient ID',
			'sent_date'        => 'Sent Date',
			'received_date'    => 'Received Date',
			'referral_type'    => 'Referral Type',
			'referral_subtype' => 'Referral Subtype',
		);

		return $columns;
	}

	/**
	 * Returns true if self-referrals mode is enabled.
	 *
	 * @return bool
	 */
	private function is_self_referrals_view() {
		return isset( $_GET['self_referrals'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['self_referrals'] ) );
	}

	/**
	 * Get total count of self-referral records.
	 *
	 * @return int
	 */
	public function get_self_referrals_count() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'show_referrals';
		$count      = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE sender_id = recipient_id" );

		return absint( $count );
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array(
			'ref_name'      => array( 'ref_name', false ),
			'ref_email'     => array( 'ref_email', false ),
			'ref_phoneno'   => array( 'ref_phoneno', false ),
			'sender_id'     => array( 'sender_id', false ),
			'recipient_id'  => array( 'recipient_id', false ),
			'sent_date'     => array( 'sent_date', false ),
			'received_date' => array( 'received_date', false ),
			'referral_type' => array( 'referral_type', false ),
		);
	}

	/**
	 * Process date filtering to modify the SQL query and filter by date.
	 *
	 * @return void
	 */
	private function process_date_filtering() {
		// Get the selected date filter
		$date_filter = isset( $_GET['date_filter'] ) ? $_GET['date_filter'] : 'all';

		if ( 'all' !== $date_filter ) {
			// Modify the SQL query to filter by date
			$start_date = '';
			$end_date   = '';

			// Adjust $start_date and $end_date based on $date_filter
			switch ( $date_filter ) {
				case 'today':
					$start_date = date( 'Y-m-d' ) . ' 00:00:00';
					$end_date   = date( 'Y-m-d' ) . ' 23:59:59';
					break;
				case 'yesterday':
					$start_date = date( 'Y-m-d', strtotime( 'yesterday' ) ) . ' 00:00:00';
					$end_date   = date( 'Y-m-d', strtotime( 'yesterday' ) ) . ' 23:59:59';
					break;
				case 'this_month':
					$start_date = date( 'Y-m-01' ) . ' 00:00:00';
					$end_date   = date( 'Y-m-t' ) . ' 23:59:59';
					break;
				case 'this_year':
					$start_date = date( 'Y-01-01' ) . ' 00:00:00';
					$end_date   = date( 'Y-12-31' ) . ' 23:59:59';
					break;
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'show_referrals';

			$this->items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $table_name WHERE DATE( sent_date ) >= %s AND DATE( sent_date ) <= %s",
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
	private function process_search() {
		// Get the search query
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

		if ( ! empty( $search ) ) {
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
	public function search_box( $text, $input_id )
	{
	$input_id = $input_id . '-search-input';
	$search = isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : '';
	?>
	<p class="search-box">
	<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php echo $search; ?>" />
	<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
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
	public function column_default( $item, $column_name ) {
		global $wpdb;

		switch ( $column_name ) {
			case 'ref_name':
			case 'ref_email':
			case 'ref_phoneno':
			case 'ref_message':
				return $item[ $column_name ];

			case 'sender_id':
				// Fetch sender name based on sender_id
				$sender_name = $wpdb->get_var( $wpdb->prepare( "SELECT display_name FROM {$wpdb->prefix}users WHERE ID = %d", $item['sender_id'] ) );
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
	private function get_buddyboss_profile_data( $user_id ) {
		if ( function_exists( 'xprofile_get_field_data' ) ) {
			// Adjust the field_id based on your BuddyBoss setup
			//    11 - Chapter
			//    40 - Chapter Role ( self-assigned ) Custom order
			$field_id = '11'; // Replace with the actual field ID for the chapter

			// Get the field data for the specified user and field ID
			$field_data = xprofile_get_field_data( $field_id, $user_id );

			return $field_data;
		}

		return '';
	}

	/**
	 * Render the ref_name column with row actions.
	 *
	 * @param array $item Referral row.
	 * @return string
	 */
	public function column_ref_name( $item ) {
		$ref_id = isset( $item['ref_id'] ) ? absint( $item['ref_id'] ) : 0;

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'page'     => 'referral_data_page',
					'action'   => 'send_notice_email',
					'referral' => $ref_id,
				),
				admin_url( 'admin.php' )
			),
			'rpp_send_notice_email_' . $ref_id
		);

		$actions = array(
			'send_notice_email' => '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Send notice email', 'rpp-referral-data-show' ) . '</a>',
		);

		return sprintf( '%1$s %2$s', esc_html( $item['ref_name'] ), $this->row_actions( $actions ) );
	}


	/**
	 * column_cb function
	 *
	 * @param datatype $item description
	 * @return string
	 */
	public function column_cb( $item ) {
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
	protected function get_bulk_actions() {
		return array(
			'delete'            => __( 'Delete', 'rpp-referral-data-show' ),
			'send_notice_email' => __( 'Send notice email', 'rpp-referral-data-show' ),
		);
	}

	/**
	 * Process the bulk action.
	 *
	 * @return void
	 */
	protected function process_bulk_action() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_action = $this->current_action();
		$page           = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( 'referral_data_page' !== $page || empty( $current_action ) ) {
			return;
		}

		if ( 'send_notice_email' === $current_action ) {
			$referral_ids = isset( $_REQUEST['referral'] ) ? (array) wp_unslash( $_REQUEST['referral'] ) : array();
			$referral_ids = array_map( 'absint', $referral_ids );
			$referral_ids = array_filter( $referral_ids );

			if ( empty( $referral_ids ) ) {
				return;
			}

			if ( 1 === count( $referral_ids ) && isset( $_REQUEST['_wpnonce'] ) ) {
				$single_nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
				if ( ! wp_verify_nonce( $single_nonce, 'rpp_send_notice_email_' . $referral_ids[0] ) ) {
					return;
				}
			} else {
				check_admin_referer( 'bulk-referrals' );
			}

			$sent_count = 0;
			foreach ( $referral_ids as $referral_id ) {
				if ( $this->send_notice_email_for_referral( $referral_id ) ) {
					++$sent_count;
				}
			}

			$base_url = add_query_arg(
				array(
					'page' => 'referral_data_page',
				),
				admin_url( 'admin.php' )
			);
			if ( $this->is_self_referrals_view() ) {
				$base_url = add_query_arg( 'self_referrals', '1', $base_url );
			}

			$redirect_url = add_query_arg(
				array(
					'notice' => $sent_count > 0 ? 'send_notice_success' : 'send_notice_error',
					'count'  => $sent_count,
				),
				$base_url
			);
			wp_safe_redirect( $redirect_url );
			exit;
		}

		if ( 'delete' === $this->current_action() ) {
			check_admin_referer( 'bulk-referrals' );
			$referral_ids = isset( $_REQUEST['referral'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['referral'] ) ) : array();

			if ( ! empty( $referral_ids ) ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'show_referrals';

				foreach ( $referral_ids as $referral_id ) {
					$wpdb->delete( $table_name, array( 'ref_id' => $referral_id ) );
				}
			}
		}
	}

	/**
	 * Send notice email for a referral record.
	 *
	 * @param int $referral_id Referral ID.
	 * @return bool
	 */
	private function send_notice_email_for_referral( $referral_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'show_referrals';
		$referral   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE ref_id = %d", $referral_id ),
			ARRAY_A
		);

		if ( empty( $referral ) ) {
			return false;
		}

		$recipient_id    = isset( $referral['recipient_id'] ) ? absint( $referral['recipient_id'] ) : 0;
		$sender_id       = isset( $referral['sender_id'] ) ? absint( $referral['sender_id'] ) : 0;
		$recipient_email = '';

		// Self-referral notices should go to the member who submitted the referral.
		if ( $sender_id > 0 ) {
			$sender_user = get_userdata( $sender_id );
			if ( $sender_user instanceof WP_User && is_email( $sender_user->user_email ) ) {
				$recipient_email = $sender_user->user_email;
			}
		}
		// Fallback to recipient account email only if sender email is unavailable.
		if ( empty( $recipient_email ) && $recipient_id > 0 ) {
			$recipient_user = get_userdata( $recipient_id );
			if ( $recipient_user instanceof WP_User && is_email( $recipient_user->user_email ) ) {
				$recipient_email = $recipient_user->user_email;
			}
		}

		if ( empty( $recipient_email ) || ! is_email( $recipient_email ) ) {
			error_log(
				sprintf(
					'RPP send_notice_email skipped for referral #%d. Missing valid user email (sender_id=%d, recipient_id=%d).',
					$referral_id,
					$sender_id,
					$recipient_id
				)
			);
			return false;
		}

		$subject = sprintf(
			/* translators: %d: referral ID */
			__( 'Self-referral notice for %1$s referral.', 'rpp-referral-data-show' ),
			$referral['ref_name']
		);
		$message = sprintf(
			__("Hi!
<br><br>
RPP noticed you recently submitted a referral! Great job! However it looks like it didn’t make it to the person you have intended. 
<br><br>
Here are some reasons that may have happened: <br>
- Instead of selecting the RPP Members Name from the top search box, the name was just typed in. <br>
- Your name was accidentally put in the top box instead of the RPP member you are sending a referral to.<br>
<br>
The details of the referral are below for you.
<br><br>
Referral Name: %1\$s<br>
Referral Email: %2\$s<br>
Sent Date: %3\$s<br>
Referral Message: <br>
%4\$s<br>
", 'rpp-referral-data-show' ),
isset( $referral['ref_name'] ) ? sanitize_text_field( $referral['ref_name'] ) : '',
isset( $referral['ref_email'] ) ? sanitize_email( $referral['ref_email'] ) : '',
isset( $referral['sent_date'] ) ? sanitize_text_field( $referral['sent_date'] ) : '',
isset( $referral['ref_message'] ) ? sanitize_textarea_field( $referral['ref_message'] ) : '',
			
		);

		$message .= sprintf( 
			__( '<br>
Good news! This is fixable. 
Please use the link below to get to the referral so you can edit it and update it.<br>
<a href="https://referralpartnersplus.com/members/me/my-referral-history">My Sent Referrals.</a><br><br>
Thank you.', 'rpp-referral-data-show' ) );

		/**
		 * Filter subject of self-referral notice email.
		 *
		 * @param string $subject   Email subject.
		 * @param array  $referral  Referral row data.
		 * @param int    $referral_id Referral ID.
		 */
		$subject = apply_filters( 'rpp_self_referral_notice_email_subject', $subject, $referral, $referral_id );

		/**
		 * Filter body of self-referral notice email.
		 *
		 * @param string $message   Email body.
		 * @param array  $referral  Referral row data.
		 * @param int    $referral_id Referral ID.
		 */
		$message = apply_filters( 'rpp_self_referral_notice_email_message', $message, $referral, $referral_id );

		/**
		 * Filter headers for self-referral notice email.
		 *
		 * @param array|string $headers Email headers.
		 * @param array        $referral Referral row data.
		 * @param int          $referral_id Referral ID.
		 */
		$default_headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$headers         = apply_filters( 'rpp_self_referral_notice_email_headers', $default_headers, $referral, $referral_id );

		$mail_error = null;
		$mail_error_hook = static function ( $wp_error ) use ( &$mail_error ) {
			$mail_error = $wp_error;
		};

		add_action( 'wp_mail_failed', $mail_error_hook, 10, 1 );
		$sent = (bool) wp_mail( $recipient_email, $subject, $message, $headers );
		remove_action( 'wp_mail_failed', $mail_error_hook, 10 );

		if ( ! $sent ) {
			if ( is_wp_error( $mail_error ) ) {
				error_log(
					sprintf(
						'RPP send_notice_email failed for referral #%d to %s. Error: %s',
						$referral_id,
						$recipient_email,
						$mail_error->get_error_message()
					)
				);
			} else {
				error_log(
					sprintf(
						'RPP send_notice_email failed for referral #%d to %s. No wp_mail_failed error provided.',
						$referral_id,
						$recipient_email
					)
				);
			}
		}

		return $sent;
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'ref_id';
		$order   = 'asc';

		// If orderby is set, use this as the sort column
		if ( ! empty( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		}

		// If order is set use this as the order
		if ( ! empty( $_GET['order'] ) ) {
			$order = $_GET['order'];
		}

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		if ( 'asc' === $order ) {
			return $result;
		}

		return -$result;
	}

	/**
	 * Generate CSV file content
	 *
	 * @return string
	 */
	public function generate_csv() {
		ob_start();

		$output = fopen( 'php://output', 'w' );

		// Debug statement to check data before generating CSV
		error_log( 'Data for CSV: ' . print_r( $this->items, true ) );

		// Output CSV column headers excluding unwanted columns
		$columns = array_diff_key( $this->get_columns(), array_flip( array( 'ref_id', 'is_seen', 'cb' ) ) );
		fputcsv( $output, $columns );

		// Fetch data from the table
		$data = $this->items;

		// Output each row excluding unwanted columns
		foreach ( $data as $row ) {
			unset( $row['ref_id'] );
			unset( $row['is_seen'] );
			unset( $row['cb'] );
			fputcsv( $output, $row );
		}

		fclose( $output );

		return ob_get_clean();
	}

	/**
	 * Resend Referral Notices
	 *
	 * @return boolean
	 */
	public function resend_referral_notice() {
		$notice_sent = true;

		return $notice_sent;
	}
}
