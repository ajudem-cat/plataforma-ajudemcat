<?php
/**
 * This file manages assets of the Factory Bootstap.
 *
 * @author        Alex Kovalev <alex@byonepress.com>
 * @author        Paul Kashtanoff <paul@byonepress.com>
 * @since         1.0.0
 * @package       factory-bootstrap
 * @copyright (c) 2018, OnePress Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Bootstrap Manager class.
 *
 * @since 3.2.0
 */
class Wbcr_FactoryBootstrap423_Manager {

	/**
	 * A plugin for which the manager was created.
	 *
	 * @since 3.2.0
	 * @var Wbcr_Factory422_Plugin
	 */
	public $plugin;

	/**
	 * Contains scripts to include.
	 *
	 * @since 3.2.0
	 * @var string[]
	 */
	public $scripts = [];

	/**
	 * Contains styles to include.
	 *
	 * @since 3.2.0
	 * @var string[]
	 */
	public $styles = [];

	/**
	 * Createas a new instance of the license api for a given plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct( Wbcr_Factory422_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_enqueue_scripts', [ $this, 'loadAssets' ] );
		add_filter( 'admin_body_class', [ $this, 'adminBodyClass' ] );
	}

	/**
	 * Includes the Bootstrap scripts.
	 *
	 * @since 3.2.0
	 *
	 * @param array|string $scripts
	 */
	public function enqueueScript( $scripts ) {
		if ( is_array( $scripts ) ) {
			foreach ( $scripts as $script ) {
				if ( ! in_array( $script, $this->scripts ) ) {
					$this->scripts[] = $script;
				}
			}
		} else {
			if ( ! in_array( $scripts, $this->scripts ) ) {
				$this->scripts[] = $scripts;
			}
		}
	}

	/**
	 *  * Includes the Bootstrap styles.
	 *
	 * @since 3.2.0
	 *
	 * @param array|string $styles
	 */
	public function enqueueStyle( $styles ) {

		if ( is_array( $styles ) ) {
			foreach ( $styles as $style ) {
				if ( ! in_array( $style, $this->styles ) ) {
					$this->styles[] = $style;
				}
			}
		} else {
			if ( ! in_array( $styles, $this->styles ) ) {
				$this->styles[] = $styles;
			}
		}
	}

	/**
	 * Loads Bootstrap assets.
	 *
	 * @since 3.2.0
	 * @return void
	 * @see   admin_enqueue_scripts
	 *
	 */
	public function loadAssets( $hook ) {

		do_action( 'wbcr_factory_422_bootstrap_enqueue_scripts', $hook );
		do_action( 'wbcr_factory_422_bootstrap_enqueue_scripts_' . $this->plugin->getPluginName(), $hook );

		$dependencies = [];
		if ( ! empty( $this->scripts ) ) {
			$dependencies[] = 'jquery';
			$dependencies[] = 'jquery-ui-core';
			$dependencies[] = 'jquery-ui-widget';
		}

		foreach ( $this->scripts as $script ) {
			switch ( $script ) {
				case 'plugin.iris':
					$dependencies[] = 'jquery-ui-widget';
					$dependencies[] = 'jquery-ui-slider';
					$dependencies[] = 'jquery-ui-draggable';
					break;
			}
		}

		if ( ! empty( $this->scripts ) ) {
			$this->enqueueScripts( $this->scripts, 'js', $dependencies );
		}
		if ( ! empty( $this->styles ) ) {
			$this->enqueueScripts( $this->styles, 'css', $dependencies );
		}

		$user_id    = get_current_user_id();
		$color_name = get_user_meta( $user_id, 'admin_color', true );

		if ( $color_name !== 'fresh' ) {
			if ( file_exists( FACTORY_BOOTSTRAP_423_DIR . '/assets/flat/css/bootstrap.' . $color_name . '.css' ) ) {
				wp_enqueue_style( 'wbcr-factory-bootstrap-423-colors', FACTORY_BOOTSTRAP_423_URL . '/assets/flat/css/bootstrap.' . $color_name . '.css' );
			}
		}

		if ( $color_name == 'light' ) {
			$primary_dark  = '#037c9a';
			$primary_light = '#04a4cc';
		} else if ( $color_name == 'blue' ) {
			$primary_dark  = '#d39323';
			$primary_light = '#e1a948';
		} else if ( $color_name == 'coffee' ) {
			$primary_dark  = '#b78a66';
			$primary_light = '#c7a589';
		} else if ( $color_name == 'ectoplasm' ) {
			$primary_dark  = '#839237';
			$primary_light = '#a3b745';
		} else if ( $color_name == 'ocean' ) {
			$primary_dark  = '#80a583';
			$primary_light = '#9ebaa0';
		} else if ( $color_name == 'midnight' ) {
			$primary_dark  = '#d02a21';
			$primary_light = '#e14d43';
		} else if ( $color_name == 'sunrise' ) {
			$primary_dark  = '#c36822';
			$primary_light = '#dd823b';
		} else {
			$primary_dark  = '#0074a2';
			$primary_light = '#2ea2cc';
		}

		?>
        <script>
			if( !window.factory ) {
				window.factory = {};
			}
			if( !window.factory.factoryBootstrap423 ) {
				window.factory.factoryBootstrap423 = {};
			}
			window.factory.factoryBootstrap423.colors = {
				primaryDark: '<?php echo $primary_dark ?>',
				primaryLight: '<?php echo $primary_light ?>'
			};
        </script>
		<?php
	}

