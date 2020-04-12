<?php
/**
 * Ajax requests handler
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 2018 Webraftic Ltd
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get snippet library table content
 */
function wbcr_inp_ajax_get_snippet_library() {
	if ( ! WINP_Plugin::app()->currentUserCan() ) {
		wp_die( - 1, 403 );
	}

	check_ajax_referer( 'winp-snippet-library', 'winp_nonce' );
	?>
    <div class="wrap">
        <form id="winp-snippet-library-list" method="get">
            <input type="hidden" name="page" value="<?php echo WINP_Plugin::app()->request->request( 'page', 1, true ); ?>"/>
            <input type="hidden" name="order" value="<?php echo WINP_Plugin::app()->request->request( 'order', 'asc', true ); ?>"/>
            <input type="hidden" name="orderby" value="<?php echo WINP_Plugin::app()->request->request( 'orderby', 'title', true ); ?>"/>
            <div id="winp-snippet-library-table" style="">
                <p><?php _e( 'Loading...', 'insert-php' ); ?></p>
				<?php
				wp_nonce_field( 'winp-ajax-custom-list-nonce', 'winp_ajax_custom_list_nonce' );
				?>
            </div>
        </form>
    </div>
	<?php
	wp_die();
}

add_action( 'wp_ajax_winp_get_snippet_library', 'wbcr_inp_ajax_get_snippet_library' );

/**
 * Snippet synchronization
 */
function wbcr_inp_ajax_snippet_synchronization() {
	if ( ! WINP_Plugin::app()->currentUserCan() ) {
		wp_die( - 1, 403 );
	}

	$snippet_id   = WINP_Plugin::app()->request->post( 'snippet_id', 0, 'intval' );
	$snippet_name = WINP_Plugin::app()->request->post( 'snippet_name', '', true );

	check_ajax_referer( "wbcr_inp_save_snippet_{$snippet_id}_as_template" );

	$result = WINP_Plugin::app()->get_api_object()->synchronization( $snippet_id, $snippet_name );

	exit( $result );
}

add_action( 'wp_ajax_winp_snippet_synchronization', 'wbcr_inp_ajax_snippet_synchronization' );

/**
 * Snippet create from library
 */
function wbcr_inp_ajax_snippet_create() {
	if ( ! WINP_Plugin::app()->currentUserCan() ) {
		wp_die( - 1, 403 );
	}

	check_ajax_referer( 'winp-ajax-custom-list-nonce', 'winp_ajax_custom_list_nonce' );

	$snippet_id = WINP_Plugin::app()->request->post( 'snippet_id', 0, true );
	$post_id    = WINP_Plugin::app()->request->post( 'post_id', 0, true );
	$common     = WINP_Plugin::app()->request->post( 'common', 0 );
	$result     = WINP_Plugin::app()->get_api_object()->create_from_library( $snippet_id, $post_id, $common );

	echo( $result );
	exit();
}

add_action( 'wp_ajax_winp_snippet_create', 'wbcr_inp_ajax_snippet_create' );

/**
 * Snippet delete from library
 */
function wbcr_inp_ajax_snippet_delete() {
	if ( ! WINP_Plugin::app()->currentUserCan() ) {
		wp_die( - 1, 403 );
	}

	$snippet_id = WINP_Plugin::app()->request->post( 'snippet_id', 0, true );

	check_ajax_referer( 'winp-ajax-snippet-delete-' . $snippet_id, 'winp_ajax_snippet_delete_nonce' );

	$result = WINP_Plugin::app()->get_api_object()->delete_snippet( $snippet_id );

	echo( $result );
	exit();
}

add_action( 'wp_ajax_winp_snippet_delete', 'wbcr_inp_ajax_snippet_delete' );

/**
 * Action wp_ajax for fetching the first time table structure
 */
function wbcr_inp_ajax_sts_display_callback() {
	if ( ! WINP_Plugin::app()->currentUserCan() ) {
		wp_die( - 1, 403 );
	}

	check_ajax_referer( 'winp-ajax-custom-list-nonce', 'winp_ajax_custom_list_nonce' );

	require_once( WINP_PLUGIN_DIR . '/admin/includes/class.snippets.table.php' );

	// Create an instance of our package class...
	$snippet_list_table = new WINP_Snippet_Library_Table( true );
	// Fetch, prepare, sort, and filter our data...
	$snippet_list_table->prepare_items();
	ob_start();
	$snippet_list_table->display();
	$display = ob_get_clean();
	die( json_encode( [
		'display' => $display,
	] ) );
}

add_action( 'wp_ajax_winp_sts_display', 'wbcr_inp_ajax_sts_display_callback' );

/**
 * Action wp_ajax for fetching ajax_response
 */
function wbcr_inp_ajax_sts_history_callback() {
	if ( ! WINP_Plugin::app()->currentUserCan() ) {
		wp_die( - 1, 403 );
	}

	check_ajax_referer( 'winp-ajax-custom-list-nonce', 'winp_ajax_custom_list_nonce' );

	require_once( WINP_PLUGIN_DIR . '/admin/includes/class.snippets.table.php' );

	$snippet_list_table = new WINP_Snippet_Library_Table( true );
	$snippet_list_table->ajax_response();
}

add_action( 'wp_ajax_winp_fetch_sts_history', [ $this, 'wbcr_inp_ajax_sts_history_callback' ] );
