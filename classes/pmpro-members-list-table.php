<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class PMPro_Members_List_Table extends WP_List_Table {
	/**
	 * The text domain of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	protected $plugin_text_domain;

	/**
	 * Call the parent constructor to override the defaults $args
	 *
	 * @param string $plugin_text_domain    Text domain of the plugin.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->plugin_text_domain = 'paid-memberships-pro';

		parent::__construct(
			array(
				'plural'   => 'members',
				// Plural value used for labels and the objects being listed.
				'singular' => 'member',
				// Singular label for an object being listed, e.g. 'post'.
				'ajax'     => false,
				// If true, the parent class will call the _js_vars() method in the footer
			)
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
	 *
	 * @since   2.0.0
	 */
	public function prepare_items() {
		// check if a search was performed.
		$user_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$this->_column_headers = $this->get_column_info();

		// check and process any actions such as bulk actions.
		$this->handle_table_actions();

		// fetch table data
		$table_data = $this->sql_table_data();
		usort( $table_data, array( $this, 'sort_data' ) );

		// filter the data in case of a search.
		if ( $user_search_key ) {
			$table_data = $this->filter_table_data( $table_data, $user_search_key );
		}

		// required for pagination
		$users_per_page = $this->get_items_per_page( 'users_per_page' );
		$table_page     = $this->get_pagenum();

		// provide the ordered data to the List Table.
		// we need to manually slice the data based on the current pagination.
		$this->items = array_slice( $table_data, ( ( $table_page - 1 ) * $users_per_page ), $users_per_page );

		// set the pagination arguments
		$total_users = count( $table_data );
		$this->set_pagination_args(
			array(
				'total_items' => $total_users,
				'per_page'    => $users_per_page,
				'total_pages' => ceil( $total_users / $users_per_page ),
			)
		);
	}

	/**
	 * Get a list of columns.
	 *
	 * The format is: 'internal-name' => 'Title'
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'ID'            => 'ID',
			'display_name'  => 'Display Name',
			'user_email'    => 'Email',
			'membership'    => 'Level Name',
			'membership_id' => 'Level ID',
			'startdate'     => 'Subscribe Date',
			'enddate'       => 'End Date',
			'joindate'      => 'Initial Date',
		);
		return $columns;
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
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		/**
		 * actual sorting still needs to be done by prepare_items.
		 * specify which columns should have the sort icon.
		 *
		 * key => value
		 * column name_in_list_table => columnname in the db
		 */
		return array(
			'ID'            => array(
				'ID',
				false,
			),
			'display_name'  => array(
				'display_name',
				false,
			),
			'user_email'    => array(
				'user_email',
				false,
			),
			'membership'    => array(
				'membership',
				false,
			),
			'membership_id' => array(
				'membership_id',
				false,
			),
			'startdate'     => array(
				'startdate',
				false,
			),
			'enddate'       => array(
				'enddate',
				false,
			),
			'joindate'      => array(
				'joindate',
				false,
			),
		);
	}

		/**
		 * Allows you to sort the data by the variables set in the $_GET
		 *
		 * @return Mixed
		 */
	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'startdate';
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

		if ( $order === 'asc' ) {
			return $result;
		}

		return -$result;
	}

	/**
	 * Text displayed when no user data is available
	 *
	 * @since   2.0.0
	 *
	 * @return void
	 */
	public function no_items() {
		_e( 'No users avaliable.', $this->plugin_text_domain );
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function sql_table_data() {
		global $wpdb;
		$sql_table_data = array();
		$mysqli_query   =
			"
			SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, u.user_nicename, u.display_name, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership 
			FROM $wpdb->users u 
			LEFT JOIN $wpdb->usermeta umh 
			ON umh.meta_key = 'pmpromd_hide_directory' 
			AND u.ID = umh.user_id 
			LEFT JOIN $wpdb->pmpro_memberships_users mu 
			ON u.ID = mu.user_id 
			LEFT JOIN $wpdb->pmpro_membership_levels m 
			ON mu.membership_id = m.id
			WHERE mu.status = 'active' 
			AND (umh.meta_value IS NULL 
			OR umh.meta_value <> '1') 
			AND mu.membership_id > 0 ";

		$sql_table_data = $wpdb->get_results( $mysqli_query, ARRAY_A );
		return $sql_table_data;
	}

	/**
	 * Filter the table data based on the user search key
	 *
	 * @since 2.0.0
	 *
	 * @param array  $table_data
	 * @param string $search_key
	 * @returns array
	 */
	public function filter_table_data( $table_data, $search_key ) {
		$filtered_table_data = array_values(
			array_filter(
				$table_data,
				function( $row ) use ( $search_key ) {
					foreach ( $row as $row_val ) {
						if ( stripos( $row_val, $search_key ) !== false ) {
							return true;
						}
					}
				}
			)
		);
		return $filtered_table_data;
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
			case 'display_name':
			case 'user_email':
			case 'membership':
			case 'membership_id':
			case 'cycle_period':
			case 'cycle_number':
				return $item[ $column_name ];
			case 'startdate':
				$startdate = $item[ $column_name ];
				return date( 'Y-m-d', $startdate );
			case 'enddate':
				if ( 0 == $item[ $column_name ] ) {
					return 'Recurring';
				} else {
					return date( 'Y-m-d', $item[ $column_name ] );
				}
			case 'joindate':
				if ( $item['startdate'] == $item['joindate'] ) {
					return 'Join = Start';
				} else {
					return date( 'Y-m-d', $item[ $column_name ] );
				}

			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Get value for checkbox column.
	 *
	 * The special 'cb' column
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<label class="screen-reader-text" for="user_' . $item['ID'] . '">' . sprintf( __( 'Select %s' ), $item['user_login'] ) . '</label>'
			. "<input type='checkbox' name='users[]' id='user_{$item['ID']}' value='{$item['ID']}' />"
		);
	}

	public function get_some_actions() {
		?>
		<ul class="subsubsub">
		<li>
			<?php _e( 'Show', 'paid-memberships-pro' ); ?>
			<select name="l" onchange="jQuery('#posts-filter').submit();">
				<option value="" 
				<?php
				if ( ! $l ) {
					?>
					selected="selected"<?php } ?>><?php _e( 'All Levels', 'paid-memberships-pro' ); ?></option>
				<?php
					$levels = $wpdb->get_results( "SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name" );
				foreach ( $levels as $level ) {
					?>
					<option value="<?php echo $level->id; ?>" 
					  <?php
						if ( $l == $level->id ) {
							?>
						selected="selected"<?php } ?>><?php echo $level->name; ?></option>
					<?php
				}
				?>
				<option value="cancelled" 
				<?php
				if ( $l == 'cancelled' ) {
					?>
					selected="selected"<?php } ?>><?php _e( 'Cancelled Members', 'paid-memberships-pro' ); ?></option>
				<option value="expired" 
				<?php
				if ( $l == 'expired' ) {
					?>
					selected="selected"<?php } ?>><?php _e( 'Expired Members', 'paid-memberships-pro' ); ?></option>
				<option value="oldmembers" 
				<?php
				if ( $l == 'oldmembers' ) {
					?>
					selected="selected"<?php } ?>><?php _e( 'Old Members', 'paid-memberships-pro' ); ?></option>
			</select>
		</li>
	</ul>
		<?php
	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		global $membership_levels;
		if ( $which == 'top' ) {
			$existing_levels = $membership_levels;
			if ( 1 < count( $existing_levels ) ) {
				$pmpro_levels_dropdown  = '<select name="dropdown-levels" id="dropdown-levels">';
				$pmpro_levels_dropdown .= '<option value="">Select a Level</option>';
				foreach ( $existing_levels as $key => $value ) {
					$pmpro_levels_dropdown .= '<option value="' . $value->id . '">' . $value->id . ' => ' . $value->name . '</option>';
				}
				$pmpro_levels_dropdown .= '<select>';
				echo $pmpro_levels_dropdown;
			}
			// The code that goes before the table is here
			echo ' <span id="return-levels">Add Levels here, remove Bulk Actions</span>';
		}
		if ( $which == 'bottom' ) {
			// The code that goes after the table is there
			echo ' Add Search here, remove Bulk Actions';
		}
	}

	/**
	 * Process actions triggered by the user
	 *
	 * @since    2.0.0
	 */
	public function handle_table_actions() {
		/**
		 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
		 *
		 * action - is set if checkbox from top-most select-all is set, otherwise returns -1
		 * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
		 */

		// check for individual row actions
		$the_table_action = $this->current_action();

		if ( 'view_usermeta' === $the_table_action ) {
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'view_usermeta_nonce' ) ) {
				$this->invalid_nonce_redirect();
			} else {
				$this->graceful_exit();
			}
		}

		// check for table bulk actions
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk-download' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk-download' ) ) {

			// verify the nonce.
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			/**
			 * Note: the nonce field is set by the parent class
			 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			 */
			if ( ! wp_verify_nonce( $nonce, 'bulk-users' ) ) {
				$this->invalid_nonce_redirect();
			} else {
				$this->page_bulk_download( $_REQUEST['users'] );
				$this->graceful_exit();
			}
		}
	}

	/**
	 * Stop execution and exit
	 *
	 * @since    2.0.0
	 *
	 * @return void
	 */
	public function graceful_exit() {
		exit;
	}

	/**
	 * Die when the nonce check fails.
	 *
	 * @since    2.0.0
	 *
	 * @return void
	 */
	public function invalid_nonce_redirect() {
		wp_die(
			__( 'Invalid Nonce', $this->plugin_text_domain ),
			__( 'Error', $this->plugin_text_domain ),
			array(
				'response'  => 403,
				'back_link' => esc_url( add_query_arg( array( 'page' => wp_unslash( $_REQUEST['page'] ) ), admin_url( 'pmpro-membershiplevels' ) ) ),
			)
		);
	}
}
