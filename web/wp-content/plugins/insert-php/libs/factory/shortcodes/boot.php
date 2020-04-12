<?php
/**
 * Factory Shortcodes
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       factory-shortcodes
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

if ( defined( 'FACTORY_SHORTCODES_329_LOADED' ) ) {
	return;
}

define( 'FACTORY_SHORTCODES_329_VERSION', '3.2.9' );

define( 'FACTORY_SHORTCODES_329_LOADED', true );

define( 'FACTORY_SHORTCODES_329_DIR', dirname( __FILE__ ) );

#comp merge
require( FACTORY_SHORTCODES_329_DIR . '/shortcodes.php' );
require( FACTORY_SHORTCODES_329_DIR . '/shortcode.class.php' );
#endcomp
