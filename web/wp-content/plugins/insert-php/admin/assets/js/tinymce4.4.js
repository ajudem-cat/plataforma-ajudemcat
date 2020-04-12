(function($) {
	$(document).on('tinymce-editor-setup', function(event, editor) {

		if( void 0 === wbcr_inp_shortcode_snippets ) {
			console.log('Unknown error.');
			return;
		}

		if( $.isEmptyObject(wbcr_inp_shortcode_snippets) ) {
			return;
		}

		editor.settings.toolbar1 += ',wbcr_insert_php_button';

		var menu = [];

		$.each(wbcr_inp_shortcode_snippets, function(index, item) {
			menu.push({
				text: item.title,
				value: item.id,
				onclick: function() {
					var content = "";
					var snippet_type = item.type;
					var snippet_name = item.name;
					var selected_content = editor.selection.getContent();

					for( var tag in item ) {
						if( !item.hasOwnProperty(tag) ) {
							continue;
						}

						if( 'type' === tag ) {
							snippet_type = item[tag];
						} else if( 'name' === tag ) {
							snippet_name = item[tag];
						} else if( tag.indexOf('snippet_tags') === -1 ) {
							if( !('' !== selected_content && 'content' === tag) ) {
								content += ' ' + tag + '="' + item[tag] + '"';
							}
						}
					}

					if( '' === snippet_name || undefined === snippet_name ) {
						if( 'universal' === snippet_type ) {
							snippet_name = "wbcr_snippet";
						} else {
							snippet_name = "wbcr_" + snippet_type + "_snippet";
						}
					}

					if( '' === selected_content ) {
						editor.selection.setContent('[' + snippet_name + content + ']');
					} else {
						editor.selection.setContent(
							'[' + snippet_name + content + ']' +
							selected_content +
							'[/' + snippet_name + ']');
					}
				}
			});
		});

		editor.addButton('wbcr_insert_php_button', {
			title: wbcr_inp_tinymce_snippets_button_title,
			type: 'menubutton',
			icon: 'icon wbcr-inp-shortcode-icon',
			menu: menu
		});

	});
})(jQuery);