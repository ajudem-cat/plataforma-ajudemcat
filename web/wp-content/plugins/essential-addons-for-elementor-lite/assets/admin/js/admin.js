(function($) {
    'use strict';
    /**
     * Eael Tabs
     */
    $('.eael-tabs li a').on('click', function(e) {
        e.preventDefault();
        $('.eael-tabs li a').removeClass('active');
        $(this).addClass('active');
        var tab = $(this).attr('href');
        $('.eael-settings-tab').removeClass('active');
        $('.eael-settings-tabs')
            .find(tab)
            .addClass('active');
    });

    $('.eael-get-pro').on('click', function() {
        Swal.fire({
            type: 'warning',
            title: '<h2><span>Go</span> Premium',
            html:
                'Purchase our <b><a href="https://wpdeveloper.net/in/upgrade-essential-addons-elementor" rel="nofollow">premium version</a></b> to unlock these pro components!',
            showConfirmButton: false,
            timer: 3000
        });
    });

    // Save Button reacting on any changes
    var saveButton = $('.js-eael-settings-save');

    $('.eael-checkbox input:enabled').on('click', function(e) {
        saveButton
            .addClass('save-now')
            .removeAttr('disabled')
            .css('cursor', 'pointer');
    });

    // Saving Data With Ajax Request
    $('.js-eael-settings-save').on('click', function(event) {
        event.preventDefault();

        var _this = $(this);

        if ($(this).hasClass('save-now')) {
            $.ajax({
                url: localize.ajaxurl,
                type: 'post',
                data: {
                    action: 'save_settings_with_ajax',
                    security: localize.nonce,
                    fields: $('form#eael-settings').serialize()
                },
                beforeSend: function() {
                    _this.html(
                        '<svg id="eael-spinner" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><circle cx="24" cy="4" r="4" fill="#fff"/><circle cx="12.19" cy="7.86" r="3.7" fill="#fffbf2"/><circle cx="5.02" cy="17.68" r="3.4" fill="#fef7e4"/><circle cx="5.02" cy="30.32" r="3.1" fill="#fef3d7"/><circle cx="12.19" cy="40.14" r="2.8" fill="#feefc9"/><circle cx="24" cy="44" r="2.5" fill="#feebbc"/><circle cx="35.81" cy="40.14" r="2.2" fill="#fde7af"/><circle cx="42.98" cy="30.32" r="1.9" fill="#fde3a1"/><circle cx="42.98" cy="17.68" r="1.6" fill="#fddf94"/><circle cx="35.81" cy="7.86" r="1.3" fill="#fcdb86"/></svg><span>Saving Data..</span>'
                    );
                },
                success: function(response) {
                    setTimeout(function() {
                        _this.html('Save Settings');
                        Swal.fire({
                            type: 'success',
                            title: 'Settings Saved!',
                            footer: 'Have Fun :-)',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        saveButton.removeClass('save-now');
                    }, 500);
                },
                error: function() {
                    Swal.fire({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!'
                    });
                }
            });
        } else {
            $(this)
                .attr('disabled', 'true')
                .css('cursor', 'not-allowed');
        }
    });

    // Regenerate Assets
    $('#eael-regenerate-files').on('click', function(e) {
        e.preventDefault();
        var _this = $(this);

        $.ajax({
            url: localize.ajaxurl,
            type: 'post',
            data: {
                action: 'clear_cache_files_with_ajax',
                security: localize.nonce
            },
            beforeSend: function() {
                _this.html(
                    '<svg id="eael-spinner" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><circle cx="24" cy="4" r="4" fill="#fff"/><circle cx="12.19" cy="7.86" r="3.7" fill="#fffbf2"/><circle cx="5.02" cy="17.68" r="3.4" fill="#fef7e4"/><circle cx="5.02" cy="30.32" r="3.1" fill="#fef3d7"/><circle cx="12.19" cy="40.14" r="2.8" fill="#feefc9"/><circle cx="24" cy="44" r="2.5" fill="#feebbc"/><circle cx="35.81" cy="40.14" r="2.2" fill="#fde7af"/><circle cx="42.98" cy="30.32" r="1.9" fill="#fde3a1"/><circle cx="42.98" cy="17.68" r="1.6" fill="#fddf94"/><circle cx="35.81" cy="7.86" r="1.3" fill="#fcdb86"/></svg><span>Generating...</span>'
                );
            },
            success: function(response) {
                setTimeout(function() {
                    _this.html('Regenerate Assets');

                    Swal.fire({
                        type: 'success',
                        title: 'Assets Regenerated!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }, 1000);
            },
            error: function() {
                Swal.fire({
                    type: 'error',
                    title: 'Ops!',
                    footer: 'Something went wrong!',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    });

    // Elements global control
    $(document).on('click', '.eael-global-control-enable', function(e) {
        e.preventDefault();

        $('.eael-checkbox-container .eael-checkbox input:enabled').each(function(i) {
            $(this)
                .prop('checked', true)
                .change();
        });

        saveButton
            .addClass('save-now')
            .removeAttr('disabled')
            .css('cursor', 'pointer');
    });

    $(document).on('click', '.eael-global-control-disable', function(e) {
        e.preventDefault();

        $('.eael-checkbox-container .eael-checkbox input:enabled').each(function(i) {
            $(this)
                .prop('checked', false)
                .change();
        });

        saveButton
            .addClass('save-now')
            .removeAttr('disabled')
            .css('cursor', 'pointer');
    });

    // Popup
    $(document).on('click', '.eael-admin-settings-popup', function(e) {
        e.preventDefault();

        var title = $(this).data('title');
        var placeholder = $(this).data('placeholder');
        var type = $(this).data('option') || 'text';
        var options = $(this).data('options') || {};
        var prepareOptions = {};
        var target = $(this).data('target');
        var val = $(target).val();

        if (Object.keys(options).length > 0) {
            prepareOptions['all'] = 'All';

            for (var index in options) {
                prepareOptions[index] = options[index].toUpperCase();
            }
        }

        Swal.fire({
            title: title,
            input: type,
            inputPlaceholder: placeholder,
            inputValue: val,
            inputOptions: prepareOptions,
            preConfirm: function(res) {
                $(target).val(res);

                saveButton
                    .addClass('save-now')
                    .removeAttr('disabled')
                    .css('cursor', 'pointer');
            }
        });
    });
})(jQuery);