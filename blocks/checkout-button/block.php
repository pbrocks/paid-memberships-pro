<?php
/**
 * Sets up checkout-button block, does not format frontend
 *
 * @package blocks/checkout-button
 **/

namespace PMPro\blocks\checkout_button;

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

// Only load if Gutenberg is available.
if ( ! function_exists( 'register_block_type' ) ) {
	return;
}

add_action( 'init', __NAMESPACE__ . '\register_dynamic_block' );
/**
 * Register the dynamic block.
 *
 * @since 2.1.0
 *
 * @return void
 */
function register_dynamic_block() {
	// Hook server side rendering into render callback.
	register_block_type( 'pmpro/checkout-button', [
		'render_callback' => __NAMESPACE__ . '\render_dynamic_block',
	] );
}

/**
 * Server rendering for checkout-button block.
 *
 * @param array $attributes contains text, level, and css_class strings.
 * @return string
 **/
function render_dynamic_block( $attributes ) {
	$text      = 'Buy PHP';
	$css_class = 'pmpro_btn';

	if ( empty( $attributes['level'] ) ) {
		$level = array(0);
	} else {
		$level = $attributes['level'];
	}
	
	if ( ! empty( $attributes['text'] ) ) {
		$text = $attributes['text'];
	}
	if ( ! empty( $attributes['css_class'] ) ) {
		$css_class = $attributes['css_class'];
	}

	return( pmpro_getCheckoutButton( $level, $text, $css_class ) );
}
