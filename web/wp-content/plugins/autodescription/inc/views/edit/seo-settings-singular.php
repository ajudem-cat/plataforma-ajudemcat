<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
 */

use The_SEO_Framework\Bridges\PostSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'inpost', $instance );

//* Setup default vars.
$post_id = $this->get_the_real_ID(); // We also have access to object $post at the main call...

$_generator_args = [
	'id'       => $post_id,
	'taxonomy' => '',
];

switch ( $instance ) :
	case 'inpost_main':
		$default_tabs = [
			'general'    => [
				'name'     => \__( 'General', 'autodescription' ),
				'callback' => PostSettings::class . '::_general_tab',
				'dashicon' => 'admin-generic',
			],
			'social'     => [
				'name'     => \__( 'Social', 'autodescription' ),
				'callback' => PostSettings::class . '::_social_tab',
				'dashicon' => 'share',
			],
			'visibility' => [
				'name'     => \__( 'Visibility', 'autodescription' ),
				'callback' => PostSettings::class . '::_visibility_tab',
				'dashicon' => 'visibility',
			],
		];

		/**
		 * Allows for altering the inpost SEO settings metabox tabs.
		 *
		 * @since 2.9.0
		 * @since 4.0.0 Removed the second parameter (post type label)
		 *
		 * @param array $default_tabs The default tabs.
		 * @param null  $depr         The post type label. Deprecated.
		 */
		$tabs = (array) \apply_filters( 'the_seo_framework_inpost_settings_tabs', $default_tabs, null );

		echo '<div class="tsf-flex tsf-flex-inside-wrap">';
		PostSettings::_flex_nav_tab_wrapper( 'inpost', $tabs );
		echo '</div>';
		break;

	case 'inpost_general':
		if ( $this->get_option( 'display_seo_bar_metabox' ) ) :
			?>
			<div class="tsf-flex-setting tsf-flex" id="tsf-doing-it-right-wrap">
				<div class="tsf-flex-setting-label tsf-flex">
					<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
						<div class="tsf-flex-setting-label-item tsf-flex">
							<div><strong><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></strong></div>
							<div><span class="tsf-ajax"></span></div>
						</div>
					</div>
				</div>
				<div class="tsf-flex-setting-input tsf-flex">
					<div>
						<?php echo $this->get_generated_seo_bar( $_generator_args ); ?>
					</div>
				</div>
			</div>
			<?php
		endif;

		if ( $this->is_static_frontpage( $post_id ) ) {
			// When the homepage title is set, we can safely get the custom field.
			// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
			$title_placeholder = $this->escape_title( $this->get_option( 'homepage_title' ) )
							   ? $this->get_custom_field_title( $_generator_args )
							   : $this->get_generated_title( $_generator_args );
			// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

			$description_placeholder = $this->escape_description( $this->get_option( 'homepage_description' ) )
									?: $this->get_generated_description( $_generator_args );
		} else {
			$title_placeholder       = $this->get_generated_title( $_generator_args );
			$description_placeholder = $this->get_generated_description( $_generator_args );
		}

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
							'https://support.google.com/webmasters/answer/35624#page-titles'
						);
						?>
						</div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_title' );
					$this->get_option( 'display_pixel_counter' )
						and $this->output_pixel_counter_wrap( 'autodescription_title', 'title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-title-wrap">
					<input class="large-text" type="text" name="autodescription[_genesis_title]" id="autodescription_title" placeholder="<?php echo esc_attr( $title_placeholder ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_post_meta_item( '_genesis_title', $post_id ) ); ?>" autocomplete=off />
					<?php $this->output_js_title_elements(); ?>
				</div>

				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_title_no_blogname">
						<?php
						if ( $this->is_static_frontpage( $post_id ) ) :
							// Disable the input, and hide the previously stored value.
							?>
							<input type="checkbox" id="autodescription_title_no_blogname" value="1" <?php checked( $this->get_post_meta_item( '_tsf_title_no_blogname' ) ); ?> disabled />
							<input type="hidden" name="autodescription[_tsf_title_no_blogname]" value="1" <?php checked( $this->get_post_meta_item( '_tsf_title_no_blogname' ) ); ?> />
							<?php
							esc_html_e( 'Remove the blog name?', 'autodescription' );
							echo ' ';
							$this->make_info( __( 'This option must be managed on the SEO Settings page for the homepage.', 'autodescription' ) );
						else :
							?>
							<input type="checkbox" name="autodescription[_tsf_title_no_blogname]" id="autodescription_title_no_blogname" value="1" <?php checked( $this->get_post_meta_item( '_tsf_title_no_blogname' ) ); ?> />
							<?php
							esc_html_e( 'Remove the blog name?', 'autodescription' );
							echo ' ';
							$this->make_info( __( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ) );
						endif;
						?>
					</label>
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
							'https://support.google.com/webmasters/answer/35624#meta-descriptions'
						);
						?>
						</div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_description' );
					$this->get_option( 'display_pixel_counter' )
						and $this->output_pixel_counter_wrap( 'autodescription_description', 'description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_genesis_description]" id="autodescription_description" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="4" cols="4" autocomplete=off><?php echo $this->esc_attr_preserve_amp( $this->get_post_meta_item( '_genesis_description', $post_id ) ); ?></textarea>
				<?php $this->output_js_description_elements(); ?>
			</div>
		</div>
		<?php
		break;

	case 'inpost_visibility':
		$canonical             = $this->get_post_meta_item( '_genesis_canonical_uri' );
		$canonical_placeholder = $this->create_canonical_url( $_generator_args );

		//* Get robots defaults.
		$r_defaults = $this->robots_meta(
			$_generator_args,
			The_SEO_Framework\ROBOTS_IGNORE_SETTINGS | The_SEO_Framework\ROBOTS_IGNORE_PROTECTION
		);
		$r_settings = [
			'noindex'   => [
				'id'        => 'autodescription_noindex',
				'option'    => '_genesis_noindex',
				'force_on'  => 'index',
				'force_off' => 'noindex',
				'label'     => __( 'Indexing', 'autodescription' ),
				'_default'  => empty( $r_defaults['noindex'] ) ? 'index' : 'noindex',
			],
			'nofollow'  => [
				'id'        => 'autodescription_nofollow',
				'option'    => '_genesis_nofollow',
				'force_on'  => 'follow',
				'force_off' => 'nofollow',
				'label'     => __( 'Link following', 'autodescription' ),
				'_default'  => empty( $r_defaults['nofollow'] ) ? 'follow' : 'nofollow',
			],
			'noarchive' => [
				'id'        => 'autodescription_noarchive',
				'option'    => '_genesis_noarchive',
				'force_on'  => 'archive',
				'force_off' => 'noarchive',
				'label'     => __( 'Archiving', 'autodescription' ),
				'_default'  => empty( $r_defaults['noarchive'] ) ? 'archive' : 'noarchive',
			],
		];

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_canonical" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
							$this->make_info(
								__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
								'https://support.google.com/webmasters/answer/139066'
							);
						?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="url" name="autodescription[_genesis_canonical_uri]" id="autodescription_canonical" placeholder="<?php echo esc_url( $canonical_placeholder ); ?>" value="<?php echo esc_url( $this->get_post_meta_item( '_genesis_canonical_uri' ) ); ?>" autocomplete=off />
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<div class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Robots Meta Settings', 'autodescription' ); ?></strong></div>
						<div>
						<?php
							$this->make_info(
								__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this page.', 'autodescription' ),
								'https://developers.google.com/search/reference/robots_meta_tag#valid-indexing--serving-directives'
							);
						?>
						</div>
					</div>
					<?php
					if ( $this->is_static_frontpage( $post_id ) ) {
						printf(
							'<div class=tsf-flex-setting-label-sub-item><span class="description attention">%s</span></div>',
							esc_html__( 'Warning: No public site should ever apply "noindex" or "nofollow" to the homepage.', 'autodescription' )
						);
						printf(
							'<div class=tsf-flex-setting-label-sub-item><span class="description">%s</span></div>',
							esc_html__( 'Note: A non-default selection will overwrite the global homepage settings.', 'autodescription' )
						);
					}
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<?php
				foreach ( $r_settings as $_s ) :
					?>
					<div class="tsf-flex-setting tsf-flex">
						<div class="tsf-flex-setting-label tsf-flex">
							<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
								<label for="<?php echo esc_attr( $_s['id'] ); ?>" class="tsf-flex-setting-label-item tsf-flex">
									<div><strong><?php echo esc_html( $_s['label'] ); ?></strong></div>
								</label>
							</div>
						</div>
						<div class="tsf-flex-setting-input tsf-flex">
						<?php
							// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
							echo $this->make_single_select_form( [
								'id'      => $_s['id'],
								'class'   => 'tsf-select-block',
								'name'    => sprintf( 'autodescription[%s]', $_s['option'] ),
								'label'   => '',
								'options' => [
									/* translators: %s = default option value */
									0  => sprintf( __( 'Default (%s)', 'autodescription' ), $_s['_default'] ),
									-1 => $_s['force_on'],
									1  => $_s['force_off'],
								],
								'default' => $this->get_post_meta_item( $_s['option'] ),
							] );
							// phpcs:enable, WordPress.Security.EscapeOutput
						?>
						</div>
					</div>
					<?php
				endforeach;
				?>
			</div>
		</div>

		<?php
		$can_do_archive_query = $this->post_type_supports_taxonomies() && $this->get_option( 'alter_archive_query' );
		$can_do_search_query  = (bool) $this->get_option( 'alter_search_query' );
		?>

		<?php if ( $can_do_archive_query || $can_do_search_query ) : ?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<div class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Archive Settings', 'autodescription' ); ?></strong></div>
					</div>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<?php if ( $can_do_search_query ) : ?>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_exclude_local_search"><input type="checkbox" name="autodescription[exclude_local_search]" id="autodescription_exclude_local_search" value="1" <?php checked( $this->get_post_meta_item( 'exclude_local_search' ) ); ?> />
						<?php
						esc_html_e( 'Exclude this page from all search queries on this site.', 'autodescription' );
						?>
					</label>
				</div>
				<?php endif; ?>
				<?php if ( $can_do_archive_query ) : ?>
				<div class="tsf-checkbox-wrapper">
					<label for="autodescription_exclude_from_archive"><input type="checkbox" name="autodescription[exclude_from_archive]" id="autodescription_exclude_from_archive" value="1" <?php checked( $this->get_post_meta_item( 'exclude_from_archive' ) ); ?> />
						<?php
						esc_html_e( 'Exclude this page from all archive queries on this site.', 'autodescription' );
						?>
					</label>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_redirect" class="tsf-flex-setting-label-item tsf-flex">
						<div>
							<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
						</div>
						<div>
							<?php
							$this->make_info(
								__( 'This will force visitors to go to another URL.', 'autodescription' ),
								'https://support.google.com/webmasters/answer/93633'
							);
							?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="url" name="autodescription[redirect]" id="autodescription_redirect" value="<?php echo esc_url( $this->get_post_meta_item( 'redirect' ) ); ?>" autocomplete=off />
			</div>
		</div>
		<?php
		break;

	case 'inpost_social':
		$social_placeholders = $this->_get_social_placeholders( $_generator_args );

		// Yes, this is hacky, but we don't want to lose the user's input.
		$show_og = (bool) $this->get_option( 'og_tags' );
		$show_tw = (bool) $this->get_option( 'twitter_tags' );

		?>
		<div class="tsf-flex-setting tsf-flex" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_og_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Open Graph Title', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_og_title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-og-title-wrap">
					<input class="large-text" type="text" name="autodescription[_open_graph_title]" id="autodescription_og_title" placeholder="<?php echo esc_attr( $social_placeholders['title']['og'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_post_meta_item( '_open_graph_title' ) ); ?>" autocomplete=off />
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_og_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Open Graph Description', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_og_description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_open_graph_description]" id="autodescription_og_description" placeholder="<?php echo esc_attr( $social_placeholders['description']['og'] ); ?>" rows="3" cols="4" autocomplete=off><?php echo $this->esc_attr_preserve_amp( $this->get_post_meta_item( '_open_graph_description' ) ); ?></textarea>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_twitter_title" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Twitter Title', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_twitter_title' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<div id="tsf-twitter-title-wrap">
					<input class="large-text" type="text" name="autodescription[_twitter_title]" id="autodescription_twitter_title" placeholder="<?php echo esc_attr( $social_placeholders['title']['twitter'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_post_meta_item( '_twitter_title' ) ); ?>" autocomplete=off />
				</div>
			</div>
		</div>

		<div class="tsf-flex-setting tsf-flex" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_twitter_description" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong>
							<?php
							esc_html_e( 'Twitter Description', 'autodescription' );
							?>
						</strong></div>
					</label>
					<?php
					$this->get_option( 'display_character_counter' )
						and $this->output_character_counter_wrap( 'autodescription_twitter_description' );
					?>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<textarea class="large-text" name="autodescription[_twitter_description]" id="autodescription_twitter_description" placeholder="<?php echo esc_attr( $social_placeholders['description']['twitter'] ); ?>" rows="3" cols="4" autocomplete=off><?php echo $this->esc_attr_preserve_amp( $this->get_post_meta_item( '_twitter_description' ) ); ?></textarea>
			</div>
		</div>
		<?php

		//* Fetch image placeholder.
		if ( $this->is_static_frontpage( $post_id ) && $this->get_option( 'homepage_social_image_url' ) ) {
			$image_details     = current( $this->get_image_details( $_generator_args, true, 'social', true ) );
			$image_placeholder = isset( $image_details['url'] ) ? $image_details['url'] : '';
		} else {
			$image_details     = current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) );
			$image_placeholder = isset( $image_details['url'] ) ? $image_details['url'] : '';
		}

		?>
		<div class="tsf-flex-setting tsf-flex">
			<div class="tsf-flex-setting-label tsf-flex">
				<div class="tsf-flex-setting-label-inner-wrap tsf-flex">
					<label for="autodescription_socialimage-url" class="tsf-flex-setting-label-item tsf-flex">
						<div><strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong></div>
						<div>
						<?php
						$this->make_info(
							__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
							'https://developers.facebook.com/docs/sharing/best-practices#images'
						);
						?>
						</div>
					</label>
				</div>
			</div>
			<div class="tsf-flex-setting-input tsf-flex">
				<input class="large-text" type="url" name="autodescription[_social_image_url]" id="autodescription_socialimage-url" placeholder="<?php echo esc_url( $image_placeholder ); ?>" value="<?php echo esc_url( $this->get_post_meta_item( '_social_image_url' ) ); ?>" autocomplete=off />
				<input type="hidden" name="autodescription[_social_image_id]" id="autodescription_socialimage-id" value="<?php echo absint( $this->get_post_meta_item( '_social_image_id' ) ); ?>" disabled class="tsf-enable-media-if-js" />
				<div class="hide-if-no-tsf-js tsf-social-image-buttons">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput -- Already escaped.
					echo $this->get_social_image_uploader_form( 'autodescription_socialimage' );
					?>
				</div>
			</div>
		</div>
		<?php
		break;
endswitch;
