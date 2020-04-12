<?php
/**
 * Admin boot
 *
 * @author    Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 05.06.2018
 * @version   1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Добавляет подсказку и кнопку в сообщение фатальной ошибки.
 *
 * С версии Wordpress 5.2, нам доступен специальный режим, перехвата php ошибок.
 * Если пользователь например допустит синтаксическую ошибку при редактировании
 * сниппета, то он вместо белого экрана (если php ошибки отключены на сервере)
 * увидит сообщение от Wordpress сгенерированное классом WP_Fatal_Error_Handler.
 *
 * Мы решили добавить в это сообщение кнопку для перехода в безопасный режим.
 */
add_filter( 'wp_php_error_message', function ( $message ) {
	$safe_mode_url    = admin_url( 'edit.php?post_type=' . WINP_SNIPPETS_POST_TYPE . '&wbcr-php-snippets-safe-mode' );
	$safe_mode_button = '<div style="margin:20px 0;padding:20px; background:#ffe8e8;">' . __( 'If you see this message after saving the snippet to the Woody ad snippets plugin, please enable safe mode in the Woody plugin. Safe mode will allow you to continue working in the admin panel of your site and change the snippet in which you made a php error.', 'insert_php' ) . '</div>';
	$safe_mode_button .= '<a href="' . $safe_mode_url . '" class="button">' . __( 'Enable safe mode in Woody ad snippets', 'insert_php' ) . '</a>';

	return $message . $safe_mode_button;
} );

/**
 * Инициализации метабоксов и страницы "о плагине".
 *
 * Этот хук реализует условную логику, при которой пользователь переодически будет
 * видет страницу "О плагине", а конкретно при активации и обновлении плагина.
 */
add_action( 'admin_init', function () {

	$plugin = WINP_Plugin::app();

	// Register metaboxes
	require_once( WINP_PLUGIN_DIR . '/admin/metaboxes/base-options.php' );
	WINP_Helper::register_factory_metaboxes( new WINP_BaseOptionsMetaBox( $plugin ), WINP_SNIPPETS_POST_TYPE, $plugin );

	if ( ( defined( 'FACTORY_ADVERTS_DEBUG' ) && FACTORY_ADVERTS_DEBUG ) || ! WINP_Plugin::app()->premium->is_activate() ) {
		require_once( WINP_PLUGIN_DIR . '/admin/metaboxes/info.php' );
		WINP_Helper::register_factory_metaboxes( new WINP_InfoMetaBox( $plugin ), WINP_SNIPPETS_POST_TYPE, $plugin );
	}

	$snippet_type = WINP_Helper::get_snippet_type();

	if ( $snippet_type !== WINP_SNIPPET_TYPE_PHP ) {
		require_once( WINP_PLUGIN_DIR . '/admin/metaboxes/view-options.php' );
		WINP_Helper::register_factory_metaboxes( new WINP_ViewOptionsMetaBox( $plugin ), WINP_SNIPPETS_POST_TYPE, $plugin );
	}

	do_action( 'wbcr/inp/boot/metaboxes/revisions', '' );

	// If the user has updated the plugin or activated it for the first time,
	// you need to show the page "What's new?"
	if ( ! WINP_Plugin::app()->isNetworkAdmin() ) {
		$about_page_viewed = WINP_Plugin::app()->request->get( 'wbcr_inp_about_page_viewed', null );
		if ( is_null( $about_page_viewed ) ) {
			if ( WINP_Helper::is_need_show_about_page() ) {
				try {
					$redirect_url = '';
					if ( class_exists( 'Wbcr_FactoryPages422' ) ) {
						$redirect_url = WINP_Plugin::app()->getPluginPageUrl( 'about', [ 'wbcr_inp_about_page_viewed' => 1 ] );
					}
					if ( $redirect_url ) {
						wp_safe_redirect( $redirect_url );
						die();
					}
				} catch( Exception $e ) {
				}
			}
		} else {
			if ( WINP_Helper::is_need_show_about_page() ) {
				delete_option( $plugin->getOptionName( 'what_new_210' ) );
			}
		}
	}
} );

function wbcr_inp_admin_revisions() {
	$plugin = WINP_Plugin::app();

	require_once( WINP_PLUGIN_DIR . '/admin/metaboxes/revisions.php' );
	WINP_Helper::register_factory_metaboxes( new WINP_RevisionsMetaBox( $plugin ), WINP_SNIPPETS_POST_TYPE, $plugin );
}

add_action( 'wbcr/inp/boot/metaboxes/revisions', 'wbcr_inp_admin_revisions' );

// ---
// Editor
//

/**
 * Enqueue scripts
 */
