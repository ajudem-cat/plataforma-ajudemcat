<?php

/**
 * Snippet block for Gutenberg editor
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 11.12.2018, Webcraftic
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_Gutenberg_Snippet {
	
	/**
	 * @var array
	 */
	protected $shortcode_data;
	
	/**
	 * WINP_Gutenberg_Snippet constructor.
	 */
	public function __construct() {
		$this->registerHooks();
	}
	
	private function registerHooks() {
		add_action( 'init', array( $this, 'init' ) );
	}
	
	public function init() {
		$this->shortcode_data = WINP_Helper::get_shortcode_data();
		
		if ( empty( $this->shortcode_data ) ) {
			return;
		}
		
		if ( function_exists( 'register_block_type' ) ) {
			wp_register_script( 'wp-plugin-insert-php', WINP_PLUGIN_URL . '/admin/assets/gutenberg/build/index.build.js', array(
				'wp-editor',
				'wp-blocks',
				'wp-element',
				'wp-i18n'
			), WINP_PLugin::app()->getPluginVersion() );
			
			wp_register_style( 'wp-plugin-insert-php', WINP_PLUGIN_URL . '/admin/assets/css/snippet-block.css', array() );
			
			/**
			 * Register snippets object, so it can be accessible within Gutenberg.
			 */
			wp_localize_script( 'wp-plugin-insert-php', 'winp_snippets', array(
				'data' => $this->prepared_snippets_data()
			) );
			
			register_block_type( 'wp-plugin-insert-php/winp-snippet', array(
				'editor_script'   => 'wp-plugin-insert-php',
				'editor_style'    => 'wp-plugin-insert-php',
				'render_callback' => array( $this, 'render_snippet_content' )
			) );
		}
	}
	
	/**
	 * Prepare snippets data.
	 *
	 * @return array
	 */
	public function prepared_snippets_data() {
		$prepared_object = array();
		
		foreach ( (array) $this->shortcode_data as $item ) {
			
			if ( ! isset( $item['id'] ) ) {
				continue;
			}
			
			$tags = isset( $item['snippet_tags'] ) ? $item['snippet_tags'] : array();
			
			$tags = array_values( array_filter( $tags, array( $this, 'snippet_tags_filter' ) ) );
			
			$prepared_object[ $item['id'] ] = array(
				'id'    => $item['id'],
				'title' => $item['title'],
				'type'  => isset( $item['type'] ) ? $item['type'] : '',
				'tags'  => $tags
			);
		}

		return $prepared_object;
	}
	
	public function snippet_tags_filter( $tag ) {
		return $tag !== 'id';
	}
	
	/**
	 * Renders snippets content which comes from Gutenber's render_callback.
	 *
	 * @param array $attributes Array list of attributes passed.
	 * @param string|null $content Block content.
	 *
	 * @return string
	 */
	public function render_snippet_content( $attributes, $content ) {
		
		$snipped_id = isset( $attributes['id'] ) ? $attributes['id'] : null;
		
		if ( ! is_numeric( $snipped_id ) ) {
			return '';
		}
		
		unset( $attributes['id'] );
		
		$snippets        = $this->prepared_snippets_data();
		$current_snippet = isset( $snippets[ $snipped_id ] ) ? $snippets[ $snipped_id ] : null;
		
		if ( empty( $current_snippet ) ) {
			return '';
		}
		
		$snippet_attrs = $current_snippet['tags'];
		$type          = $current_snippet['type'];
		
		$shortcode_attributes = apply_filters( 'wbcr/inp/gutenberg/shortcode_attributes', ' id="' . $snipped_id . '" ', $snipped_id );
		
		$attr_values = isset( $attributes['attrValues'] ) ? $attributes['attrValues'] : null;
		if ( ! empty( $attr_values ) ) {
			if ( empty( $snippet_attrs ) ) {
				return '';
			}
			
			if ( count( $snippet_attrs ) !== count( $attr_values ) ) {
				return '';
			}
			
			foreach ( $attr_values as $key => $value ) {
				$snippet_attr = $snippet_attrs[ $key ];
				$value        = esc_attr( $value );
				
				if ( empty( $value ) ) {
					continue;
				}
				$shortcode_attributes .= " {$snippet_attr}=\"{$value}\"";
			}
			
			$shortcode_attributes = trim( $shortcode_attributes );
		}
		
		$shortcode_name = apply_filters( 'wbcr/inp/gutenberg/shortcode_name', sprintf( "wbcr%s_snippet", ( $type === WINP_SNIPPET_TYPE_UNIVERSAL ? '' : '_' . $type ) ), $snipped_id );
		
		$shortcode = "[{$shortcode_name} {$shortcode_attributes}]";
		
		if ( ! empty( $content ) ) {
			$shortcode .= "{$content}[/{$shortcode_name}]";
		}
		
		return do_shortcode( $shortcode );
	}

	function renderShippet( $attributes ) {
		if ( empty( $attributes ) ) {
			return;
		}
		ob_start();
		?>
        <div style="background:red; color:white; padding:1em;"><?php esc_html_e( $attributes['content'] ); ?></div>
		<?php
		return ob_get_clean();
	}

}
