<?php
	/**
	 * A group of classes and methods to create and manage custom types.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package core
	 * @since 1.0.0
	 */

	//add_action('factory_422_plugin_activation', 'FactoryTaxonomy000::activationHook');
	//add_action('factory_422_plugin_deactivation', 'FactoryTaxonomy000::deactivationHook');

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryTaxonomies330') ) {

		/**
		 * A base class to manage types.
		 *
		 * @since 1.0.0
		 */
		class Wbcr_FactoryTaxonomies330 {

			/**
			 * Registered custom types.
			 *
			 * @since 1.0.0
			 * @var Wbcr_FactoryTaxonomies330_Taxonomy[]
			 */
			private static $terms = array();

			/**
			 * Registers a new custom type.
			 *
			 * If the second argument is given, capabilities for this type
			 * will be setup on the plugin configuration.
			 *
			 * @param string $class_name
			 * @param Wbcr_Factory422_Plugin $plugin
			 */
			public static function register($className, $plugin = null)
			{
				$type = new $className($plugin);

				$pluginName = !empty($plugin)
					? $plugin->getPluginName()
					: '-';
				if( !isset(self::$terms[$pluginName]) ) {
					self::$terms[$pluginName] = array();
				}

				self::$terms[$pluginName][] = $type;
			}

			/**
			 * A plugin activation hook.
			 *
			 * @since 1.0.0
			 * @param Factory422_Plugin
			 * @return void
			 */
			/*public static function activationHook($plugin)
			{
				$pluginName = $plugin->pluginName;

				// Sets capabilities for terms.
				if( isset(self::$terms[$pluginName]) ) {
					foreach(self::$terms[$pluginName] as $type) {
						if( empty($type->capabilities) )
							continue;
						foreach($type->capabilities as $roleName) {
							$role = get_role($roleName);
							if( !$role )
								continue;

							$role->add_cap('edit_' . $type->name);
							$role->add_cap('read_' . $type->name);
							$role->add_cap('delete_' . $type->name);
							$role->add_cap('edit_' . $type->name . 's');
							$role->add_cap('edit_others_' . $type->name . 's');
							$role->add_cap('publish_' . $type->name . 's');
							$role->add_cap('read_private_' . $type->name . 's');
						}
					}
				}
			}*/

			/**
			 * A plugin deactivation hook.
			 *
			 * @since 1.0.0
			 * @param Factory422_Plugin
			 * @return void
			 */
			/*public static function deactivationHook($plugin)
			{

				$pluginName = $plugin->pluginName;
				global $wp_roles;
				$all_roles = $wp_roles->roles;

				// Sets capabilities for terms.
				if( isset(self::$terms[$pluginName]) ) {
					foreach(self::$terms[$pluginName] as $type) {
						if( empty($type->capabilities) )
							continue;

						foreach($all_roles as $roleName => $roleInfo) {

							$role = get_role($roleName);
							if( !$role )
								continue;

							$role->remove_cap('edit_' . $type->name);
							$role->remove_cap('read_' . $type->name);
							$role->remove_cap('delete_' . $type->name);
							$role->remove_cap('edit_' . $type->name . 's');
							$role->remove_cap('edit_others_' . $type->name . 's');
							$role->remove_cap('publish_' . $type->name . 's');
							$role->remove_cap('read_private_' . $type->name . 's');
						}
					}
				}
			}*/
		}
	}