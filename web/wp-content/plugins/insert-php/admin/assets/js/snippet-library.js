jQuery(document).ready( function($) {
    // Импортировать выбранный сниппет из списка
    $( '.wbcr-inp-enable-snippet-button' ).click( function() {
        if ( confirm(winp_snippet_library.is_import) ) {
            if ($(this).hasClass('disabled')) return;

            $(this).addClass('disabled');

            $.post(
                ajaxurl,
                {
                    action: 'winp_snippet_create',
                    winp_ajax_custom_list_nonce: $('#winp_ajax_custom_list_nonce').val(),
                    snippet_id: $(this).data('snippet'),
					common: $(this).data('common')
                },
                function(data) {
                    if (data) {
                        window.location = 'post.php?post=' + data + '&action=edit';
                    } else {
                        $( '.wbcr-inp-enable-snippet-button' ).removeClass('disabled');
                        alert(winp_snippet_library.import_failed);
                    }
                }
            );
        }
    });

    // Удалить выбранный сниппет из списка
    $( '.wbcr-inp-delete-snippet-button' ).click( function() {
        if ( confirm(winp_snippet_library.is_delete) ) {
            if ($(this).hasClass('disabled')) return;

            $(this).addClass('disabled');
            var snippet_id = $(this).data('snippet');

            $.post(
                ajaxurl,
                {
                    action: 'winp_snippet_delete',
					winp_ajax_snippet_delete_nonce: $('#winp_ajax_snippet_delete_' + snippet_id).val(),
                    snippet_id: snippet_id
                },
                function(data) {
                    if (data) {
                        window.location.reload();
                    } else {
                        $( '.wbcr-inp-enable-snippet-button' ).removeClass('disabled');
                        alert(winp_snippet_library.delete_failed);
                    }
                }
            );
        }
    });
});