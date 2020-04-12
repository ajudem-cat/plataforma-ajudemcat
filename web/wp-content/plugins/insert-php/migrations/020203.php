<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WINPUpdate020203 extends Wbcr_Factory422_Update {

	public function install() {
		if ( is_multisite() && $this->plugin->isNetworkActive() ) {
			global $wpdb;

			$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $id ) {

					switch_to_blog( $id );

					$this->new_migration();

					restore_current_blog();
				}
			}

			WINP_Helper::flush_page_cache();

			return;
		}

		$this->new_migration();
		WINP_Helper::flush_page_cache();
	}

	public function new_migration() {
		$this->move_code();
	}

	/**
	 * We mode snippet code from the metadata to the post_content cell in posts table
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.3
	 */
	private function move_code() {
		wp_raise_memory_limit();

		global $wpdb;

		$snippets = $wpdb->get_results( "	
			SELECT p.ID
			  FROM {$wpdb->posts} p
			    INNER JOIN {$wpdb->postmeta} m ON ( p.ID = m.post_id ) 
			  WHERE m.meta_key = '" . WINP_Plugin::app()->getPrefix() . "snippet_code' 			   
			    AND p.post_type = '" . WINP_SNIPPETS_POST_TYPE . "' 
			    AND ((p.post_status = 'publish' 
				    OR p.post_status = 'future' 
				    OR p.post_status = 'draft' 
				    OR p.post_status = 'pending' 
				    OR p.post_status = 'trash' 
				    OR p.post_status = 'auto-draft' 
				    OR p.post_status = 'inherit' 
				    OR p.post_status = 'private')
			  )" );

		if ( ! empty( $snippets ) ) {
			foreach ( (array) $snippets as $snippet ) {
				$is_snippet_code_moved = WINP_Helper::getMetaOption( $snippet->ID, 'snippet_code_moved' );

				if ( ! $is_snippet_code_moved ) {
					$snippet_code = WINP_Helper::getMetaOption( $snippet->ID, 'snippet_code' );

					if ( ! empty( $snippet_code ) ) {
						$wpdb->update( $wpdb->posts, [
							'post_content' => $snippet_code
						], [
							'ID'        => (int) $snippet->ID,
							'post_type' => WINP_SNIPPETS_POST_TYPE
						], [ '%s' ], [ '%d', '%s' ] );
					}

					unset( $snippet_code );
				}
			}
		}

		unset( $snippets );
	}
}