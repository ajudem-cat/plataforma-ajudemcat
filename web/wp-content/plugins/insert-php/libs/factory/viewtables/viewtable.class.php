<?php

	/**
	 * A group of classes and methods to create and manage post viewtables.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package factory-viewtables
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryViewtables410_Viewtable') ) {

		abstract class Wbcr_FactoryViewtables410_Viewtable {

			/**
			 * A type used to display the table.
			 * @var Wbcr_FactoryTypes410_Type
			 */
			public $type;

			/**
			 * Table's columns
			 * @var FactoryViewtables410_Columns
			 */
			public $columns;

			/**
			 * Scripts that must be included on edit page.
			 * @var Wbcr_Factory422_ScriptList
			 */
			public $scripts;

			/**
			 * Styles that must be included on edit page.
			 * @var Wbcr_Factory422_StyleList
			 */
			public $styles;

			/**
			 * Creates a new instance of a viewtabl.
			 *
			 * @since 1.0.0
			 * @param Wbcr_Factory422_Plugin $plugin
			 */
			public function __construct(Wbcr_Factory422_Plugin $plugin)
			{
				$this->plugin = $plugin;
			}

			public function connect($type)
			{

				$this->type = $type;
				$this->columns = new FactoryViewtables410_Columns();

				$this->scripts = $this->plugin->newScriptList();
				$this->styles = $this->plugin->newStyleList();

				$this->configure();

				add_filter('manage_edit-' . $type->name . '_columns', array($this, 'actionColumns'));
				add_action('manage_' . $type->name . '_posts_custom_column', array($this, 'actionColumnValues'), 2);

				// includes styles and scripts
				if( !$this->scripts->isEmpty() || !$this->styles->isEmpty() ) {
					add_action('admin_enqueue_scripts', array($this, 'actionAdminScripts'));
				}

				// remove quiik edit for non-public types
				if( $type->template !== 'public' ) {
					add_filter('post_row_actions', array($this, 'actionPostRowActions'), 10, 2);
				}

				// remove buld edit action
				if( $type->template !== 'public' ) {
					add_filter('bulk_actions-edit-' . $this->type->name, array($this, 'actionBulk'));
				}
			}

			public function configure()
			{
			}

			/**
			 * @param $columns
			 * @return array
			 */
			public function actionColumns($columns)
			{

				if( $this->columns->isClearn ) {
					$columns = array();
					$columns["cb"] = "<input type=\"checkbox\" />";
				}

				foreach($this->columns->getAll() as $column) {
					$columns[$column['id']] = $column['title'];
				}

				return $columns;
			}

			/**
			 * @param $column
			 * @return bool
			 */
			public function actionColumnValues($column)
			{
				global $post;

				$postfix = strtoupper(substr($column, 0, 1)) . substr($column, 1, strlen($column));
				$function_name = 'column' . $postfix;
				$full_mode = (isset($_GET['mode']) && $_GET['mode'] == 'excerpt');

				if( !method_exists($this, $function_name) ) {
					return false;
				}
				call_user_func(array($this, $function_name), $post, $full_mode);
			}

			/**
			 * Actions that includes registered fot this type scritps and styles.
			 * @global Wp_Post $post
			 * @param string $hook
			 */
			public function actionAdminScripts($hook)
			{
				global $post;

				if( !$post ) {
					return;
				}
				if( $hook !== 'edit.php' ) {
					return;
				}
				if( $post->post_type != $this->type->name ) {
					return;
				}
				if( $this->scripts->isEmpty() && $this->styles->isEmpty() ) {
					return;
				}

				$this->scripts->connect();
				$this->styles->connect();
			}

			public function actionPostRowActions($actions)
			{
				global $post;

				if( $post->post_type !== $this->type->name ) {
					return $actions;
				}
				unset($actions['inline hide-if-no-js']);

				return $actions;
			}

			public function actionBulk($actions)
			{
				global $post;

				if( !$post ) {
					return $actions;
				}
				if( $post->post_type !== $this->type->name ) {
					return $actions;
				}
				unset($actions['edit']);

				return $actions;
			}
		}
	}