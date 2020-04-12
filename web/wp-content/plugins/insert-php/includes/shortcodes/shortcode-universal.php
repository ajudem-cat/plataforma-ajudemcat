<?php
/**
 * Universal Shortcode
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_SnippetShortcodeUniversal extends WINP_SnippetShortcode {

	public $shortcode_name = 'wbcr_snippet';

	/**
	 * Content render
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 */
	public function html( $attr, $content, $tag ) {
		$id = $this->getSnippetId( $attr, WINP_SNIPPET_TYPE_UNIVERSAL );

		if ( ! $id ) {
			echo '<span style="color:red">' . __( '[' . esc_html( $tag ) . ']: PHP snippets error (not passed the snippet ID)', 'insert-php' ) . '</span>';

			return;
		}

		$snippet      = get_post( $id );
		$snippet_meta = get_post_meta( $id, '' );

		if ( ! $snippet || empty( $snippet_meta ) ) {
			return;
		}

		$attr = $this->filterAttributes( $attr, $id );

		// Let users pass arbitrary variables, through shortcode attributes.
		// @since 2.0.5
		extract( $attr, EXTR_SKIP );

		$is_activate     = $this->getSnippetActivate( $snippet_meta );
		$snippet_content = $this->getSnippetContent( $snippet, $snippet_meta, $id );
		$snippet_scope   = $this->getSnippetScope( $snippet_meta );
		$is_condition    = WINP_Plugin::app()->getExecuteObject()->checkCondition( $id );

		if ( ! $is_activate || empty( $snippet_content ) || $snippet_scope != 'shortcode' || ! $is_condition || WINP_Helper::is_safe_mode() ) {
			return;
		}

		eval( "?>" . $snippet_content . "<?php " );
	}

}