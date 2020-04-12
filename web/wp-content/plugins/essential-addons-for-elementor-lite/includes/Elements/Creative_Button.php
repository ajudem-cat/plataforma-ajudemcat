<?php
namespace Essential_Addons_Elementor\Elements;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Scheme_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Widget_Base;
use \Elementor\Icons_Manager;

class Creative_Button extends Widget_Base {
	

	public function get_name() {
		return 'eael-creative-button';
	}

	public function get_title() {
		return esc_html__( 'Creative Button', 'essential-addons-for-elementor-lite');
	}

	public function get_icon() {
		return 'eaicon-creative-button';
	}

   	public function get_categories() {
		return [ 'essential-addons-elementor' ];
	}
    
	public function get_keywords()
	{
        return [
			'button',
			'ea button',
			'creative button',
			'ea creative button',
			'cta',
			'call to action',
			'ea',
			'marketing button',
			'essential addons'
		];
    }

	public function get_custom_help_url()
	{
        return 'https://essential-addons.com/elementor/docs/creative-buttons/';
    }


	protected function _register_controls() {


		if(!apply_filters('eael/pro_enabled', false)) {

			// Content Controls
			$this->start_controls_section(
				'eael_section_creative_button_content',
				[
					'label' => esc_html__( 'Button Content', 'essential-addons-for-elementor-lite')
				]
			);


			$this->add_control(
				'creative_button_text',
				[
					'label'       => __( 'Button Text', 'essential-addons-for-elementor-lite'),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => 'Click Me!',
					'placeholder' => __( 'Enter button text', 'essential-addons-for-elementor-lite'),
					'title'       => __( 'Enter button text here', 'essential-addons-for-elementor-lite'),
				]
			);

			$this->add_control(
				'creative_button_secondary_text',
				[
					'label'       => __( 'Button Secondary Text', 'essential-addons-for-elementor-lite'),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => 'Go!',
					'placeholder' => __( 'Enter button secondary text', 'essential-addons-for-elementor-lite'),
					'title'       => __( 'Enter button secondary text here', 'essential-addons-for-elementor-lite'),
				]
			);
			
			$this->add_control(
				'creative_button_link_url',
				[
					'label'       => esc_html__( 'Link URL', 'essential-addons-for-elementor-lite'),
					'type'        => Controls_Manager::URL,
					'label_block' => true,
					'default'     => [
						'url'         => '#',
						'is_external' => '',
					],
					'show_external' => true,
				]
			);

			$this->add_control(
				'eael_creative_button_icon_new',
				[
					'label' => esc_html__( 'Icon', 'essential-addons-for-elementor-lite'),
					'type'  => Controls_Manager::ICONS,
					'fa4compatibility' => 'eael_creative_button_icon',
					'condition'	=> [
						'creative_button_effect!' => ['eael-creative-button--tamaya']
					]
				]
			);

			$this->add_control(
				'eael_creative_button_icon_alignment',
				[
					'label'   => esc_html__( 'Icon Position', 'essential-addons-for-elementor-lite'),
					'type'    => Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'left'  => esc_html__( 'Before', 'essential-addons-for-elementor-lite'),
						'right' => esc_html__( 'After', 'essential-addons-for-elementor-lite'),
					],
					'condition' => [
						'eael_creative_button_icon_new!' => '',
						'creative_button_effect!' => ['eael-creative-button--tamaya']
					],
				]
			);
			

			$this->add_responsive_control(
				'eael_creative_button_icon_indent',
				[
					'label' => esc_html__( 'Icon Spacing', 'essential-addons-for-elementor-lite'),
					'type'  => Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 60,
						],
					],
					'condition' => [
						'eael_creative_button_icon_new!' => '',
						'creative_button_effect!' => ['eael-creative-button--tamaya']
					],
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button-icon-right' => 'margin-left: {{SIZE}}px;',
						'{{WRAPPER}} .eael-creative-button-icon-left'  => 'margin-right: {{SIZE}}px;',
						'{{WRAPPER}} .eael-creative-button--shikoba i' => 'left: {{SIZE}}%;',
					],
				]
			);
		} else {
			do_action('eael_creative_button_pro_controls', $this);
		}



		$this->end_controls_section();
		
        if(!apply_filters('eael/pro_enabled', false)) {
			$this->start_controls_section(
				'eael_section_pro',
				[
					'label' => __( 'Go Premium for More Features', 'essential-addons-for-elementor-lite')
				]
			);
		
			$this->add_control(
				'eael_control_get_pro',
				[
					'label'   => __( 'Unlock more possibilities', 'essential-addons-for-elementor-lite'),
					'type'    => Controls_Manager::CHOOSE,
					'options' => [
						'1' => [
							'title' => __( '', 'essential-addons-for-elementor-lite'),
							'icon'  => 'fa fa-unlock-alt',
						],
					],
					'default'     => '1',
					'description' => '<span class="pro-feature"> Get the  <a href="https://wpdeveloper.net/in/upgrade-essential-addons-elementor" target="_blank">Pro version</a> for more stunning elements and customization options.</span>'
				]
			);
			
			$this->end_controls_section();
		}

		// Style Controls
		$this->start_controls_section(
			'eael_section_creative_button_settings',
			[
				'label' => esc_html__( 'Button Effects &amp; Styles', 'essential-addons-for-elementor-lite'),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);
		if(!apply_filters('eael/pro_enabled', false)) {
			$this->add_control(
				'creative_button_effect',
				[
					'label'       => esc_html__( 'Set Button Effect', 'essential-addons-for-elementor-lite'),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'eael-creative-button--default',
					'options'     => [
						'eael-creative-button--default' 	=> esc_html__( 'Default', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--winona' 		=> esc_html__( 'Winona', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--ujarak' 		=> esc_html__( 'Ujarak', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--wayra' 		=> esc_html__( 'Wayra', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--tamaya' 		=> esc_html__( 'Tamaya', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--rayen' 		=> esc_html__( 'Rayen', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--pipaluk' 	=> esc_html__( 'Pipaluk (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--moema' 		=> esc_html__( 'Moema (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--wave' 		=> esc_html__( 'Wave (Pro)', 		'essential-addons-for-elementor-lite' ),
						'eael-creative-button--aylen' 		=> esc_html__( 'Aylen (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--saqui' 		=> esc_html__( 'Saqui (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--wapasha' 	=> esc_html__( 'Wapasha (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--nuka' 		=> esc_html__( 'Nuka (Pro)', 		'essential-addons-for-elementor-lite' ),
						'eael-creative-button--antiman' 	=> esc_html__( 'Antiman (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--quidel' 		=> esc_html__( 'Quidel (Pro)', 	'essential-addons-for-elementor-lite' ),
						'eael-creative-button--shikoba' 	=> esc_html__( 'Shikoba (Pro)', 	'essential-addons-for-elementor-lite' ),
					],
					'condition' => [
						'use_gradient_background' => ''
					],
					'description' => '10 more effects on <a href="https://wpdeveloper.net/in/upgrade-essential-addons-elementor">Pro version</a>'
				]
			);
			$this->add_control(
				'use_gradient_background',
				[
					'label' => __( 'Use Gradient Background', 'essential-addons-for-elementor-lite'),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'essential-addons-for-elementor-lite'),
					'label_off' => __( 'Hide', 'essential-addons-for-elementor-lite'),
					'return_value' => 'yes',
					'default' => '',
				]
			);
			$this->start_controls_tabs( 'eael_creative_button_tabs' );

			$this->start_controls_tab( 'normal', [ 'label' => esc_html__( 'Normal', 'essential-addons-for-elementor-lite') ] );

			$this->add_control(
				'eael_creative_button_text_color',
				[
					'label'     => esc_html__( 'Text Color', 'essential-addons-for-elementor-lite'),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button'                                      => 'color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--tamaya::before' => 'color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--tamaya::after'  => 'color: {{VALUE}};',
					],
				]
			);
			$this->add_control(
				'eael_creative_button_background_color',
				[
					'label' => esc_html__( 'Background Color', 'essential-addons-for-elementor-lite'),
					'type' => Controls_Manager::COLOR,
					'default' => '#f54',
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--ujarak:hover' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--wayra:hover' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--tamaya::before' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--tamaya::after' => 'background-color: {{VALUE}};',
					],
					'condition' => [
						'use_gradient_background' => ''
					],
				]
			);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name' => 'eael_creative_button_gradient_background',
					'types' => [ 'gradient', 'classic' ],
					'selector' => '{{WRAPPER}} .eael-creative-button',
					'condition' => [
						'use_gradient_background' => 'yes'
					],
				]
			);
			
			$this->add_group_control(
				Group_Control_Border:: get_type(),
				[
					'name'     => 'eael_creative_button_border',
					'selector' => '{{WRAPPER}} .eael-creative-button',
				]
			);
			
			$this->add_control(
				'eael_creative_button_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'essential-addons-for-elementor-lite'),
					'type'  => Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button'         => 'border-radius: {{SIZE}}px;',
						'{{WRAPPER}} .eael-creative-button::before' => 'border-radius: {{SIZE}}px;',
						'{{WRAPPER}} .eael-creative-button::after'  => 'border-radius: {{SIZE}}px;',
					],
				]
			);
			

			
			$this->end_controls_tab();

			$this->start_controls_tab( 'eael_creative_button_hover', [ 'label' => esc_html__( 'Hover', 'essential-addons-for-elementor-lite') ] );

			$this->add_control(
				'eael_creative_button_hover_text_color',
				[
					'label'     => esc_html__( 'Text Color', 'essential-addons-for-elementor-lite'),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button:hover' => 'color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--winona::after' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'eael_creative_button_hover_background_color',
				[
					'label' => esc_html__( 'Background Color', 'essential-addons-for-elementor-lite'),
					'type' => Controls_Manager::COLOR,
					'default' => '#f54',
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button:hover'                                     => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--ujarak::before'      => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--wayra:hover::before' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--tamaya:hover'        => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--rayen::before'       => 'background-color: {{VALUE}};',
					],
					'condition' => [
						'use_gradient_background' => ''
					],
				]
			);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name' => 'eael_creative_button_hover_gradient_background',
					'types' => [ 'gradient', 'classic' ],
					'selector' => '{{WRAPPER}} .eael-creative-button:hover',
					'condition' => [
						'use_gradient_background' => 'yes'
					],
				]
			);

			$this->add_control(
				'eael_creative_button_hover_border_color',
				[
					'label'     => esc_html__( 'Border Color', 'essential-addons-for-elementor-lite'),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button:hover'                                 => 'border-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--wapasha::before' => 'border-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--antiman::before' => 'border-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--pipaluk::before' => 'border-color: {{VALUE}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--quidel::before'  => 'background-color: {{VALUE}};',
					],
				]
			);
			
			$this->end_controls_tab();
			
			$this->end_controls_tabs();

			$this->add_responsive_control(
				'eael_creative_button_alignment',
				[
					'label'       => esc_html__( 'Button Alignment', 'essential-addons-for-elementor-lite'),
					'type'        => Controls_Manager::CHOOSE,
					'label_block' => true,
					'options'     => [
						'flex-start' => [
							'title' => esc_html__( 'Left', 'essential-addons-for-elementor-lite'),
							'icon'  => 'fa fa-align-left',
						],
						'center' => [
							'title' => esc_html__( 'Center', 'essential-addons-for-elementor-lite'),
							'icon'  => 'fa fa-align-center',
						],
						'flex-end' => [
							'title' => esc_html__( 'Right', 'essential-addons-for-elementor-lite'),
							'icon'  => 'fa fa-align-right',
						],
					],
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button-wrapper' => 'justify-content: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'eael_creative_button_width',
				[
					'label'      => esc_html__( 'Width', 'essential-addons-for-elementor-lite'),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range'      => [
						'px' => [
							'min'  => 0,
							'max'  => 500,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography:: get_type(),
				[
					'name'     => 'eael_creative_button_typography',
					'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .eael-creative-button .cretive-button-text, {{WRAPPER}} .eael-creative-button--winona::after'
				]
			);


			$this->add_responsive_control(
				'eael_creative_button_icon_size',
				[
					'label'      => esc_html__( 'Icon Size', 'essential-addons-for-elementor-lite'),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'default' => [
						'size' => 30,
						'unit' => 'px',
					],
					'range'      => [
						'px' => [
							'min'  => 0,
							'max'  => 500,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button img' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};',
					],
				]
			);
			
			$this->add_responsive_control(
				'eael_creative_button_padding',
				[
					'label'      => esc_html__( 'Button Padding', 'essential-addons-for-elementor-lite'),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .eael-creative-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--winona::after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--winona > .creative-button-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--tamaya::before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--rayen::before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--rayen > .creative-button-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .eael-creative-button.eael-creative-button--saqui::after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			
		}else {
			do_action('eael_creative_button_style_pro_controls', $this);
		}
			
			
		$this->add_group_control(
			Group_Control_Box_Shadow:: get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .eael-creative-button',
			]
		);
			
			
		$this->end_controls_section();
		
		
		
		$this->end_controls_section();	
		
		
	}


	protected function render() {
		$settings = $this->get_settings();
		$icon_migrated = isset($settings['__fa4_migrated']['eael_creative_button_icon_new']);
		$icon_is_new = empty($settings['eael_creative_button_icon']);

		$this->add_render_attribute( 'eael_creative_button', [
			'class'	=> [ 'eael-creative-button', esc_attr($settings['creative_button_effect'] ) ],
			'href'	=> esc_attr($settings['creative_button_link_url']['url'] ),
		]);

		if( $settings['creative_button_link_url']['is_external'] ) {
			$this->add_render_attribute( 'eael_creative_button', 'target', '_blank' );
		}
		
		if( $settings['creative_button_link_url']['nofollow'] ) {
			$this->add_render_attribute( 'eael_creative_button', 'rel', 'nofollow' );
		}

		$this->add_render_attribute( 'eael_creative_button', 'data-text', esc_attr($settings['creative_button_secondary_text'] ));
	?>
	<div class="eael-creative-button-wrapper">
		
		<a <?php echo $this->get_render_attribute_string( 'eael_creative_button' ); ?>>
			
			<div class="creative-button-inner">
				
				<?php if ( $settings['creative_button_effect'] !== 'eael-creative-button--tamaya' && $settings['eael_creative_button_icon_alignment'] == 'left' ) : ?>
					<?php if($icon_migrated || $icon_is_new) { ?>
						<?php if ( isset( $settings['eael_creative_button_icon_new']['value']['url']) ) : ?>
							<img src="<?php echo esc_attr($settings['eael_creative_button_icon_new']['value']['url'] ); ?>" class="eael-creative-button-icon-left" alt="<?php echo esc_attr(get_post_meta($settings['eael_creative_button_icon_new']['value']['id'], '_wp_attachment_image_alt', true)); ?>">
						<?php else : ?>
							<?php if( ! empty($settings['eael_creative_button_icon_new']['value']) ) {
								echo '<i class="'.esc_attr($settings['eael_creative_button_icon_new']['value'] ).' eael-creative-button-icon-left" aria-hidden="true"></i>';
							} ?>
						<?php endif; ?>
					<?php } else { ?>
						<i class="<?php echo esc_attr($settings['eael_creative_button_icon'] ); ?> eael-creative-button-icon-left" aria-hidden="true"></i> 
					<?php } ?>
				<?php endif; ?>

				<span class="cretive-button-text"><?php echo $settings['creative_button_text']; ?></span>

				<?php if ($settings['creative_button_effect'] !== 'eael-creative-button--tamaya' && $settings['eael_creative_button_icon_alignment'] == 'right' ) : ?>
					<?php if($icon_migrated || $icon_is_new) { ?>
						<?php if ( isset( $settings['eael_creative_button_icon_new']['value']['url']) ) : ?>
							<img src="<?php echo esc_attr($settings['eael_creative_button_icon_new']['value']['url'] ); ?>" class="eael-creative-button-icon-right" alt="<?php echo esc_attr(get_post_meta($settings['eael_creative_button_icon_new']['value']['id'], '_wp_attachment_image_alt', true)); ?>">
						<?php else : ?>
							<?php if( ! empty($settings['eael_creative_button_icon_new']['value']) ) {
								echo '<i class="'.esc_attr($settings['eael_creative_button_icon_new']['value'] ).' eael-creative-button-icon-right" aria-hidden="true"></i>';
							} ?>
						<?php endif; ?>
					<?php } else { ?>
						<i class="<?php echo esc_attr($settings['eael_creative_button_icon'] ); ?> eael-creative-button-icon-right" aria-hidden="true"></i> 
					<?php } ?>
				<?php endif; ?>

			</div>
		</a>
	</div>
	<?php
	
	}
}