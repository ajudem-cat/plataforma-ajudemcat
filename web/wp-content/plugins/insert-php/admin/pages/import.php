<?php
/**
 * This class is implemented page: import in the admin panel.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       core
 * @copyright (c) 2018, OnePress Ltd
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common Settings
 */
class WINP_ImportPage extends WINP_Page {

	/**
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	public function __construct( Wbcr_Factory422_Plugin $plugin ) {
		$this->menu_post_type = WINP_SNIPPETS_POST_TYPE;

		$this->id         = "import";
		$this->menu_title = __( 'Import/Export', 'insert-php' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	public function assets( $scripts, $styles ) {
		$this->scripts->request( 'jquery' );

		$this->scripts->request( [
			'control.checkbox',
			'control.dropdown'
		], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.core',
			'bootstrap.form-group',
			'bootstrap.separator',
			'control.dropdown',
			'control.checkbox',
		], 'bootstrap' );

		$this->styles->add( WINP_PLUGIN_URL . '/admin/assets/css/import.css' );
	}

	private function getMessage() {
		$error        = WINP_Plugin::app()->request->request( 'wbcr_inp_error', '' );
		$imported     = WINP_Plugin::app()->request->request( 'wbcr_inp_imported', - 1 );
		$import_error = WINP_Plugin::app()->request->request( 'wbcr_import_error', '' );

		if ( ! empty( $error ) ) { ?>
            <div id="message" class="alert alert-danger">
                <p><?php _e( 'An error occurred when processing the import files.', 'insert-php' ) ?></p>
            </div>
		<?php } else if ( intval( $imported ) >= 0 ) {
			$imported = intval( $imported );
			if ( 0 === $imported ) {
				$message = __( 'No snippets were imported.', 'insert-php' );
			} else {
				$message = sprintf( _n( 'Successfully imported <strong>%1$d</strong> snippet.', 'Successfully imported <strong>%1$d</strong> snippets.', $imported, 'insert-php' ), $imported );
			} ?>
            <div id="message" class="alert alert-success">
                <p><?php echo $message ?></p>
            </div>
			<?php
		} else if ( ! empty( $import_error ) ) { ?>
            <div id="message" class="alert alert-warning">
                <p>
                    <span class="dashicons dashicons-info"></span><?php echo sprintf( __( 'To import more then one snippet at a time, you need to purchase <a href="%s">Woody snippets PRO</a>', 'insert-php' ), WINP_Plugin::app()->get_support()->get_site_url( true, 'import-page' ) ); ?>
                </p>
            </div>
			<?php
		} else if ( isset( $_POST['wbcr_inp_import_form_action'] ) ) { ?>
            <div id="message" class="alert alert-warning">
                <p><?php _e( 'No files selected!', 'insert-php' ) ?></p>
            </div>
		<?php }
	}

	public function indexAction() {

		$import_url = remove_query_arg( [ 'tab' ] );
		$export_url = add_query_arg( 'tab', 'export', $import_url );

		$import_tab = true;
		$export_tab = false;

		if ( WINP_Plugin::app()->request->get( 'tab', 'import' ) == 'export' ) {
			$import_tab = false;
			$export_tab = true;
		}

		$max_size_bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );

		?>
        <div class="wrap">
            <div class="<?php echo WINP_Helper::get_factory_class(); ?> winp-import-snippets">
                <form method="post" class="form-horizontal" enctype="multipart/form-data">
					<?php $this->getMessage() ?>
                    <h3><?php _e( 'Woody ad snippets Import', 'insert-php' ) ?></h3>
                    <div class="row">
                        <div class="<?php echo $import_tab ? 'col-md-9' : 'col-md-12' ?>">
                            <div class="nav-tab-wrapper">
                                <a href="<?php echo $import_url; ?>" class="nav-tab<?php echo( $import_tab ? ' nav-tab-active' : '' ); ?>">
									<?php _e( 'Import', 'insert-php' ); ?>
                                </a>
                                <a href="<?php echo $export_url; ?>" class="nav-tab<?php echo( $export_tab ? ' nav-tab-active' : '' ); ?>">
									<?php _e( 'Export', 'insert-php' ); ?>
                                </a>
                            </div>
							<?php if ( $import_tab ) : ?>
                                <div id="tab1">
                                    <p style="padding-bottom: 15px"><?php _e( 'Upload one or more Php Snippets export files and the snippets will be imported.', 'insert-php' ); ?></p>
                                    <h4><?php _e( 'Duplicate Snippets', 'insert-php' ); ?></h4>
                                    <p class="description">
										<?php esc_html_e( 'What should happen if an existing snippet is found with an identical name to an imported snippet?', 'insert-php' ); ?>
                                    </p>
                                    <div style="padding-top: 10px;" class="winp-import-radio-container">
                                        <fieldset>
                                            <p>
                                                <label style="font-weight: normal;">
                                                    <input type="radio" name="duplicate_action" value="ignore" checked="checked">
													<?php _e( 'Ignore any duplicate snippets: import all snippets from the file regardless and leave all existing snippets unchanged.', 'insert-php' ); ?>
                                                </label>
                                            </p>
                                            <p>
                                                <label style="font-weight: normal;">
                                                    <input type="radio" name="duplicate_action" value="replace">
													<?php _e( 'Replace any existing snippets with a newly imported snippet of the same name.', 'insert-php' ); ?>
                                                </label>
                                            </p>
                                            <p>
                                                <label style="font-weight: normal;">
                                                    <input type="radio" name="duplicate_action" value="skip">
													<?php _e( 'Do not import any duplicate snippets; leave all existing snippets unchanged.', 'insert-php' ); ?>
                                                </label>
                                            </p>
                                        </fieldset>
                                    </div>
                                    <h3><?php _e( 'Upload Files', 'insert-php' ); ?></h3>
                                    <p class="description">
										<?php _e( 'Choose one or more Php Snippets (.json) files to upload, then click "Upload files and import".', 'insert-php' ); ?>
                                    </p>
                                    <fieldset>
                                        <p>
                                            <label for="upload" style="font-weight: normal;">
												<?php _e( 'Choose files from your computer:', 'insert-php' ); ?>
                                            </label>
											<?php printf( /* translators: %s: size in bytes */ esc_html__( '(Maximum size: %s)', 'insert-php' ), size_format( $max_size_bytes ) ); ?>
                                            <input type="file" id="upload" name="wbcr_inp_import_files[]" size="25" accept="application/json,.json,text/xml" multiple="multiple">
                                            <input type="hidden" name="action" value="save">
                                            <input type="hidden" name="max_file_size" value="<?php echo esc_attr( $max_size_bytes ); ?>">
                                        </p>
                                    </fieldset>
                                    <div class="form-group form-horizontal">
                                        <div class="control-group controls col-sm-12">
											<?php wp_nonce_field( 'wbcr_inp_import_form', 'wbcr_inp_import_form_nonce_field' ); ?>
                                            <input name="<?php echo 'wbcr_inp_import_form_action' ?>" class="btn btn-primary" type="submit" value="<?php _e( 'Upload files and import', 'insert-php' ) ?>"/>
                                        </div>
                                    </div>
                                </div>
							<?php else: ?>
                                <div id="tab2">
                                    <div class="winp-more-padding"></div>
                                    <div class="row">
                                        <div class="col-md-2 col-lg-4">&nbsp;</div>
                                        <div class="col-sm-12 col-md-8 col-lg-4" style="text-align: center">
                                            <p class="winp-icon"><span class="dashicons dashicons-category"></span></p>
                                            <p class="winp-header-modal"><?php _e( 'Bulk export [Premium]', 'insert-php' ) ?></p>
                                            <p class="winp-title-modal"><?php _e( 'Bulk export allows exporting all snippets in one click. Bulk export has advanced settings of snippet segmentation by types, tags, or activity. If you value your time use our premium plugin and export all of your snippets in one click.', 'insert-php' ) ?></p>
											<?php WINP_Helper::get_purchase_button() ?>
                                        </div>
                                        <div class="col-md-2 col-lg-4">&nbsp;</div>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
						<?php if ( $import_tab ) : ?>
                            <div class="col-md-3">
                                <div id="winp-dashboard-widget" class="winp-right-widget">
									<?php
									apply_filters( 'wbcr/inp/dashboard/widget/print', '' );
									?>
                                </div>
                            </div>
						<?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
		<?php
	}
}
