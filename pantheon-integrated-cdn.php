<?php
/**
 * Plugin Name:     Pantheon Integrated CDN
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Pantheon
 * Author URI:      https://pantheon.io
 * Text Domain:     pantheon-integrated-cdn
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Pantheon_Integrated_Cdn
 */

/**
 * Register the class autoloader
 */
spl_autoload_register( function( $class ) {
	$class = ltrim( $class, '\\' );
	if ( 0 !== stripos( $class, 'Pantheon_Integrated_CDN\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class );
	array_shift( $parts ); // Don't need "Pantheon_Integrated_CDN"
	$last = array_pop( $parts ); // File should be 'class-[...].php'
	$last = 'class-' . $last . '.php';
	$parts[] = $last;
	$file = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
	if ( file_exists( $file ) ) {
		require $file;
	}
});

add_filter( 'wp', array( 'Pantheon_Integrated_CDN\Emitter', 'action_wp' ) );
