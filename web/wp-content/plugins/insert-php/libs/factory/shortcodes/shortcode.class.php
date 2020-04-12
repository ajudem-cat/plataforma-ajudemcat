<?php
/**
 * The file contains a base class for all shortcodes.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       core
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wbcr_FactoryShortcodes329_Shortcode' ) ) {

	/**
	 * The base class for all shortcodes.
	 *
	 * @since 1.0.0
	 */
	abstract class Wbcr_FactoryShortcodes329_Shortcode {

		private static $meta_key_shorcode_assets_for_posts = 'factory_shortcodes_assets';

		/**
		 * Shortcode name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $shortcode_name = null;

		/**
		 * If true, the assets methods will be called in header.
		 *
		 * @since 3.0.0
		 * @var boolean
		 */
		public $assets_in_header = false;

		/**
		 * A manager that created and track this shortcode.
		 *
		 * @since 1.0.0
		 * @var Wbcr_FactoryShortcodes329_ShortcodeManager
		 */
		private $manager;

		/**
		 * Scripts to include on the same page.
		 *
		 * @since 1.0.0
		 * @var Wbcr_Factory422_ScriptList
		 */
		public $scripts;

		/**
		 * Styles to include on the same page.
		 *
		 * @since 1.0.0
		 * @var Wbcr_Factory422_StyleList
		 */
		public $styles;

		/**
		 * If set true, this shortcode will be tracked on altering post content.
		 *
		 * When this shortcode will be found in a post content, the method onTrack will be fired.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		public $track = false;

		/**
		 * If true, it means that shortcodes assets have been conected already.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $connected = false;

		/**
		 * /**
		 * Creates a new instance of a shortcode objects.
		 *
		 * @since 1.0.0
		 *
		 * @param Wbcr_Factory422_Plugin $plugin
		 */
		public function __construct( $plugin ) {
			$this->plugin = $plugin;

			$this->scripts = $this->plugin->newScriptList();
			$this->styles  = $this->plugin->newStyleList();

			if ( ! is_array( $this->shortcode_name ) ) {
				$this->shortcode_name = [ $this->shortcode_name ];
			}

			if ( $this->assets_in_header ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'actionEnqueueScripts' ] );
			}

			if ( is_admin() ) {
				add_action( 'save_post', [ $this, 'actionSavePost' ] );
			}
		}

		/**
		 * Adds shortcode scripts and styles of it's nedded.
		 *
		 * Calls on the hook "wp_enqueue_scripts".
		 *
		 * @since 1.0.0
		 * @return void
		 * @global Wp_post $post
		 */
		public function actionEnqueueScripts() {
			global $post;
			if ( empty( $post ) ) {
				return;
			}

			foreach ( $this->shortcode_name as $shortcode_name ) {
				if ( $this->connected ) {
					return;
				}

				$metaValue = get_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts, true );

				if ( ! isset( $metaValue[ $shortcode_name ] ) ) {
					continue;
				}

				$result = $this->assets( $metaValue[ $shortcode_name ], false, true );

				if ( ! $result ) {
					continue;
				}

				$this->scripts->connect();
				$this->styles->connect();

				$this->connected = true;
			}
		}

		/**
		 * Adds shortcode scripts and styles of it's nedded.
		 *
		 * Calls on the hook "save_post".
		 *
		 * @return void|int
		 */
		public function actionSavePost( $post_id ) {
			if ( wp_is_post_revision( $post_id ) ) {
				return $post_id;
			}

			$post = get_post( $post_id );

			if ( empty( $post ) ) {
				return;
			}

			$this->onPostSave( $post );

			if ( $this->track ) {
				$this->trackShortcode( $post );
			}
		}

		/**
		 * Checks if a post contains a given shortcode and extract its atrributes and content on post saving.
		 *
		 * @since 1.0.0
		 *
		 * @param object $post   A current post.
		 *
		 * @return void
		 */
		private function trackShortcode( $post ) {
			if ( empty( $this->shortcode_name ) ) {
				return;
			}

			$matches = [];

			$shortcodes = $this->shortcode_name;

			if ( ! is_array( $shortcodes ) ) {
				$shortcodes = [ $this->shortcode_name ];
			}

			$tagregexp = join( '|', $shortcodes );

			$start   = '(\[(' . $tagregexp . ')([^\[\]]*)\])';
			$end     = '\[\/\2\]';
			$pattern = '/' . $start . '(.*?)' . $end . '/is';

			$count = preg_match_all( $pattern, $post->post_content, $matches, PREG_SET_ORDER );

			$found_shortcodes = get_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts, true );

			if ( ! is_array( $found_shortcodes ) ) {
				$found_shortcodes = [];
			}

			foreach ( $shortcodes as $shortcode ) {
				unset( $found_shortcodes[ $shortcode ] );
			}

			if ( ! $count ) {
				update_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts, $found_shortcodes );

				return;
			}

			// clears info about previously existing shortcodes

			foreach ( $matches as $order => $match ) {

				$shortcode    = $match[2];
				$attrContent  = str_replace( '\\', '', $match[3] );
				$innerContent = str_replace( '\\', '', $match[4] );

				$attrs = shortcode_parse_atts( $attrContent );

				if ( ! isset( $found_shortcodes[ $shortcode ] ) ) {
					$found_shortcodes[ $shortcode ] = [];
				}
				$found_shortcodes[ $shortcode ][] = $attrs;

				// allows to perfome custom actions

				do_action( 'factory_shortcode_found', $shortcode, $attrs, $innerContent );

				$this->onTrack( $shortcode, $attrs, $innerContent, $post->ID );
			}

			// saves info about existing shortcodes for a given post

			delete_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts );
			update_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts, $found_shortcodes );
		}

		/**
		 * Returns a shortcode html markup.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function render( $attr, $content, $tag ) {

			if ( ! $this->connected ) {
				$this->assets( [ $attr ], true, false );
				$this->scripts->connect( true );
				$this->styles->connect( true );
			}

			// fix for compability

			global $post;
			if ( ! empty( $post ) && ! empty( $this->shortcode_name ) ) {
				$found_shortcodes = get_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts, true );

				if ( $found_shortcodes && ! is_array( $found_shortcodes ) ) {

					$shortcodes = $this->shortcode_name;
					if ( ! is_array( $shortcodes ) ) {
						$shortcodes = [ $this->shortcode_name ];
					}

					$found_shortcodes                     = [];
					$found_shortcodes[ $shortcodes[0] ]   = [];
					$found_shortcodes[ $shortcodes[0] ][] = $attr;

					update_post_meta( $post->ID, self::$meta_key_shorcode_assets_for_posts, $found_shortcodes );
				}
			}

			ob_start();
			$this->html( $attr, $content, $tag );
			$html = ob_get_clean();

			//return nl2br($html);
			return $html;
		}

		/**
		 * Configures assets (js and css) for the shortcodes.
		 *
		 * The method should be overwritten in a deferred class.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function assets() {
		}


		public function onPostSave( $post ) {
		}

		public function onTrack( $shortcode, $attrs, $innerContent, $postId ) {
		}

		/**
		 * Renders shortcode html.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public abstract function html( $attr, $content, $tag );
	}
}