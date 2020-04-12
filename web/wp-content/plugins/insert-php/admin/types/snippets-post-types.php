<?php

/**
 * Php Snippets Type
 * Declaration for custom post type of Php code snippets
 *
 * @link http://codex.wordpress.org/Post_Types
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_SnippetsType extends Wbcr_FactoryTypes410_Type {

	/**
	 * Custom post name.
	 *
	 * @var string
	 */
	//public $name = 'wbcr-scrapes';

	/**
	 * Template that defines a set of type options.
	 * Allowed values: public, private, internal.
	 *
	 * @var string
	 */
	public $template = 'private';

	/**
	 * Capabilities for roles that have access to manage the type.
	 *
	 * @link http://codex.wordpress.org/Roles_and_Capabilities
	 * @var array
	 */
	public $capabilities = [ 'administrator' ];

	/**
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	function __construct( Wbcr_Factory422_Plugin $plugin ) {
		$this->name           = WINP_SNIPPETS_POST_TYPE;
		$this->plural_title   = __( 'Woody snippets', 'insert-php' );
		$this->singular_title = __( 'Woody snippets', 'insert-php' );

		parent::__construct( $plugin );

		add_action( 'admin_head', [ $this, 'print_left_menu_styles' ] );
	}

	/**
	 * Prints styles for Woody add snippets menu
	 */
	public function print_left_menu_styles() {
		?>
        <!-- Woody ad snippets -->
        <style>
            #menu-posts-wbcr-snippets .wp-menu-open .wp-menu-name {
                background: #242525;
            }
        </style>
        <!-- /Woody ad snippets -->
		<?php
	}

	/**
	 * Type configurator.
	 */
	public function configure() {
		$plural_name   = $this->plural_title;
		$singular_name = $this->singular_title;

		$labels = [
			'singular_name'      => $this->singular_title,
			'name'               => $this->plural_title,
			'all_items'          => sprintf( __( 'Snippets', 'insert-php' ), $plural_name ),
			'add_new'            => sprintf( __( '+ Add snippet', 'insert-php' ), $singular_name ),
			'add_new_item'       => sprintf( __( 'Add new', 'insert-php' ), $singular_name ),
			'edit'               => sprintf( __( 'Edit', 'insert-php' ) ),
			'edit_item'          => sprintf( __( 'Edit snippet', 'insert-php' ), $singular_name ),
			'new_item'           => sprintf( __( 'New snippet', 'insert-php' ), $singular_name ),
			'view'               => sprintf( __( 'View', 'factory' ) ),
			'view_item'          => sprintf( __( 'View snippet', 'insert-php' ), $singular_name ),
			'search_items'       => sprintf( __( 'Search snippets', 'insert-php' ), $plural_name ),
			'not_found'          => sprintf( __( 'Snippet is not found', 'insert-php' ), $plural_name ),
			'not_found_in_trash' => sprintf( __( 'Snippt is not found in trash', 'insert-php' ), $plural_name ),
			'parent'             => sprintf( __( 'Parent snippet', 'insert-php' ), $plural_name )
		];

		$this->options['labels']     = apply_filters( 'wbcr_inp_items_lables', $labels );
		$this->options['can_export'] = WINP_Plugin::app()->get_api_object()->is_key() ? true : false;

		$parameters   = [ 'title', 'revisions' ];
		$snippet_type = WINP_Helper::get_snippet_type();
		if ( $snippet_type === WINP_SNIPPET_TYPE_TEXT ) {
			$parameters[] = 'editor';
		}
		$this->options['supports'] = apply_filters( 'wbcr_inp_items_supports', $parameters );

		/**
		 * Menu
		 */

		$this->menu->icon = WINP_PLUGIN_URL . '/admin/assets/img/menu-icon-4.png';

		/**
		 * View table
		 */

		$this->view_table = 'WINP_SnippetsViewTable';

		/**
		 * Scripts & styles
		 */

		$this->scripts->request( [ 'jquery', 'jquery-effects-highlight', 'jquery-effects-slide' ] );

		$this->scripts->request( [
			'bootstrap.datepicker',
			'control.checkbox',
			'control.dropdown',
			'control.list',
			'bootstrap.tooltip',
			'bootstrap.modal',
		], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.core',
			'bootstrap.datepicker',
			'bootstrap.form-group',
			'bootstrap.form-metabox',
			'bootstrap.wp-editor',
			'bootstrap.separator',
			'control.checkbox',
			'control.dropdown',
			'control.list',
		], 'bootstrap' );
	}
}