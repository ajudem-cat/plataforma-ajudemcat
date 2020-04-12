<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<?php

$wt_cli_non_necessary_description =  isset($stored_options['thirdparty_description']) ? $stored_options['thirdparty_description'] : '';
$wt_cli_non_necessary_head_scripts =  isset($stored_options['thirdparty_head_section']) ? $stored_options['thirdparty_head_section'] : '';
$wt_cli_non_necessary_body_scripts =  isset($stored_options['thirdparty_body_section']) ? $stored_options['thirdparty_body_section'] : '';
$wt_cli_non_necessary_enabled =  isset($stored_options['thirdparty_on_field']) ? $stored_options['thirdparty_on_field'] : false;
$wt_cli_default_state =  isset($stored_options['third_party_default_state']) ? $stored_options['third_party_default_state'] : true;

?>
<style>
    .vvv_textbox{
        height: 150px;
        width:100%;
    }
</style>
<script type="text/javascript">
    var cli_success_message='<?php echo __('Settings updated.', 'cookie-law-info');?>';
    var cli_error_message='<?php echo __('Unable to update Settings.', 'cookie-law-info');?>';
</script>   
<div class="wrap">
    <div class="cookie-law-info-form-container">
        <div class="cli-plugin-toolbar top">
            <h3><?php echo __('Non-necessary Cookie Settings','cookie-law-info'); ?></h3>
        </div>
        <form method="post" action="<?php echo esc_url($_SERVER["REQUEST_URI"]); ?>" id="cli_non-ncessary_form" class="cookie-sensitivity-form">
            <?php wp_nonce_field('cookielawinfo-update-thirdparty'); ?> 
            <table class="form-table cli_non_necessary_form cli-admin-table">
                <tr>
                    <td>
                        <label for="thirdparty_on_field"><?php echo __('Enable Non-necessary Cookie','cookie-law-info'); ?></label>
                        <input type="radio" id="thirdparty_on_field_yes" name="thirdparty_on_field" class="styled" value="true" <?php echo ( filter_var($wt_cli_non_necessary_enabled, FILTER_VALIDATE_BOOLEAN) == true ) ? ' checked="checked" ' : ' '; ?> /><?php echo __('Yes','cookie-law-info');?>
                        <input type="radio" id="thirdparty_on_field_no" name="thirdparty_on_field" class="styled" value="false" <?php echo ( filter_var($wt_cli_non_necessary_enabled, FILTER_VALIDATE_BOOLEAN) == false ) ? ' checked="checked" ' : ''; ?> /><?php echo __('No','cookie-law-info');?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="third_party_default_state"><?php echo __('Default state','cookie-law-info'); ?></label>
                        <input type="radio" id="third_party_default_state_yes" name="third_party_default_state" class="styled" value="true" <?php echo ( filter_var($wt_cli_default_state, FILTER_VALIDATE_BOOLEAN) == true ) ? ' checked="checked" ' : ' '; ?> /><?php echo __('Enabled','cookie-law-info');?>
                        <input type="radio" id="third_party_default_state_no" name="third_party_default_state" class="styled" value="false" <?php echo ( filter_var($wt_cli_default_state, FILTER_VALIDATE_BOOLEAN) == false ) ? ' checked="checked" ' : ''; ?> /><?php echo __('Disabled','cookie-law-info');?>
                        <span class="cli_form_help">
                            <?php echo __('If you enable this option, the category toggle button will be in the active state for cookie consent.','cookie-law-info'); ?> <br />
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                       <label for="thirdparty_description"><?php echo __('Description','cookie-law-info');?></label>
                        <textarea name="thirdparty_description" class="vvv_textbox"><?php
                        echo apply_filters('format_to_edit', stripslashes($wt_cli_non_necessary_description));
                        ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="thirdparty_head_section"><?php echo __('This script will be added to the page HEAD section if the above settings is enabled and user has give consent.','cookie-law-info');?></label>
                        <textarea name="thirdparty_head_section" class="vvv_textbox"><?php
                        echo apply_filters('format_to_edit', stripslashes($wt_cli_non_necessary_head_scripts));
                        ?></textarea>
                        <span class="cli_form_help">
                        <?php echo __('Print scripts in the head tag on the front end if above cookie settings is enabled and user has given consent.','cookie-law-info'); ?> <br />
                                eg:- &lt;script&gt;console.log("header script");&lt;/script&gt
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="thirdparty_body_section"><?php echo __('This script will be added right after the BODY section if the above settings is enabled and user has given consent.','cookie-law-info'); ?></label>
                        <textarea name="thirdparty_body_section" class="vvv_textbox"><?php echo apply_filters('format_to_edit', stripslashes($wt_cli_non_necessary_body_scripts));?></textarea>
                        <span class="cli_form_help">
                            <?php echo __('Print scripts before the closing body tag on the front end if above cookie settings is enabled and user has given consent.','cookie-law-info'); ?> <br />eg:- &lt;script&gt;console.log("body script");&lt;/script&gt;
                        </span>
                    </td>
                </tr>
            </table>
            <div class="cli-plugin-toolbar bottom">
                <div class="left">
                </div>
                <div class="right">
                    <input type="hidden" name="cli_non-necessary_ajax_update" value="1">
                    <input type="submit" name="update_admin_settings_form" value="<?php _e('Update Settings', 'cookie-law-info'); ?>" class="button-primary" style="float:right;" onClick="return cli_store_settings_btn_click(this.name)" />
                    <span class="spinner" style="margin-top:9px"></span>
                </div>
            </div>
        </form>
    </div>
</div>