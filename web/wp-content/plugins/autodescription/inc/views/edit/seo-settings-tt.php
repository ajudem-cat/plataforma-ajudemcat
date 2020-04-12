<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Term
 */

use The_SEO_Framework\Bridges\TermSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch Term ID and taxonomy.
$term_id = $term->term_id;
$meta    = $this->get_term_meta( $term_id );

$title       = $meta['doctitle'];
$description = $meta['description'];
$canonical   = $meta['canonical'];
$noindex     = $meta['noindex'];
$nofollow    = $meta['nofollow'];
$noarchive   = $meta['noarchive'];
$redirect    = $meta['redirect'];

$social_image_url = $meta['social_image_url'];
$social_image_id  = $meta['social_image_id'];

$og_title       = $meta['og_title'];
$og_description = $meta['og_description'];
$tw_title       = $meta['tw_title'];
$tw_description = $meta['tw_description'];

$_generator_args = [
	'id'       => $term_id,
	'taxonomy' => $taxonomy,
];

$show_og = (bool) $this->get_option( 'og_tags' );
$show_tw = (bool) $this->get_option( 'twitter_tags' );

$social_placeholders = $this->_get_social_placeholders( $_generator_args );

$title_placeholder       = $this->get_generated_title( $_generator_args );
$description_placeholder = $this->get_generated_description( $_generator_args );

//! Social image placeholder.
$image_details     = current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) );
$image_placeholder = isset( $image_details['url'] ) ? $image_details['url'] : '';

$canonical_placeholder = $this->create_canonical_url( $_generator_args ); // implies get_custom_field = false
$robots_defaults       = $this->robots_meta( $_generator_args, The_SEO_Framework\ROBOTS_IGNORE_PROTECTION | The_SEO_Framework\ROBOTS_IGNORE_SETTINGS );

// TODO reintroduce the info blocks, and place the labels at the left, instead??
$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-meta[noindex]',
		'name'      => 'autodescription-meta[noindex]',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => __( 'Indexing', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noindex'] ) ? 'index' : 'noindex',
		'_value'    => $noindex,
		'_info'     => [
			__( 'This tells search engines not to show this term in their search results.', 'autodescription' ),
			'https://support.google.com/webmasters/answer/93710',
		],
	],
	'nofollow'  => [
		'id'        => 'autodescription-meta[nofollow]',
		'name'      => 'autodescription-meta[nofollow]',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => __( 'Link following', 'autodescription' ),
		'_default'  => empty( $robots_defaults['nofollow'] ) ? 'follow' : 'nofollow',
		'_value'    => $nofollow,
		'_info'     => [
			__( 'This tells search engines not to follow links on this term.', 'autodescription' ),
			'https://support.google.com/webmasters/answer/96569',
		],
	],
	'noarchive' => [
		'id'        => 'autodescription-meta[noarchive]',
		'name'      => 'autodescription-meta[noarchive]',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => __( 'Archiving', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noarchive'] ) ? 'archive' : 'noarchive',
		'_value'    => $noarchive,
		'_info'     => [
			__( 'This tells search engines not to save a cached copy of this term.', 'autodescription' ),
			'https://support.google.com/webmasters/answer/79812',
		],
	],
];

