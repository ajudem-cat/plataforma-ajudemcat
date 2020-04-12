<?php
	/**
	 * The file contains a class for standard metabox that contains only the Publish/Update button.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package factory-metaboxes
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryMetaboxes409_PublishMetabox') ) {
		/**
		 * A metabox containing the standart Publish/Update button.
		 *
		 * @since 1.0.0
		 */
		class Wbcr_FactoryMetaboxes409_PublishMetabox extends Wbcr_FactoryMetaboxes409_Metabox {

			/**
			 * @param Wbcr_Factory422_Plugin $plugin
			 */
			public function __construct(Wbcr_Factory422_Plugin $plugin = null)
			{
				$this->title = __('Control');
				$this->context = 'side';
				$this->priority = 'high';

				parent::__construct($plugin);
			}

			public function html()
			{
				global $action, $post;

				$post_type = $post->post_type;
				$post_type_object = get_post_type_object($post_type);
				$can_publish = current_user_can($post_type_object->cap->publish_posts);

				?>
				<div class="submitbox" id="submitpost">
					<div id="minor-publishing">
						<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key
						?>
						<div style="display:none;">
							<?php submit_button(__('Save', 'factory'), 'button', 'save'); ?>
						</div>
						<div id="misc-publishing-actions">
							<?php
								// translators: Publish box date format, see http://php.net/date
								$datef = __('M j, Y @ G:i');
								if( 0 != $post->ID ) {
									if( 'future' == $post->post_status ) { // scheduled for publishing at a future date
										$stamp = __('Scheduled for: <b>%1$s</b>');
									} else if( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
										$stamp = __('Created on: <b>%1$s</b>');
									} else if( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
										$stamp = __('Created <b>immediately</b>');
									} else if( time() < strtotime($post->post_date_gmt . ' +0000') ) { // draft, 1 or more saves, future date specified
										$stamp = __('Schedule for: <b>%1$s</b>');
									} else { // draft, 1 or more saves, date specified
										$stamp = __('Created on: <b>%1$s</b>');
									}
									$date = date_i18n($datef, strtotime($post->post_date));
								} else { // draft (no saves, and thus no date specified)
									$stamp = __('Created <b>immediately</b>');
									$date = date_i18n($datef, strtotime(current_time('mysql')));
								}

								if( $can_publish ) : // Contributors don't get to choose the date of publish
									?>
									<div class="misc-pub-section curtime">
									<span id="timestamp">
                <?php printf($stamp, $date); ?></span>
									</div><?php // /misc-pub-section
									?>
								<?php endif; ?>
						</div>
						<div class="clear"></div>
					</div>
					<div id="major-publishing-actions">
						<div id="delete-action">
							<?php
								if( current_user_can("delete_post", $post->ID) ) {
									if( !EMPTY_TRASH_DAYS ) {
										$delete_text = __('Delete Permanently');
									} else {
										$delete_text = __('Move to Trash');
									}
									?>
									<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
								} ?>
						</div>
						<div id="publishing-action">
							<img src="<?php echo esc_url(admin_url('images/wpspin_light.gif')); ?>" class="ajax-loading" id="ajax-loading" alt=""/>
							<?php
								if( !in_array($post->post_status, array(
										'publish',
										'future',
										'private'
									)) || 0 == $post->ID
								) {
									if( $can_publish ) :
										if( !empty($post->post_date_gmt) && time() < strtotime($post->post_date_gmt . ' +0000') ) : ?>
											<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>"/>
											<?php submit_button(__('Schedule'), 'primary', 'publish', false, array(
												'tabindex' => '5',
												'accesskey' => 'p'
											)); ?>
										<?php else : ?>
											<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>"/>
											<?php submit_button(__('Create'), 'primary', 'publish', false, array(
												'tabindex' => '5',
												'accesskey' => 'p'
											)); ?>
										<?php endif;
									else : ?>
										<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>"/>
										<?php submit_button(__('Submit for Review'), 'primary', 'publish', false, array(
											'tabindex' => '5',
											'accesskey' => 'p'
										)); ?>
									<?php
									endif;
								} else { ?>
									<input name="original_publish" type="hidden" id="original_publish" value="<?php _e('Update') ?>"/>
									<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('Update') ?>"/>
								<?php
								} ?>
						</div>
						<div class="clear"></div>
					</div>
				</div>

			<?php
			}
		}
	}