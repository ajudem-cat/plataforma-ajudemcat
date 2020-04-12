<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}
// @formatter:off
function winp_uninstall() {
	global $wpdb;

	$post_type = 'wbcr-snippets';
	$taxonomy  = 'wbcr-snippet-tags';

	$snippets = get_posts( array(
		'post_type'   => $post_type,
		'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
		'numberposts' => - 1
	) );

	if ( ! empty( $snippets ) ) {
		foreach ( (array) $snippets as $snippet ) {
			wp_delete_post( $snippet->ID, true );
		}
	}

	$query = 'SELECT t.name, t.term_id
            FROM ' . $wpdb->terms . ' AS t
            INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy = "' . $taxonomy . '"';

	$terms = $wpdb->get_results( $query );
	if ( ! empty( $terms ) ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, $post_type, array() );
		}
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}

		unregister_taxonomy( $taxonomy );
	}

	// remove plugin options
	$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'wbcr_inp_%';" );
}

if ( is_multisite() ) {
	global $wpdb, $wp_version;

	$wpdb->query( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'wbcr_inp_%';" );

	$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

	if ( ! empty( $blogs ) ) {
		foreach ( $blogs as $id ) {
			switch_to_blog( $id );

			winp_uninstall();

			restore_current_blog();
		}
	}
} else {
	if ( get_option( 'wbcr_inp_complete_uninstall', true ) ) {
		winp_uninstall();
	}
}
// @formatter:on

