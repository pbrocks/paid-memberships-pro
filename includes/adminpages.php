<?php
/*
	Get array of PMPro Capabilities
*/
function pmpro_getPMProCaps()
{
	$pmpro_caps = array(
		//pmpro_memberships_menu //this controls viewing the menu itself
		'pmpro_membershiplevels',
		'pmpro_pagesettings',
		'pmpro_paymentsettings',
		'pmpro_emailsettings',
		'pmpro_advancedsettings',
		'pmpro_addons',
		'pmpro_memberslist',
		'pmpro_memberslisttable',
		'pmpro_reports',
		'pmpro_orders',
		'pmpro_discountcodes',
		'pmpro_updates'
	);
	
	return $pmpro_caps;
}

/*
	Dashboard Menu
*/
function pmpro_add_pages()
{
	global $wpdb;   

	//array of all caps in the menu
	$pmpro_caps = pmpro_getPMProCaps();
	
	//the top level menu links to the first page they have access to
	foreach($pmpro_caps as $cap)
	{
		if(current_user_can($cap))
		{
			$top_menu_cap = $cap;
			break;
		}
	}
	
	if(empty($top_menu_cap))
		return;
	
	add_menu_page(__('Memberships', 'paid-memberships-pro' ), __('Memberships', 'paid-memberships-pro' ), 'pmpro_memberships_menu', 'pmpro-membershiplevels', $top_menu_cap, 'dashicons-groups');
	add_submenu_page('pmpro-membershiplevels', __('Page Settings', 'paid-memberships-pro' ), __('Page Settings', 'paid-memberships-pro' ), 'pmpro_pagesettings', 'pmpro-pagesettings', 'pmpro_pagesettings');
	add_submenu_page('pmpro-membershiplevels', __('Payment Settings', 'paid-memberships-pro' ), __('Payment Settings', 'paid-memberships-pro' ), 'pmpro_paymentsettings', 'pmpro-paymentsettings', 'pmpro_paymentsettings');
	add_submenu_page('pmpro-membershiplevels', __('Email Settings', 'paid-memberships-pro' ), __('Email Settings', 'paid-memberships-pro' ), 'pmpro_emailsettings', 'pmpro-emailsettings', 'pmpro_emailsettings');
	add_submenu_page('pmpro-membershiplevels', __('Advanced Settings', 'paid-memberships-pro' ), __('Advanced Settings', 'paid-memberships-pro' ), 'pmpro_advancedsettings', 'pmpro-advancedsettings', 'pmpro_advancedsettings');
	add_submenu_page('pmpro-membershiplevels', __('Add Ons', 'paid-memberships-pro' ), __('Add Ons', 'paid-memberships-pro' ), 'pmpro_addons', 'pmpro-addons', 'pmpro_addons');
	add_submenu_page('pmpro-membershiplevels', __('Members List', 'paid-memberships-pro' ), __('Members List', 'paid-memberships-pro' ), 'pmpro_memberslist', 'pmpro-memberslist', 'pmpro_memberslist');
	$list_table = add_submenu_page( 'pmpro-membershiplevels', __( 'List Table', 'paid-memberships-pro' ), __( 'List Table', 'paid-memberships-pro' ), 'pmpro_memberslist', 'pmpro-memberslisttable', 'pmpro_memberslisttable', 22 );
	add_submenu_page('pmpro-membershiplevels', __('Reports', 'paid-memberships-pro' ), __('Reports', 'paid-memberships-pro' ), 'pmpro_reports', 'pmpro-reports', 'pmpro_reports');
	add_submenu_page('pmpro-membershiplevels', __('Orders', 'paid-memberships-pro' ), __('Orders', 'paid-memberships-pro' ), 'pmpro_orders', 'pmpro-orders', 'pmpro_orders');
	add_submenu_page('pmpro-membershiplevels', __('Discount Codes', 'paid-memberships-pro' ), __('Discount Codes', 'paid-memberships-pro' ), 'pmpro_discountcodes', 'pmpro-discountcodes', 'pmpro_discountcodes');

	add_action( 'load-' . $list_table, 'pmpro_list_table_screen_options' );
	add_action( 'load-' . $list_table, 'pmpro_list_table_help_tabs' );
	//updates page only if needed
	if(pmpro_isUpdateRequired())
		add_submenu_page('pmpro-membershiplevels', __('Updates Required', 'paid-memberships-pro' ), __('Updates Required', 'paid-memberships-pro' ), 'pmpro_updates', 'pmpro-updates', 'pmpro_updates');	

	//rename the automatically added Memberships submenu item
	global $submenu;
	if(!empty($submenu['pmpro-membershiplevels']))
	{
		if(current_user_can("pmpro_membershiplevels"))
		{
			$submenu['pmpro-membershiplevels'][0][0] = __( 'Membership Levels', 'paid-memberships-pro' );
			$submenu['pmpro-membershiplevels'][0][3] = __( 'Membership Levels', 'paid-memberships-pro' );
		}
		elseif(current_user_can($top_menu_cap))
		{
			unset($submenu['pmpro-membershiplevels'][0]);
		}
		else
		{
			unset($submenu['pmpro-membershiplevels']);
		}
	}
}
add_action('admin_menu', 'pmpro_add_pages');

