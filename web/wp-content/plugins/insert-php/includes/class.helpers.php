<?php
/**
 * Helpers tools
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 09.11.2017, Webcraftic
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_Helper {

	private static $meta_options = [];

	/**
	 * @return bool
	 */
	public static function is_safe_mode() {
		global $wbcr_inp_safe_mode;

		if ( ! WINP_Plugin::app()->currentUserCan() ) {
			return false;
		}

		if ( $wbcr_inp_safe_mode || isset( $_COOKIE['wbcr-php-snippets-safe-mode'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Enables safe mode, in which the php code will not be executed.
	 */
	public static function enable_safe_mode() {
		global $wbcr_inp_safe_mode;

		if ( ! WINP_Plugin::app()->currentUserCan() ) {
			return false;
		}

		if ( ( ! $wbcr_inp_safe_mode || ! isset( $_COOKIE['wbcr-php-snippets-safe-mode'] ) ) ) {
			$wbcr_inp_safe_mode = true;
			setcookie( "wbcr-php-snippets-safe-mode", 1, time() + 3600, '/' );

			return true;
		}

		return false;
	}

	/**
	 * Disable safe mode, in which the php code will not be executed.
	 */
	public static function disable_safe_mode() {
		global $wbcr_inp_safe_mode;

		if ( ! WINP_Plugin::app()->currentUserCan() ) {
			return false;
		}

		if ( $wbcr_inp_safe_mode || isset( $_COOKIE['wbcr-php-snippets-safe-mode'] ) ) {
			$wbcr_inp_safe_mode = false;

			unset( $_COOKIE['wbcr-php-snippets-safe-mode'] );

			setcookie( 'wbcr-php-snippets-safe-mode', null, - 1, '/' );
			setcookie( 'wbcr-php-snippets-safe-mode', null, - 1, '/wp-admin' );

			return true;
		}

		return false;
	}

	/**
	 * Should show a page about the plugin or not.
	 *
	 * @return bool
	 */
	public static function is_need_show_about_page() {
		$need_show_about = (int) get_option( WINP_Plugin::app()->getOptionName( 'what_new_210' ) );

		$is_ajax = WINP_Helper::doing_ajax();
		$is_cron = WINP_Helper::doing_cron();
		$is_rest = WINP_Helper::doing_rest_api();

		if ( $need_show_about && ! $is_ajax && ! $is_cron && ! $is_rest ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets and verified available attributes for snippets shortcodes.
	 *
	 * @param bool $tinymce
	 *
	 * @return mixed|void
	 */
	public static function get_shortcode_data( $tinymce = false ) {

		$snippets = get_posts( [
			'post_type'   => WINP_SNIPPETS_POST_TYPE,
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'   => WINP_Plugin::app()->getPrefix() . 'snippet_scope',
					'value' => 'shortcode'
				],
				[
					'key'   => WINP_Plugin::app()->getPrefix() . 'snippet_activate',
					'value' => 1
				]
			],
			'post_status' => 'publish',
			'numberposts' => - 1
		] );

		$result = [];

		if ( ! empty( $snippets ) ) {
			foreach ( (array) $snippets as $snippet ) {
				$tag_names    = [ 'id' ];
				$snippet_type = WINP_Helper::get_snippet_type( $snippet->ID );

				$available_tags = WINP_Helper::getMetaOption( $snippet->ID, 'snippet_tags' );
				$available_tags = trim( rtrim( $available_tags ) );

				if ( ! empty( $available_tags ) ) {
					$available_tags = array_map( 'trim', explode( ',', $available_tags ) );
					$available_tags = array_unique( $available_tags );
				} else {
					if ( $snippet_type !== 'text' ) {
						$available_tags = [ 'id', 'title' ];
					} else {
						$available_tags = [ 'id' ];
					}
				}

				$tags = [
					'id'    => $snippet->ID,
					'type'  => $snippet_type,
					'name'  => $snippet_type == WINP_SNIPPET_TYPE_UNIVERSAL ? 'wbcr_snippet' : 'wbcr_' . $snippet_type . '_snippet',
					'title' => empty( $snippet->post_title ) ? '(no titled, ID=' . $snippet->ID . ')' : $snippet->post_title
				];

				if ( ! empty( $available_tags ) ) {
					foreach ( (array) $available_tags as $tag ) {
						if ( '' != $tag ) {
							if ( 'title' == $tag ) {
								$tags['title'] = empty( $snippet->post_title ) ? '(no titled, ID=' . $snippet->ID . ')' : $snippet->post_title;
								$tag_names[]   = 'title';
							} else if ( 'id' != $tag ) {
								$tag          = preg_replace( '/[^a-zA-Z0-9_\x7f-\xff]/', '', $tag );
								$tags[ $tag ] = "";
								$tag_names[]  = $tag;
							}
						}
					}
				}

				$tags['snippet_tags'] = $tag_names;

				$result[] = $tags;
			}
		}

		return apply_filters( 'wbcr/inp/helper/get_shortcode_data', $result, $tinymce );
	}

	/**
	 * Get snippet type
	 *
	 * @param null $post_id
	 *
	 * @return array|mixed|string
	 */
	public static function get_snippet_type( $post_id = null ) {
		global $post;

		$_post = $post;

		$snippet_type = WINP_Plugin::app()->request->get( 'winp_item', WINP_SNIPPET_TYPE_PHP, 'sanitize_key' );
		$get_post     = WINP_Plugin::app()->request->get( 'post', '' );

		if ( empty( $post_id ) && ! empty( $get_post ) && ! is_array( $get_post ) ) {
			$post_id = esc_attr( $get_post );
		}

		if ( ! empty( $post_id ) ) {
			$_post = get_post( $post_id );
		}

		if ( ! empty( $_post ) && WINP_SNIPPETS_POST_TYPE === $_post->post_type ) {
			$_snippet_type = get_post_meta( $_post->ID, WINP_Plugin::app()->getPrefix() . 'snippet_type', true );
			$snippet_type  = $_snippet_type ? $_snippet_type : $snippet_type;
		}

		return $snippet_type;
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @author matzeeable https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist
	 * @since  2.1.0
	 * @return boolean
	 */
	public static function doing_rest_api() {
		$prefix     = rest_get_url_prefix();
		$rest_route = WINP_Plugin::app()->request->get( 'rest_route', null );
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
		     || ! is_null( $rest_route ) // (#2)
		        && strpos( trim( $rest_route, '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}

		// (#3)
		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );

		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}

	/**
	 * @since 2.1.0
	 * @return bool
	 */
	public static function doing_ajax() {
		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * @since 2.1.0
	 * @return bool
	 */
	public static function doing_cron() {
		if ( function_exists( 'wp_doing_cron' ) ) {
			return wp_doing_cron();
		}

		return defined( 'DOING_CRON' ) && DOING_CRON;
	}

	/**
	 * In the new version of the plugin, we moved the code of the snippets
	 * from the meta data to the post table, to the post_content cell.
	 *
	 * If the migration was an error, we need to reliably get the
	 * snippet code if the post_content cell is empty.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.1
	 *
	 * @param WP_Post $post
	 *
	 * @return string snippet code
	 */
	public static function get_snippet_code( $post ) {
		if ( empty( $post->post_content ) ) {
			return WINP_Helper::getMetaOption( $post->ID, 'snippet_code' );
		}

		return $post->post_content;
	}

	/**
	 * Get meta option
	 *
	 * @param int    $post_id
	 * @param string $option_name
	 * @param mixed  $default
	 *
	 * @return mixed|array
	 */
	public static function getMetaOption( $post_id, $option_name, $default = null ) {
		$post_id = (int) $post_id;

		if ( ! isset( self::$meta_options[ $post_id ] ) || empty( self::$meta_options[ $post_id ] ) ) {
			$meta_vals = get_post_meta( $post_id, '', true );

			if ( ! empty( $meta_vals ) && is_array( $meta_vals ) ) {
				foreach ( $meta_vals as $name => $val ) {
					self::$meta_options[ $post_id ][ $name ] = $val[0];
				}
			}
		}

		return isset( self::$meta_options[ $post_id ][ WINP_Plugin::app()->getPrefix() . $option_name ] ) ? self::$meta_options[ $post_id ][ WINP_Plugin::app()->getPrefix() . $option_name ] : $default;
	}

	/**
	 * Udpdate meta option
	 *
	 * @param int    $post_id
	 * @param string $option_name
	 * @param mixed  $option_value
	 *
	 * @return bool|int
	 */
	public static function updateMetaOption( $post_id, $option_name, $option_value ) {
		$post_id = (int) $post_id;

		return update_post_meta( $post_id, WINP_Plugin::app()->getPrefix() . $option_name, $option_value );
	}

	/**
	 * Remove meta option
	 *
	 * @param int    $post_id
	 * @param string $option_name
	 *
	 * @return bool|int
	 */
	public static function removeMetaOption( $post_id, $option_name ) {
		$post_id = (int) $post_id;

		return delete_post_meta( $post_id, WINP_Plugin::app()->getPrefix() . $option_name );
	}

	/**
	 * Check capabilities for snippets post type.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.0
	 */
	public static function has_post_capabilities() {
		$role = get_role( 'administrator' );

		if ( ! $role ) {
			return false;
		}

		return $role->has_cap( 'edit_' . WINP_SNIPPETS_POST_TYPE );
	}

	/**
	 * Set capabilities for snippets post type.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.0
	 */
	public static function set_post_capabilities() {
		$role = get_role( 'administrator' );

		if ( ! $role ) {
			return false;
		}

		$role->add_cap( 'edit_' . WINP_SNIPPETS_POST_TYPE );
		$role->add_cap( 'read_' . WINP_SNIPPETS_POST_TYPE );
		$role->add_cap( 'delete_' . WINP_SNIPPETS_POST_TYPE );
		$role->add_cap( 'edit_' . WINP_SNIPPETS_POST_TYPE . 's' );
		$role->add_cap( 'edit_others_' . WINP_SNIPPETS_POST_TYPE . 's' );
		$role->add_cap( 'publish_' . WINP_SNIPPETS_POST_TYPE . 's' );
		$role->add_cap( 'read_private_' . WINP_SNIPPETS_POST_TYPE . 's' );

		return true;
	}

	/**
	 * Create a demo snippets with examples of use
	 */
	public static function create_demo_snippets() {

		update_option( WINP_Plugin::app()->getOptionName( 'activate_by_default' ), 1 );
		update_option( WINP_Plugin::app()->getOptionName( 'complete_uninstall' ), 0 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_theme' ), 'default' );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_indent_with_tabs' ), 1 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_tab_size' ), 4 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_indent_unit' ), 4 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_wrap_lines' ), 1 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_line_numbers' ), 1 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_auto_close_brackets' ), 1 );
		update_option( WINP_Plugin::app()->getOptionName( 'code_editor_highlight_selection_matches' ), 0 );

		$posts = [
			[
				'post_title'   => __( 'Simple php snippet: Disable emojis', 'insert-php' ),
				'post_name'    => 'simple-php-snippet',
				'post_content' => self::get_simple_php_snippet(),
				'meta'         => [
					'type'        => WINP_SNIPPET_TYPE_PHP,
					'description' => __( 'Emojis are little icons used to express ideas or emotions. While these icons are fun and all, are they really necessary for your WordPress site? This snippet to disable emojis on your site to make it faster.', 'insert-php' ),
					'tags'        => [ 'php', 'disable features' ]
				]
			],
			[
				'post_title'   => __( 'Simple text snippet: What is Lorem Ipsum?', 'insert-php' ),
				'post_name'    => 'simple-text-snippet',
				'post_content' => self::get_simple_text_snippet(),
				'meta'         => [
					'type'        => WINP_SNIPPET_TYPE_TEXT,
					'description' => __( 'This ordinary maintenance text. With this snippet, you can fill your pages with meaningless English text.', 'insert-php' ),
					'filters'     => 'a:1:{i:0;O:8:"stdClass":2:{s:10:"conditions";a:2:{i:0;O:8:"stdClass":2:{s:4:"type";s:5:"scope";s:10:"conditions";a:1:{i:0;O:8:"stdClass":4:{s:5:"param";s:18:"location-some-page";s:8:"operator";s:6:"equals";s:4:"type";s:6:"select";s:5:"value";s:9:"base_sing";}}}i:1;O:8:"stdClass":2:{s:4:"type";s:5:"scope";s:10:"conditions";a:2:{i:0;O:8:"stdClass":4:{s:5:"param";s:18:"location-post-type";s:8:"operator";s:6:"equals";s:4:"type";s:6:"select";s:5:"value";s:4:"post";}i:1;O:8:"stdClass":4:{s:5:"param";s:18:"location-post-type";s:8:"operator";s:6:"equals";s:4:"type";s:6:"select";s:5:"value";s:4:"page";}}}}s:4:"type";s:6:"showif";}}',
					'tags'        => [ 'text', 'lorem ipsum' ]
				]
			],
			[
				'post_title'   => __( 'Simple universal snippet: Google analytics tracking', 'insert-php' ),
				'post_name'    => 'simple-universal-snippet',
				'post_content' => self::get_simple_universal_snippet(),
				'meta'         => [
					'type'        => WINP_SNIPPET_TYPE_UNIVERSAL,
					'description' => __( 'Google analytics tracking code will be added to all pages before the &lt;/head&gt; tag. Please remember to set the Tracking ID before activating the snippet.' ),
					'filters'     => 'a:1:{i:0;O:8:"stdClass":2:{s:10:"conditions";a:1:{i:0;O:8:"stdClass":2:{s:4:"type";s:5:"scope";s:10:"conditions";a:1:{i:0;O:8:"stdClass":4:{s:5:"param";s:18:"location-some-page";s:8:"operator";s:6:"equals";s:4:"type";s:6:"select";s:5:"value";s:8:"base_web";}}}}s:4:"type";s:6:"showif";}}',
					'tags'        => [ 'universal', 'tracking' ]
				]
			]
		];

		foreach ( $posts as $post ) {
			// '@' here is to hide unexpected output while plugin activation
			$post_id = @wp_insert_post( [
				'post_content' => $post['post_content'],
				'post_title'   => $post['post_title'],
				'post_status'  => 'publish',
				'post_type'    => WINP_SNIPPETS_POST_TYPE
			] );

			if ( ! is_wp_error( $post_id ) ) {
				if ( isset( $post['meta']['type'] ) ) {
					WINP_Helper::updateMetaOption( $post_id, 'snippet_type', $post['meta']['type'] );

					if ( $post['meta']['type'] == WINP_SNIPPET_TYPE_PHP ) {
						WINP_Helper::updateMetaOption( $post_id, 'snippet_scope', 'evrywhere' );
					}
					if ( $post['meta']['type'] == WINP_SNIPPET_TYPE_TEXT ) {
						WINP_Helper::updateMetaOption( $post_id, 'snippet_scope', 'shortcode' );
					}
					if ( $post['meta']['type'] == WINP_SNIPPET_TYPE_UNIVERSAL ) {
						WINP_Helper::updateMetaOption( $post_id, 'snippet_scope', 'auto' );
						WINP_Helper::updateMetaOption( $post_id, 'snippet_location', 'header' );
					}
				}

				if ( isset( $post['meta']['description'] ) ) {
					WINP_Helper::updateMetaOption( $post_id, 'snippet_description', $post['meta']['description'] );
				}

				if ( isset( $post['meta']['filters'] ) && is_serialized( $post['meta']['filters'] ) ) {
					$unserialized_filters = unserialize( $post['meta']['filters'] );
					WINP_Helper::updateMetaOption( $post_id, 'snippet_filters', $unserialized_filters );
					WINP_Helper::updateMetaOption( $post_id, 'changed_filters', 1 );
				}

				if ( isset( $post['meta']['tags'] ) && ! empty( $post['meta']['tags'] ) ) {
					if ( ! taxonomy_exists( WINP_SNIPPETS_TAXONOMY ) ) {
						register_taxonomy( WINP_SNIPPETS_TAXONOMY, WINP_SNIPPETS_POST_TYPE, [] );
					}

					wp_set_post_terms( $post_id, $post['meta']['tags'], WINP_SNIPPETS_TAXONOMY, true );
				}
			}
		}

		update_option( WINP_Plugin::app()->getOptionName( 'demo_snippets_created' ), 1 );
	}

	/**
	 * Returns an example php snippet content
	 *
	 * @return string
	 */
	protected static function get_simple_php_snippet() {
		$output = "/**" . PHP_EOL;
		$output .= "* Disable WP 4.2 emoji" . PHP_EOL;
		$output .= "*/" . PHP_EOL;
		$output .= "function ace_remove_emoji() {" . PHP_EOL;
		$output .= "\tadd_filter( 'emoji_svg_url', '__return_false' );" . PHP_EOL;
		$output .= "\tremove_action( 'admin_print_styles', 'print_emoji_styles' );" . PHP_EOL;
		$output .= "\tremove_action( 'wp_head', 'print_emoji_detection_script', 7 );" . PHP_EOL;
		$output .= "\tremove_action( 'admin_print_scripts', 'print_emoji_detection_script' );" . PHP_EOL;
		$output .= "\tremove_action( 'wp_print_styles', 'print_emoji_styles' );" . PHP_EOL;
		$output .= "\tremove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );" . PHP_EOL;
		$output .= "\tremove_filter( 'the_content_feed', 'wp_staticize_emoji' );" . PHP_EOL;
		$output .= "\tremove_filter( 'comment_text_rss', 'wp_staticize_emoji' );" . PHP_EOL;
		$output .= "\t// filter to remove TinyMCE emojis" . PHP_EOL;
		$output .= "\tadd_filter( 'tiny_mce_plugins', 'ace_disable_emoji_tinymce' );" . PHP_EOL;
		$output .= "}" . PHP_EOL;
		$output .= "add_action( 'init', 'ace_remove_emoji' );" . PHP_EOL;
		$output .= "/**" . PHP_EOL;
		$output .= "* Remove tinyMCE emoji" . PHP_EOL;
		$output .= "*/" . PHP_EOL;
		$output .= "function ace_disable_emoji_tinymce( \$plugins ) {" . PHP_EOL;
		$output .= "\tunset( \$plugins['wpemoji'] );" . PHP_EOL;
		$output .= "\treturn \$plugins;" . PHP_EOL;
		$output .= "}" . PHP_EOL;

		return $output;
	}

	/**
	 * Returns an example of the content of a text snippet.
	 *
	 * @return string
	 */
	protected static function get_simple_text_snippet() {
		$output = '<h3>What is Lorem Ipsum?</h3>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';
		$output .= PHP_EOL . "{{SNIPPET_CONTENT}}" . PHP_EOL;

		return $output;
	}

	/**
	 * Returns an example of the content of a universal snippet.
	 *
	 * @return string
	 */
	protected static function get_simple_universal_snippet() {
		$output = "<!-- Global Site Tag (gtag.js) - Google Analytics -->" . PHP_EOL;
		$output .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID\"></script>" . PHP_EOL;
		$output .= "<script>" . PHP_EOL;
		$output .= "\twindow.dataLayer = window.dataLayer || [];" . PHP_EOL;
		$output .= "\tfunction gtag(){dataLayer.push(arguments);}" . PHP_EOL;
		$output .= "\tgtag('js', new Date());" . PHP_EOL;
		$output .= "\tgtag('config', 'GA_TRACKING_ID');" . PHP_EOL;
		$output .= "</script>" . PHP_EOL;
		$output .= "<!-- End global Site Tag (gtag.js) - Google Analytics -->" . PHP_EOL;

		return $output;
	}

	/**
	 * Wrapper for register shortcode
	 *
	 * @param $name
	 * @param $obj
	 */
	public static function register_shortcode( $name, $obj ) {
		Wbcr_FactoryShortcodes329::register( $name, $obj );
	}

	/**
	 * Wrapper for get factory class for container
	 *
	 * @return string
	 */
	public static function get_factory_class() {
		return "factory-bootstrap-423 factory-fontawesome-000";
	}

	/**
	 * Wrapper for Wbcr_FactoryForms420_OptionsValueProvider object
	 *
	 * @param $plugin
	 *
	 * @return Wbcr_FactoryForms420_OptionsValueProvider
	 */
	public static function get_options_value_provider( $plugin ) {
		return new Wbcr_FactoryForms420_OptionsValueProvider( $plugin );
	}

	/**
	 * Wrapper for get factory form object
	 *
	 * @param $options
	 * @param $plugin
	 *
	 * @return Wbcr_FactoryForms420_Form
	 */
	public static function get_factory_form( $options, $plugin ) {
		return new Wbcr_FactoryForms420_Form( $options, $plugin );
	}

	/**
	 * Wrapper for register factory metaboxes
	 *
	 * @param $class_name_or_object
	 * @param $post_type
	 * @param $plugin
	 */
	public static function register_factory_metaboxes( $class_name_or_object, $post_type, $plugin ) {
		Wbcr_FactoryMetaboxes409::registerFor( $class_name_or_object, $post_type, $plugin );
	}

	/**
	 * Render html for purchase button
	 */
	public static function get_purchase_button( $utm_tracking_location = 'snippet-library-page' ) {
		$price_url = WINP_Plugin::app()->get_support()->get_pricing_url( true, $utm_tracking_location );
		$price     = WINP_Plugin::app()->premium->get_price();
		$price     = empty( $price ) ? 19 : $price;
		?>
        <p class="winp-purchase-button">
            <a class="button" id="winp-library-buy-button" href="<?php echo esc_url( $price_url ) ?>" target="_blank">
                <span><?php echo __( 'Purchase premium for', 'insert-php' ) . ' $' . esc_attr( $price ); ?></span>
            </a>
        </p>
		<?php
	}

	/**
	 * Check if current user is admin or editor
	 * todo: удалить этот метод, потому что он дублирует функционал WINP_Plugin::app()->currentUserCan
	 *
	 * @return bool
	 */
	public static function winp_check_user_admin() {
		return current_user_can( 'manage_options' ) || current_user_can( 'administrator' );
	}

	/*
     * Flushes as many page cache plugin's caches as possible.
     *
     * @return void
     */
	public static function flush_page_cache() {
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				wp_cache_clear_cache( $blog_id );
			} else {
				wp_cache_clear_cache();
			}
		} else if ( has_action( 'cachify_flush_cache' ) ) {
			do_action( 'cachify_flush_cache' );
		} else if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
		} else if ( function_exists( 'wp_fast_cache_bulk_delete_all' ) ) {
			wp_fast_cache_bulk_delete_all();
		} else if ( class_exists( 'WpFastestCache' ) ) {
			$wpfc = new WpFastestCache();
			$wpfc->deleteCache();
		} else if ( class_exists( 'c_ws_plugin__qcache_purging_routines' ) ) {
			c_ws_plugin__qcache_purging_routines::purge_cache_dir(); // quick cache
		} else if ( class_exists( 'zencache' ) ) {
			zencache::clear();
		} else if ( class_exists( 'comet_cache' ) ) {
			comet_cache::clear();
		} else if ( class_exists( 'WpeCommon' ) ) {
			// WPEngine cache purge/flush methods to call by default
			$wpe_methods = [
				'purge_varnish_cache',
			];

			// More agressive clear/flush/purge behind a filter
			if ( apply_filters( 'wbcr/factory/flush_wpengine_aggressive', false ) ) {
				$wpe_methods = array_merge( $wpe_methods, [ 'purge_memcached', 'clear_maxcdn_cache' ] );
			}

			// Filtering the entire list of WpeCommon methods to be called (for advanced usage + easier testing)
			$wpe_methods = apply_filters( 'wbcr/factory/wpengine_methods', $wpe_methods );

			foreach ( $wpe_methods as $wpe_method ) {
				if ( method_exists( 'WpeCommon', $wpe_method ) ) {
					WpeCommon::$wpe_method();
				}
			}
		} else if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		} else if ( file_exists( WP_CONTENT_DIR . '/wp-cache-config.php' ) && function_exists( 'prune_super_cache' ) ) {
			// fallback for WP-Super-Cache
			global $cache_path;
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				prune_super_cache( get_supercache_dir( $blog_id ), true );
				prune_super_cache( $cache_path . 'blogs/', true );
			} else {
				prune_super_cache( $cache_path . 'supercache/', true );
				prune_super_cache( $cache_path, true );
			}
		}
	}
}
