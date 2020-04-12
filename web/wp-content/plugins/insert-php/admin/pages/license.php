<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WINP_License_Page is used as template to display form to active premium functionality.
 *
 * @since 2.0.7
 */
class WINP_License_Page extends WINP_Page {

	/**
	 * {@inheritdoc}
	 */
	public $type = "page";

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-admin-network';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 0;

	/**
	 * {@inheritdoc}
	 */
	public $available_for_multisite = true;

	/**
	 * @var string Name of the paid plan.
	 */
	public $plan_name;

	// PREMIUM SECTION
	// ------------------------------------------------------------------
	/**
	 * @since 2.0.7
	 * @var bool
	 */
	protected $is_premium;

	/**
	 * @since 2.0.7
	 * @var \WBCR\Factory_422\Premium\Provider
	 */
	protected $premium;

	/**
	 * @since 2.0.7
	 * @var bool
	 */
	protected $is_premium_active;

	/**
	 * @since 2.0.7
	 * @var bool
	 */
	protected $premium_has_subscription;

	/**
	 * @since 2.0.7
	 * @var \WBCR\Factory_422\Premium\Interfaces\License
	 */
	protected $premium_license;

	// END PREMIUM SECTION
	// ------------------------------------------------------------------

	/**
	 * {@inheritdoc}
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	public function __construct ( Wbcr_Factory422_Plugin $plugin ) {
		$this->plugin = $plugin;

		parent::__construct( $plugin );

		$this->menu_post_type = WINP_SNIPPETS_POST_TYPE;
		$this->id             = 'license';
		$this->menu_title     = __( 'License', 'insert-php' );

		$this->premium                  = WINP_Plugin::app()->premium;
		$this->is_premium               = $this->premium->is_activate();
		$this->is_premium_active        = $this->premium->is_active();
		$this->premium_has_subscription = $this->premium->has_paid_subscription();
		$this->premium_license          = $this->premium->get_license();
	}

	/**
	 * [MAGIC] Magic method that configures assets for a page.
	 */
	public function assets ( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add( WINP_PLUGIN_URL . '/admin/assets/css/license-manager.css' );

		$this->styles->request( array(
			'bootstrap.core',
			'bootstrap.form-groups',
			'bootstrap.separator',
		), 'bootstrap' );

		$this->scripts->add( WINP_PLUGIN_URL . '/admin/assets/js/license-manager.js' );
	}

	/**
	 * Get before content.
	 *
	 * @return string Before content.
	 */
	protected function get_plan_description () {
		return '';
	}

	/**
	 * @return string
	 */
	protected function get_hidden_license_key () {
		if ( ! $this->is_premium ) {
			return '';
		}

		return $this->premium_license->get_hidden_key();
	}

	/**
	 * @return string
	 */
	protected function get_plan () {
		if ( ! $this->is_premium ) {
			return 'free';
		}

		return $this->premium->get_plan();
	}

	/**
	 * @return mixed
	 */
	protected function get_expiration_days () {
		return $this->premium_license->get_expiration_time( 'days' );
	}

	/**
	 * @return string
	 */
	protected function get_billing_cycle_readable () {
		if ( ! $this->is_premium ) {
			return '';
		}

		$billing_cycle = $this->premium->get_billing_cycle();
		$billing       = 'lifetime';

		if ( 1 == $billing_cycle ) {
			$billing = 'month';
		} else if ( 12 == $billing_cycle ) {
			$billing = 'year';
		}

		return $billing;
	}

	/**
	 * Тип лицензии, цветовое оформление для формы лицензирования
	 * free - бесплатная
	 * gift - пожизненная лицензия, лицензия на особых условиях
	 * trial - красный цвет, применяется для триалов, если лиценизия истекла или заблокирована
	 * paid - обычная оплаченная лицензия, в данный момент активна.
	 *
	 * @return string
	 */
	protected function get_license_type () {
		if ( ! $this->is_premium ) {
			return 'free';
		}

		$license = $this->premium_license;

		if ( $license->is_lifetime() ) {
			return 'gift';
		} else if ( $license->get_expiration_time( 'days' ) < 1 ) {
			return 'trial';
		}

		return 'paid';
	}

