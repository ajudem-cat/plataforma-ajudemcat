<?php
	/**
	 * The file contains a base class for all types.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2017, Webcraftic Ltd
	 *
	 * @package core
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryTaxonomies330_Taxonomy') ) {

		/**
		 * The base class that provides abstraction for custom post type.
		 *
		 * @since 1.0.0
		 */
		abstract class Wbcr_FactoryTaxonomies330_Taxonomy {

			/**
			 * Internal taxonomy name.
			 *
			 * @since 1.0.0
			 * @var string
			 */
			public $name;

			/**
			 * Internal type name.
			 *
			 * @since 1.0.0
			 * @var string/array
			 */
			public $post_types;

			/**
			 * Plural visible title.
			 *
			 * @since 1.0.0
			 * @var string
			 */
			public $plural_title;

			/**
			 * Singular visible title.
			 *
			 * @since 1.0.0
			 * @var string
			 */
			public $singular_title;

			/**
			 * One of pre defined templates for options.
			 * Allowed values: public, private, internal.
			 *
			 * @since 1.0.0
			 * @var string
			 */
			public $template = 'public';


			/**
			 * Capabilities for roles that have access to manage the type.
			 *
			 * @link http://codex.wordpress.org/Roles_and_Capabilities
			 *
			 * @since 1.0.0
			 * @var string[]
			 */
			public $capabilities = array('administrator', 'editor');

			/**
			 * Scripts that must be included on edit page.
			 *
			 * @since 1.0.0
			 * @var Wbcr_Factory422_ScriptList[]
			 */
			public $scripts;

			/**
			 * Styles that must be included on edit page.
			 *
			 * @since 1.0.0
			 * @var Wbcr_Factory422_StyleList[]
			 */
			public $styles;

			/**
			 * Options for a custom type.
			 *
			 * @link http://codex.wordpress.org/Function_Reference/register_post_type
			 * @since 1.0.0
			 */
			public $options = array(
				'hierarchical' => true,
				'labels' => null,
				'description' => null,
				'public' => null,
				'publicly_queryable' => null,
				'exclude_from_search' => null,
				'show_ui' => null,
				'rest_base' => null,
				'rest_controller_class' => null,
				'show_tagcloud' => null,
				'show_in_quick_edit' => null,
				'capabilities' => array(),
				'show_in_nav_menus' => null,
				'show_in_rest' => null,
				'show_admin_column' => null,
				'update_count_callback' => null,
				'query_var' => null,
				'rewrite' => null,
				'meta_box_cb' => null,
				'sort' => null
			);

			/**
			 * Messages for a custom post type.
			 *
			 * @link http://codex.wordpress.org/Function_Reference/register_post_type
			 * @since 1.0.0
			 */
			public $messages = array();


			/**
			 * Creates a new instance of a type.
			 *
			 * @param Wbcr_Factory422_Plugin $plugin
			 */
			public function __construct($plugin)
			{
				$this->plugin = $plugin;

				$this->metaboxes = array();

				$this->scripts = $this->plugin->newScriptList();
				$this->styles = $this->plugin->newStyleList();

				add_action('init', array($this, 'register'));
			}

			/**
			 * Registers a custom post type.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function register()
			{
				// type's titles
				$singular_name = $this->plural_title
					? $this->plural_title
					: $this->name;
				$plural_name = $this->singular_title
					? $this->singular_title
					: $this->name;

				$this->options['labels'] = array(
					'name' => $singular_name,
					'singular_name' => $plural_name,
				);

				if( $this->template ) {
					$this->applyTypeTemplate($this->template);
				}

				if( is_admin() ) {
					$this->registerForAdmin();
				} else $this->registerForPublic();
			}

			/**
			 * Registers the custom taxonomy for the public area.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function registerForPublic()
			{
				register_taxonomy($this->name, $this->post_types, $this->options);
			}


			/**
			 * Registers the custom post type for the admin area.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function registerForAdmin()
			{

				$this->buildLables();
				$this->configure();

				if( !$this->scripts->isEmpty('bootstrap') || !$this->styles->isEmpty('bootstrap') ) {
					add_action('wbcr_factory_bootstrap_enqueue_scripts_' . $this->plugin->getPluginName(), array(
						$this,
						'actionAdminBootstrapScripts'
					));
				}

				// includes styles and scripts
				if( !$this->scripts->isEmpty() || !$this->styles->isEmpty() ) {
					add_action('admin_enqueue_scripts', array($this, 'actionAdminScripts'));
				}

				// Add the fields to the taxonomy, using our callback function
				add_action($this->name . '_edit_form_fields', array($this, 'addCustomFields'), 10);
				add_action($this->name . '_add_form_fields', array($this, 'addCustomFields'), 10);

				// Save the changes made on the taxonomy, using our callback function
				add_action('edited_' . $this->name, array($this, 'saveCustomFields'), 10);
				add_action('created_' . $this->name, array($this, 'saveCustomFields'), 10);

				/*add_action("{$this->name}_pre_edit_form", function () {
					echo '<div class="factory-bootstrap-423 factory-fontawesome-000">';
				});
				add_action("{$this->name}_edit_form", function () {
					echo '</div>';
				});*/

				/*if( !empty($this->capabilities) ) {

					$this->options['capability_type'] = $this->name;
					$this->options['capabilities'] = array(
						'edit_post' => 'edit_' . $this->name,
						'read_post' => 'read_' . $this->name,
						'delete_post' => 'delete_' . $this->name,
						'delete_posts' => 'delete_' . $this->name . 's',
						'edit_posts' => 'edit_' . $this->name . 's',
						'edit_others_posts' => 'edit_others_' . $this->name . 's',
						'publish_posts' => 'publish_' . $this->name . 's',
						'read_private_posts' => 'read_private_' . $this->name . 's',
						'create_posts' => 'edit_' . $this->name . 's'
					);
				} elseif( $this->options['capability_type'] == null ) {
					$this->options['capability_type'] = 'post';
				}*/

				// register view table
				/*if( !empty($this->viewTable) && class_exists($this->viewTable) ) {
					$this->viewTable = new $this->viewTable($this->plugin);
					$this->viewTable->connect($this);
				}*/

				register_taxonomy($this->name, $this->post_types, $this->options);
			}

			public function addCustomFields($tag)
			{
			}

			public function saveCustomFields($term_id)
			{
			}

			/**
			 * Actions that includes registered fot this type scritps and styles.
			 * @global object $post
			 * @param string $hook
			 */
			public function actionAdminBootstrapScripts($hook)
			{
				global $tax;

				if( !in_array($hook, array('edit-tags.php', 'term.php')) ) {
					return;
				}
				if( $tax->name != $this->name ) {
					return;
				}
				if( $this->scripts->isEmpty('bootstrap') && $this->styles->isEmpty('bootstrap') ) {
					return;
				}

				$this->scripts->connect('bootstrap');
				$this->styles->connect('bootstrap');
			}

			/**
			 * Actions that includes registered fot this type scritps and styles.
			 * @global object $post
			 * @param string $hook
			 */
			public function actionAdminScripts($hook)
			{
				global $tax;

				if( !in_array($hook, array('edit-tags.php', 'term.php')) ) {
					return;
				}
				if( $tax->name != $this->name ) {
					return;
				}
				if( $this->scripts->isEmpty() && $this->styles->isEmpty() ) {
					return;
				}

				$this->scripts->connect();
				$this->styles->connect();
			}

			/**
			 * Applies a given template to the type options.
			 * @param string $templateName allowed values: 'public', 'private', 'internal'
			 * @throws Exception Invalide template name for the type "%s"
			 */
			private function applyTypeTemplate($templateName)
			{

				if( !in_array($templateName, array('public', 'private', 'internal')) ) {
					throw new Exception(sprintf('Invalide template name for the type "%s"', $this->name));
				}

				switch( $templateName ) {
					case 'public':

						$this->options['public'] = true;

						break;
					case 'private':

						$this->options['public'] = false;

						$this->options['show_in_menu'] = true;
						$this->options['show_ui'] = true;
						$this->options['publicly_queryable'] = false;
						$this->options['exclude_from_search'] = true;

						break;
					case 'internal':

						$this->options['public'] = false;
						break;
				}
			}

			/**
			 * Builds labels for the post type.
			 */
			private function buildLables()
			{

				// type's titles
				$singular_name = $this->options['labels']['singular_name'];
				$plural_name = $this->options['labels']['name'];

				$labels = array(
					'name' => $plural_name,
					'singular_name' => $singular_name,
					'search_items' => sprintf(__('Search %1$s', 'factory_types_410'), $plural_name),
					'popular_items' => sprintf(__('Popular %1$s', 'factory_types_410'), $plural_name),
					'all_items' => sprintf(__('All %1$s', 'factory_types_410'), $plural_name),
					'parent_item' => sprintf(__('Parent %1$s', 'factory_types_410'), $singular_name),
					'parent_item_colon' => sprintf(__('Parent %1$s:', 'factory_types_410'), $singular_name),
					'edit_item' => sprintf(__('Edit %1$s', 'factory_types_410'), $singular_name),
					'update_item' => sprintf(__('Update %1$s', 'factory_types_410'), $singular_name),
					'add_new_item' => sprintf(__('Add New %1$s', 'factory_types_410'), $singular_name),
					'new_item_name' => sprintf(__('New %1$s Name', 'factory_types_410'), $singular_name),
					'separate_items_with_commas' => sprintf(__('Separate %1$s with commas', 'factory_types_410'), $plural_name),
					'add_or_remove_items' => sprintf(__('Add or remove %1$s', 'factory_types_410'), $plural_name),
					'choose_from_most_used' => sprintf(__('Choose from the most used %1$s', 'factory_types_410'), $plural_name),
					'not_found' => sprintf(__('No %1$s found.', 'factory_types_410'), $plural_name),
					'menu_name' => sprintf(__('%1$s', 'factory_types_410'), $plural_name),
				);

				$this->options['labels'] = $labels;
			}

			public function actionAddMetaboxs()
			{
				//remove_meta_box('submitdiv', $this->name, 'side');
			}

			public abstract function configure();

			public function useit()
			{
				return true;
			}
		}
	}