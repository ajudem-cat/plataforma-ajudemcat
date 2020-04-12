<?php
/**
 * Woody Request class
 *
 * Contains methods for executing requests and processing responses.
 * Uses the WINP\JsonMapper\Mapper to convert the response to a convenient object.
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 11.12.2018, Webcraftic
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_Request {
	//В новых версиях Вуди начиная с 2.2.10 будет обращаться к новой версии API библиотеки сниппетов
	// это делается для обратной совместимости, чтобы старые версии продолжили работать со старым API
	const WINP_REQUEST_URL = 'http://api.woodysnippet.com/v2/woody/';
	// Старое API http://142.93.91.206/v1/woody/

	/**
	 * WINP_REQUEST constructor.
	 */
	public function __construct() {
		require_once WINP_PLUGIN_DIR . '/includes/jsonmapper/class-json-mapper.php';
		require_once WINP_PLUGIN_DIR . '/includes/jsonmapper/exceptions/class-exception.php';
	}

	/**
	 * Get license key
	 *
	 * @return string
	 */
	private function get_key() {
		return WINP_Plugin::app()->premium->get_license()->get_key();
	}

	/**
	 * Get license plugin_id
	 *
	 * @return string
	 */
	private function get_plugin_id() {
		return WINP_Plugin::app()->premium->get_setting( 'plugin_id' );
	}

	/**
	 * Get base64 token string
	 *
	 * @return string
	 */
	private function get_token() {
		return base64_encode( $this->get_key() );
	}

	/**
	 * Get headers
	 *
	 * @return array
	 */
	private function get_headers() {
		return array(
			'Authorization' => 'Bearer ' . $this->get_token(),
			'PluginId'      => $this->get_plugin_id(),
		);
	}

	/**
	 * Check is key data available
	 *
	 * @return bool
	 */
	public function is_key() {
		return WINP_Plugin::app()->premium->is_activate() && $this->get_key();
	}

	/**
	 * Make POST request with authorization headers and return response
	 *
	 * @param string $point
	 * @param array $args
	 *
	 * @return array|bool|WP_Error
	 */
	public function post( $point, $args = array() ) {
		if ( ! $this->is_key() ) {
			return false;
		}

		$args['headers'] = $this->get_headers();

		return wp_remote_post( self::WINP_REQUEST_URL . $point, $args );
	}

	/**
	 * Make GET request with authorization headers and return response
	 *
	 * @param string $point
	 * @param array $args
	 *
	 * @return array|bool|WP_Error
	 */
	public function get( $point, $args = array() ) {
		if ( ! $this->is_key() ) {
			return false;
		}

		$args['headers'] = $this->get_headers();

		return wp_remote_get( self::WINP_REQUEST_URL . $point, $args );
	}

	/**
	 * Make PUT request with authorization headers and return response
	 *
	 * @param string $point
	 * @param array $args
	 *
	 * @return array|bool|WP_Error
	 */
	public function put( $point, $args = array() ) {
		if ( ! $this->is_key() ) {
			return false;
		}

		$args['method']  = Requests::PUT;
		$args['headers'] = $this->get_headers();

		return wp_remote_request( self::WINP_REQUEST_URL . $point, $args );
	}

	/**
	 * Check response
	 *
	 * @param $response
	 *
	 * @return bool
	 */
	public function check_response( $response ) {
		if ( empty( $response ) || $response instanceof WP_Error ) {
			return false;
		}

		if ( ! isset( $response['body'] ) || empty( $response['body'] ) ) {
			return false;
		}

		if ( 200 != $response['response']['code'] && 201 != $response['response']['code'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Check body
	 *
	 * @param $body
	 *
	 * @return bool
	 */
	public function check_body( $body ) {
		if ( empty( $body ) ) {
			return false;
		}

		if ( ! is_array( $body ) && ! is_object( $body ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get response text error
	 *
	 * @param $response
	 *
	 * @return string
	 */
	public function get_response_error( $response ) {
		if ( empty( $response ) ) {
			return 'Empty response';
		}

		if ( $response instanceof WP_Error ) {
			return $response->get_error_message();
		}

		if ( is_array( $response ) ) {
			if ( ! isset( $response['body'] ) || empty( $response['body'] ) ) {
				return 'Empty body';
			}

			if ( 200 != $response['response']['code'] && 201 != $response['response']['code'] ) {
				return $response['response']['message'] . ' [Code: ' . $response['response']['code'] . ']';
			}
		}

		return 'Unknown error';
	}

	/**
	 * Get mapped object by name
	 *
	 * @param $json
	 * @param $object_name
	 *
	 * @return bool|mixed
	 */
	public function map_object( $json, $object_name ) {
		if ( ! $this->check_response( $json ) ) {
			error_log( 'Snippet api [map_object]: ' . $this->get_response_error( $json ) );

			return false;
		}

		$body = json_decode( $json['body'] );

		if ( ! $this->check_body( $body ) ) {
			error_log( 'Snippet api [map_objects]: Wrong body' );

			return false;
		}

		$mapper = new WINP\JsonMapper\Mapper();

		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bExceptionOnMissingData       = true;

		try {
			return $mapper->map( $body, new $object_name() );
		} catch ( WINP\JsonMapper\Exception $exception ) {
			error_log( 'Snippet api [map_object]: ' . $exception->getMessage() );

			return false;
		}
	}

	/**
	 * Get mapped objects by name
	 *
	 * @param $json
	 * @param $object_name
	 *
	 * @return bool|mixed
	 */
	public function map_objects( $json, $object_name ) {
		if ( ! $this->check_response( $json ) ) {
			error_log(
				'Snippet api [map_objects]: ' . $this->get_response_error( $json )
			);

			return false;
		}

		$body = json_decode( $json['body'] );

		if ( ! $this->check_body( $body ) ) {
			error_log( 'Snippet api [map_objects]: Wrong body' );

			return false;
		}

		$mapper = new WINP\JsonMapper\Mapper();

		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bExceptionOnMissingData       = true;

		try {
			return $mapper->mapArray(
				$body,
				array(),
				$object_name
			);
		} catch ( WINP\JsonMapper\Exception $exception ) {
			error_log( 'Snippet api [map_objects]: ' . $exception->getMessage() );

			return false;
		}
	}

}
