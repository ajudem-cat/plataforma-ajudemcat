jQuery(document).ready(function($) {
	// Запрос экспорта сниппета
	$('#winp-snippet-sync').click(function() {
		if( $(this).hasClass('disabled') ) {
			return;
		}

		$('#winp-sync-snippet-name').val('');
		$('#winp-sync-content').find('.winp-modal-error').css('visibility', 'hidden');
		$('#winp-sync-modal').factoryBootstrap423_modal('show');
	});

	// Экспорт сниппета
	$('#winp-sync-save-button').click(function() {
		var self = $(this);

		if( $(this).hasClass('disabled') ) {
			return;
		}

		if( $('#winp-sync-snippet-name').val() === '' ) {
			$('#winp-sync-snippet-name')[0].reportValidity();

			return;
		}

		$(this).addClass('disabled');

		$('#winp-sync-save-button span').html('<img src="' + winp_snippet_sync.src_loader + '">');

		$.post(ajaxurl, {
				action: 'winp_snippet_synchronization',
				snippet_id: $('#post_ID').val(),
				snippet_name: $('#winp-sync-snippet-name').val(),
				_wpnonce: self.data('nonce')
			},
			function(data) {
				$('#winp-sync-save-button').removeClass('disabled');
				$('#winp-sync-save-button span').html(winp_snippet_sync.save);

				if( true == data ) {
					$('.winp-sync-buttons').css('color', 'green');
					$('#winp-snippet-sync').replaceWith('<span class="dashicons dashicons-plus-alt winp-green"></span> ' + winp_snippet_sync.saved);

					$('#winp-sync-modal').factoryBootstrap423_modal('hide');
				} else {
					var error_text = winp_snippet_sync.export_failed;
					if( typeof data == 'string' ) {
						error_text = data;
					}
					$('#winp-sync-content').find('.winp-modal-error span.warning-text').text(error_text);
					$('#winp-sync-content').find('.winp-modal-error').css('visibility', 'visible');
				}
			}
		);
	});
});

(function($) {
	window.wimp_snippet_list = {
		/** added method display
		 * for getting first sets of data
		 **/
		display: function() {
			$.ajax({
				url: ajaxurl,
				dataType: 'json',
				data: {
					winp_ajax_custom_list_nonce: $('#winp_ajax_custom_list_nonce').val(),
					action: 'winp_sts_display'
				},
				success: function(response) {
					$('#winp-snippet-library-table').html(response.display);
					$('tbody').on('click', '.toggle-row', function(e) {
						e.preventDefault();
						$(this).closest('tr').toggleClass('is-expanded');
					});
					window.wimp_snippet_list.init();
				},
				error: function(response) {
					alert(winp_snippet_sync.import_failed);
				}
			});
		},
		init: function() {
			var timer;
			var delay = 500;

			// Импортировать выбранный сниппет из списка сниппетов в модальном окне
			$('.wbcr-inp-enable-snippet-button').click(function() {
				if( confirm(winp_snippet_sync.import + '?') ) {
					$('#winp-snippet-library, #winp-snippet-sync').addClass('disabled');
					$.post(
						ajaxurl,
						{
							action: 'winp_snippet_create',
							snippet_id: $(this).data('snippet'),
							post_id: $('#auto_draft').length > 0 && $('#auto_draft').val() == 1
							         ? 0
							         : $('#post_ID').val()
						},
						function(data) {
							if( data ) {
								window.location = 'post.php?post=' + data + '&action=edit';
							} else {
								$('#winp-snippet-library, #winp-snippet-sync').removeClass('disabled');
								alert(winp_snippet_sync.import_failed);
							}
						}
					);

					//$('#winp-sync-modal').factoryBootstrap423_modal('hide');
				}
			});

			$('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function(e) {
				e.preventDefault();
				var query = this.search.substring(1);
				var data = {
					paged: window.wimp_snippet_list.__query(query, 'paged') || '1',
					order: window.wimp_snippet_list.__query(query, 'order') || 'asc',
					orderby: window.wimp_snippet_list.__query(query, 'orderby') || 'title'
				};
				window.wimp_snippet_list.update(data);
			});
			$('input[name=paged]').on('keyup', function(e) {
				if( 13 == e.which ) {
					e.preventDefault();
				}
				var data = {
					paged: parseInt($('input[name=paged]').val()) || '1',
					order: $('input[name=order]').val() || 'asc',
					orderby: $('input[name=orderby]').val() || 'title'
				};
				window.clearTimeout(timer);
				timer = window.setTimeout(function() {
					window.wimp_snippet_list.update(data);
				}, delay);
			});
			$('#winp-snippet-library-list').on('submit', function(e) {
				e.preventDefault();
			});
		},
		/** AJAX call
		 *
		 * Send the call and replace table parts with updated version!
		 *
		 * @param    object    data The data to pass through AJAX
		 */
		update: function(data) {
			$.ajax({
				url: ajaxurl,
				data: $.extend(
					{
						winp_ajax_custom_list_nonce: $('#winp_ajax_custom_list_nonce').val(),
						action: 'winp_fetch_sts_history'
					},
					data
				),
				success: function(response) {
					var response = $.parseJSON(response);
					if( response.rows.length ) {
						$('#the-list').html(response.rows);
					}
					if( response.column_headers.length ) {
						$('thead tr, tfoot tr').html(response.column_headers);
					}
					if( response.pagination.bottom.length ) {
						$('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
					}
					if( response.pagination.top.length ) {
						$('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());
					}
					window.wimp_snippet_list.init();
				},
				error: function(response) {
					alert(winp_snippet_sync.import_failed);
				}
			});
		},
		/**
		 * Filter the URL Query to extract variables
		 *
		 * @see http://css-tricks.com/snippets/javascript/get-url-variables/
		 *
		 * @param    string    query The URL query part containing the variables
		 * @param    string    variable Name of the variable we want to get
		 *
		 * @return   string|boolean The variable value if available, false else.
		 */
		__query: function(query, variable) {
			var vars = query.split('&');
			for( var i = 0; i < vars.length; i++ ) {
				var pair = vars[i].split('=');
				if( pair[0] == variable ) {
					return pair[1];
				}
			}
			return false;
		}
	};
})(jQuery);