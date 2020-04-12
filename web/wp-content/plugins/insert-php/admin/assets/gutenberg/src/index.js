const {registerBlockType, createBlock} = wp.blocks;
const {Fragment} = wp.element;
const {TextControl, SelectControl} = wp.components;
const {InspectorControls, PlainText, RichText, InnerBlocks} = wp.editor;

const snippets = window.winp_snippets.data,
	firstSnippet = snippets[Object.keys(snippets)[0]] || '',
	__ = wp.i18n.__;

registerBlockType('wp-plugin-insert-php/winp-snippet', {
	title: __('Woody snippets'),
	description: __('Executes PHP code, uses conditional logic to insert ads, text, media content and external service’s code. Ensures no content duplication.'),
	//icon: 'format-aside',
	icon: <svg
		width="25"
		height="25"
		viewBox="0 0 254 254"
		shapeRendering="geometricPrecision"
		textRendering="geometricPrecision"
		imageRendering="optimizeQuality"
		fillRule="evenodd"
		clipRule="evenodd">
		<path d="M76 187l44 48 45-49c1-1-12 5-22 5-10-1-23-15-23-15s-3 9-19 13c-11 4-25-2-25-2z"/>
		<ellipse cx={99} cy={134} rx={11} ry={12}/>
		<ellipse cx={143} cy={135} rx={11} ry={12}/>
		<path d="M103 103s-10-12-4-35c6-22 16-29 23-33 8-5-5 16-4 20 2 3 14-25 39-27 25-1 41 21 41 21l-13-1s13 5 18 11 11 15 10 18-5 1-5 1 19 13 20 24c1 10-11-10-39-14-29-4-50 14-45 8s17-16 29-17 27 3 27 3-21-12-19-16c2-5 16-2 16-2s-9-8-20-9c-10-2-19 3-18-1 1-5 10-13 15-12 6 1-12-6-24-2-11 5-19 11-26 25s-9 22-13 20c-3-1-4-17-4-17l-4 35zm-60 35l16-21s3-8-1-12c-5-3-9-1-9-1l-22 28s-3 3-2 6c0 2 2 5 2 5l21 28s7 3 10-2c4-5 2-9 2-9l-17-22z"/>
		<path d="M199 138l-17-21s-3-8 2-12c4-3 9-1 9-1l23 28s3 3 3 6c0 2-3 5-3 5l-22 28s-7 3-11-2-2-9-2-9l18-22z"/>
	</svg>,
	category: 'formatting',
	attributes: {
		id: {
			type: 'int',
			default: ''
		},
		attrs: {
			type: 'array',
			default: firstSnippet.tags || []
		},
		attrValues: {
			type: 'array',
			default: []
		},
	},
	edit(props) {

		const {id, attrValues, attrs} = props.attributes;

		let defaultProps = {};

		if( id === '' ) {
			defaultProps['id'] = firstSnippet.id || '';
		}

		if( defaultProps !== {} ) {
			props.setAttributes(defaultProps);
		}

		let options = [],
			snippedIds = Object.keys(snippets),
			s = 0;

		for( ; s < snippedIds.length; s++ ) {
			let currentSnippedId = snippedIds[s];
			options.push({
				label: snippets[currentSnippedId].title,
				value: currentSnippedId
			})
		}

		function onSnippetChange(id) {
			props.setAttributes({
				id: id,
				attrs: snippets[id].tags
			});
		}

		function onAttributeChange(name, value) {
			let outcome = [];

			for( let i = 0; i < attrs.length; i++ ) {
				if( attrs[i] === name ) {
					outcome[i] = value;
				} else {
					if( attrValues.hasOwnProperty(i) ) {
						outcome[i] = attrValues[i];
					} else {
						outcome[i] = '';
					}
				}
			}

			props.setAttributes({attrValues: outcome});
		}

		function prepareTags() {
			let tags = [];

			if( attrs ) {
				for( let i = 0; i < attrs.length; i++ ) {
					const name = attrs[i];
					tags.push(<div className="winp-snippet-gb-content-tag">
						<TextControl
							label={__('Attribute "') + attrs[i] + '":'}
							className="winp-snippet-gb-tag"
							value={attrValues[i] || ''}
							name={name}
							placeholder={__('Attribute value (variable $' + attrs[i] + ')')}
							onChange={(value) => onAttributeChange(name, value)}
						/>
					</div>);
				}
			}

			return tags;
		}

		return ([
			<div className="winp-snippet-gb-container">
				<div className="winp-snippet-gb-dropdown">
					<SelectControl
						label={__('Select snippet shortcode:')}
						value={id}
						options={options}
						onChange={onSnippetChange}
					/>
				</div>
				<div className="winp-snippet-gb-content">
					<InnerBlocks/>
				</div>
				<InspectorControls>
					<div id="winp-snippet-gb-content-tags">
						{prepareTags()}
					</div>
				</InspectorControls>
			</div>

		]);
	},

	save: function(props) {
		return <div>
			<InnerBlocks.Content/>
		</div>;
	}
});