function wbcr_inp_enqueue_scripts() {
	global $pagenow;

	$screen = get_current_screen();

	if ( ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) && WINP_SNIPPETS_POST_TYPE == $screen->post_type ) {
		wp_enqueue_script( 'wbcr-inp-admin-scripts', WINP_PLUGIN_URL . '/admin/assets/js/scripts.js', [
			'jquery',
			'jquery-ui-tooltip'
		], WINP_Plugin::app()->getPluginVersion() );
	}
}

/**
 * Asset scripts for the tinymce editor
 *
 * @param string $hook
 */
function wbcr_inp_enqueue_tinymce_assets( $hook ) {
	$pages = [
		'post.php',
		'post-new.php',
		'widgets.php'
	];

	if ( ! in_array( $hook, $pages ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_script( 'wbcr-inp-tinymce-button-widget', WINP_PLUGIN_URL . '/admin/assets/js/tinymce4.4.js', [ 'jquery' ], WINP_Plugin::app()->getPluginVersion(), true );
}

add_action( 'admin_enqueue_scripts', 'wbcr_inp_enqueue_tinymce_assets' );
add_action( 'admin_enqueue_scripts', 'wbcr_inp_enqueue_scripts' );

/**
 * Adds js variable required for shortcodes.
 *
 * @since 1.1.0
 * @see   before_wp_tiny_mce
 */
function wbcr_inp_tinymce_data( $hook ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// styles for the plugin shorcodes
	$shortcode_icon  = WINP_PLUGIN_URL . '/admin/assets/img/shortcode-icon5.png';
	$shortcode_title = __( 'Woody ad snippets', 'insert-php' );

	$result                  = WINP_Helper::get_shortcode_data( true );
	$shortcode_snippets_json = json_encode( $result );
	?>
    <!-- <?php echo WINP_Plugin::app()->getPluginTitle() ?> for tinymce -->
    <style>
        i.wbcr-inp-shortcode-icon {
            background: url("<?php echo $shortcode_icon ?>") center no-repeat;
        }
    </style>
    <script>
		var wbcr_inp_tinymce_snippets_button_title = '<?php echo $shortcode_title ?>';
		var wbcr_inp_post_tinymce_nonce = '<?php echo wp_create_nonce( 'wbcr_inp_tinymce_post_nonce' ) ?>';
		var wbcr_inp_shortcode_snippets = <?php echo $shortcode_snippets_json ?>;
    </script>
    <!-- /end <?php echo WINP_Plugin::app()->getPluginTitle() ?> for tinymce -->
	<?php
}

add_action( 'admin_print_scripts-post.php', 'wbcr_inp_tinymce_data' );
add_action( 'admin_print_scripts-post-new.php', 'wbcr_inp_tinymce_data' );
add_action( 'admin_print_scripts-widgets.php', 'wbcr_inp_tinymce_data' );

/**
 * Deactivate snippet on trashed
 *
 * @since 2.0.6
 *
 * @param $post_id
 *
 */
function wbcr_inp_trash_post( $post_id ) {
	$post_type = get_post_type( $post_id );
	if ( $post_type == WINP_SNIPPETS_POST_TYPE ) {
		WINP_Helper::updateMetaOption( $post_id, 'snippet_activate', 0 );
	}
}

add_action( 'wp_trash_post', 'wbcr_inp_trash_post' );

/**
 * Removes the default 'new item' from the admin menu to add own page 'new item' later.
 *
 * @param $menu
 *
 * @return mixed
 * @see menu_order
 *
 */
function wbcr_inp_remove_new_item( $menu ) {
	global $submenu;

	if ( ! isset( $submenu[ 'edit.php?post_type=' . WINP_SNIPPETS_POST_TYPE ] ) ) {
		return $menu;
	}
	unset( $submenu[ 'edit.php?post_type=' . WINP_SNIPPETS_POST_TYPE ][10] );

	return $menu;
}

add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'admin_menu', 'wbcr_inp_remove_new_item' );

/**
 * If the user tried to get access to the default 'new item',
 * redirects forcibly to our page 'new item'.
 *
 * @see current_screen
 */
function wbcr_inp_redirect_to_new_item() {
	$screen = get_current_screen();

	if ( empty( $screen ) ) {
		return;
	}
	if ( 'add' !== $screen->action || 'post' !== $screen->base || WINP_SNIPPETS_POST_TYPE !== $screen->post_type ) {
		return;
	}

	$winp_item = WINP_Plugin::app()->request->get( 'winp_item', null );
	if ( ! is_null( $winp_item ) ) {
		return;
	}

	$url = admin_url( 'edit.php?post_type=' . WINP_SNIPPETS_POST_TYPE . '&page=new-item-' . WINP_Plugin::app()->getPluginName() );

	wp_safe_redirect( $url );

	exit;
}

add_action( 'current_screen', 'wbcr_inp_redirect_to_new_item' );
