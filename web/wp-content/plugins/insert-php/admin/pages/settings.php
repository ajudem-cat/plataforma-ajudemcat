<?php
/**
 * The file contains a short help info.
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
class WINP_SettingsPage extends WINP_Page {

	/**
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	public function __construct( Wbcr_Factory422_Plugin $plugin ) {
		$this->menu_post_type = WINP_SNIPPETS_POST_TYPE;

		$this->id         = "settings";
		$this->menu_title = __( 'Settings', 'insert-php' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;

		do_action( 'wbcr/inp/settings/after_construct' );
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
	}

	/**
	 * Returns options for the Basic Settings screen.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function getOptions() {

		$options = [];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'activate_by_default',
			'title'   => __( 'Activate by Default', 'insert-php' ),
			'default' => true,
			'hint'    => __( 'When creating a new snippet or updating an old one, make the code snippets active by default.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'keep_html_entities',
			'title'   => __( 'Keep the HTML entities, don\'t convert to its character', 'insert-php' ),
			'default' => false,
			'hint'    => __( 'If you want to use an HTML entity in your code (for example &gt; or &quot;), but the editor keeps on changing them to its equivalent character (> and " for the previous example), then you might want to enable this option.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'complete_uninstall',
			'title'   => __( 'Complete Uninstall', 'insert-php' ),
			'default' => false,
			'hint'    => __( 'When the plugin is deleted from the Plugins menu, also delete all snippets and plugin settings.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'support_old_shortcodes',
			'title'   => __( 'Support old shortcodes [insert_php]', 'insert-php' ),
			'default' => false,
			'hint'    => __( 'If you used our plugin from version 1.3.0, then you could use the old shortcodes [insert_php][/insert_php]; from version 2.2.0 we disabled this type of shortcodes by default, as their use is not safe. If you still want to execute your php code via [insert_php][/insert_php] shortcodes, you can enable this option.', 'insert-php' )
		];

		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">Code Editor</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'    => 'dropdown',
			'name'    => 'code_editor_theme',
			'title'   => __( 'Code style', 'insert-php' ),
			'data'    => $this->getAvailableThemes(),
			'default' => 'default',
			'hint'    => __( 'The optional feature. You can customize the code style in the snippet editor. The "Default" style is applied by default.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'code_editor_indent_with_tabs',
			'title'   => __( 'Indent With Tabs', 'insert-php' ),
			'default' => false,
			'hint'    => __( 'The optional feature. Whether, when indenting, the first N*tabSize spaces should be replaced by N tabs. The default is false.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'integer',
			'way'     => 'buttons',
			'name'    => 'code_editor_tab_size',
			'title'   => __( 'Tab Size', 'insert-php' ),
			'default' => 4,
			'hint'    => __( 'The optional feature. Pressing Tab in the code editor increases left indent to N spaces. N is a number pre-defined by you.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'integer',
			'way'     => 'buttons',
			'name'    => 'code_editor_indent_unit',
			'title'   => __( 'Indent Unit', 'insert-php' ),
			'default' => 4,
			'hint'    => __( 'The optional feature. The indent for code lines (units). Example: select a snippet, press Tab. The left indent in the selected code increases to N spaces. N is a number pre-defined by you.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'code_editor_wrap_lines',
			'title'   => __( 'Wrap Lines', 'insert-php' ),
			'default' => true,
			'hint'    => __( 'The optional feature. If ON, the editor will wrap long lines. Otherwise, it will create a horizontal scroll.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'code_editor_line_numbers',
			'title'   => __( 'Line Numbers', 'insert-php' ),
			'default' => true,
			'hint'    => __( 'The optional feature. If ON, all lines in the editor will be numbered.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'code_editor_auto_close_brackets',
			'title'   => __( 'Auto Close Brackets', 'insert-php' ),
			'default' => true,
			'hint'    => __( 'The optional feature. If ON, the editor will automatically close opened quotes or brackets. Sometimes, it speeds up coding.', 'insert-php' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'code_editor_highlight_selection_matches',
			'title'   => __( 'Highlight Selection Matches', 'insert-php' ),
			'default' => false,
			'hint'    => __( 'The optional feature. If ON, it searches for matches for the selected variable/function name. Highlight matches with green. Improves readability.', 'insert-php' )
		];

		$options = apply_filters( 'wbcr/inp/settings/form_options', $options );

		$options[] = [
			'type' => 'separator'
		];

		return $options;
	}


	public function indexAction() {

		// creating a form
		$form = WINP_Helper::get_factory_form( [
			'scope' => substr( $this->plugin->getPrefix(), 0, - 1 ),
			'name'  => 'setting'
		], $this->plugin );

		$form->setProvider( WINP_Helper::get_options_value_provider( $this->plugin ) );

		$form->add( $this->getOptions() );

		if ( isset( $_POST['wbcr_inp_setting_form_save'] ) ) {
			check_admin_referer( 'wbcr_inp_settings_form', 'wbcr_inp_settings_form_nonce_field' );

			if ( ! WINP_Plugin::app()->currentUserCan() ) {
				wp_die( __( 'Sorry, you are not allowed to save settings as this user.' ), __( 'You need a higher level of permission.' ), 403 );
			}

			$form->save();

			do_action( 'wbcr/inp/settings/after_form_save' );
		}
		?>
        <div class="wrap">
            <div class="<?php echo WINP_Helper::get_factory_class(); ?>">
                <h3><?php _e( 'Settings', 'insert-php' ) ?></h3>
                <div class="row">
                    <div class="col-md-9">
                        <form method="post" class="form-horizontal">
							<?php if ( isset( $_POST['wbcr_inp_setting_form_save'] ) ) { ?>
                                <div id="message" class="alert alert-success">
                                    <p><?php _e( 'The settings have been updated successfully!', 'insert-php' ) ?></p>
                                </div>
							<?php } ?>
                            <div style="padding-top: 10px;">
								<?php $form->html(); ?>
                            </div>
                            <div class="form-group form-horizontal">
                                <label class="col-sm-2 control-label"> </label>
                                <div class="control-group controls col-sm-10">
									<?php wp_nonce_field( 'wbcr_inp_settings_form', 'wbcr_inp_settings_form_nonce_field' ); ?>
                                    <input name="wbcr_inp_setting_form_save" class="btn btn-primary" type="submit" value="<?php _e( 'Save settings', 'insert-php' ) ?>"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <div id="winp-dashboard-widget" class="winp-right-widget">
							<?php
							apply_filters( 'wbcr/inp/dashboard/widget/print', '' );
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Retrieve a list of the available CodeMirror themes
	 *
	 * @return array the available themes
	 */
	public function getAvailableThemes() {
		static $themes = null;

		if ( ! is_null( $themes ) ) {
			return $themes;
		}

		$themes      = [];
		$themes_dir  = WINP_PLUGIN_DIR . '/admin/assets/css/cmthemes/';
		$theme_files = glob( $themes_dir . '*.css' );

		foreach ( $theme_files as $i => $theme ) {
			$theme    = str_replace( $themes_dir, '', $theme );
			$theme    = str_replace( '.css', '', $theme );
			$themes[] = [ $theme, $theme ];
		}

		array_unshift( $themes, [ 'default', 'default' ] );

		return $themes;
	}
}
