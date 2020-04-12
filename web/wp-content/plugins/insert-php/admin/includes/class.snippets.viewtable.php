<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_SnippetsViewTable extends Wbcr_FactoryViewtables410_Viewtable {

	public function configure() {
		/**
		 * Columns
		 */
		$this->columns->clear();
		//$this->columns->add('status', __('Status', 'insert-php'));
		$this->columns->add( 'title', __( 'Snippet title', 'insert-php' ) );
		$this->columns->add( 'winp_description', __( 'Description', 'insert-php' ) );
		$this->columns->add( 'winp_actions', __( 'Status', 'insert-php' ) );
		$this->columns->add( 'winp_where_use', __( 'Where use?', 'insert-php' ) );
		$this->columns->add( 'winp_taxonomy-' . WINP_SNIPPETS_TAXONOMY, __( 'Tags', 'insert-php' ) );
		$this->columns->add( 'winp_snippet_type', '' );

		/**
		 * Scripts & styles
		 */
		$this->styles->add( WINP_PLUGIN_URL . '/admin/assets/css/list-table.css' );
		$this->runActions();
	}


	/**
	 * Column 'Type'
	 *
	 * @param $post
	 */
	public function columnWinp_snippet_type( $post ) {
		$type  = WINP_Helper::getMetaOption( $post->ID, 'snippet_type', WINP_SNIPPET_TYPE_PHP );
		$class = 'wbcr-inp-type-' . esc_attr( $type );
		$type  = $type == 'universal' ? 'uni' : $type;

		echo '<div class="wbcr-inp-snippet-type-label ' . esc_attr( $class ) . '">' . esc_html( $type ) . '</div>';
	}

	public function columnWinp_description( $post ) {
		echo esc_html( WINP_Helper::getMetaOption( $post->ID, 'snippet_description' ) );
	}

	/**
	 * Column 'Where_use'
	 *
	 * @param $post
	 */
	public function columnWinp_where_use( $post ) {
		$snippet_scope = WINP_Helper::getMetaOption( $post->ID, 'snippet_scope' );

		if ( $snippet_scope == 'evrywhere' ) {
			echo __( 'Run everywhere', 'insert-php' );
		} else if ( $snippet_scope == 'auto' ) {
			$items = [
				WINP_SNIPPET_AUTO_HEADER           => __( 'Header', 'insert-php' ),
				WINP_SNIPPET_AUTO_FOOTER           => __( 'Footer', 'insert-php' ),
				WINP_SNIPPET_AUTO_BEFORE_POST      => __( 'Insert Before Post', 'insert-php' ),
				WINP_SNIPPET_AUTO_BEFORE_CONTENT   => __( 'Insert Before Content', 'insert-php' ),
				WINP_SNIPPET_AUTO_BEFORE_PARAGRAPH => __( 'Insert Before Paragraph', 'insert-php' ),
				WINP_SNIPPET_AUTO_AFTER_PARAGRAPH  => __( 'Insert After Paragraph', 'insert-php' ),
				WINP_SNIPPET_AUTO_AFTER_CONTENT    => __( 'Insert After Content', 'insert-php' ),
				WINP_SNIPPET_AUTO_AFTER_POST       => __( 'Insert After Post', 'insert-php' ),
				WINP_SNIPPET_AUTO_BEFORE_EXCERPT   => __( 'Insert Before Excerpt', 'insert-php' ),
				WINP_SNIPPET_AUTO_AFTER_EXCERPT    => __( 'Insert After Excerpt', 'insert-php' ),
				WINP_SNIPPET_AUTO_BETWEEN_POSTS    => __( 'Between Posts', 'insert-php' ),
				WINP_SNIPPET_AUTO_BEFORE_POSTS     => __( 'Before post', 'insert-php' ),
				WINP_SNIPPET_AUTO_AFTER_POSTS      => __( 'After post', 'insert-php' ),
			];

			$snippet_location = WINP_Helper::getMetaOption( $post->ID, 'snippet_location', '' );

			switch ( $snippet_location ) {
				case WINP_SNIPPET_AUTO_HEADER:
				case WINP_SNIPPET_AUTO_FOOTER:
					$text = __( 'Everywhere', 'insert-php' ) . '[' . $items[ $snippet_location ] . ']';
					break;

				case WINP_SNIPPET_AUTO_BEFORE_POST:
				case WINP_SNIPPET_AUTO_BEFORE_CONTENT:
				case WINP_SNIPPET_AUTO_BEFORE_PARAGRAPH:
				case WINP_SNIPPET_AUTO_AFTER_PARAGRAPH:
				case WINP_SNIPPET_AUTO_AFTER_CONTENT:
				case WINP_SNIPPET_AUTO_AFTER_POST:
					$text = __( 'Posts, Pages, Custom post types', 'insert-php' ) . '[' . $items[ $snippet_location ] . ']';
					break;

				case WINP_SNIPPET_AUTO_BEFORE_EXCERPT:
				case WINP_SNIPPET_AUTO_AFTER_EXCERPT:
				case WINP_SNIPPET_AUTO_BETWEEN_POSTS:
				case WINP_SNIPPET_AUTO_BEFORE_POSTS:
				case WINP_SNIPPET_AUTO_AFTER_POSTS:
					$text = __( 'Categories, Archives, Tags, Taxonomies', 'insert-php' ) . '[' . $items[ $snippet_location ] . ']';
					break;

				default:
					$text = __( 'Everywhere', 'insert-php' );
			}

			echo __( 'Automatic insertion', 'insert-php' ) . ': ' . esc_html( $text );
		} else {
			$snippet_type = WINP_Helper::get_snippet_type( $post->ID );
			$snippet_type = ( $snippet_type == WINP_SNIPPET_TYPE_UNIVERSAL ? '' : $snippet_type . '_' );

			echo esc_html( apply_filters( 'wbcr/inp/viewtable/where_use', '[wbcr_' . $snippet_type . 'snippet id="' . $post->ID . '"]', $post->ID ) );
		}
	}

	/**
	 * Column 'Actions'
	 *
	 * @param $post
	 */
	public function columnWinp_actions( $post ) {
		$post_id     = (int) $post->ID;
		$is_activate = (int) WINP_Helper::getMetaOption( $post_id, 'snippet_activate', 0 );
		$icon        = 'dashicons-controls-play';

		if ( $is_activate ) {
			$icon = 'dashicons-controls-pause';
		}

		echo '<a class="wbcr-inp-enable-snippet-button button" href="' . wp_nonce_url( admin_url( 'edit.php?post_type=' . WINP_SNIPPETS_POST_TYPE . '&amp;post=' . $post_id . '&amp;action=wbcr_inp_activate_snippet' ), 'wbcr_inp_snippert_' . $post_id . '_action_nonce' ) . '"><span class="dashicons ' . esc_attr( $icon ) . '"></span></a>';
	}

	/*
	 * Activate/Deactivate snippet
	 */
	protected function runActions() {
		if ( WINP_Plugin::app()->request->get( 'post_type', '', true ) == WINP_SNIPPETS_POST_TYPE ) {
			$post   = WINP_Plugin::app()->request->get( 'post', 0 );
			$action = WINP_Plugin::app()->request->get( 'action', '', 'sanitize_key' );

			if ( ! empty( $action ) && ! empty( $post ) && 'wbcr_inp_activate_snippet' == $action ) {
				$post_id = (int) $post;
				$wpnonce = WINP_Plugin::app()->request->get( '_wpnonce', '' );

				if ( ( ! empty( $wpnonce ) && ! wp_verify_nonce( $wpnonce, 'wbcr_inp_snippert_' . $post_id . '_action_nonce' ) ) || ! WINP_Plugin::app()->currentUserCan() ) {
					wp_die( 'Permission error. You can not edit this page.' );
				}

				$is_activate   = (int) WINP_Helper::getMetaOption( $post_id, 'snippet_activate', 0 );
				$snippet_scope = WINP_Helper::getMetaOption( $post_id, 'snippet_scope' );
				$snippet_type  = WINP_Helper::get_snippet_type( $post_id );

				/**
				 * Prevent activation of the snippet if it contains an error. This will not allow the user to break his site.
				 *
				 * @since 2.0.5
				 */
				if ( ( 'evrywhere' == $snippet_scope || 'auto' == $snippet_scope ) && $snippet_type != WINP_SNIPPET_TYPE_TEXT && $snippet_type != WINP_SNIPPET_TYPE_CSS && $snippet_type != WINP_SNIPPET_TYPE_JS && ! $is_activate ) {
					if ( WINP_Plugin::app()->getExecuteObject()->getSnippetError( $post_id ) ) {
						wp_safe_redirect( add_query_arg( [
							'action'                       => 'edit',
							'post'                         => $post_id,
							'wbcr_inp_save_snippet_result' => 'code-error',
						], admin_url( 'post.php' ) ) );
						exit;
					}
				}

				$status = ! $is_activate;

				update_post_meta( $post_id, $this->plugin->getPrefix() . 'snippet_activate', $status );

				$redirect_url = add_query_arg( [
					'post_type'                => WINP_SNIPPETS_POST_TYPE,
					'wbcr_inp_snippet_updated' => 1,
				], admin_url( 'edit.php' ) );

				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}
}
