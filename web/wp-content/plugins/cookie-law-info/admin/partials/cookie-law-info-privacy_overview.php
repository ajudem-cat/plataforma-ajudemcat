<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}  
if (!current_user_can('manage_options')) 
{
    wp_die(__('You do not have sufficient permission to perform this operation', 'cookie-law-info'));
}
$options = array(
    'privacy_overview_title',
    'privacy_overview_content',
);
// Get options:
$stored_options = get_option('cookielawinfo_privacy_overview_content_settings', array(
    'privacy_overview_content' => '','privacy_overview_title' => '',
)); 
// Check if form has been set:
if (isset($_POST['update_privacy_overview_content_settings_form'])) {

    // Check nonce:
    check_admin_referer('cookielawinfo-update-privacy-overview-content');

    $stored_options['privacy_overview_title'] = sanitize_text_field( isset( $_POST['privacy_overview_title'] )  ? $_POST['privacy_overview_title'] : $stored_options['privacy_overview_title'] );
    $stored_options['privacy_overview_content'] = wp_kses_post( isset( $_POST['privacy_overview_content'] ) && $_POST['privacy_overview_content'] !== '' ? $_POST['privacy_overview_content'] : $stored_options['privacy_overview_content'] );
    update_option('cookielawinfo_privacy_overview_content_settings', $stored_options);
    echo '<div class="updated"><p><strong>' . __('Settings Updated.', 'cookie-law-info') . '</strong></p></div>';
}

$stored_options = get_option('cookielawinfo_privacy_overview_content_settings', array(
   'privacy_overview_content' => '','privacy_overview_title' => '',
));
$privacy_title = isset($stored_options['privacy_overview_title']) ? $stored_options['privacy_overview_title'] : '';
$privacy_content = isset($stored_options['privacy_overview_content']) ? $stored_options['privacy_overview_content'] : '';
?>
<style>
    .vvv_textbox{
        height: 150px;
        width: 80%;
    }
    .cli-textbox{
        width: 100%;
        height: 35px;
        margin-bottom: 5px;
    }
</style>
<div class="wrap">

    <div class="cookie-law-info-form-container">
        <div class="cli-plugin-toolbar top">
            <h3><?php _e('Privacy Overview', 'cookie-law-info');?></h3>
        </div>
        <form method="post" action="<?php echo esc_url($_SERVER["REQUEST_URI"]); ?>">
        <?php wp_nonce_field('cookielawinfo-update-privacy-overview-content'); ?>
            <table class="form-table cli_privacy_overview_form" >
                <tr valign="top">
                    <td>
                        <label for="privacy_overview_title"><?php _e('Privacy Overview Title', 'cookie-law-info'); ?></label>
                        <input type="text" name="privacy_overview_title" value="<?php echo sanitize_text_field( stripslashes( $privacy_title ) ); ?>" class="cli-textbox" />
                    </td>
                 </tr>
                <tr valign="top">
                    <td>
                    <label for="privacy_overview_content"><?php _e('This will be shown in the settings visible for user on consent screen.', 'cookie-law-info'); ?></label>
                        <?php 
                        $cli_use_editor= apply_filters('cli_use_editor_in_po',true);
                        if($cli_use_editor)
                        {
                            wp_editor( stripslashes($privacy_content) , 'cli_privacy_overview_content', $wpe_settings = array('textarea_name'=>'privacy_overview_content'));
                        }
                        else
                        {
                            ?>
                            <textarea style="width:100%; height:250px;" name="privacy_overview_content"><?php echo wp_kses_post( stripslashes($privacy_content) ) ;?></textarea>
                            <?php
                        }
                        ?>     
                        <div class="clearfix"></div>
                        <span class="cli_form_help"><?php _e('This will be shown in the settings visible for user on consent screen.', 'cookie-law-info'); ?></span>
                    </td>
                </tr>
            </table>
            <div class="cli-plugin-toolbar bottom">
                <div class="left">
                </div>
                <div class="right">
                    <input type="submit" name="update_privacy_overview_content_settings_form" value="<?php _e('Save Settings', 'cookie-law-info'); ?>" style="float: right;" class="button-primary" />
                    <span class="spinner" style="margin-top:9px"></span>
                </div>
            </div>
        </form>
    </div>
</div>