<?php
/**
 * This class is implemented page: snippet library
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 2019, OnePress Ltd
 *s
 * @package core
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common Settings
 */
class WINP_SnippetLibraryPage extends WINP_Page {

	/**
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	public function __construct( Wbcr_Factory422_Plugin $plugin ) {
		$this->menu_post_type = WINP_SNIPPETS_POST_TYPE;

		$this->id         = 'snippet-library';
		$this->menu_title = __( 'Snippets library', 'insert-php' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;

		require_once( WINP_PLUGIN_DIR . '/admin/includes/class.snippets.table.php' );
	}

	/**
	 * Assets
	 *
	 * @param $scripts
	 * @param $styles
	 */
	public function assets( $scripts, $styles ) {
		$this->scripts->request( 'jquery' );

		$this->styles->request(
			array(
				'bootstrap.core',
			),
			'bootstrap'
		);

		$this->styles->add( WINP_PLUGIN_URL . '/admin/assets/css/snippets-table.css' );

		wp_enqueue_script( 'winp-snippet-library', WINP_PLUGIN_URL . '/admin/assets/js/snippet-library.js' );
		wp_localize_script(
			'winp-snippet-library',
			'winp_snippet_library',
			array(
				'is_import'     => __( 'Import snippet?', 'insert-php' ),
				'is_delete'     => __( 'Delete snippet?', 'insert-php' ),
				'import_failed' => __( 'An error occurred during import', 'insert-php' ),
				'delete_failed' => __( 'An error occurred during delete', 'insert-php' ),
			)
		);
	}

	/**
	 * Render html part with snippets list
	 *
	 * @param $common
	 */
	public function render_html( $common ) {
		// Create an instance of our package class...
		$snippet_list_table = new WINP_Snippet_Library_Table();

		$is_pro = $is_pro = WINP_Plugin::app()->get_api_object()->is_key();
		if ( $is_pro ) : ?>
            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
            <form id="winp-snippet-library" method="get">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo WINP_Plugin::app()->request->request( 'page', 1 ); ?>"/>
                <!-- Now we can render the completed list table -->
                <?php
                $snippet_list_table->prepare_items( $common );
                $snippet_list_table->display();
                ?>
            </form>

            <?php wp_nonce_field( 'winp-snippet-library', 'winp-snippet-library-nonce' ); ?>
        <?php else: ?>
            <div class="winp-more-padding"></div>
            <div class="row">
                <div class="col-md-2 col-lg-4">&nbsp;</div>
                <div class="col-sm-12 col-md-8 col-lg-4" style="text-align: center">
                    <p class="winp-icon"><span class="dashicons dashicons-category"></span></p>
					<?php if ( $common ) : ?>
						<p class="winp-header-modal"><?php _e('Snippets Library [Premium]', 'insert-php') ?></p>
						<p class="winp-title-modal"><?php _e('Here you see all of your snippet templates. We’ve gathered the frequently used snippets so you can use them in your projects. Snippet templates help to implement your ideas faster without wasting time on searching and studying PHP code. The feature is available in the premium version only.', 'insert-php') ?></p>
					<?php else: ?>
						<p class="winp-header-modal"><?php _e('My Templates [Premium]', 'insert-php') ?></p>
						<p class="winp-title-modal"><?php _e('Whenever you or someone from your team save snippets as templates on any website, they go to this section. Our remote server provides snippet synchronization. Thus, snippet templates are always available without extra import/export. The feature is available in the premium version only.', 'insert-php') ?></p>
                    <?php endif; ?>
                    <?php WINP_Helper::get_purchase_button() ?>
                </div>
                <div class="col-md-2 col-lg-4">&nbsp;</div>
            </div>
        <?php endif;
	}

	/**
	 * Prints the contents of the page.
	 */
	public function indexAction() {
		$my_snippets_tab = true;
		$library_tab     = false;
		$my_snippets_url = remove_query_arg( array( 'tab' ) );
		$library_url     = add_query_arg( 'tab', 'library', $my_snippets_url );

		if ( WINP_Plugin::app()->request->get( 'tab' ) == 'library' ) {
			$my_snippets_tab = false;
			$library_tab     = true;
		} ?>
        <div class="wrap">
            <div class="winp-snippet-library">
                <h3><?php _e( 'Snippets library', 'insert-php' ); ?></h3>

                <div class="nav-tab-wrapper">
                    <a href="<?php echo $my_snippets_url; ?>" class="nav-tab<?php echo( $my_snippets_tab ? ' nav-tab-active' : '' ); ?>">
						<?php _e( 'My snippets', 'insert-php' ); ?>
                    </a>
					<a href="<?php echo $library_url; ?>" class="nav-tab<?php echo( $library_tab ? ' nav-tab-active' : '' ); ?>">
		                <?php _e( 'Snippets library', 'insert-php' ); ?>
					</a>
                </div>

				<?php if ( $library_tab ) : ?>
                    <div id="tab1">
						<?php $this->render_html( true ); ?>
                    </div>
				<?php else: ?>
                    <div id="tab2">
						<?php $this->render_html( false ); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
		<?php
	}
}
