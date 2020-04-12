<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WINPUpdate020000 extends Wbcr_Factory422_Update {

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
		if ( is_multisite() && $this->plugin->isNetworkActive() ) {
			$first_activation = get_site_option( $this->plugin->getOptionName( 'first_activation' ) );
		} else {
			$first_activation = get_option( $this->plugin->getOptionName( 'first_activation' ) );
		}

		$is_upgraded = get_option( $this->plugin->getOptionName( 'upgrade_up_to_201' ) );

		if ( ! $first_activation && ! $is_upgraded ) {
			update_option( $this->plugin->getOptionName( 'what_new_210' ), 1 );

			# Allow to display notifications for users who have migrated plugin version 1.3.0
			update_option( $this->plugin->getOptionName( 'need_show_attention_notice' ), 1 );
			# Enable option "Support old shortcodes"
			update_option( $this->plugin->getOptionName( 'support_old_shortcodes' ), 1 );

			// Create a demo snippets with examples of use
			if ( ! get_option( $this->plugin->getOptionName( 'demo_snippets_created' ) ) ) {
				WINP_Helper::create_demo_snippets();
			}
		}

		if ( ! WINP_Helper::has_post_capabilities() ) {
			WINP_Helper::set_post_capabilities();
		}
	}

}