	/**
	 * @param array  $sripts
	 * @param string $type
	 * @param array  $dependencies
	 */
	protected function enqueueScripts( array $sripts, $type = 'js', array $dependencies ) {

		$is_first = true;

		/**
		 * Sets permission for file caching and combining into one file.
		 *
		 * @since 4.1.0
		 */
		$cache_enable = apply_filters( 'wbcr/factory/bootstrap/cache_enable', true );

		$cache_id       = md5( implode( ',', $this->scripts ) . $type . $this->plugin->getPluginVersion() );
		$cache_dir_path = FACTORY_BOOTSTRAP_423_DIR . '/assets/cache/';
		$cache_dir_url  = FACTORY_BOOTSTRAP_423_URL . '/assets/cache/';

		$cache_filepath = $cache_dir_path . $cache_id . ".min." . $type;
		$cache_fileurl  = $cache_dir_url . $cache_id . ".min." . $type;

		if ( $cache_enable && file_exists( $cache_filepath ) ) {
			if ( $type == 'js' ) {
				wp_enqueue_script( 'wbcr-factory-bootstrap-' . $cache_id, $cache_fileurl, $dependencies, $this->plugin->getPluginVersion() );
			} else {
				wp_enqueue_style( 'wbcr-factory-bootstrap-' . $cache_id, $cache_fileurl, [], $this->plugin->getPluginVersion() );
			}
		} else {
			$cache_dir_exists = false;
			if ( ! file_exists( $cache_dir_path ) ) {
				if ( @mkdir( $cache_dir_path, 0755 ) && wp_is_writable( $cache_dir_path ) ) {
					$cache_dir_exists = true;
				}
			} else {
				if ( wp_is_writable( $cache_dir_path ) ) {
					$cache_dir_exists = true;
				}
			}

			$concat_files = [];
			foreach ( $sripts as $script_to_load ) {
				$script_to_load = sanitize_text_field( $script_to_load );
				if ( $cache_enable && $cache_dir_exists ) {
					$fname = FACTORY_BOOTSTRAP_423_DIR . "/assets/$type-min/$script_to_load.min." . $type;
					if ( file_exists( $fname ) ) {
						$f              = @fopen( $fname, 'r' );
						$concat_files[] = @fread( $f, filesize( $fname ) );
						@fclose( $f );
					}
				} else {
					if ( $type == 'js' ) {
						wp_enqueue_script( md5( $script_to_load ), FACTORY_BOOTSTRAP_423_URL . "/assets/$type-min/$script_to_load.min." . $type, $is_first ? $dependencies : false, $this->plugin->getPluginVersion() );
					} else {
						wp_enqueue_style( md5( $script_to_load ), FACTORY_BOOTSTRAP_423_URL . "/assets/$type-min/$script_to_load.min." . $type, [], $this->plugin->getPluginVersion() );
					}
					$is_first = false;
				}
			}

			if ( $cache_enable && $cache_dir_exists && ! empty( $concat_files ) ) {

				$cf            = @fopen( $cache_filepath, 'w' );
				$write_content = implode( PHP_EOL, $concat_files );
				@fwrite( $cf, $write_content );
				@fclose( $cf );
				chmod( $cache_filepath, 0755 );

				if ( file_exists( $cache_filepath ) ) {
					if ( $type == 'js' ) {
						wp_enqueue_script( 'wbcr-factory-bootstrap-' . $cache_id, $cache_fileurl, $dependencies, $this->plugin->getPluginVersion() );
					} else {
						wp_enqueue_style( 'wbcr-factory-bootstrap-' . $cache_id, $cache_fileurl, [], $this->plugin->getPluginVersion() );
					}
				}
			}
		}
	}

	/**
	 * Adds the body classes: 'factory-flat or 'factory-volumetric'.
	 *
	 * @since 3.2.0
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function adminBodyClass( $classes ) {
		$classes .= FACTORY_FLAT_ADMIN ? ' factory-flat ' : ' factory-volumetric ';

		return $classes;
	}
}
