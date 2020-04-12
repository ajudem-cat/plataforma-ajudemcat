<?php
/**
 * This class is implemented metabox.
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 2018, OnePress Ltd
 *s
 * @package core
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WINP_MetaBox
 */
class WINP_MetaBox extends Wbcr_FactoryMetaboxes409_Metabox {

	public function __construct( $plugin ) {
		parent::__construct( $plugin );
	}

}
