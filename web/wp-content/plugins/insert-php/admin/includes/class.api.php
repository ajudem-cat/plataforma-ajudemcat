<?php

/**
 * Woody API class
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 11.12.2018, Webcraftic
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_Api extends WINP_Request {

	const WINP_API_SNIPPET = 'snippet';
	const WINP_API_TYPE = 'type';

	/**
	 * WINP_Api constructor.
	 */
	public function __construct() {
		parent::__construct();

		require_once WINP_PLUGIN_DIR . '/includes/jsonmapper/class/snippet.php';
		require_once WINP_PLUGIN_DIR . '/includes/jsonmapper/class/type.php';
	}

	/**
	 * Set page parameters
	 *
	 * @param $json
	 *
	 * @return bool
	 */
	private function set_page_params( $json ) {
		if ( ! $this->check_response( $json ) ) {
			return false;
		}

		if ( empty( $json['headers'] ) ) {
			return false;
		}

		global $winp_api_total_items;

		$winp_api_total_items = isset( $json['headers']['x-pagination-total-count'] ) ? $json['headers']['x-pagination-total-count'] : 0;

		return true;
	}

	/**
	 * Get total items for last query
	 *
	 * @return int
	 */
	public function get_total_items() {
		global $winp_api_total_items;

		return $winp_api_total_items;
	}

	/**
	 * Get all snippets
	 *
	 * @param boolean $common   - если true, то выводить общие сниппеты без привязки к пользователю
	 * @param array   $parameters
	 *
	 * @return bool|mixed
	 */
	public function get_all_snippets( $common = false, $parameters = [] ) {
		$url  = $common ? 'common' : self::WINP_API_SNIPPET;
		$args = $parameters ? '&' . implode( '&', $parameters ) : '';
		$json = $this->get( $url . '?expand=type' . $args );

		$this->set_page_params( $json );

		return $this->map_objects( $json, 'WINP\JsonMapper\Snippet' );
	}

	/**
	 * Get snippet
	 *
	 * @param integer $id
	 * @param boolean $common   - если true, то запрос на общий сниппет
	 *
	 * @return bool|mixed
	 */
	public function get_snippet( $id, $common = false ) {
		$url  = $common ? 'common' : self::WINP_API_SNIPPET;
		$json = $this->get( $url . '/view?id=' . $id . '&expand=type' );

		$snippet = $this->map_object( $json, 'WINP\JsonMapper\Snippet' );
		$snippet->execute_everywhere = $snippet->execute_everywhere ? 'evrywhere' : 'shortcode';
		return $snippet;
	}

	/**
	 * Create snippet
	 *
	 * @param string  $title
	 * @param string  $content
	 * @param string  $description
	 * @param integer $type_id
	 *
	 * @return bool|mixed
	 */
	public function create_snippet( $title, $content, $description, $type_id ) {
		$args = [
			'body' => [
				'title'       => $title,
				'content'     => $content,
				'description' => $description,
				'type_id'     => $type_id,    // Тип снипета
			],
		];

		$json = $this->post( self::WINP_API_SNIPPET . '/create', $args );

		return $this->map_object( $json, 'WINP\JsonMapper\Snippet' );
	}

	/**
	 * Update snippet
	 *
	 * @param integer $id
	 * @param string  $title
	 * @param string  $content
	 * @param string  $description
	 * @param integer $type_id
	 *
	 * @return bool|mixed
	 */
	public function update_snippet( $id, $title, $content, $description, $type_id ) {
		$args = [
			'body' => [
				'title'       => $title,
				'content'     => $content,
				'description' => $description,
				'type_id'     => $type_id,    // Тип снипета
			],
		];

		$json = $this->put( self::WINP_API_SNIPPET . '/update/?id=' . $id, $args );

		return $this->map_object( $json, 'WINP\JsonMapper\Snippet' );
	}

	/**
	 * Delete snippet
	 *
	 * @param integer $id
	 *
	 * @return boolean
	 */
	public function delete_snippet( $id ) {
		$json = $this->post( self::WINP_API_SNIPPET . '/delete/?id=' . $id );

		if ( 200 == $json['response']['code'] || 204 == $json['response']['code'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all types
	 *
	 * @return object|boolean
	 */
	public function get_all_types() {
		$json = $this->get( self::WINP_API_TYPE );

		return $this->map_objects( $json, 'WINP\JsonMapper\Type' );
	}

	/**
	 * Get type
	 *
	 * @param $id
	 *
	 * @return object|boolean
	 */
	public function get_type( $id ) {
		$json = $this->get( self::WINP_API_TYPE . '/view/?id=' . $id );

		return $this->map_object( $json, 'WINP\JsonMapper\Type' );
	}

	/**
	 * Check if snippet changed
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function is_changed( $post_id ) {
		$data = get_post_meta( $post_id, WINP_Plugin::app()->getPrefix() . 'snippet_check_data', true );
		if ( ! empty( $data ) && isset( $data['content'] ) ) {
			$post = get_post( $post_id );

			return $data['content'] != $post->post_content || WINP_Helper::getMetaOption( $post->ID, 'snippet_description' ) != $data['description'];
		} else {
			return true;
		}
	}

	/**
	 * Get tipy id by type title
	 *
	 * @param $type_title
	 *
	 * @return int
	 */
	private function get_type_id_by_type( $type_title ) {
		if ( $type_title ) {
			$types = $this->get_all_types();
			if ( ! empty( $types ) && is_array( $types ) ) {
				foreach ( $types as $type ) {
					if ( $type_title == $type->slug ) {
						return $type->id;
					}
				}
			}
		}

		return 0;
	}

	/**
	 * Snippet synchronization
	 *
	 * @param $id
	 * @param $name
	 *
	 * @return bool|string
	 */
	public function synchronization( $id, $name ) {
		$post = get_post( $id );

		if ( $post ) {
			$type_id = WINP_Helper::getMetaOption( $post->ID, 'snippet_api_type', 0 );

			if ( ! $type_id ) {
				$type    = WINP_Helper::get_snippet_type( $post->ID );
				$type_id = $this->get_type_id_by_type( $type );
			}

			if ( $type_id ) {
				$title        = ! empty( $name ) ? $name : $post->post_title;
				$description  = WINP_Helper::getMetaOption( $post->ID, 'snippet_description', '' );
				$snippet_code = WINP_Helper::get_snippet_code( $post );
				$snippet      = $this->create_snippet( $title, $snippet_code, $description, $type_id );

				if ( $snippet ) {
					$data = [
						'content'     => $snippet_code,
						'description' => $description,
					];
					WINP_Helper::updateMetaOption( $post->ID, 'snippet_check_data', $data );
					WINP_Helper::updateMetaOption( $post->ID, 'snippet_api_snippet', $snippet->id );
					WINP_Helper::updateMetaOption( $post->ID, 'snippet_api_type', $type_id );

					return true;
				}

				return __( 'Synchronization snippet error', 'insert-php' );
			}

			return __( 'Unknown sippet type', 'insert-php' );
		}

		return false;
	}

	/**
	 * Create snippet from library
	 *
	 * @param integer $snippet_id
	 * @param integer $post_id
	 * @param boolean $common
	 *
	 * @return bool|integer
	 */
	public function create_from_library( $snippet_id, $post_id, $common ) {
		if ( $snippet_id ) {
			$snippet = $this->get_snippet( $snippet_id, $common );
			if ( $snippet ) {
				if ( ! $post_id ) {
					$post    = [
						'post_title'   => $snippet->title,
						'post_content' => $snippet->content,
						'post_type'    => WINP_SNIPPETS_POST_TYPE,
						'post_status'  => 'publish',
					];
					$post_id = wp_insert_post( $post );
					WINP_Helper::updateMetaOption( $post_id, 'snippet_activate', 0 );
				} else {
					$post = [
						'ID'           => $post_id,
						'post_title'   => $snippet->title,
						'post_content' => $snippet->content,
					];
					wp_update_post( $post );
				}

				WINP_Helper::updateMetaOption( $post_id, 'snippet_api_snippet', $snippet_id );
				WINP_Helper::updateMetaOption( $post_id, 'snippet_type', $snippet->type->slug );
				WINP_Helper::updateMetaOption( $post_id, 'snippet_api_type', $snippet->type_id );
				WINP_Helper::updateMetaOption( $post_id, 'snippet_description', $snippet->description );
				WINP_Helper::updateMetaOption( $post_id, 'snippet_scope', $snippet->execute_everywhere );

				return $post_id;
			}
		}

		return false;
	}

}