/*
	Admin Bar
*/
function pmpro_admin_bar_menu() {
	global $wp_admin_bar;
	
	//view menu at all?
	if ( !current_user_can('pmpro_memberships_menu') || !is_admin_bar_showing() )
		return;
	
	//array of all caps in the menu
	$pmpro_caps = pmpro_getPMProCaps();
	
	//the top level menu links to the first page they have access to
	foreach($pmpro_caps as $cap)
	{
		if(current_user_can($cap))
		{
			$top_menu_page = str_replace("_", "-", $cap);
			break;
		}
	}		
	
	$wp_admin_bar->add_menu( array(
	'id' => 'paid-memberships-pro',
	'title' => __( '<span class="ab-icon"></span>Memberships', 'paid-memberships-pro' ),
	'href' => get_admin_url(NULL, '/admin.php?page=' . $top_menu_page) ) );
	
	if(current_user_can('pmpro_membershiplevels'))
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-membership-levels',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Membership Levels', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-membershiplevels') ) );
	
	if(current_user_can('pmpro_pagesettings'))
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-page-settings',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Page Settings', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-pagesettings') ) );
	
	if(current_user_can('pmpro_paymentsettings'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-payment-settings',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Payment Settings', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-paymentsettings') ) );
	
	if(current_user_can('pmpro_emailsettings'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-email-settings',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Email Settings', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-emailsettings') ) );
	
	if(current_user_can('pmpro_advancedsettings'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-advanced-settings',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Advanced Settings', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-advancedsettings') ) );
	
	if(current_user_can('pmpro_addons'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-addons',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Add Ons', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-addons') ) );	
	
	if(current_user_can('pmpro_memberslist'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-members-list',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Members List', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-memberslist') ) );
	
	if(current_user_can('pmpro_memberslist'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-members-list-table',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'List Table', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-memberslisttable') ) );
	
	if(current_user_can('pmpro_reports'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-reports',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Reports', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-reports') ) );
	
	if(current_user_can('pmpro_orders'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-orders',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Orders', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-orders') ) );
	
	if(current_user_can('pmpro_discountcodes'))	
		$wp_admin_bar->add_menu( array(
		'id' => 'pmpro-discount-codes',
		'parent' => 'paid-memberships-pro',
		'title' => __( 'Discount Codes', 'paid-memberships-pro' ),
		'href' => get_admin_url(NULL, '/admin.php?page=pmpro-discountcodes') ) );
}
add_action('admin_bar_menu', 'pmpro_admin_bar_menu', 1000);

/*
	Functions to load pages from adminpages directory
*/
function pmpro_reports()
{
	require_once(PMPRO_DIR . "/adminpages/reports.php");
}

function pmpro_memberslist()
{
	require_once(PMPRO_DIR . "/adminpages/memberslist.php");
}


/**
 * Screen options for the List Table
 *
 * Callback for the load-($page_hook_suffix)
 * Called when the plugin page is loaded
 *
 * @since    2.0.0
 */
function pmpro_list_table_screen_options() {
	global $user_list_table;
	$arguments = array(
		'label'   => __( 'Members Per Page', 'paid-memberships-pro' ),
		'default' => 13,
		'option'  => 'users_per_page',
	);

	add_screen_option( 'per_page', $arguments );

	// instantiate the User List Table
	$user_list_table = new PMPro_Members_List_Table();
}

function pmpro_list_table_help_tabs() {
	$screen = get_current_screen();
	$screen->add_help_tab(
		array(
			'id'      => 'sortable_overview',
			'title'   => __( 'Sortable Overview', 'paid-memberships-pro' ),
			'content' => '<p>' . __( 'Overview of your plugin or theme here', 'paid-memberships-pro' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'sortable_faq',
			'title'   => __( 'Sortable FAQ', 'paid-memberships-pro' ),
			'content' => '<p>' . __( 'Frequently asked questions and their answers here', 'paid-memberships-pro' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'sortable_support',
			'title'   => __( 'Sortable Support', 'paid-memberships-pro' ),
			'content' => '<p>' . __( 'For support, visit the <a href="https://www.paidmembershipspro.com/forums/forum/members-forum/" target="_blank">Support Forums</a>', 'paid-memberships-pro' ) . '</p>',
		)
	);

	$screen->set_help_sidebar( '<p>' . __( 'This is the content you will be adding to the sidebar.', 'paid-memberships-pro' ) . '</p>' );
}

add_action( 'admin_enqueue_scripts', 'pmpro_add_admin_scripts', 11 );
function pmpro_add_admin_scripts() {
	wp_register_script( 'pmpro-list-table', plugins_url( '/js/selected-level.js', __FILE__ ), array( 'jquery' ), time() );
	wp_enqueue_script( 'pmpro-list-table' );
	wp_register_style( 'pmpro-list-table', plugins_url( '/css/list-table.css', __FILE__ ), time() );
	wp_enqueue_style( 'pmpro-list-table' );
}

function pmpro_memberslisttable() {
	// require_once PMPRO_DIR . '/adminpages/memberslisttable.php';
		global $user_list_table;

	// query, filter, and sort the data
	$user_list_table = new PMPro_Members_List_Table();
	$user_list_table->prepare_items();
	require_once dirname( __DIR__ ) . '/adminpages/admin_header.php';

	// render the List Table
	?>
		<h2><?php _e( 'PMPro Members List Table', 'paid-memberships-pro' ); ?>
		<a target="_blank" href="<?php echo admin_url('admin-ajax.php');?>?action=memberslist_csv&s=<?php echo esc_attr($s);?>&l=<?php echo $l?>" class="add-new-h2"><?php _e('Export to CSV', 'paid-memberships-pro' );?></a>
		</h2>
			<div id="member-list-table-demo">			
				<div id="pbrx-post-body">		
					<form id="member-list-form" method="get">
						<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
						<?php
							$user_list_table->search_box( __( 'Find Member', 'paid-memberships-pro' ), 'pbrx-user-find' );
							$user_list_table->display();
						?>
				</form>
			</div>			
		</div>
	<?php
}

function pmpro_discountcodes()
{
	require_once(PMPRO_DIR . "/adminpages/discountcodes.php");
}

function pmpro_membershiplevels()
{
	require_once(PMPRO_DIR . "/adminpages/membershiplevels.php");
}

function pmpro_pagesettings()
{
	require_once(PMPRO_DIR . "/adminpages/pagesettings.php");
}

function pmpro_paymentsettings()
{
	require_once(PMPRO_DIR . "/adminpages/paymentsettings.php");
}

function pmpro_emailsettings()
{
	require_once(PMPRO_DIR . "/adminpages/emailsettings.php");
}

function pmpro_advancedsettings()
{
	require_once(PMPRO_DIR . "/adminpages/advancedsettings.php");
}

function pmpro_addons()
{
	require_once(PMPRO_DIR . "/adminpages/addons.php");
}

function pmpro_orders()
{
	require_once(PMPRO_DIR . "/adminpages/orders.php");
}

function pmpro_updates()
{
	require_once(PMPRO_DIR . "/adminpages/updates.php");
}

/**
 * Function to add a post display state for special PMPro pages in the page list table.
 *
 * @param array   $post_states An array of post display states.
 * @param WP_Post $post The current post object.
 */
function pmpro_display_post_states( $post_states, $post ) {
	// Get assigned page settings.
	global $pmpro_pages;

	if ( intval( $pmpro_pages['account'] ) === $post->ID ) {
		$post_states['pmpro_account_page'] = __( 'Membership Account Page', 'paid-memberships-pro' );
	}

	if ( intval( $pmpro_pages['billing'] ) === $post->ID ) {
		$post_states['pmpro_billing_page'] = __( 'Membership Billing Information Page', 'paid-memberships-pro' );
	}

	if ( intval( $pmpro_pages['cancel'] ) === $post->ID ) {
		$post_states['pmpro_cancel_page'] = __( 'Membership Cancel Page', 'paid-memberships-pro' );
	}

	if ( intval( $pmpro_pages['checkout'] ) === $post->ID ) {
		$post_states['pmpro_checkout_page'] = __( 'Membership Checkout Page', 'paid-memberships-pro' );
	}

	if ( intval( $pmpro_pages['confirmation'] ) === $post->ID ) {
		$post_states['pmpro_confirmation_page'] = __( 'Membership Confirmation Page', 'paid-memberships-pro' );
	}

	if ( intval( $pmpro_pages['invoice'] ) === $post->ID ) {
		$post_states['pmpro_invoice_page'] = __( 'Membership Invoice Page', 'paid-memberships-pro' );
	}

	if ( intval( $pmpro_pages['levels'] ) === $post->ID ) {
		$post_states['pmpro_levels_page'] = __( 'Membership Levels  Page', 'paid-memberships-pro' );
	}

	return $post_states;
}
add_filter( 'display_post_states', 'pmpro_display_post_states', 10, 2 );

/*
Function to add links to the plugin action links
*/
function pmpro_add_action_links($links) {
	
	//array of all caps in the menu
	$pmpro_caps = pmpro_getPMProCaps();
	
	//the top level menu links to the first page they have access to
	foreach($pmpro_caps as $cap)
	{
		if(current_user_can($cap))
		{
			$top_menu_page = str_replace("_", "-", $cap);
			break;
		}
	}
	
	$new_links = array(
		'<a href="' . get_admin_url(NULL, 'admin.php?page=' . $top_menu_page) . '">Settings</a>',
	);
	return array_merge($new_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(PMPRO_DIR . "/paid-memberships-pro.php"), 'pmpro_add_action_links');

/*
Function to add links to the plugin row meta
*/
function pmpro_plugin_row_meta($links, $file) {
	if(strpos($file, 'paid-memberships-pro.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url( apply_filters( 'pmpro_docs_url', 'http://paidmembershipspro.com/documentation/' ) ) . '" title="' . esc_attr( __( 'View PMPro Documentation', 'paid-memberships-pro' ) ) . '">' . __( 'Docs', 'paid-memberships-pro' ) . '</a>',
			'<a href="' . esc_url( apply_filters( 'pmpro_support_url', 'http://paidmembershipspro.com/support/' ) ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'paid-memberships-pro' ) ) . '">' . __( 'Support', 'paid-memberships-pro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmpro_plugin_row_meta', 10, 2);
