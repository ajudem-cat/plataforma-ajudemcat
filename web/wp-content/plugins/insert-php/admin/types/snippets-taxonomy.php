<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	class WINP_SnippetsTaxonomy extends Wbcr_FactoryTaxonomies330_Taxonomy {

		/**
		 * Custom post name.
		 * @var string
		 */
		public $name = WINP_SNIPPETS_TAXONOMY;

		public $post_types = WINP_SNIPPETS_POST_TYPE;

		/**
		 * Template that defines a set of type options.
		 * Allowed values: public, private, internal.
		 * @var string
		 */
		public $template = 'private';

		/**
		 * Capabilities for roles that have access to manage the type.
		 * @link http://codex.wordpress.org/Roles_and_Capabilities
		 * @var array
		 */
		public $capabilities = array('administrator');

		function __construct($plugin)
		{
			$this->plural_title   = __( 'Tags', 'insert-php' );
			$this->singular_title = __( 'Tag', 'insert-php' );

			$this->options['hierarchical']          = false;
			$this->options['show_admin_column']     = true;
			$this->options['show_in_nav_menus']     = true;
			$this->options['update_count_callback'] = ''; // use default handler
			$this->options['show_in_quick_edit']    = true;

			parent::__construct( $plugin );
		}

		/**
		 * Taxonomy configurator.
		 */
		public function configure()
		{
		}
	}


