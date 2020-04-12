<?php

/**
 * Import snippet
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 16.11.2018, Webcraftic
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_Import_Snippet {

	/**
	 * WINP_Export_Snippet constructor.
	 */
	public function __construct() {
		$this->registerHooks();
	}

	/**
	 * Register hooks
	 */
	public function registerHooks() {
		add_action( 'admin_init', [ $this, 'import_snippets_proccess' ] );
	}

	/**
	 * Process the uploaded import files
	 *
	 * @uses import_snippets() to process the import file
	 * @uses wp_redirect() to pass the import results to the page
	 * @uses add_query_arg() to append the results to the current URI
	 */
	public function import_snippets_proccess() {
		if ( isset( $_POST['wbcr_inp_import_form_action'] ) ) {

			if ( ! WINP_Plugin::app()->currentUserCan() ) {
				wp_die( __( 'Sorry, you are not allowed to import snippets as this user.' ), __( 'You need a higher level of permission.' ), 403 );
			}

			check_admin_referer( 'wbcr_inp_import_form', 'wbcr_inp_import_form_nonce_field' );

			if ( ! isset( $_FILES['wbcr_inp_import_files'] ) || ! count( $_FILES['wbcr_inp_import_files'] ) || ! isset( $_FILES['wbcr_inp_import_files']['tmp_name'][0] ) || empty( $_FILES['wbcr_inp_import_files']['tmp_name'][0] ) ) {
				return;
			}

			$url = remove_query_arg( [ 'wbcr_inp_error', 'wbcr_inp_imported' ] );

			// Only ine files for free version
			if ( ! WINP_Plugin::app()->get_api_object()->is_key() && count( $_FILES['wbcr_inp_import_files']['tmp_name'] ) > 1 ) {
				$url = add_query_arg( [ 'wbcr_import_error' => true ], $url );
				wp_redirect( esc_url_raw( $url ) );
				exit;
			}

			$count      = 0;
			$uploads    = $_FILES['wbcr_inp_import_files'];
			$dup_action = WINP_Plugin::app()->request->post( 'duplicate_action', 'ignore', true );
			$error      = false;

			foreach ( $uploads['tmp_name'] as $i => $import_file ) {
				$ext       = pathinfo( $uploads['name'][ $i ] );
				$ext       = $ext['extension'];
				$mime_type = $uploads['type'][ $i ];

				if ( 'json' === $ext || 'application/json' === $mime_type ) {
					$result = $this->importSnippet( $import_file, $dup_action );
				} else {
					$result = apply_filters( 'wbcr/inp/import/snippet', false, $ext, $mime_type, $import_file, $dup_action );
				}

				if ( false === $result || - 1 === $result ) {
					$error = true;
				} else {
					$count += count( $result );
				}
			}

			$url = add_query_arg( $error ? [ 'wbcr_inp_error' => true ] : [ 'wbcr_inp_imported' => $count ], $url );
			wp_redirect( esc_url_raw( $url ) );
			exit;
		}
	}

	/**
	 * Import snippets
	 *
	 * @param $file
	 * @param $dup_action
	 *
	 * @return int|bool|array
	 */
	public function importSnippet( $file, $dup_action ) {
		if ( ! file_exists( $file ) || ! is_file( $file ) ) {
			return false;
		}

		$raw_data = file_get_contents( $file );
		$data     = json_decode( $raw_data, true );
		$snippets = isset( $data['snippets'] ) ? $data['snippets'] : [];

		$imported = $this->saveImportedSnippets( $snippets, $dup_action );

		return $imported;
	}

	/**
	 * Update taxonomy tags
	 *
	 * @param $snippet_id
	 * @param $tags
	 */
	private function updateTaxonomyTags( $snippet_id, $tags ) {
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag_slug ) {
				$term = get_term_by( 'slug', $tag_slug, WINP_SNIPPETS_TAXONOMY );
				if ( $term ) {
					wp_set_post_terms( $snippet_id, [ $term->term_id ], WINP_SNIPPETS_TAXONOMY, true );
				}
			}
		}
	}

	/**
	 * Update post meta
	 *
	 * @param $post_id
	 * @param $meta_name
	 * @param $meta_value
	 */
	private function updateMeta( $post_id, $meta_name, $meta_value ) {
		update_post_meta( $post_id, WINP_Plugin::app()->getPrefix() . $meta_name, $meta_value );
	}

	/**
	 * Save snippet
	 *
	 * @param $snippet
	 *
	 * @return int
	 */
	private function saveSnippet( $snippet ) {
		$content = $snippet['content'];

		if ( WINP_SNIPPET_TYPE_TEXT != $snippet['type'] ) {
			$content = empty( $content ) && isset( $snippet['code'] ) && ! empty( $snippet['code'] ) ? $snippet['code'] : $content;
		}

		$data = [
			'post_title'   => $snippet['title'],
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => WINP_SNIPPETS_POST_TYPE
		];

		if ( isset( $snippet['id'] ) && 0 != $snippet['id'] ) {
			$data['ID'] = $snippet['id'];
		}

		$snippet['id'] = wp_insert_post( $data );

		$this->updateMeta( $snippet['id'], 'snippet_location', $snippet['location'] );
		$this->updateMeta( $snippet['id'], 'snippet_type', $snippet['type'] );
		$this->updateMeta( $snippet['id'], 'snippet_filters', $snippet['filters'] );
		$this->updateMeta( $snippet['id'], 'changed_filters', $snippet['changed_filters'] );
		$this->updateMeta( $snippet['id'], 'snippet_scope', $snippet['scope'] );
		$this->updateMeta( $snippet['id'], 'snippet_description', $snippet['description'] );
		$this->updateMeta( $snippet['id'], 'snippet_tags', $snippet['attributes'] );
		$this->updateMeta( $snippet['id'], 'snippet_activate', 0 );

		$this->updateTaxonomyTags( $snippet['id'], $snippet['tags'] );

		return $snippet['id'];
	}

	/**
	 * Save imported snippets
	 *
	 * @param $snippets
	 * @param $dup_action
	 *
	 * @return array
	 */
	private function saveImportedSnippets( $snippets, $dup_action ) {
		$existing_snippets = [];
		if ( 'replace' === $dup_action || 'skip' === $dup_action ) {
			$all_snippets = get_posts( [ 'post_type' => WINP_SNIPPETS_POST_TYPE ] );
			foreach ( $all_snippets as $snippet ) {
				$existing_snippets[ $snippet->post_name ] = $snippet->ID;
			}
		}

		$imported = [];

		foreach ( $snippets as $snippet ) {
			if ( 'ignore' !== $dup_action && isset( $existing_snippets[ $snippet['name'] ] ) ) {
				if ( 'replace' === $dup_action ) {
					$snippet['id'] = $existing_snippets[ $snippet['name'] ];
				} else if ( 'skip' === $dup_action ) {
					continue;
				}
			}

			if ( $snippet_id = $this->saveSnippet( $snippet ) ) {
				$imported[] = $snippet_id;
			}
		}

		return $imported;
	}

}
