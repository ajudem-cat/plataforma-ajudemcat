<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_BaseOptionsMetaBox extends Wbcr_FactoryMetaboxes409_FormMetabox {

	/**
	 * A visible title of the metabox.
	 *
	 * Inherited from the class FactoryMetabox.
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $title;

	/**
	 * The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/add_meta_box
	 * Inherited from the class FactoryMetabox.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $priority = 'core';

	public $css_class = 'factory-bootstrap-423 factory-fontawesome-000';

	protected $errors = [];
	protected $source_channel;
	protected $facebook_group_id;
	protected $paginate_url;

	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->title = __( 'Base options', 'insert-php' );

		add_action( 'admin_head', [ $this, 'removeMediaButton' ] );
		add_action( 'admin_footer', [ $this, 'adminFooter' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'deregisterDefaultEditorResourses' ] );

		$snippet_type = WINP_Helper::get_snippet_type();

		if ( $snippet_type !== WINP_SNIPPET_TYPE_TEXT ) {
			add_action( 'admin_footer-post.php', [ $this, 'printCodeEditorScripts' ], 99 );
			add_action( 'admin_footer-post-new.php', [ $this, 'printCodeEditorScripts' ], 99 );
			add_action( 'edit_form_after_editor', [ $this, 'php_editor_markup' ], 10, 1 );
		}

		add_action( 'admin_body_class', [ $this, 'admin_body_class' ] );
		add_action( 'edit_form_top', [ $this, 'editFormTop' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'post_submitbox_misc_actions' ] );
		add_action( 'edit_form_after_title', [ $this, 'keep_html_entities' ] );

		add_filter( 'pre_post_content', [ $this, 'stop_post_filters' ] );
		add_filter( 'content_save_pre', [ $this, 'init_post_filters' ], 9999 );
	}

	/**
	 * Configures a metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param Wbcr_Factory422_StyleList  $styles    A set of style to include.
	 *
	 * @param Wbcr_Factory422_ScriptList $scripts   A set of scripts to include.
	 *
	 * @return void
	 */
	public function configure( $scripts, $styles ) {
		//method must be overriden in the derived classed.
		$styles->add( WINP_PLUGIN_URL . '/admin/assets/dist/css/ccm.min.css' );
		$styles->add( WINP_PLUGIN_URL . '/admin/assets/css/code-editor-style.css' );

		$code_editor_theme = $this->plugin->getPopulateOption( 'code_editor_theme' );

		if ( ! empty( $code_editor_theme ) && $code_editor_theme != 'default' ) {
			$this->styles->add( WINP_PLUGIN_URL . '/admin/assets/css/cmthemes/' . $code_editor_theme . '.css' );
		}

		$scripts->add( WINP_PLUGIN_URL . '/admin/assets/dist/js/ccm.min.js', [ 'jquery' ], 'winp-snippet-codemirror' );
		$scripts->add( WINP_PLUGIN_URL . '/admin/assets/js/transition.js', [ 'jquery' ], 'winp-snippet-transition' );

		if ( WINP_Plugin::app()->get_api_object()->is_key() ) {
			wp_localize_script( 'jquery', 'winp_snippet_sync', [
				'import'        => __( 'Import snippet', 'insert-php' ),
				'export'        => __( 'Export snippet', 'insert-php' ),
				'import_failed' => __( 'An error occurred during import', 'insert-php' ),
				'export_failed' => __( 'An error occurred during export', 'insert-php' ),
				'save'          => __( 'Save', 'insert-php' ),
				'saved'         => __( 'Snippet template succefully saved!', 'insert-php' ),
				'src_loader'    => WINP_PLUGIN_URL . '/admin/assets/img/ajax-loader.gif',
			] );
		}
	}

	/**
	 * Disable post filtering. Snippets code cannot be filtered, otherwise it will cause errors.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.3
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function stop_post_filters( $value ) {
		global $wbcr__has_kses, $wbcr__has_targeted_link_rel_filters;

		$screen = get_current_screen();
		if ( empty( $screen ) || $screen->post_type !== WINP_SNIPPETS_POST_TYPE ) {
			return $value;
		}

		$snippet_type = WINP_Helper::get_snippet_type();

		if ( $snippet_type !== WINP_SNIPPET_TYPE_TEXT ) {
			// Prevent content filters from corrupting JSON in post_content.
			$wbcr__has_kses = ( false !== has_filter( 'content_save_pre', 'wp_filter_post_kses' ) );
			if ( $wbcr__has_kses ) {
				kses_remove_filters();
			}
			$wbcr__has_targeted_link_rel_filters = ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) );
			if ( $wbcr__has_targeted_link_rel_filters ) {
				wp_remove_targeted_link_rel_filters();
			}
		}

		return $value;
	}

	/**
	 * Enable post filtering.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.3
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function init_post_filters( $value ) {
		global $wbcr__has_kses, $wbcr__has_targeted_link_rel_filters;

		$screen = get_current_screen();
		if ( empty( $screen ) || $screen->post_type !== WINP_SNIPPETS_POST_TYPE ) {
			return $value;
		}

		if ( $wbcr__has_kses ) {
			kses_init_filters();
		}

		if ( $wbcr__has_targeted_link_rel_filters ) {
			wp_init_targeted_link_rel_filters();
		}

		unset( $wbcr__has_kses );
		unset( $wbcr__has_targeted_link_rel_filters );

		return $value;
	}

	/**
	 * Add the codemirror editor in the `post` screen
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.1
	 */
	public function keep_html_entities( $post ) {
		$current_screen = get_current_screen();

		if ( empty( $post ) || ( $current_screen->post_type != WINP_SNIPPETS_POST_TYPE ) ) {
			return false;
		}

		if ( WINP_Plugin::app()->getOption( 'keep_html_entities' ) && strstr( $post->post_content, '&' ) ) {

			// First the ampresands
			$post->post_content = str_replace( '&amp', htmlentities( '&amp' ), $post->post_content );

			// Then the rest of the entities
			$html_flags = defined( 'ENT_HTML5' ) ? ENT_QUOTES | ENT_HTML5 : ENT_QUOTES;
			$entities   = get_html_translation_table( HTML_ENTITIES, $html_flags );

			unset( $entities[ array_search( '&amp;', $entities ) ] );

			$regular_expression = str_replace( ';', '', '/(' . implode( '|', $entities ) . ')/i' );

			preg_match_all( $regular_expression, $post->post_content, $matches );

			if ( isset( $matches[0] ) && count( $matches[0] ) > 0 ) {
				foreach ( $matches[0] as $_entity ) {
					$post->post_content = str_replace( $_entity, htmlentities( $_entity ), $post->post_content );
				}
			}
		}
	}

	/**
	 * Remove media button
	 */
	public function removeMediaButton() {
		global $post;

		if ( empty( $post ) || $post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
			return;
		}
		remove_action( 'media_buttons', 'media_buttons' );
	}

	/**
	 * Add html to admin footer
	 */
	public function adminFooter() {
		global $pagenow;

		$screen = get_current_screen();

		if ( ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) && WINP_SNIPPETS_POST_TYPE == $screen->post_type ) {
			$snippet_id   = get_the_ID();
			$is_key       = WINP_Plugin::app()->get_api_object()->is_key();
			$premium_text = $is_key ? '' : ' [Premium]';
			$button_nonce = ' data-nonce="' . wp_create_nonce( "wbcr_inp_save_snippet_{$snippet_id}_as_template" ) . '"';

			?>
            <div class="factory-bootstrap-423 factory-fontawesome-000">
                <div class="modal fade" id="winp-sync-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                     aria-hidden="true" style="display: none">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="winp-sync-content">
                                <p class="winp-icon">
                                    <span class="dashicons dashicons-category"></span>
                                </p>
                                <p class="winp-header-modal">
									<?php _e( 'Save a Snippet as a Template', 'insert-php' ); ?>
									<?php echo $premium_text; ?>
                                </p>
								<?php if ( $is_key ) : ?>
                                    <p class="winp-title-modal">
										<?php _e( 'Your snippets will be available for export and reuse on any page or website', 'insert-php' ); ?></p>
								<?php else : ?>
                                    <p class="winp-title-modal">
										<?php _e( 'A copy of your snippet and its settings are stored on our remote server. You can access it from any website where you’ve activated the plugin’s premium version. If you have our plugin on multiple websites or work in a team, it’s quite handy to use templates. The feature is available in the premium version only.', 'insert-php' ); ?>
                                    </p>
								<?php endif; ?>
								<?php if ( $is_key ) : ?>
                                    <input type="text" id="winp-sync-snippet-name" required
                                           placeholder="<?php _e( 'Enter template name', 'insert-php' ); ?>">
                                    <button type="button" class="btn btn-secondary" id="winp-sync-save-button"<?php echo $button_nonce; ?>>
                                        <span style="width: 40px"><?php _e( 'Save', 'insert-php' ); ?></span>
                                    </button>
                                    <div class="winp-modal-error">
                                        <span class="dashicons dashicons-warning"></span>
                                        <span class="warning-text"></span>
                                    </div>
								<?php else : ?>
									<?php WINP_Helper::get_purchase_button( 'edit-snippet' ) ?>
								<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php //wp_nonce_field( 'winp-snippet-library', 'winp-snippet-library-nonce' ); ?>
            </div>
			<?php
		}
	}

	/**
	 * Deregister other CodeMirror styles
	 */
	public function deregisterDefaultEditorResourses() {
		global $post;

		if ( empty( $post ) || $post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
			return;
		}

		/* Remove other CodeMirror styles */
		wp_deregister_style( 'codemirror' );
	}

	public function printCodeEditorScripts() {
		global $post;

		if ( empty( $post ) || $post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
			return;
		}

		$snippet_type     = WINP_Helper::get_snippet_type();
		$code_editor_mode = 'application/x-httpd-php';
		if ( $snippet_type == WINP_SNIPPET_TYPE_PHP ) {
			$code_editor_mode = 'text/x-php';
		} else if ( $snippet_type === WINP_SNIPPET_TYPE_CSS ) {
			$code_editor_mode = 'text/css';
		} else if ( $snippet_type === WINP_SNIPPET_TYPE_JS ) {
			$code_editor_mode = 'application/javascript';
		} else if ( $snippet_type === WINP_SNIPPET_TYPE_HTML ) {
			$code_editor_mode = 'text/html';
		}
		$code_editor_theme = $this->plugin->getPopulateOption( 'code_editor_theme' );
		?>
        <script>
			/* Loads CodeMirror on the snippet editor */
			(function() {

				var atts = [];

				atts['mode'] = '<?php echo $code_editor_mode ?>';

				atts['matchBrackets'] = true;
				atts['styleActiveLine'] = true;
				atts['continueComments'] = true;
				atts['autoCloseTags'] = true;
				atts['viewportMargin'] = Infinity;

				atts['inputStyle'] = 'contenteditable';
				atts['direction'] = 'ltr';
				atts['lint'] = true;
				atts['gutters'] = ["CodeMirror-lint-markers"];

				atts['matchTags'] = {
					'bothTags': true
				};

				atts['extraKeys'] = {
					'Ctrl-Enter': function(cm) {
						document.getElementById('post_content').submit();
					},
					'Ctrl-Space': 'autocomplete',
					'Ctrl-/': 'toggleComment',
					'Cmd-/': 'toggleComment',
					'Alt-F': 'findPersistent',
					'Ctrl-F': 'findPersistent',
					'Cmd-F': 'findPersistent'
				};

				atts['indentWithTabs'] = <?php $this->printBool( $this->plugin->getPopulateOption( 'code_editor_indent_with_tabs', true ) ) ?>;
				atts['tabSize'] = <?php echo (int) $this->plugin->getPopulateOption( 'code_editor_tab_size', 4 ) ?>;
				atts['indentUnit'] = <?php echo (int) $this->plugin->getPopulateOption( 'code_editor_indent_unit', 4 ) ?>;
				atts['lineNumbers'] = <?php $this->printBool( $this->plugin->getPopulateOption( 'code_editor_line_numbers', true ) ) ?>;
				atts['lineWrapping'] = <?php $this->printBool( $this->plugin->getPopulateOption( 'code_editor_wrap_lines', true ) ) ?>;
				atts['autoCloseBrackets'] = <?php $this->printBool( $this->plugin->getPopulateOption( 'code_editor_auto_close_brackets', true ) ) ?>;
				<?php if ($this->plugin->getPopulateOption( 'code_editor_highlight_selection_matches', true )) { ?>
				atts['highlightSelectionMatches'] = {
					showToken: true,
					style: 'winp-matchhighlight'
				};
				<?php } else { ?>
				atts['highlightSelectionMatches'] = false;
				<?php } ?>

				<?php if(! empty( $code_editor_theme ) && $code_editor_theme != 'default'): ?>
				atts['theme'] = '<?php echo esc_attr( $code_editor_theme ) ?>';
				<?php endif; ?>

				Woody_CodeMirror.fromTextArea(document.getElementById('post_content'), atts);
			})();

			jQuery(document).ready(function($) {
				$('.wp-editor-tabs').remove();
			});
        </script>
		<?php
	}

	/**
	 * Markup PHP snippet editor.
	 *
	 * @param Wp_Post $post   Post Object.
	 */
	function php_editor_markup( $post ) {

		if ( WINP_SNIPPETS_POST_TYPE == $post->post_type ) {
			wp_nonce_field( basename( __FILE__ ), WINP_SNIPPETS_POST_TYPE );

			$snippet_code = WINP_Helper::get_snippet_code( $post );

			?>
            <div class="wp-editor-container winp-editor-container">
                <textarea id="post_content" name="post_content"
                          class="wp-editor-area winp-php-content"><?php echo esc_html( $snippet_code ); ?></textarea>
            </div>
			<?php
		}
	}

	/**
	 * Adds one or more classes to the body tag in the dashboard.
	 *
	 * @param string $classes   Current body classes.
	 *
	 * @return string Altered body classes.
	 */
	public function admin_body_class( $classes ) {
		global $post;

		if ( ! empty( $post ) && $post->post_type == WINP_SNIPPETS_POST_TYPE ) {
			$snippet_type = WINP_Helper::get_snippet_type();

			$new_classes = "wbcr-inp-snippet-type-" . esc_attr( $snippet_type );

			if ( $snippet_type !== WINP_SNIPPET_TYPE_TEXT ) {
				$new_classes .= " winp-snippet-enabled";
			}

			return ' ' . $new_classes . ' ' . $classes;
		}

		return $classes;
	}

	/**
	 * Add hidden tag to edit post form
	 * Set post title for snippet post with status auto-draft
	 *
	 * @param $current_post
	 */
	public function editFormTop( $current_post ) {
		if ( empty( $current_post ) || $current_post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
			return;
		}

		$snippet_type = WINP_Plugin::app()->request->get( 'winp_item', WINP_SNIPPET_TYPE_PHP, 'sanitize_key' );
		$snippet_type = WINP_Helper::getMetaOption( $current_post->ID, 'snippet_type', $snippet_type );

		echo '<input type="hidden" id="wbcr_inp_snippet_type" name="wbcr_inp_snippet_type" value="' . esc_attr( $snippet_type ) . '">';

		if ( 'auto-draft' == $current_post->post_status && WINP_SNIPPETS_POST_TYPE == $current_post->post_type && WINP_Helper::getMetaOption( $current_post->ID, 'snippet_draft', false ) ) {
			global $post;

			$post->post_title = get_post( $current_post->ID )->post_title;
		}
	}

	/**
	 * Add button "Save as template" on post edit page
	 *
	 * @param WP_Post $post
	 */
	public function post_submitbox_misc_actions( $post ) {
		if ( empty( $post ) || ( $post->post_type != WINP_SNIPPETS_POST_TYPE ) ) {
			return;
		}

		if ( WINP_Helper::getMetaOption( $post->ID, 'snippet_draft', false ) ) {
			return;
		}

		$disabled = '';
		if ( $post->ID && WINP_Plugin::app()->get_api_object()->is_key() ) {
			$disabled = WINP_Plugin::app()->get_api_object()->is_changed( $post->ID ) == true ? '' : ' disabled';
		}
		?>
        <div class="winp-sync-buttons">
            <a class="wbcr-inp-import-snippet-button button<?php echo $disabled; ?>" id="winp-snippet-sync"
               href="javascript:void(0)">
                <span class="dashicons dashicons-cloud" title="<?php _e( 'Export snippet', 'insert-php' ); ?>"></span>
				<?php _e( 'Save as template', 'insert-php' ); ?>
            </a>
        </div>
		<?php
	}

	/**
	 * @param bool $bool_val
	 */
	protected function printBool( $bool_val ) {
		echo $bool_val ? 'true' : 'false';
	}

	/**
	 * Configures a form that will be inside the metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param Wbcr_FactoryForms420_Form $form   A form object to configure.
	 *
	 * @return void
	 * @see   Wbcr_FactoryMetaboxes409_FormMetabox
	 */
	public function form( $form ) {
		$snippet_type = WINP_Helper::get_snippet_type();

		if ( $snippet_type === WINP_SNIPPET_TYPE_PHP ) {
			$option_name = 'Run everywhere';
			$data        = [
				[ 'evrywhere', __( $option_name, 'insert-php' ) ],
				[ 'shortcode', __( 'Where there is a shortcode', 'insert-php' ) ],
			];

			$events = [
				'evrywhere' => [
					'hide' => '.factory-control-snippet_custom_name',
				],
				'shortcode' => [
					'show' => '.factory-control-snippet_custom_name',
				],
			];
		} else {
			if ( $snippet_type === WINP_SNIPPET_TYPE_TEXT ) {
				$hint = __( 'If you want to place some content into your snippet from the shortcode just wrap it inside [wbcr_text_snippet id="xxx"]content[/wbcr_text_snippet]. To use this content inside the snippet use {{SNIPPET_CONTENT}} variable.', 'insert-php' );
			} else {
				$_type = $snippet_type === WINP_SNIPPET_TYPE_UNIVERSAL ? '' : '_' . $snippet_type;
				$hint  = sprintf( __( 'If you want to place some content into your snippet from the shortcode just wrap it inside [wbcr%s_snippet id="xxx"]content[/wbcr%s_snippet]. To use this content inside the snippet use $content variable.', 'insert-php' ), $_type, $_type );
			}

			$option_name = 'Automatic insertion';
			$data        = [
				[ 'auto', __( $option_name, 'insert-php' ) ],
				[ 'shortcode', __( 'Where there is a shortcode', 'insert-php' ), $hint ],
			];

			$events = [
				'auto'      => [
					'show' => '.factory-control-snippet_location',
					'hide' => '.factory-control-snippet_custom_name',
				],
				'shortcode' => [
					'hide' => '.factory-control-snippet_location,.factory-control-snippet_p_number',
					'show' => '.factory-control-snippet_custom_name',
				],
			];
		}

		$items[] = [
			'type'    => 'dropdown',
			'way'     => 'buttons',
			'name'    => 'snippet_scope',
			'data'    => $data,
			'title'   => __( 'Where to execute the code?', 'insert-php' ),
			'hint'    => sprintf( esc_html__( 'If you select the "%s" option, after activating the widget, the php code will be launched on all pages of your site. Another option works only where you have set a shortcode snippet (widgets, post).', 'insert-php' ), __( $option_name, 'insert-php' ) ),
			'default' => 'shortcode',
			'events'  => $events,
		];

		$is_pro  = WINP_Plugin::app()->get_api_object()->is_key();
		$items[] = [
			'type'      => 'textbox',
			'name'      => 'snippet_custom_name',
			'title'     => __( 'Custom shortcode name', 'insert-php' ),
			'hint'      => __( 'By default, all snippet shortcodes look like this: [wbcr_snippet id=”121”]. Such shortcodes are hard to remember. In addition, when you move a snippet to another website its ID can be changed. So the best solution for the snippets use regularly is to define a custom shortcode name. The custom shortcode may look like this: [soccer_match_date]', 'insert-php' ),
			'default'   => '',
			// Добавляем класс
			'cssClass'  => ! $is_pro ? [ 'winp-field-premium-element winp-field-w250' ] : [ 'winp-field-w250' ],
			// Добавляем атрибут disable
			'htmlAttrs' => ! $is_pro ? [ 'disabled' => 'disabled' ] : [],
		];

		if ( $snippet_type !== WINP_SNIPPET_TYPE_PHP ) {
			$data = [
				[
					'title' => __( 'Everywhere', 'insert-php' ),
					'type'  => 'group',
					'items' => [
						[
							WINP_SNIPPET_AUTO_HEADER,
							__( 'Head', 'insert-php' ),
							__( 'Snippet will be placed in the source code before </head>.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_FOOTER,
							__( 'Footer', 'insert-php' ),
							__( 'Snippet will be placed in the source code before </body>.', 'insert-php' ),
						],
					],
				],
				[
					'title' => __( 'Posts, Pages, Custom post types', 'insert-php' ),
					'type'  => 'group',
					'items' => [
						[
							WINP_SNIPPET_AUTO_BEFORE_POST,
							__( 'Insert Before Post', 'insert-php' ),
							__( 'Snippet will be placed before the title of the post/page.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_BEFORE_CONTENT,
							__( 'Insert Before Content', 'insert-php' ),
							__( 'Snippet will be placed before the content of the post/page.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_BEFORE_PARAGRAPH,
							__( 'Insert Before Paragraph', 'insert-php' ),
							__( 'Snippet will be placed before the paragraph, which number you can specify in the Location number field.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_AFTER_PARAGRAPH,
							__( 'Insert After Paragraph', 'insert-php' ),
							__( 'Snippet will be placed after the paragraph, which number you can specify in the Location number field.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_AFTER_CONTENT,
							__( 'Insert After Content', 'insert-php' ),
							__( 'Snippet will be placed after the content of the post/page.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_AFTER_POST,
							__( 'Insert After Post', 'insert-php' ),
							__( 'Snippet will be placed in the very end of the post/page.', 'insert-php' ),
						],
					],
				],
				[
					'title' => __( 'Categories, Archives, Tags, Taxonomies', 'insert-php' ),
					'type'  => 'group',
					'items' => [
						[
							WINP_SNIPPET_AUTO_BEFORE_EXCERPT,
							__( 'Insert Before Excerpt', 'insert-php' ),
							__( 'Snippet will be placed before the excerpt of the post/page.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_AFTER_EXCERPT,
							__( 'Insert After Excerpt', 'insert-php' ),
							__( 'Snippet will be placed after the excerpt of the post/page.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_BETWEEN_POSTS,
							__( 'Between Posts', 'insert-php' ),
							__( 'Snippet will be placed between each post.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_BEFORE_POSTS,
							__( 'Before post', 'insert-php' ),
							__( 'Snippet will be placed before the post, which number you can specify in the Location number field.', 'insert-php' ),
						],
						[
							WINP_SNIPPET_AUTO_AFTER_POSTS,
							__( 'After post', 'insert-php' ),
							__( 'Snippet will be placed after the post, which number you can specify in the Location number field.', 'insert-php' ),
						],
					],
				],
			];

			if ( $snippet_type === WINP_SNIPPET_TYPE_TEXT ) {
				unset( $data[0] );
				$data = array_values( $data );
			}

			$items[] = [
				'type'    => 'dropdown',
				'name'    => 'snippet_location',
				'data'    => $data,
				'title'   => __( 'Insertion location', 'insert-php' ),
				'hint'    => __( 'Select the location for you snippet.', 'insert-php' ),
				'default' => WINP_SNIPPET_AUTO_HEADER,
				'events'  => [
					WINP_SNIPPET_AUTO_HEADER           => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_FOOTER           => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_BEFORE_POST      => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_BEFORE_CONTENT   => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_AFTER_CONTENT    => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_AFTER_POST       => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_BEFORE_EXCERPT   => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_AFTER_EXCERPT    => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_BETWEEN_POSTS    => [
						'hide' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_BEFORE_PARAGRAPH => [
						'show' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_AFTER_PARAGRAPH  => [
						'show' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_BEFORE_POSTS     => [
						'show' => '.factory-control-snippet_p_number',
					],
					WINP_SNIPPET_AUTO_AFTER_POSTS      => [
						'show' => '.factory-control-snippet_p_number',
					],
				],
			];

			$items[] = [
				'type'    => 'textbox',
				'name'    => 'snippet_p_number',
				'title'   => __( 'Location number', 'insert-php' ),
				'hint'    => __( 'Paragraph / Post number', 'insert-php' ),
				'default' => 0,
			];
		}

		if ( $snippet_type === WINP_SNIPPET_TYPE_CSS || $snippet_type === WINP_SNIPPET_TYPE_JS ) {
			$items[] = [
				'type'    => 'dropdown',
				'way'     => 'buttons',
				'name'    => 'snippet_linking',
				'data'    => [
					[ 'external', __( 'External File', 'insert-php' ) ],
					[ 'inline', __( 'Inline Code', 'insert-php' ) ],
				],
				'title'   => __( 'Linking type', 'insert-php' ),
				'hint'    => __( 'Select how the snippet will be linked to the page.', 'insert-php' ),
				'default' => 'external',
			];
		}

		$items[] = [
			'type'    => 'textarea',
			'name'    => 'snippet_description',
			'title'   => __( 'Description', 'insert-php' ),
			'hint'    => __( 'You can write a short note so that you can always remember why this code or your colleague was able to apply this code in his works.', 'insert-php' ),
			'tinymce' => [
				'height'  => 150,
				'plugins' => '',
			],
			'default' => '',
		];

		if ( $snippet_type !== WINP_SNIPPET_TYPE_TEXT ) {
			$shorcode_name = $snippet_type === WINP_SNIPPET_TYPE_UNIVERSAL ? 'wbcr_snippet' : 'wbcr_' . $snippet_type . '_snippet';
			$items[]       = [
				'type'        => 'textbox',
				'name'        => 'snippet_tags',
				'title'       => __( 'Available attributes', 'insert-php' ),
				'hint'        => sprintf( esc_html__( "Available attributes for shortcode via comma. Only numbers, letters and underscore characters are allowed. Attribute id is always available. With this option you can set additional attributes for the shortcode. Example: start_date attribute to [%s id='xxx' start_date='2018/01/15'] shortcode. Now we can get attribute value in the snippet with the \$start_date variable. It's convenient if you want to print out different results depending on this attributes.", "insert-php" ), $shorcode_name ),
				'placeholder' => 'title, pass_attr1, pass_attr2'
				//'default'     => ''
			];
		}

		$form->add( $items );
	}

	/**
	 * Validate the snippet code before saving to database
	 *
	 * @param $snippet_code
	 * @param $snippet_type
	 *
	 * @return bool true if code produces errors
	 */
	private function validateCode( $snippet_code, $snippet_type ) {
		global $post;

		$snippet_code = stripslashes( $snippet_code );

		if ( empty( $snippet_code ) ) {
			return true;
		}

		ob_start( [ $this, 'codeErrorCallback' ] );

		$result = $snippet_type == WINP_SNIPPET_TYPE_UNIVERSAL ? eval( "?> " . $snippet_code . " <?php " ) : eval( $snippet_code );

		// elimination of errors 500 in eval() functions, with the directive display_errors = off;
		header( 'HTTP/1.0 200 OK' );

		ob_end_clean();

		do_action( 'wbcr_inp_after_execute_snippet', $post->ID, $snippet_code, $result );

		return false !== $result;
	}

	/**
	 * This friendly notice will be shown to the user in case of php errors.
	 *
	 * @param $out
	 *
	 * @return string
	 */
	private function codeErrorCallback( $out ) {
		$error = error_get_last();

		if ( is_null( $error ) ) {
			return $out;
		}

		$m = '<h3>' . __( "Don't Panic", 'code-snippets' ) . '</h3>';
		$m .= '<p>' . sprintf( __( 'The code snippet you are trying to save produced a fatal error on line %d:', 'code_snippets' ), $error['line'] ) . '</p>';
		$m .= '<strong>' . $error['message'] . '</strong>';
		$m .= '<p>' . __( 'The previous version of the snippet is unchanged, and the rest of this site should be functioning normally as before.', 'code-snippets' ) . '</p>';
		$m .= '<p>' . __( 'Please use the back button in your browser to return to the previous page and try to fix the code error.', 'code-snippets' );
		$m .= ' ' . __( 'If you prefer, you can close this page and discard the changes you just made. No changes will be made to this site.', 'code-snippets' ) . '</p>';

		return $m;
	}

	/**
	 * Filter the code by removing close php tag from beginning and adding open php tag to beginning (if not)
	 *
	 * @param $code
	 * @param $snippet_type
	 *
	 * @return mixed|string
	 */
	private function filterCode( $code, $snippet_type ) {
		if ( empty( $code ) ) {
			return $code;
		}

		if ( $snippet_type == WINP_SNIPPET_TYPE_CSS || $snippet_type == WINP_SNIPPET_TYPE_JS ) {
			$code = strip_tags( $code );
		} else if ( $snippet_type == WINP_SNIPPET_TYPE_HTML ) {
			$code = preg_replace( '/<\\?.*(\\?>|$)/Us', '', $code );
		} else if ( $snippet_type != WINP_SNIPPET_TYPE_PHP ) {
			/* Remove ?> from beginning of snippet */
			$code = preg_replace( '|^[\s]*\?>|', '', $code );

			/* Если количество закрывающих тегов не равно количеству открывающих, то добавим лишний */
			$start_count = substr_count( $code, '<?' );
			$end_count   = substr_count( $code, '?>' );

			if ( $start_count !== $end_count ) {
				if ( $start_count > $end_count ) {
					$code = $code . '?>';
				} else {
					$code = '<?php ' . $code;
				}
			}
		}

		return $code;
	}

	/**
	 * On saving form
	 *
	 * @param $postId
	 *
	 * @todo Доработать с учетом изменения имени поля для ввода кода
	 *
	 */
	public function onSavingForm( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$location = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'snippet_location', WINP_SNIPPET_AUTO_HEADER, true );
		WINP_Helper::updateMetaOption( $post_id, 'snippet_location', $location );

		$type = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'snippet_type', WINP_SNIPPET_TYPE_PHP, true );
		WINP_Helper::updateMetaOption( $post_id, 'snippet_type', $type );

		$linking = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'snippet_linking', '', true );
		WINP_Helper::updateMetaOption( $post_id, 'snippet_linking', $linking );

		// Save Conditional execution logic for the snippet
		$filters = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'snippet_filters', '' );
		$filters = ! empty( $filters ) ? json_decode( stripslashes( $filters ) ) : '';
		WINP_Helper::updateMetaOption( $post_id, 'snippet_filters', $filters );

		$changed_filters = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'changed_filters', 0 );
		$changed_filters = intval( $changed_filters );
		WINP_Helper::updateMetaOption( $post_id, 'changed_filters', $changed_filters );

		do_action( 'wbcr/inp/base_option/on_saving_form', $post_id );
	}

	/**
	 * After saving form
	 *
	 * @param int $post_id
	 */
	public function afterSavingForm( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$is_default_activate = WINP_Plugin::app()->getPopulateOption( 'activate_by_default', true );
		$snippet_scope       = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'snippet_scope', null, true );
		$snippet_type        = WINP_Helper::get_snippet_type( $post_id );
		$post_content        = get_post_field( 'post_content', $post_id );

		if ( $snippet_type != WINP_SNIPPET_TYPE_TEXT ) {
			$snippet_content = ! empty( $post_content ) ? WINP_Plugin::app()->getExecuteObject()->prepareCode( $post_content, $post_id ) : '';
		} else {
			$snippet_content = $post_content;
		}

		WINP_Helper::updateMetaOption( $post_id, 'snippet_activate', false );

		$validate = true;

		if ( $snippet_scope == 'evrywhere' || $snippet_scope == 'auto' ) {
			if ( $snippet_type != WINP_SNIPPET_TYPE_TEXT && $snippet_type != WINP_SNIPPET_TYPE_CSS && $snippet_type != WINP_SNIPPET_TYPE_JS && $snippet_type != WINP_SNIPPET_TYPE_HTML ) {
				$validate = $this->validateCode( $snippet_content, $snippet_type );
			} else {
				$validate = true;
			}
		}

		if ( $validate && $is_default_activate && WINP_Plugin::app()->currentUserCan() ) {
			WINP_Helper::updateMetaOption( $post_id, 'snippet_activate', true );
		} else {
			if ( ! defined( 'WP_SANDBOX_SCRAPING' ) ) {
				define( 'WP_SANDBOX_SCRAPING', true );
			}
			/* Display message if a parse error occurred */
			wp_redirect( add_query_arg( [
				'action'                       => 'edit',
				'post'                         => $post_id,
				'wbcr_inp_save_snippet_result' => 'code-error',
			], admin_url( 'post.php' ) ) );

			exit;
		}
	}
}
