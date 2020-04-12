<?php
/**
 * A group of classes and methods to create and manage custom types.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       factory-types
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wbcr/factory/before_plugin_activation', 'Wbcr_FactoryTypes410::activationHook' );
add_action( 'wbcr/factory/before_plugin_deactivation', 'Wbcr_FactoryTypes410::deactivationHook' );

/**
 * A base class to manage types.
 *
 * @since 1.0.0
 */
class Wbcr_FactoryTypes410 {

	/**
	 * Registered custom types.
	 *
	 * @since 1.0.0
	 * @var Wbcr_FactoryTypes410_Type[]
	 */
	private static $types = [];

	/**
	 * Registers a new custom type.
	 *
	 * If the second argument is given, capabilities for this type
	 * will be setup on the plugin configuration.
	 *
	 * @param string                 $class_name
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	public static function register( $class_name, Wbcr_Factory422_Plugin $plugin = null ) {
		$type = new $class_name( $plugin );

		$plugin_name = ! empty( $plugin ) ? $plugin->getPluginName() : '-';
		if ( ! isset( self::$types[ $plugin_name ] ) ) {
			self::$types[ $plugin_name ] = [];
		}

		self::$types[ $plugin_name ][] = $type;
	}

	/**
	 * A plugin activation hook.
	 *
	 * @since 1.0.0
	 *
	 * @param Wbcr_Factory422_Plugin $plugin
	 *
	 * @return void
	 */
	public static function activationHook( $plugin ) {
		$plugin_name = $plugin->getPluginName();

		// Sets capabilities for types.
		if ( isset( self::$types[ $plugin_name ] ) ) {
			foreach ( self::$types[ $plugin_name ] as $type ) {
				if ( empty( $type->capabilities ) ) {
					continue;
				}
				foreach ( $type->capabilities as $roleName ) {
					$role = get_role( $roleName );
					if ( ! $role ) {
						continue;
					}

					$role->add_cap( 'edit_' . $type->name );
					$role->add_cap( 'read_' . $type->name );
					$role->add_cap( 'delete_' . $type->name );
					$role->add_cap( 'edit_' . $type->name . 's' );
					$role->add_cap( 'edit_others_' . $type->name . 's' );
					$role->add_cap( 'publish_' . $type->name . 's' );
					$role->add_cap( 'read_private_' . $type->name . 's' );
				}
			}
		}
	}

	/**
	 * A plugin deactivation hook.
	 *
	 * @since 1.0.0
	 *
	 * @param Wbcr_Factory422_Plugin $plugin
	 *
	 * @return void
	 */
	public static function deactivationHook( Wbcr_Factory422_Plugin $plugin ) {

		$plugin_name = $plugin->getPluginName();
		global $wp_roles;
		$all_roles = $wp_roles->roles;

		// Sets capabilities for types.
		if ( isset( self::$types[ $plugin_name ] ) ) {
			foreach ( self::$types[ $plugin_name ] as $type ) {
				if ( empty( $type->capabilities ) ) {
					continue;
				}

				foreach ( $all_roles as $roleName => $roleInfo ) {

					$role = get_role( $roleName );
					if ( ! $role ) {
						continue;
					}

					$role->remove_cap( 'edit_' . $type->name );
					$role->remove_cap( 'read_' . $type->name );
					$role->remove_cap( 'delete_' . $type->name );
					$role->remove_cap( 'edit_' . $type->name . 's' );
					$role->remove_cap( 'edit_others_' . $type->name . 's' );
					$role->remove_cap( 'publish_' . $type->name . 's' );
					$role->remove_cap( 'read_private_' . $type->name . 's' );
				}
			}
		}
	}
}
