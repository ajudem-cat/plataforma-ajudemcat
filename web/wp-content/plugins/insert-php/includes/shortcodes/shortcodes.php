<?php
/**
 * A base shortcode for all lockers
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_SnippetShortcode extends Wbcr_FactoryShortcodes329_Shortcode {
	
	public $shortcode_name = 'wbcr_php_snippet';
	
	/**
	 * Includes assets
	 * @var bool
	 */
	public $assets_in_header = true;
	
	/**
	 * Filter attributes
	 *
	 * @param $attr
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function filterAttributes( $attr, $post_id ) {
		if ( ! empty( $attr ) ) {
			$available_tags = WINP_Helper::getMetaOption( $post_id, 'snippet_tags', null );
			
			if ( ! empty( $available_tags ) ) {
				$available_tags = explode( ',', $available_tags );
				$available_tags = array_map( 'trim', $available_tags );
			}
			
			foreach ( $attr as $name => $value ) {
				$is_allow_attr = in_array( $name, array( 'id', 'title' ) );
				$validate_name = preg_match( '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name );
				
				if ( ! $is_allow_attr && ( ( ! empty( $available_tags ) && ! in_array( $name, $available_tags ) ) || ! $validate_name ) ) {
					unset( $attr[ $name ] );
				} else {
					// issue PCS-1
					// before sending the value to the shortcode, using encodeURIComponent(val).replace(/\./g, ‘%2E’); fixes the issue. Will the next update stop this from working?
					$value = urldecode( $value );
					
					// Remove script tag
					$value = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $value );
					
					// Remove any attribute starting with "on" or xmlns
					$value = preg_replace( '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $value );
					
					// Remove javascript: and vbscript: protocols
					$value = preg_replace( '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $value );
					$value = preg_replace( '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $value );
					$value = preg_replace( '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $value );
					
					// Filter value
					$value         = filter_var( $value, FILTER_SANITIZE_SPECIAL_CHARS );
					$attr[ $name ] = filter_var( $value, FILTER_SANITIZE_MAGIC_QUOTES );
				}
			}
		}
		
		return $attr;
	}
	
	/**
	 * Get snippet id
	 *
	 * @param $attr
	 * @param $type
	 *
	 * @return int|null
	 */
	public function getSnippetId( $attr, $type ) {
		$id = isset( $attr['id'] ) ? (int) $attr['id'] : null;
		if ( $id && $type != WINP_Helper::get_snippet_type( $id ) ) {
			$id = 0;
		}
		
		return $id;
	}
	
	/**
	 * Get snippet activate
	 *
	 * @param $snippet_meta
	 *
	 * @return bool
	 */
	public function getSnippetActivate( $snippet_meta ) {
		return isset( $snippet_meta[ $this->plugin->getPrefix() . 'snippet_activate' ] ) && $snippet_meta[ $this->plugin->getPrefix() . 'snippet_activate' ][0];
	}
	
	/**
	 * Get snippet scope
	 *
	 * @param $snippet_meta
	 *
	 * @return null
	 */
	public function getSnippetScope( $snippet_meta ) {
		return isset( $snippet_meta[ $this->plugin->getPrefix() . 'snippet_scope' ] ) ? $snippet_meta[ $this->plugin->getPrefix() . 'snippet_scope' ][0] : null;
	}
	
	/**
	 * Get snippet content
	 *
	 * @param WP_Post $snippet
	 * @param array $snippet_meta
	 * @param int $id
	 *
	 * @return null|string
	 */
	public function getSnippetContent( $snippet, $snippet_meta, $id ) {
		$snippet_code = WINP_Helper::get_snippet_code($snippet);
		return WINP_Plugin::app()->getExecuteObject()->prepareCode( $snippet_code, $id );
	}
	
	/**
	 * Content render
	 *
	 * @param array $attr
	 * @param string $content
	 * @param string $tag
	 */
	public function html( $attr, $content, $tag ) {
	
	}
	
}