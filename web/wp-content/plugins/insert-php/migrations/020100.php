<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WINPUpdate020100 extends Wbcr_Factory422_Update {

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

			return;
		}

		$this->new_migration();
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.0
	 */
	public function new_migration() {
		global $wpdb;

		if ( null === get_option( $this->plugin->getOptionName( 'complete_uninstall' ), null ) ) {
			update_option( $this->plugin->getOptionName( 'complete_uninstall' ), 0 );
		}

		if ( null === get_option( $this->plugin->getOptionName( 'what_new_210' ), null ) ) {
			update_option( $this->plugin->getOptionName( 'what_new_210' ), 1 );
		}

		$snippets = $wpdb->get_results( "	
			SELECT p.ID
			  FROM {$wpdb->posts} p
			  WHERE p.post_type = '" . WINP_SNIPPETS_POST_TYPE . "' 
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
				$snippet_type = WINP_Helper::getMetaOption( $snippet->ID, 'snippet_type' );

				if ( empty( $snippet_type ) ) {
					WINP_Helper::updateMetaOption( $snippet->ID, 'snippet_type', WINP_SNIPPET_TYPE_PHP );
				}
			}
		}

		unset( $snippets );
	}
}