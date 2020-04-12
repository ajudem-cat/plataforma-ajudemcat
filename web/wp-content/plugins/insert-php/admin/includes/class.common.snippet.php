<?php
/**
 * Common functions for snippets
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 16.11.2018, Webcraftic
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_Common_Snippet {

	/**
	 * WINP_Common_Snippet constructor.
	 */
	public function __construct() {
	}

	/**
	 * Register hooks
	 */
	public function registerHooks() {
		add_action( 'current_screen', [ $this, 'currentScreen' ] );
		add_action( 'edit_form_before_permalink', [ $this, 'editFormBeforePermalink' ] );
		add_action( 'admin_notices', [ $this, 'createUploadsDirectory' ] );
		add_action( 'before_delete_post', [ $this, 'beforeDeletePost' ] );
		add_action( 'save_post', [ $this, 'savePost' ] );
		add_action( 'save_post_' . WINP_SNIPPETS_POST_TYPE, [ $this, 'save_snippet' ], 10, 3 );
		add_action( 'auto-draft_to_publish', [ $this, 'publish_snippet' ] );

		add_filter( 'script_loader_src', [ $this, 'unload_scripts' ], 10, 2 );
	}

	/**
	 * Create the custom-css-js dir in uploads directory
	 *
	 * Show a message if the directory is not writable
	 *
	 * Create an empty index.php file inside
	 */
	public function createUploadsDirectory() {
		$current_screen = get_current_screen();

		// Check if we are editing a custom-css-js post
		if ( $current_screen->base != 'post' || $current_screen->post_type != WINP_SNIPPETS_POST_TYPE ) {
			return false;
		}

		$dir = WINP_UPLOAD_DIR;

		// Create the dir if it doesn't exist
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		// Show a message if it couldn't create the dir
		if ( ! file_exists( $dir ) ) { ?>
            <div class="notice notice-error is-dismissible">
                <p><?php printf( __( 'The %s directory could not be created', 'insert-php' ), '<b>winp-css-js</b>' ); ?></p>
                <p><?php _e( 'Please run the following commands in order to make the directory', 'insert-php' ); ?>:
                    <br/><strong>mkdir <?php echo $dir; ?>; </strong><br/><strong>chmod 777 <?php echo $dir; ?>
                        ;</strong></p>
            </div>
			<?php return;
		}

		// Show a message if the dir is not writable
		if ( ! wp_is_writable( $dir ) ) { ?>
            <div class="notice notice-error is-dismissible">
                <p><?php printf( __( 'The %s directory is not writable, therefore the CSS and JS files cannot be saved.', 'insert-php' ), '<b>' . $dir . '</b>' ); ?></p>
                <p><?php _e( 'Please run the following command to make the directory writable', 'insert-php' ); ?>:<br/><strong>chmod
                        777 <?php echo $dir; ?> </strong></p>
            </div>
			<?php return;
		}

		// Write a blank index.php
		if ( ! file_exists( $dir . '/index.php' ) ) {
			$content = '<?php' . PHP_EOL . '// Silence is golden.';
			@file_put_contents( $dir . '/index.php', $content );
		}
	}

	/**
	 * Add quick buttons
	 */
	public function currentScreenEdit() {
		$strings = [
			'php'       => __( 'Php snippet', 'insert-php' ),
			'text'      => __( 'Text snippet', 'insert-php' ),
			'css'       => __( 'Css snippet', 'insert-php' ),
			'js'        => __( 'Js snippet', 'insert-php' ),
			'html'      => __( 'Html snippet', 'insert-php' ),
			'universal' => __( 'Universal snippet', 'insert-php' ),
		];
		$url     = 'post-new.php?post_type=' . WINP_SNIPPETS_POST_TYPE . '&winp_item=';
		?>
        <style>
            .wrap .winp-page-title-action {
                margin-left: 4px;
                padding: 4px 8px;
                position: relative;
                top: -3px;
                text-decoration: none;
                border: 1px solid #ccc;
                border-radius: 2px;
                background: #f7f7f7;
                text-shadow: none;
                font-weight: 600;
                font-size: 13px;
                line-height: normal;
                color: #0073aa;
                cursor: pointer;
            }
        </style>
        <script type="text/javascript">
			/* <![CDATA[ */
			jQuery(window).ready(function($) {
				$('#wpbody-content a.page-title-action').hide();
				var h1 = '<?php _e( 'Woody snippets', 'insert-php' ); ?> ';
				h1 += ' <select class="winp-page-title-action">';
				h1 += '<option value="<?php echo $url; ?>php"><?php echo $strings['php']; ?></option>';
				h1 += '<option value="<?php echo $url; ?>text"><?php echo $strings['text']; ?></option>';
				h1 += '<option value="<?php echo $url; ?>css"><?php echo $strings['css']; ?></option>';
				h1 += '<option value="<?php echo $url; ?>js"><?php echo $strings['js']; ?></option>';
				h1 += '<option value="<?php echo $url; ?>html"><?php echo $strings['html']; ?></option>';
				h1 += '<option value="<?php echo $url; ?>universal"><?php echo $strings['universal']; ?></option>';
				h1 += '</select>';
				h1 += '<a href="#" id="winp-add-snippet-action" class="page-title-action"><?php _e( 'Add', 'insert-php' ); ?></a>';
				$('#wpbody-content h1').html(h1);
				$('#winp-add-snippet-action').click(function() {
					window.location.href = $('select.winp-page-title-action').val();
				});
			});
        </script>
		<?php
	}

	/**
	 * Add quick buttons
	 */
	public function currentScreenPost() {
		$strings = [
			'add'  => [
				'php'       => __( 'Php snippet', 'insert-php' ),
				'text'      => __( 'Text snippet', 'insert-php' ),
				'css'       => __( 'Css snippet', 'insert-php' ),
				'js'        => __( 'Js snippet', 'insert-php' ),
				'html'      => __( 'Html snippet', 'insert-php' ),
				'universal' => __( 'Universal snippet', 'insert-php' ),
			],
			'edit' => [
				'php'       => __( 'Edit php snippet', 'insert-php' ),
				'text'      => __( 'Edit text snippet', 'insert-php' ),
				'css'       => __( 'Edit css snippet', 'insert-php' ),
				'js'        => __( 'Edit js snippet', 'insert-php' ),
				'html'      => __( 'Edit html snippet', 'insert-php' ),
				'universal' => __( 'Edit universal snippet', 'insert-php' ),
			]
		];

		$post_id = WINP_Plugin::app()->request->get( 'post', null );
		if ( ! empty( $post_id ) ) {
			$action = 'edit';
		} else {
			$action = 'add';
		}
		$type = WINP_Helper::get_snippet_type( $post_id );
		$html = $strings[ $action ][ $type ];

		if ( 'edit' == $action ) {
			$url  = 'post-new.php?post_type=' . WINP_SNIPPETS_POST_TYPE . '&winp_item=';
			$html .= ' <select class="winp-page-title-action">';
			$html .= '<option value="' . $url . 'php">' . $strings['add']['php'] . '</option>';
			$html .= '<option value="' . $url . 'text">' . $strings['add']['text'] . '</option>';
			$html .= '<option value="' . $url . 'css">' . $strings['add']['css'] . '</option>';
			$html .= '<option value="' . $url . 'js">' . $strings['add']['js'] . '</option>';
			$html .= '<option value="' . $url . 'html">' . $strings['add']['html'] . '</option>';
			$html .= '<option value="' . $url . 'universal">' . $strings['add']['universal'] . '</option>';
			$html .= '</select>';
			$html .= '<a href="#" id="winp-add-snippet-action" class="page-title-action">' . __( 'Add', 'insert-php' ) . '</a>';
		} ?>
        <script type="text/javascript">
			/* <![CDATA[ */
			jQuery(window).ready(function($) {
				$('#wpbody-content a.page-title-action').hide();
				$('#wpbody-content h1').html('<?php echo $html; ?>');
				$('#winp-add-snippet-action').click(function() {
					window.location.href = $('select.winp-page-title-action').val();
				});
			});
			/* ]]> */
        </script>
		<?php
	}

	/**
	 * Add quick buttons
	 *
	 * @param $current_screen
	 */
	public function currentScreen( $current_screen ) {
		if ( WINP_SNIPPETS_POST_TYPE !== $current_screen->post_type ) {
			return;
		}

		// Код виджета поддержки пока что нужно скрыть
		// add_action( 'admin_footer', array( $this, 'admin_footer' ) );

		if ( $current_screen->base == 'post' ) {
			add_action( 'admin_head', [ $this, 'currentScreenPost' ] );
		}

		if ( $current_screen->base == 'edit' ) {
			add_action( 'admin_head', [ $this, 'currentScreenEdit' ] );
		}
	}

	/**
	 * Show the Permalink edit form
	 *
	 * @param string $filename
	 * @param string $permalink
	 * @param string $filetype
	 */
	public function editFormBeforePermalink( $filename = '', $permalink = '', $filetype = 'css' ) {
		$filetype = WINP_Plugin::app()->request->get( 'winp_item', $filetype, true );

		if ( ! in_array( $filetype, [ 'css', 'js' ] ) ) {
			return;
		}

		if ( ! is_string( $filename ) ) {
			global $post;

			if ( ! is_object( $post ) ) {
				return;
			}

			if ( WINP_SNIPPETS_POST_TYPE !== $post->post_type ) {
				return;
			}

			$post             = $filename;
			$slug             = WINP_Helper::getMetaOption( $post->ID, 'css_js_slug', '' );
			$default_filetype = WINP_Helper::getMetaOption( $post->ID, 'filetype', '' );
			if ( $default_filetype ) {
				$filetype = $default_filetype;
			} else {
				$filetype = WINP_Helper::get_snippet_type( $post->ID );
			}

			if ( ! in_array( $filetype, [ 'css', 'js' ] ) ) {
				return;
			}

			if ( ! @file_exists( WINP_UPLOAD_DIR . '/' . $slug . '.' . $filetype ) ) {
				$slug = false;
			}
			$filename = ( $slug ) ? $slug : $post->ID;
		}

		if ( empty( $permalink ) ) {
			$permalink = WINP_UPLOAD_URL . '/' . $filename . '.' . $filetype;
		} ?>
        <div class="inside">
            <div id="edit-slug-box" class="hide-if-no-js">
                <strong><?php _e( 'Permalink', 'insert-php' ) ?>:</strong>
                <span id="sample-permalink"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( WINP_UPLOAD_URL ) . '/'; ?><span id="editable-post-name"><?php echo esc_html( $filename ); ?></span>.<?php echo esc_html( $filetype ); ?></a></span>
                <span id="winp-edit-slug-buttons"><button type="button" class="winp-edit-slug button button-small hide-if-no-js" aria-label="<?php _e( 'Edit permalink', 'insert-php' ) ?>"><?php _e( 'Edit', 'insert-php' ) ?></button></span>
                <span id="editable-post-name-full"><?php echo esc_html( $filename ); ?></span>
            </div>
			<?php wp_nonce_field( 'winp-permalink', 'winp-permalink-nonce' ); ?>
        </div>
		<?php
	}

	/**
	 * Remove the JS/CSS file from the disk when deleting the post
	 *
	 * @param $postid
	 */
	public function beforeDeletePost( $postid ) {
		global $post;

		if ( ! is_object( $post ) ) {
			return;
		}
		if ( WINP_SNIPPETS_POST_TYPE !== $post->post_type ) {
			return;
		}
		if ( ! wp_is_writable( WINP_UPLOAD_DIR ) ) {
			return;
		}

		$default_filetype = WINP_Helper::get_snippet_type( $post->ID );
		$filetype         = WINP_Helper::getMetaOption( $postid, 'filetype', $default_filetype );

		if ( ! in_array( $filetype, [ 'css', 'js' ] ) ) {
			return;
		}

		$slug      = WINP_Helper::getMetaOption( $postid, 'css_js_slug' );
		$file_name = $postid . '.' . $filetype;

		@unlink( WINP_UPLOAD_DIR . '/' . $file_name );

		if ( ! empty( $slug ) ) {
			@unlink( WINP_UPLOAD_DIR . '/' . $slug . '.' . $filetype );
		}
	}

	/**
	 * Save post
	 *
	 * @param $post_id
	 */
	public function savePost( $post_id ) {
		$nonce = WINP_Plugin::app()->request->post( 'winp-permalink-nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'winp-permalink' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( WINP_SNIPPETS_POST_TYPE != WINP_Plugin::app()->request->post( 'post_type' ) ) {
			return;
		}

		$default_filetype = WINP_Helper::get_snippet_type( $post_id );
		$filetype         = WINP_Helper::getMetaOption( $post_id, 'filetype', $default_filetype );

		if ( ! in_array( $filetype, [ 'css', 'js' ] ) ) {
			return;
		}

		$plugin_title = WINP_Plugin::app()->getPluginTitle();
		$before       = $after = '';

		$snippet_linking = WINP_Plugin::app()->request->post( WINP_Plugin::app()->getPrefix() . 'snippet_linking' );
		$linking         = ! empty( $snippet_linking ) ? $snippet_linking : 'external';
		$content         = get_post( $post_id )->post_content;

		// Save the Custom Code in a file in `wp-content/uploads/winp-css-js`
		if ( 'inline' == $linking ) {
			$before = '<!-- start ' . $plugin_title . ' CSS and JS -->' . PHP_EOL;
			$after  = '<!-- end ' . $plugin_title . ' CSS and JS -->' . PHP_EOL;

			if ( 'css' == $filetype ) {
				$before .= '<style type="text/css">' . PHP_EOL;
				$after  = '</style>' . PHP_EOL . $after;
			}

			if ( 'js' == $filetype ) {
				if ( ! preg_match( '/<script\b[^>]*>([\s\S]*?)<\/script>/im', $content ) ) {
					$before .= '<script type="text/javascript">' . PHP_EOL;
					$after  = PHP_EOL . '</script>' . PHP_EOL . $after;
				} else {
					// the content has a <script> tag, then remove the comments so they don't show up on the frontend
					$content = preg_replace( '@/\*[\s\S]*?\*/@', '', $content );
				}
			}
		}

		if ( 'external' == $linking ) {
			$before = '/******* Do not edit this file *******' . PHP_EOL . $plugin_title . ' CSS and JS' . PHP_EOL . 'Saved: ' . date( 'M d Y | H:i:s' ) . ' */' . PHP_EOL;

			// Save version for js and css file
			WINP_Helper::updateMetaOption( $post_id, 'css_js_version', time() );
		}

		if ( wp_is_writable( WINP_UPLOAD_DIR ) ) {
			$file_content = $before . $content . $after;

			// save the file as the Permalink slug
			$slug = WINP_Helper::getMetaOption( $post_id, 'css_js_slug' );
			if ( $slug ) {
				$file_name = $slug . '.' . $filetype;
				$file_slug = $slug;
			} else {
				$file_name = $post_id . '.' . $filetype;
				$file_slug = $post_id;
			}

			// Delete old file
			$old_slug = WINP_Helper::getMetaOption( $post_id, 'css_js_exist_slug' );
			if ( $old_slug ) {
				@unlink( WINP_UPLOAD_DIR . '/' . $old_slug . '.' . $filetype );
			}

			// Save exist file slug
			WINP_Helper::updateMetaOption( $post_id, 'css_js_exist_slug', $file_slug );

			@file_put_contents( WINP_UPLOAD_DIR . '/' . $file_name, $file_content );
		}
	}

	/**
	 * Action for pre saved snippet.
	 * Если это не обновление поста, если это "черновик", и есть параметр с id сниппета, то заполняем данные сниппета для просмотра
	 *
	 * @param $post_ID
	 * @param $current_post
	 * @param $update
	 */
	public function save_snippet( $post_ID, $current_post, $update ) {
		$snippet_id = WINP_Plugin::app()->request->get( 'snippet_id' );
		$common     = WINP_Plugin::app()->request->get( 'common', false );

		if ( ! $update && 'auto-draft' == $current_post->post_status && ! empty( $snippet_id ) && WINP_SNIPPETS_POST_TYPE == $current_post->post_type ) {
			$snippet        = [];
			$saved_snippets = get_user_meta( get_current_user_id(), WINP_Plugin::app()->getPrefix() . 'current_snippets', true );

			if ( ! empty( $saved_snippets ) && isset( $saved_snippets[ $snippet_id ] ) ) {
				$snippet = $saved_snippets[ $snippet_id ];
			}

			if ( empty( $snippet ) ) {
				$_snippet = WINP_Plugin::app()->get_api_object()->get_snippet( $snippet_id, $common );
				if ( ! empty( $_snippet ) ) {
					$snippet = [
						'title'   => $_snippet->title,
						'desc'    => $_snippet->description,
						'type'    => $_snippet->type->slug,
						'content' => $_snippet->content,
						'type_id' => $_snippet->type_id,
						'scope'   => $_snippet->execute_everywhere,
					];
				}
			}

			if ( ! empty( $snippet ) ) {
				$post_data = [
					'ID'           => $post_ID,
					'post_title'   => $snippet['title'],
					'post_content' => $snippet['content'],
				];
				wp_update_post( $post_data );

				WINP_Helper::updateMetaOption( $post_ID, 'snippet_api_snippet', $snippet_id );
				WINP_Helper::updateMetaOption( $post_ID, 'snippet_type', $snippet['type'] );
				WINP_Helper::updateMetaOption( $post_ID, 'snippet_api_type', $snippet['type_id'] );
				WINP_Helper::updateMetaOption( $post_ID, 'snippet_description', $snippet['desc'] );
				WINP_Helper::updateMetaOption( $post_ID, 'snippet_draft', true );
				WINP_Helper::updateMetaOption( $post_ID, 'snippet_scope', $snippet['scope'] );

				wp_redirect( admin_url( 'post.php?post=' . $post_ID . '&action=edit' ) );
			}
		}
	}

	/**
	 * Delete auto-draft status after post snippet is publishing
	 *
	 * @param $post
	 */
	public function publish_snippet( $post ) {
		if ( WINP_SNIPPETS_POST_TYPE == $post->post_type ) {
			delete_post_meta( $post->ID, WINP_Plugin::app()->getPrefix() . 'snippet_draft' );
		}
	}

	/**
	 * Action admin_footer
	 */
	public function admin_footer() {
		?>
        <script type="text/javascript">!function(e, t, n) {
				function a() {
					var e = t.getElementsByTagName("script")[0], n = t.createElement("script");
					n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
				}

				if( e.Beacon = n = function(t, n, a) {
					e.Beacon.readyQueue.push({
						method: t,
						options: n,
						data: a
					})
				}, n.readyQueue = [], "complete" === t.readyState ) {
					return a();
				}
				e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
			}(window, document, window.Beacon || function() {
			});</script>
        <script type="text/javascript">window.Beacon('init', '1a4078fd-3e77-4692-bcfa-47bb4da0cee5')</script>
		<?php
	}

	/**
	 * Unload specific scrips
	 *
	 * @param string $src
	 * @param string $handle
	 *
	 * @return bool|string
	 */
	public function unload_scripts( $src, $handle ) {
		global $post;

		// Check if we are editing a snippet post
		if ( is_admin() && ! empty( $post ) && $post->post_type == WINP_SNIPPETS_POST_TYPE ) {
			// Unload ckeditor.js from theme The Rex
			if ( 'bk-ckeditor-js' == $handle ) {
				return false;
			}
		}

		return $src;
	}

}