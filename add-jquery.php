<?php
/**
 * Adding jQuery for checkbox
 */

function jquery_for_no_expiration_if_recurring( $hook ) {
	if ( $hook != 'pmpro-membershiplevels' ) {
		// return;
	}
	wp_register_script( 'no-expiration', plugins_url( 'js/no-expiration-if-recurring.js', __FILE__ ), array( 'jquery' ), '1.2' );
	wp_enqueue_script( 'no-expiration' );
}
add_action( 'admin_enqueue_scripts', 'jquery_for_no_expiration_if_recurring', 10 );

add_action( 'admin_enqueue_scripts', 'wpse_46028_enqueue_admin_scripts' );
function wpse_46028_enqueue_admin_scripts() {
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );
	// hook the pointer
	add_action( 'admin_print_footer_scripts', 'wpse_46028_print_footer_scripts' );
}
function wpse_46028_print_footer_scripts() {
	$pointer_content = '<h3>Disabled, Yo!</h3>';
	$pointer_content .= '<p>Since we have <b>Recurring Subscriptions</b> checked by default, expiration is disabled.</p>';
	?>
	<style type="text/css">
		.wp-pointer-left {
			margin-top: 3rem; 
		}
	</style>
   <script type="text/javascript">
   //<![CDATA[
   jQuery(document).ready( function($) {
	//jQuery selector to point to 
	$('label#expiration-label').pointer({
		content: '<?php echo $pointer_content; ?>',
		position: 'left',
		close: function() {
			// This function is fired when you click the close button
		}
	  }).pointer('open');
   });
   //]]>
   </script>
	<?php
}
