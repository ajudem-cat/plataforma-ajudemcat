<?php
/**
 * Factory Metaboxes
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       factory-metaboxes
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// module provides function only for the admin area
if ( ! is_admin() ) {
	return;
}

if ( defined( 'FACTORY_METABOXES_409_LOADED' ) ) {
	return;
}

define( 'FACTORY_METABOXES_409_VERSION', '4.0.9' );

define( 'FACTORY_METABOXES_409_LOADED', true );

define( 'FACTORY_METABOXES_409_DIR', dirname( __FILE__ ) );
define( 'FACTORY_METABOXES_409_URL', plugins_url( null, __FILE__ ) );

#comp merge
require( FACTORY_METABOXES_409_DIR . '/metaboxes.php' );
require( FACTORY_METABOXES_409_DIR . '/metabox.class.php' );
require( FACTORY_METABOXES_409_DIR . '/includes/form-metabox.class.php' );
require( FACTORY_METABOXES_409_DIR . '/includes/publish-metabox.class.php' );
#endcomp