	/**
	 *
	 */
	public function indexAction () {
		?>
        <div class="wrap">
            <div class="<?php echo WINP_Helper::get_factory_class(); ?>">
				<?php wp_nonce_field( 'license' ); ?>
                <div id="winp-license-wrapper"
                     data-loader="<?php echo WINP_PLUGIN_URL . '/admin/assets/img/loader.gif'; ?>"
                     data-plugin="<?php echo get_class( $this->plugin ) ?>">

                    <div class="factory-bootstrap-423 onp-page-wrap <?php echo $this->get_license_type() ?>-license-manager-content"
                         id="license-manager">
                        <div>
                            <h3><?php printf( __( 'Activate %s', 'insert-php' ), $this->plan_name ) ?></h3>
							<?php echo $this->get_plan_description() ?>
                        </div>
                        <br>

                        <div class="onp-container">
                            <div class="license-details">
								<?php if ( $this->get_license_type() == 'free' ): ?>
                                    <a href="<?php echo $this->plugin->get_support()->get_pricing_url( true, 'license_page' ); ?>"
                                       class="purchase-premium" target="_blank" rel="noopener">
                            <span class="btn btn-gold btn-inner-wrap">
                            <?php printf( __( 'Upgrade to Premium for $%s', 'insert-php' ), $this->premium->get_price() ) ?>
                            </span>
                                    </a>
                                    <p><?php printf( __( 'Your current license for %1$s:', 'insert-php' ), $this->plugin->getPluginTitle() ) ?></p>
								<?php endif; ?>
                                <div class="license-details-block <?php echo $this->get_license_type() ?>-details-block">
									<?php if ( $this->is_premium ): ?>
                                        <a data-action="deactivate" href="#"
                                           class="btn btn-default btn-small license-delete-button winp-control-btn">
											<?php _e( 'Delete Key', 'insert-php' ) ?>
                                        </a>
                                        <a data-action="sync" href="#"
                                           class="btn btn-default btn-small license-synchronization-button winp-control-btn">
											<?php _e( 'Synchronization', 'insert-php' ) ?>
                                        </a>
									<?php endif; ?>
                                    <h3>
										<?php echo ucfirst( $this->get_plan() ); ?>

										<?php if ( $this->is_premium && $this->premium_has_subscription ): ?>
                                            <span style="font-size: 15px;">
                                    (<?php printf( __( 'Automatic renewal, every %s', '' ), esc_attr( $this->get_billing_cycle_readable() ) ); ?>
                                                )
                                </span>
										<?php endif; ?>
                                    </h3>
									<?php if ( $this->is_premium ): ?>
                                        <div class="license-key-identity">
                                            <code><?php echo esc_attr( $this->get_hidden_license_key() ) ?></code>
                                        </div>
									<?php endif; ?>
                                    <div class="license-key-description">
                                        <p><?php _e( 'Public License is a GPLv2 compatible license allowing you to change and use this version of the plugin for free. Please keep in mind this license covers only free edition of the plugin. Premium versions are distributed with other type of a license.', 'insert-php' ) ?>
                                        </p>
										<?php if ( $this->is_premium && $this->premium_has_subscription ): ?>
                                            <p class="activate-trial-hint">
												<?php _e( 'You use a paid subscription for the plugin updates. In case you don’t want to receive paid updates, please, click <a data-action="unsubscribe" class="winp-control-btn" href="#">cancel subscription</a>', 'insert-php' ) ?>
                                            </p>
										<?php endif; ?>

										<?php if ( $this->get_license_type() == 'trial' ): ?>
                                            <p class="activate-error-hint">
												<?php printf( __( 'Your license has expired, please extend the license to get updates and support.', 'insert-php' ), '' ) ?>
                                            </p>
										<?php endif; ?>
                                    </div>
                                    <table class="license-params" colspacing="0" colpadding="0">
                                        <tr>
                                            <!--<td class="license-param license-param-domain">
										<span class="license-value"><?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?></span>
										<span class="license-value-name"><?php _e( 'domain', 'insert-php' ) ?></span>
									</td>-->
                                            <td class="license-param license-param-days">
                                                <span class="license-value"><?php echo $this->get_plan() ?></span>
                                                <span class="license-value-name"><?php _e( 'plan', 'insert-php' ) ?></span>
                                            </td>
											<?php if ( $this->is_premium ) : ?>
                                                <td class="license-param license-param-sites">
                                        <span class="license-value">
                                            <?php echo esc_attr( $this->premium_license->get_count_active_sites() ); ?>
                                            <?php _e( 'of', 'insert-php' ) ?>
                                            <?php echo esc_attr( $this->premium_license->get_sites_quota() ); ?></span>
                                                    <span class="license-value-name"><?php _e( 'active sites', 'insert-php' ) ?></span>
                                                </td>
											<?php endif; ?>
                                            <td class="license-param license-param-version">
                                                <span class="license-value"><?php echo $this->plugin->getPluginVersion() ?></span>
                                                <span class="license-value-name"><span><?php _e( 'version', 'insert-php' ) ?></span></span>
                                            </td>
											<?php if ( $this->is_premium ): ?>
                                                <td class="license-param license-param-days">
													<?php if ( $this->get_license_type() == 'trial' ): ?>
                                                        <span class="license-value"><?php _e( 'EXPIRED!', 'insert-php' ) ?></span>
                                                        <span class="license-value-name"><?php _e( 'please update the key', 'insert-php' ) ?></span>
													<?php else: ?>
                                                        <span class="license-value">
													<?php
													if ( $this->premium_license->is_lifetime() ) {
														echo 'infiniate';
													} else {
														echo $this->get_expiration_days();
													}
													?>
                                                            <small> <?php _e( 'day(s)', 'insert-php' ) ?></small>
                                             </span>
                                                        <span class="license-value-name"><?php _e( 'remained', 'insert-php' ) ?></span>
													<?php endif; ?>
                                                </td>
											<?php endif; ?>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="license-input">
                                <form action="" method="post">
									<?php if ( $this->is_premium ): ?>
                                <p><?php _e( 'Have a key to activate the premium version? Paste it here:', 'insert-php' ) ?><p>
								<?php else: ?>
                                    <p><?php _e( 'Have a key to activate the plugin? Paste it here:', 'insert-php' ) ?>
                                    <p>
										<?php endif; ?>
                                        <button data-action="activate" class="btn btn-default winp-control-btn"
                                                type="button"
                                                id="license-submit">
											<?php _e( 'Submit Key', 'insert-php' ) ?>
                                        </button>
                                    <div class="license-key-wrap">
                                        <input type="text" id="license-key" name="licensekey" value=""
                                               class="form-control"/>
                                    </div>
									<?php if ( $this->is_premium ): ?>
                                        <p style="margin-top: 10px;">
											<?php printf( __( '<a href="%s" target="_blank" rel="noopener">Lean more</a> about the premium version and get the license key to activate it now!', 'insert-php' ), $this->plugin->get_support()->get_pricing_url( true, 'license_page' ) ); ?>
                                        </p>
									<?php else: ?>
                                        <p style="margin-top: 10px;">
											<?php printf( __( 'Can’t find your key? Go to <a href="%s" target="_blank" rel="noopener">this page</a> and login using the e-mail address associated with your purchase.', 'insert-php' ), $this->plugin->get_support()->get_contacts_url( true, 'license_page' ) ) ?>
                                        </p>
									<?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}
