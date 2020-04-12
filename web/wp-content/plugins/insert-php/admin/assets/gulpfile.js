let gulp = require('gulp'),
	less = require('gulp-less'),
	concat = require('gulp-concat'),
	minify = require('gulp-minify-css'),
	merge = require('merge-stream'),
	uglify = require('gulp-uglify');

// Less files to be concat and minified together
let LESS_PATHS = ['css/general.less', 'css/sync.less'];

// Css files to be concat and minified together with Less (paths above)
let CSS_PATHS = ['css/code-editor.min.css'];

// JS files to be concat and minified together
let JS_PATHS = ['js/code-editor.min.js', 'js/sync.js'];

/**
 * Prepares minification of specified Less and CSS files for CodeMirror specifically.
 *
 * Execution: gulp cm-build-css
 */
gulp.task('cm-build-css', function () {

	// Less
	let lessStream = gulp.src(LESS_PATHS)
	.pipe(less())
	.pipe(concat('less-files.less'));

	// CSS
	let cssStream = gulp.src(CSS_PATHS)
	.pipe(concat('css/code-editor.min.css'));

	return merge(lessStream, cssStream)
	// c - custom, c - code, m - mirror
		.pipe(concat('ccm.min.css'))
		.pipe(minify())
		.pipe(gulp.dest('dist/css'));
});

/**
 * Prepares minification of specified JS files for CodeMirror specifically.
 *
 * Execution: gulp cm-build-js
 */
gulp.task('cm-build-js', function () {
	// Js
	return gulp.src(JS_PATHS)
	.pipe(uglify())
	.pipe(concat('ccm.min.js'))
	.pipe(gulp.dest('dist/js'));
});