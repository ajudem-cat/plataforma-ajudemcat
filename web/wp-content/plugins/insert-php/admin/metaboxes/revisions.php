<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WINP_RevisionsMetaBox extends WINP_MetaBox {

	/**
	 * A visible title of the metabox.
	 *
	 * Inherited from the class FactoryMetabox.
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $title;

	/**
	 * The part of the page where the edit screen
	 * section should be shown ('normal', 'advanced', or 'side').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $context = 'normal';

	/**
	 * The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 * Inherited from the class FactoryMetabox.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $priority = 'core';

	public $css_class = 'factory-bootstrap-423 factory-fontawesome-000';

	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->title = __( 'Code Revisions', 'insert-php' ) . ' (PRO)';
	}

	/**
	 * Configures a metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param Wbcr_Factory422_ScriptList $scripts A set of scripts to include.
	 * @param Wbcr_Factory422_StyleList $styles A set of style to include.
	 *
	 * @return void
	 */
	public function configure( $scripts, $styles ) {
		$styles->add( WINP_PLUGIN_URL . '/admin/assets/css/revisions.css' );
	}

	public function html() {
		global $post;

		$snippet_id = (int) $post->ID;
		if ( 0 === $snippet_id ) {
			wp_die( __( 'Access denied', 'insert-php' ) );
		}
		$snippet   = get_post( $snippet_id );
		$revisions = array(
			array(
				'ID'   => 1,
				'time' => time() + 2 * DAY_IN_SECONDS + 100,
			),
			array(
				'ID'   => 2,
				'time' => time() + DAY_IN_SECONDS + HOUR_IN_SECONDS + 50,
			),
			array(
				'ID'   => 3,
				'time' => time(),
			),
		);

		$this->renderMetabox( $snippet, $revisions );
	}

	/**
	 * @param $snippet WP_Post
	 * @param $revisions WP_Post[]
	 */
	public function renderMetabox( $snippet, $revisions ) {
		?>
        <table class="wp-list-table widefat fixed striped snippet-revisions">
            <thead>
            <tr>
                <th><?php _e( "Compare", "insert-php" ); ?></th>
                <th><?php _e( "Revision", "insert-php" ); ?></th>
                <th><?php _e( "Author", "insert-php" ); ?></th>
                <th><?php _e( "Delete", "insert-php" ); ?></th>
                <th><?php _e( "Restore", "insert-php" ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $revisions as $revision ):
                ?>
                <tr>
                    <td>
                        <input class="winp_rev_radio_from" type="radio" name="winp_rev_from"
                                value="<?php echo $revision['ID']; ?>"
                                data-panel="from">
                        <input class="winp_rev_radio_to" type="radio" name="winp_rev_to"
                                value="<?php echo $revision['ID']; ?>"
                                data-panel="to">
                    </td>
                    <td><?php echo date_i18n( __( 'M j, Y @ H:i' ), $revision['time'] ); ?></td>
                    <td><?php echo wp_get_current_user()->user_login; ?></td>
                    <th><input type="checkbox" name="winp_rev_delete_mark"
                               value="<?php echo $revision['ID']; ?>"></th>
                    <th>
                        <a href="<?php echo admin_url( 'post.php?post=' . $snippet->ID . '&action=edit#' ); ?>">
                            <?php _e( 'Restore', 'insert-php' ); ?>
                        </a>
                    </th>
                </tr>
                <?php
            endforeach;
            ?>
            </tbody>
        </table>
        <?php WINP_Helper::get_purchase_button() ?>
        <div class="revisions-actionbar">
            <button type="button"
                    class="button action winp_rev_compare"><?php _e( 'Compare', 'insert-php' ); ?></button>
            <button type="button"
                    class="button action winp_rev_delete"><?php _e( 'Delete', 'insert-php' ); ?></button>
        </div>
        <?php
	}
}
