<?php
/**
 * Factory viewtable
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       factory-viewtables
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

if ( defined( 'FACTORY_VIEWTABLES_410_LOADED' ) ) {
	return;
}

define( 'FACTORY_VIEWTABLES_410_VERSION', '4.1.0' );
define( 'FACTORY_VIEWTABLES_410_LOADED', true );

define( 'FACTORY_VIEWTABLES_410_DIR', dirname( __FILE__ ) );
define( 'FACTORY_VIEWTABLES_410_URL', plugins_url( null, __FILE__ ) );

#comp merge
require( FACTORY_VIEWTABLES_410_DIR . '/viewtable.class.php' );
require( FACTORY_VIEWTABLES_410_DIR . '/includes/viewtable-columns.class.php' );
#endcomp