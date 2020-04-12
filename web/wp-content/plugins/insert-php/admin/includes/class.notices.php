<?php
/**
 * Warning notices
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 11.12.2018, Webcraftic
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_WarningNotices {

	/**
	 * @var string
	 */
	private $prefix = 'inp_';

	/**
	 * @var array
	 */
	private $notices = [];

	public function __construct() {
		/**
		 * @since 2.2.8 Filter Name Changed
		 */
		add_filter( 'wbcr/factory/admin_notices', [ $this, 'register_notices' ], 10, 2 );
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 * @param array  $notices
	 * @param string $plugin_name
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function register_notices( $notices, $plugin_name ) {
		if ( $plugin_name !== WINP_Plugin::app()->getPluginName() ) {
			return $notices;
		}

		if ( ! WINP_Plugin::app()->currentUserCan() ) {
			return $notices;
		}

		if ( WINP_Plugin::app()->getOption( 'need_show_attention_notice' ) ) {
			$this->add_notice( 'upgrade_plugin2', $this->get_upgrade_notice(), 'warning' );
		}

		$this->add_notice( 'safe_mode', $this->get_safe_mode_notice(), 'success', false );
		$this->add_notice( 'result_error', $this->get_throw_php_error_notice(), 'error', false, 0, [
			'post',
			'post-new',
			'edit'
		] );
		/*$this->add_notice( 'leave_feedback', $this->get_leave_feedback_notice(), 'success', true, time() + 3600 * 60, [
			'post',
			'post-new',
			'edit'
		] );*/

		return array_merge( $notices, $this->notices );
	}

	/**
	 * A small line that asks for feedback from user
	 *
	 * @return string
	 */
	public function get_leave_feedback_notice() {
		$post_type = WINP_Plugin::app()->request->get( 'post_type' );
		if ( ! empty( $post_type ) && WINP_SNIPPETS_POST_TYPE == $post_type ) {
			return '<strong>' . __( 'Have feedback on Woody ad Snippets?', 'insert-php' ) . '</strong> ' . __( 'Please take the time to answer a short survey on how you use this plugin and what you\'d like to see changed or added in the future.', 'insert-php' ) . '
				<a href="http://bit.ly/2MpVokA" class="button secondary" target="_blank" style="margin: auto .5em;">' . __( 'Take the survey now', 'insert-php' ) . '</a>';
		}
	}

	/**
	 * Show error notification after saving snippet. We can also show this message when the snippet is activated.
	 * We must warn the user that we can not perform the spippet due to an error.
	 *
	 * @return string|null
	 */
	public function get_throw_php_error_notice() {
		$save_snippet_result = WINP_Plugin::app()->request->get( 'wbcr_inp_save_snippet_result' );
		$post_id             = WINP_Plugin::app()->request->get( 'post' );

		if ( ! empty( $save_snippet_result ) && 'code-error' == $save_snippet_result ) {
			$post_id = ! empty( $post_id ) ? intval( $post_id ) : null;

			if ( $post_id ) {
				$error = WINP_Plugin::app()->getExecuteObject()->getSnippetError( $post_id );

				if ( false !== $error ) {
					return sprintf( '<p>%s</p><p><strong>%s</strong></p>', sprintf( __( 'The snippet has been deactivated due to an error on line %d:', 'insert-php' ), $error['line'] ), $error['message'] );
				}
			}
		}

		return null;
	}

	/**
	 * This warning is for users of the old version 1.3.0. The plugin has changed the way it works
	 * and requires user actions to continue working with plugin.
	 *
	 * @return string
	 */
	public function get_upgrade_notice() {
		$create_notice_url = admin_url( 'edit.php?post_type=' . WINP_SNIPPETS_POST_TYPE );

		$notice = '<b>' . WINP_Plugin::app()->getPluginTitle() . '</b>: ' . sprintf( __( 'Attention! If you have previously used version 1.3.0 of plugin Insert php. This new %s plugin version, we added the ability to insert php code using snippets. This is a more convenient and secure way than using shortcodes [insert_php] code execute [/ insert_php]. However, for compatibility reasons, we left support for [insert_php] shortcodes until March 2019, after that we will stop supporting shortcodes [insert_php].', 'insert-php' ), WINP_Plugin::app()->getPluginVersion() );

		$notice .= '<br><br>' . __( 'We strongly recommend you to porting your php code to snippets and call them in your posts/pages and widgets using [wbcr_php_snippet id = "000"] shortcodes.', 'insert-php' );
		$notice .= ' ' . sprintf( __( 'For more information on porting code and using snippets, see our plugin <a href="%s" target="_blank">documentation</a>', 'insert-php' ), WINP_Plugin::app()->get_support()->get_docs_url( true, 'admin-notice' ) );
		$notice .= '<br><br><a href="' . $create_notice_url . '" class="button button-default">' . __( 'Create new php snippet', 'insert-php' ) . '</a> ';
		$notice .= '<a href="https://downloads.wordpress.org/plugin/insert-php.1.3.zip" class="button button-default">' . __( 'Download old version', 'insert-php' ) . '</a>';
		$notice .= '<br><br>' . sprintf( __( 'If you still want to use the old shortcodes [insert_php] and you don’t have time to upgrade to the new version, you can enable support for old shortcodes in the plugin <a href="%s">settings</a>.', 'insert-php' ), WINP_Plugin::app()->getPluginPageUrl( 'settings' ) );
		$notice .= '<br>' . sprintf( __( 'If you have issues with the plugin new version or any suggestions, please contact us on <a href="%s" target="_blank">our forum</a>.', 'insert-php' ), 'http://forum.webcraftic.com' );

		return $notice;
	}

	/**
	 * When the safe mode of the plugin is enabled, this notification will remind
	 * the user to exit safe mode so that the user's snippets are available publicly.
	 *
	 * @return string
	 */
	public function get_safe_mode_notice() {
		if ( ! WINP_Helper::is_safe_mode() ) {
			return null;
		}

		$disable_safe_mode_url = add_query_arg( [ 'wbcr-php-snippets-disable-safe-mode' => 1 ] );

		$notice = WINP_Plugin::app()->getPluginTitle() . ': ' . __( 'Running in safe mode. This mode your snippets will not be started.', 'insert-php' );
		$notice .= ' <a href="' . $disable_safe_mode_url . '" class="button button-default">' . __( 'Disable Safe Mode', 'insert-php' ) . '</a>';

		return $notice;
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 * @param string $id
	 * @param string $message
	 * @param string $type
	 * @param bool   $dismissible
	 * @param int    $dismiss_expires
	 * @param array  $where
	 */
	protected function add_notice( $id, $message, $type = 'warning', $dismissible = true, $dismiss_expires = 0, $where = [] ) {
		if ( is_null( $message ) ) {
			return;
		}

		if ( ! empty( $this->notices ) ) {
			foreach ( $this->notices as $notice ) {
				if ( $notice['id'] == $this->prefix . $id ) {
					return;
				}
			}
		}

		$this->notices[] = [
			'id'              => $this->prefix . $id,
			'type'            => $type,
			'dismissible'     => (bool) $dismissible,
			'dismiss_expires' => (int) $dismiss_expires,
			'where'           => $where,
			'text'            => '<p>' . $message . '</p>'
		];
	}
}