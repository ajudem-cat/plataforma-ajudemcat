<?php
/**
 * WORDPRESS
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>, Alexander Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 17.05.2019, Webcraftic
 * @version       1.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin’ uh?' );

if ( ! function_exists( 'rest_get_url_prefix' ) ) :
	/**
	 * Retrieves the URL prefix for any API resource.
	 *
	 * @since 4.4.0
	 *
	 * @return string Prefix.
	 */
	function rest_get_url_prefix() {
		/**
		 * Filters the REST URL prefix.
		 *
		 * @since 4.4.0
		 *
		 * @param string $prefix   URL prefix. Default 'wp-json'.
		 */
		return apply_filters( 'rest_url_prefix', 'wp-json' );
	}
endif;

if ( ! function_exists( 'wp_parse_url' ) ) :
	/**
	 * A wrapper for PHP's parse_url() function that handles consistency in the return
	 * values across PHP versions.
	 *
	 * PHP 5.4.7 expanded parse_url()'s ability to handle non-absolute url's, including
	 * schemeless and relative url's with :// in the path. This function works around
	 * those limitations providing a standard output on PHP 5.2~5.4+.
	 *
	 * Secondly, across various PHP versions, schemeless URLs starting containing a ":"
	 * in the query are being handled inconsistently. This function works around those
	 * differences as well.
	 *
	 * Error suppression is used as prior to PHP 5.3.3, an E_WARNING would be generated
	 * when URL parsing failed.
	 *
	 * @since  1.6.9
	 * @since  WP 4.4.0
	 * @since  WP 4.7.0 The $component parameter was added for parity with PHP's parse_url().
	 *
	 * @param  (string) $url       The URL to parse.
	 * @param  (int)    $component The specific component to retrieve. Use one of the PHP
	 *                            predefined constants to specify which one.
	 *                            Defaults to -1 (= return all parts as an array).
	 *
	 * @return (mixed) False on parse failure; Array of URL components on success;
	 *                 When a specific component has been requested: null if the component
	 *                 doesn't exist in the given URL; a sting or - in the case of
	 *                 PHP_URL_PORT - integer when it does. See parse_url()'s return values.
	 * @see    http://php.net/manual/en/function.parse-url.php
	 *
	 */
	function wp_parse_url( $url, $component = - 1 ) {
		$to_unset = [];
		$url      = strval( $url );

		if ( '//' === substr( $url, 0, 2 ) ) {
			$to_unset[] = 'scheme';
			$url        = 'placeholder:' . $url;
		} else if ( '/' === substr( $url, 0, 1 ) ) {
			$to_unset[] = 'scheme';
			$to_unset[] = 'host';
			$url        = 'placeholder://placeholder' . $url;
		}

		$parts = @parse_url( $url );

		if ( false === $parts ) {
			// Parsing failure.
			return $parts;
		}

		// Remove the placeholder values.
		if ( $to_unset ) {
			foreach ( $to_unset as $key ) {
				unset( $parts[ $key ] );
			}
		}

		return _get_component_from_parsed_url_array( $parts, $component );
	}
endif;

if ( ! function_exists( '_get_component_from_parsed_url_array' ) ) :
	/**
	 * Retrieve a specific component from a parsed URL array.
	 *
	 * @since  1.6.9
	 * @since  WP 4.7.0
	 *
	 * @param  (array|false) $url_parts The parsed URL. Can be false if the URL failed to parse.
	 * @param  (int)         $component The specific component to retrieve. Use one of the PHP
	 *                                 predefined constants to specify which one.
	 *                                 Defaults to -1 (= return all parts as an array).
	 *
	 * @return (mixed) False on parse failure; Array of URL components on success;
	 *                 When a specific component has been requested: null if the component
	 *                 doesn't exist in the given URL; a sting or - in the case of
	 *                 PHP_URL_PORT - integer when it does. See parse_url()'s return values.
	 * @see    http://php.net/manual/en/function.parse-url.php
	 *
	 */
	function _get_component_from_parsed_url_array( $url_parts, $component = - 1 ) {
		if ( - 1 === $component ) {
			return $url_parts;
		}

		$key = _wp_translate_php_url_constant_to_key( $component );

		if ( false !== $key && is_array( $url_parts ) && isset( $url_parts[ $key ] ) ) {
			return $url_parts[ $key ];
		} else {
			return null;
		}
	}
endif;

if ( ! function_exists( '_wp_translate_php_url_constant_to_key' ) ) :
	/**
	 * Translate a PHP_URL_* constant to the named array keys PHP uses.
	 *
	 * @since  1.6.9
	 * @since  WP 4.7.0
	 *
	 * @param  (int) $constant PHP_URL_* constant.
	 *
	 * @return (string|bool) The named key or false.
	 * @see    http://php.net/manual/en/url.constants.php
	 *
	 */
	function _wp_translate_php_url_constant_to_key( $constant ) {
		$translation = [
			PHP_URL_SCHEME   => 'scheme',
			PHP_URL_HOST     => 'host',
			PHP_URL_PORT     => 'port',
			PHP_URL_USER     => 'user',
			PHP_URL_PASS     => 'pass',
			PHP_URL_PATH     => 'path',
			PHP_URL_QUERY    => 'query',
			PHP_URL_FRAGMENT => 'fragment',
		];

		if ( isset( $translation[ $constant ] ) ) {
			return $translation[ $constant ];
		} else {
			return false;
		}
	}
endif;

if ( ! function_exists( 'wp_scripts' ) ) :
	/**
	 * Initialize $wp_scripts if it has not been set.
	 *
	 * @since 1.6.11
	 * @since WP 4.2.0
	 *
	 * @return WP_Scripts WP_Scripts instance.
	 * @global WP_Scripts $wp_scripts
	 *
	 */
	function wp_scripts() {
		global $wp_scripts;
		if ( ! ( $wp_scripts instanceof WP_Scripts ) ) {
			$wp_scripts = new WP_Scripts(); // WPCS: override ok.
		}

		return $wp_scripts;
	}
endif;

if ( ! function_exists( 'wp_doing_ajax' ) ) :
	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * @since 1.7
	 * @since WP 4.7.0
	 *
	 * @return bool True if it's a WordPress Ajax request, false otherwise.
	 */
	function wp_doing_ajax() {
		/**
		 * Filters whether the current request is a WordPress Ajax request.
		 *
		 * @since 1.7
		 * @since WP 4.7.0
		 *
		 * @param bool $wp_doing_ajax   Whether the current request is a WordPress Ajax request.
		 */
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
endif;