<?php  
ob_start();
$overview = get_option('cookielawinfo_privacy_overview_content_settings', array('privacy_overview_content' => '','privacy_overview_title' => '',));
$cli_always_enable_text = __('Always Enabled', 'cookie-law-info'); 
$cli_enable_text = __('Enabled', 'cookie-law-info'); 
$cli_disable_text = __('Disabled', 'cookie-law-info'); 
$cli_privacy_readmore='<a class="cli-privacy-readmore" data-readmore-text="'.__('Show more', 'cookie-law-info').'" data-readless-text="'.__('Show less', 'cookie-law-info').'"></a>';
$third_party_cookie_options=get_option('cookielawinfo_thirdparty_settings');
$necessary_cookie_options=get_option('cookielawinfo_necessary_settings');
?>
<div class="cli-container-fluid cli-tab-container">
    <div class="cli-row">
        <div class="cli-col-12 cli-align-items-stretch cli-px-0">
            <div class="cli-privacy-overview">
                <?php  
                $overview_title = sanitize_text_field( stripslashes( isset($overview['privacy_overview_title']) ? $overview['privacy_overview_title'] : '' ) );
                $privacy_overview_content = wp_kses_post( isset( $overview['privacy_overview_content'] ) ? $overview['privacy_overview_content'] : '' );
                $privacy_overview_content = nl2br($privacy_overview_content); 
                $privacy_overview_content = do_shortcode( stripslashes($privacy_overview_content) );
                $content_length = strlen( strip_tags($privacy_overview_content) );
                $overview_title = trim( $overview_title );
                if(isset($overview_title) === true && $overview_title !== '') {
                    if( has_filter('wt_cli_change_privacy_overview_title_tag') )
                    {
                        echo apply_filters('wt_cli_change_privacy_overview_title_tag', $overview_title, '<h4>', '</h4>');
                    }
                    else 
                    {
                        echo "<h4>".$overview_title."</h4>";
                    }
                }
                ?>                                   
                <div class="cli-privacy-content">
                    <div class="cli-privacy-content-text"><?php echo $privacy_overview_content;?></div>
                </div>
                <?php echo $cli_privacy_readmore; ?>
            </div>
        </div>  
        <div class="cli-col-12 cli-align-items-stretch cli-px-0 cli-tab-section-container">
            <?php  
            $cookie_categories = self::get_cookie_categories();
            foreach ($cookie_categories as $key => $value) 
            {   
                
                $checked = false;
                $cli_checked='';
                if(isset($_COOKIE["cookielawinfo-checkbox-$key"]) && $_COOKIE["cookielawinfo-checkbox-$key"] =='yes')
                {
                    $checked = true;  
                    $cli_checked='checked';
                }
                else if(!isset($_COOKIE["cookielawinfo-checkbox-$key"]))
                {   
                    
                    $checked = true;
                    $cli_checked='checked';    
                    if($key === 'non-necessary' && ! self::wt_cli_check_thirdparty_state())
                    {
                        $checked = false;
                        $cli_checked='';   
                    }
                }
                if($key == 'necessary') 
                {   
                    $cli_switch='
                    <span class="cli-necessary-caption">'.$cli_always_enable_text.'</span> ';
                    $cli_cat_content = wp_kses_post ( stripslashes( isset($necessary_cookie_options['necessary_description']) ? $necessary_cookie_options['necessary_description'] : '' ) );
                }
                else
                {
                    $cli_switch=
                    '<div class="cli-switch">
                        <input type="checkbox" id="wt-cli-checkbox-'.$key.'" class="cli-user-preference-checkbox"  data-id="checkbox-'.$key.'" '.$cli_checked.' />
                        <label for="wt-cli-checkbox-'.$key.'" class="cli-slider" data-cli-enable="'.$cli_enable_text.'" data-cli-disable="'.$cli_disable_text.'"><span class="wt-cli-sr-only">'.$value.'</span></label>
                    </div>';
                    $cli_cat_content = wp_kses_post( stripslashes( isset($third_party_cookie_options['thirdparty_description']) ? $third_party_cookie_options['thirdparty_description'] : '' ) );
                }
            ?>  
            <?php 
            $wt_cli_is_thirdparty_enabled = (bool)( isset( $third_party_cookie_options['thirdparty_on_field'] )  ? Cookie_Law_Info::sanitise_settings('thirdparty_on_field',$third_party_cookie_options['thirdparty_on_field']) : false );
            if($key === "non-necessary" && $wt_cli_is_thirdparty_enabled == false)
            {
                echo '';
            }
            else
            {?>
                <div class="cli-tab-section">
                    <div class="cli-tab-header">
                        <a role="button" tabindex="0" class="cli-nav-link cli-settings-mobile" data-target="<?php echo $key; ?>" data-toggle="cli-toggle-tab" >
                            <?php echo $value ?> 
                        </a>
                    <?php echo $cli_switch; ?>
                    </div>
                    <div class="cli-tab-content">
                        <div class="cli-tab-pane cli-fade" data-id="<?php echo $key; ?>">
                            <p><?php echo do_shortcode( $cli_cat_content, 'cookielawinfo-category'); ?></p>
                        </div>
                    </div>
                </div>
            <?php }  } ?>
           
        </div>
    </div> 
</div> 
<?php $pop_out=ob_get_contents();
ob_end_clean();