?>
<h2><?php esc_html_e( 'General SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<?php if ( $this->get_option( 'display_seo_bar_metabox' ) ) : ?>
		<tr class="form-field">
			<th scope="row" valign="top"><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
			<td>
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput -- get_generated_seo_bar() escapes.
				echo $this->get_generated_seo_bar( $_generator_args );
				?>
			</td>
		</tr>
		<?php endif; ?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[doctitle]">
					<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624#page-titles'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[doctitle]' );
				$this->get_option( 'display_pixel_counter' )
					and $this->output_pixel_counter_wrap( 'autodescription-meta[doctitle]', 'title' );
				?>
			</th>
			<td>
				<div id="tsf-title-wrap">
					<input name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" type="text" placeholder="<?php echo esc_attr( $title_placeholder ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $title ); ?>" size="40" autocomplete=off />
					<?php $this->output_js_title_elements(); ?>
				</div>
				<label for="autodescription-meta[title_no_blog_name]" class="tsf-term-checkbox-wrap">
					<input type="checkbox" name="autodescription-meta[title_no_blog_name]" id="autodescription-meta[title_no_blog_name]" value="1" <?php checked( $this->get_term_meta_item( 'title_no_blog_name', $term_id ) ); ?> />
					<?php
					esc_html_e( 'Remove the blog name?', 'autodescription' );
					echo ' ';
					$this->make_info( __( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ) );
					?>
				</label>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[description]">
					<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624#meta-descriptions'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[description]' );
				$this->get_option( 'display_pixel_counter' )
					and $this->output_pixel_counter_wrap( 'autodescription-meta[description]', 'description' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="4" cols="50" class="large-text" autocomplete=off><?php echo $this->esc_attr_preserve_amp( $description ); ?></textarea>
				<?php
				$this->output_js_description_elements();
				?>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Social SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<tr class="form-field" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[og_title]">
					<strong><?php esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[og_title]' );
				?>
			</th>
			<td>
				<div id="tsf-og-title-wrap">
					<input name="autodescription-meta[og_title]" id="autodescription-meta[og_title]" type="text" placeholder="<?php echo esc_attr( $social_placeholders['title']['og'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $og_title ); ?>" size="40" autocomplete=off />
				</div>
			</td>
		</tr>

		<tr class="form-field" <?php echo $show_og ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[og_description]">
					<strong><?php esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[og_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[og_description]" id="autodescription-meta[og_description]" placeholder="<?php echo esc_attr( $social_placeholders['description']['og'] ); ?>" rows="4" cols="50" class="large-text" autocomplete=off><?php echo $this->esc_attr_preserve_amp( $og_description ); ?></textarea>
			</td>
		</tr>

		<tr class="form-field" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[tw_title]">
					<strong><?php esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[tw_title]' );
				?>
			</th>
			<td>
				<div id="tsf-tw-title-wrap">
					<input name="autodescription-meta[tw_title]" id="autodescription-meta[tw_title]" type="text" placeholder="<?php echo esc_attr( $social_placeholders['title']['twitter'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $tw_title ); ?>" size="40" autocomplete=off />
				</div>
			</td>
		</tr>

		<tr class="form-field" <?php echo $show_tw ? '' : 'style=display:none'; ?>>
			<th scope="row" valign="top">
				<label for="autodescription-meta[tw_description]">
					<strong><?php esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[tw_description]' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[tw_description]" id="autodescription-meta[tw_description]" placeholder="<?php echo esc_attr( $social_placeholders['description']['twitter'] ); ?>" rows="4" cols="50" class="large-text" autocomplete=off><?php echo $this->esc_attr_preserve_amp( $tw_description ); ?></textarea>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription_meta_socialimage-url">
					<strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
						'https://developers.facebook.com/docs/sharing/best-practices#images'
					);
					?>
				</label>
			</th>
			<td>
				<input name="autodescription-meta[social_image_url]" id="autodescription_meta_socialimage-url" type="url" placeholder="<?php echo esc_attr( $image_placeholder ); ?>" value="<?php echo esc_attr( $social_image_url ); ?>" size="40" autocomplete=off />
				<input type="hidden" name="autodescription-meta[social_image_id]" id="autodescription_meta_socialimage-id" value="<?php echo absint( $social_image_id ); ?>" disabled class="tsf-enable-media-if-js" />
				<div class="hide-if-no-tsf-js tsf-term-button-wrap">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput -- Already escaped.
					echo $this->get_social_image_uploader_form( 'autodescription_meta_socialimage' );
					?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<h2><?php esc_html_e( 'Visibility SEO Settings', 'autodescription' ); ?></h2>

<table class="form-table tsf-term-meta">
	<tbody>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[canonical]">
					<strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/139066'
					);
					?>
				</label>
			</th>
			<td>
				<input name="autodescription-meta[canonical]" id="autodescription-meta[canonical]" type=url placeholder="<?php echo esc_attr( $canonical_placeholder ); ?>" value="<?php echo esc_attr( $canonical ); ?>" size="40" autocomplete=off />
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<?php
				esc_html_e( 'Robots Meta Settings', 'autodescription' );
				echo ' ';
				$this->make_info(
					__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/reference/robots_meta_tag#valid-indexing--serving-directives'
				);
				?>
				</th>
			<td>
				<?php
				foreach ( $robots_settings as $_s ) :
					// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
					echo $this->make_single_select_form( [
						'id'      => $_s['id'],
						'class'   => 'tsf-term-select-wrap',
						'name'    => $_s['name'],
						'label'   => $_s['label'],
						'options' => [
							/* translators: %s = default option value */
							0  => sprintf( __( 'Default (%s)', 'autodescription' ), $_s['_default'] ),
							-1 => $_s['force_on'],
							1  => $_s['force_off'],
						],
						'default' => $_s['_value'],
						'info'    => $_s['_info'],
					] );
					// phpcs:enable, WordPress.Security.EscapeOutput
				endforeach;
				?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[redirect]">
					<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/93633'
					);
					?>
				</label>
			</th>
			<td>
				<input name="autodescription-meta[redirect]" id="autodescription-meta[redirect]" type=url value="<?php echo esc_attr( $redirect ); ?>" size="40" autocomplete=off />
			</td>
		</tr>
	</tbody>
</table>
<?php
