<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_InfoMetaBox extends WINP_MetaBox {

	/**
	 * A visible title of the metabox.
	 *
	 * Inherited from the class FactoryMetabox.
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $title;

	/**
	 * The part of the page where the edit screen
	 * section should be shown ('normal', 'advanced', or 'side').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $context = 'side';

	/**
	 * The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/add_meta_box
	 * Inherited from the class FactoryMetabox.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $priority = 'core';

	public $css_class = 'factory-bootstrap-423 factory-fontawesome-000';

	protected $errors = [];
	protected $source_channel;
	protected $facebook_group_id;
	protected $paginate_url;

	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->title = __( 'Robin image optimizer: notice', 'insert-php' );
	}


	/**
	 * Configures a metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param Wbcr_Factory422_ScriptList $scripts   A set of scripts to include.
	 * @param Wbcr_Factory422_StyleList  $styles    A set of style to include.
	 *
	 * @return void
	 */
	public function configure( $scripts, $styles ) {
	}

	public function html() {
		?>
        <div class="wbcr-inp-metabox-banner">
            <div class="wbcr-inp-image">
				<?php WINP_Plugin::app()->get_adverts_manager()->render_placement( 'right_sidebar' ) ?>
            </div>
        </div>
		<?php
	}
}