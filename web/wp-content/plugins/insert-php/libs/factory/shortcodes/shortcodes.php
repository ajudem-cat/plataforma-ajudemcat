<?php
	/**
	 * A group of classes and methods to create and manage shortcodes.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package core
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryShortcodes329') ) {
		/**
		 * A helper class to register new shortcodes.
		 *
		 * One shortcode manager for all shortcodes from different plugins.
		 *
		 * @since 1.0.0
		 */
		class Wbcr_FactoryShortcodes329 {

			private static $manager = false;

			/**
			 * Registering a new shortcode.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public static function register($class_name, $plugin)
			{
				if( !self::$manager ) {
					self::$manager = new Wbcr_FactoryShortcodes329_ShortcodeManager();
				}
				self::$manager->register($class_name, $plugin);
			}
		}
	}

	if( !class_exists('Wbcr_FactoryShortcodes329_ShortcodeManager') ) {
		/**
		 * Factory Shortcode Manager
		 *
		 * The main tasks of the manager is:
		 * - creating aninstance of Factory Shortcode per every call of the shortcode.
		 * - tracking shortcodes in post content.
		 */
		class Wbcr_FactoryShortcodes329_ShortcodeManager {

			/**
			 * A set of registered shortcodes.
			 *
			 * @since 1.0.0
			 * @var FactoryShortcodes329_Shortcode[]
			 */
			private $shortcodes = array();

			/**
			 * Keeps links between "class name" => "plugin"
			 *
			 * @since 3.2.0
			 * @var Wbcr_FactoryShortcodes329_Shortcode[]
			 */
			private $class_to_plugin = array();

			/**
			 * A set of shortcodes that has assets connects (js and css).
			 *
			 * @since 1.0.0
			 * @var mixed[]
			 */
			public $connected = array();

			/**
			 * This method allows to create a new shortcode object for each call.
			 *
			 * @param string $name A shortcode name.
			 * @param array $arguments
			 * @return string
			 */
			public function __call($name, $arguments)
			{
				list($prefix, $type) = explode('_', $name, 2);
				
				if( $prefix !== 'shortcode' ) {
					return;
				}

				$blank = new $type($this->class_to_plugin[$type]);

				return $blank->render($arguments[0], $arguments[1], $arguments[2]);
			}

			/**
			 * Registers a new shortcode.
			 *
			 * @since 1.0.0
			 * @param string $class_name A short code class name.
			 * @return void
			 */
			public function register($class_name, $plugin)
			{
				$shortcode = new $class_name($plugin);
				$shortcode->manager = $this;

				$this->shortcodes[] = $shortcode;

				foreach($shortcode->shortcode_name as $shortcode_name) {
					$class_name = get_class($shortcode);

					$this->class_to_plugin[$class_name] = $plugin;

					add_shortcode($shortcode_name, array($this, 'shortcode_' . $class_name));
				}
			}
		}
	}