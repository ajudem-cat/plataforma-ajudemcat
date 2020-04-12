<?php

	/**
	 * A group of classes and methods to create and manage metaboxes.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package factory-metaboxes
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryMetaboxes409') ) {

		add_action('add_meta_boxes', 'Wbcr_FactoryMetaboxes409::actionAddMetaboxes');
		add_action('admin_enqueue_scripts', 'Wbcr_FactoryMetaboxes409::actionAdminEnqueueScripts');
		add_action('save_post', 'Wbcr_FactoryMetaboxes409::actionSavePost');

		/**
		 * A base class to manage metaboxes.
		 *
		 * The main tasks of the manager is:
		 *  - to register metaboxes for custom posts
		 *  - to process data on post saving
		 *
		 * @since 1.0.0
		 */
		class Wbcr_FactoryMetaboxes409 {

			/**
			 * A variable to store metaboxes per type they defined for.
			 *
			 * @since 1.0.0
			 * @var Wbcr_FactoryMetaboxes409_Metabox[]
			 */
			public static $metaboxes = array();

			/**
			 * A variable storing post types for which there're metaboxes registered.
			 *
			 * @since 1.0.0
			 * @var array
			 */
			public static $post_types = array();

			/**
			 * @var array
			 */
			protected static $_existing_metaboxes = array();

			/**
			 * Registers a metabox by its class name.
			 *
			 * @since 1.0.0
			 * @param string|object $class_name_or_object
			 * @param Wbcr_Factory422_Plugin $plugin
			 * @return Wbcr_FactoryMetaboxes409_Metabox
			 */
			public static function register($class_name_or_object, Wbcr_Factory422_Plugin $plugin)
			{

				if( is_string($class_name_or_object) ) {

					$className = $class_name_or_object;
					if( !isset(self::$_existing_metaboxes[$className]) ) {
						self::$_existing_metaboxes[$className] = new $className($plugin);
					}
				} else {

					$className = get_class($class_name_or_object);
					if( !isset(self::$_existing_metaboxes[$className]) ) {
						self::$_existing_metaboxes[$className] = $class_name_or_object;
					}
				}

				$metabox = self::$_existing_metaboxes[$className];
				self::$metaboxes[$metabox->id] = $metabox;

				if( empty($metabox->post_types) ) {
					return $metabox;
				}
				foreach($metabox->post_types as $type) {
					self::$post_types[$type][$metabox->id] = $metabox;
				}

				return $metabox;
			}

			/**
			 * Registers a metabox for a given post type.
			 *
			 * @since 1.0.0
			 * @param type $class_name_or_object A metabox class name.
			 * @param string $post_type A post type for which a given metabox should be registered.
			 * @return Wbcr_FactoryMetaboxes409_Metabox
			 */
			public static function registerFor($class_name_or_object, $post_type, $plugin)
			{

				$metabox = self::register($class_name_or_object, $plugin);
				self::$metaboxes[$metabox->id]->addPostType($post_type);
				self::$post_types[$post_type][$metabox->id] = $metabox;

				return $metabox;
			}

			/**
			 * On calling the action "add_meta_boxes".
			 *
			 * Registering metaboxes via Wordpress API.
			 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public static function actionAddMetaboxes()
			{

				foreach(self::$post_types as $type => $metaboxes) {
					foreach($metaboxes as $metabox) {

						add_meta_box($metabox->id, $metabox->title, array(
							$metabox,
							'show'
						), $type, $metabox->context, $metabox->priority);
					}
				}
			}

			/**
			 * On calling the action "admin_enqueue_scripts".
			 *
			 * Adding scripts and styles for registered metaboxes for respective pages.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public static function actionAdminEnqueueScripts($hook)
			{
				if( !in_array($hook, array('post.php', 'post-new.php')) ) {
					return;
				}
				foreach(self::$metaboxes as $metabox)
					$metabox->connect();
			}

			/**
			 * On calling the action "save_post".
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public static function actionSavePost($post_id)
			{

				// verify the post type
				if( !isset($_POST['post_type']) ) {
					return $post_id;
				}

				foreach(self::$metaboxes as $metabox) {
					$metabox->actionSavePost($post_id);
				}
			}
		}
	}