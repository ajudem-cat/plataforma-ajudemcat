<?php
/*
	Note: This plugin requires WordPress version 3.3.1 or higher.

	Information about the Insert PHP plugin can be found here:
	http://www.willmaster.com/software/WPplugins/go/iphphome_iphplugin

	Instructions and examples can be found here:
	http://www.willmaster.com/software/WPplugins/go/iphpinstructions_iphplugin
	*/

// todo: This is the code of the old version of the plugin, left unchanged for compatibility. Delete in the new major version of the plugin
if ( ! function_exists( 'will_bontrager_insert_php' ) ) {

	function will_bontrager_insert_php( $content ) {
		if ( WINP_Helper::is_safe_mode() ) {
			return $content;
		}

		$will_bontrager_content = $content;
		preg_match_all( '!\[insert_php[^\]]*\](.*?)\[/insert_php[^\]]*\]!is', $will_bontrager_content, $will_bontrager_matches );
		$will_bontrager_nummatches = count( $will_bontrager_matches[0] );
		for ( $will_bontrager_i = 0; $will_bontrager_i < $will_bontrager_nummatches; $will_bontrager_i ++ ) {
			ob_start();
			eval( $will_bontrager_matches[1][ $will_bontrager_i ] );
			$will_bontrager_replacement = ob_get_contents();
			ob_clean();
			ob_end_flush();
			$will_bontrager_content = preg_replace( '/' . preg_quote( $will_bontrager_matches[0][ $will_bontrager_i ], '/' ) . '/', $will_bontrager_replacement, $will_bontrager_content, 1 );
		}

		return $will_bontrager_content;
	} # function will_bontrager_insert_php()

	add_filter( 'the_content', 'will_bontrager_insert_php', 9 );
} # if( ! function_exists('will_bontrager_insert_php') )

