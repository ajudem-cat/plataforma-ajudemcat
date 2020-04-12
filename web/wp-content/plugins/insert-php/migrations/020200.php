<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WINPUpdate020200 extends Wbcr_Factory422_Update {

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
	}

	public function new_migration() {
		$this->update_plugin_activated_option();
		$this->set_post_capabilities();
		$this->move_code();
	}

	/**
	 * In older versions of the plugin, the plugin_activated option did not exist.
	 * The first activation time is writed in the plugin_activated option.
	 * We can take the time of the first activation of the plugin from another
	 * options first_activation and update option plugin_activated.
	 *
	 * The time of the first activation of the plugin is very important if we want to know
	 * how much the user already uses this plugin.
	 *
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.0
	 */
	private function update_plugin_activated_option() {
		if ( is_multisite() && $this->plugin->isNetworkActive() ) {
			$first_activation = get_site_option( $this->plugin->getOptionName( 'first_activation' ) );
		} else {
			$first_activation = get_option( $this->plugin->getOptionName( 'first_activation' ) );
		}

		if ( $first_activation && isset( $first_activation['timestamp'] ) ) {
			update_option( $this->plugin->getOptionName( 'plugin_activated' ), (int) $first_activation['timestamp'] );
		}
	}

	/**
	 * Set capabilities for snippets post type
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.0
	 */
	private function set_post_capabilities() {
		if ( ! WINP_Helper::has_post_capabilities() ) {
			WINP_Helper::set_post_capabilities();
		}
	}

	/**
	 * We mode snippet code from the metadata to the post_content cell in posts table
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.2.1
	 */
	private function move_code() {
		wp_raise_memory_limit();

		global $wpdb;

		$snippets = $wpdb->get_results( "	
			SELECT p.ID
			  FROM {$wpdb->posts} p
			    INNER JOIN {$wpdb->postmeta} m ON ( p.ID = m.post_id ) 
			  WHERE p.post_content = '' 
			    AND m.meta_key = '" . WINP_Plugin::app()->getPrefix() . "snippet_code' 
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
				$snippet_code = WINP_Helper::getMetaOption( $snippet->ID, 'snippet_code' );

				if ( ! empty( $snippet_code ) ) {
					$wpdb->update( $wpdb->posts, [
						'post_content' => $snippet_code
					], [
						'ID'        => (int) $snippet->ID,
						'post_type' => WINP_SNIPPETS_POST_TYPE
					], [ '%s' ], [ '%d', '%s' ] );

					WINP_Helper::updateMetaOption( $snippet->ID, 'snippet_code_moved', 1 );
				}

				unset( $snippet_code );
			}
		}

		unset( $snippets );
	}
}