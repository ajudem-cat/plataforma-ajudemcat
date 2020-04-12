<?php
/**
 * The file contains a short help info.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       core
 * @copyright (c) 2018, OnePress Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common Settings
 */
class WINP_AboutPage extends WINP_Page {

	/**
	 * @param Wbcr_Factory422_Plugin $plugin
	 */
	public function __construct( Wbcr_Factory422_Plugin $plugin ) {
		$this->menu_post_type = WINP_SNIPPETS_POST_TYPE;

		$this->id         = "about";
		$this->menu_title = __( 'About', 'insert-php' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	public function indexAction() {
		global $wp_version;

		?>
        <div class="wrap about-wrap full-width-layout" id="wbcr-inp-about">
        <!-- News Title !-->
        <h1>Welcome to Woody ad snippets <?php echo $this->plugin->getPluginVersion() ?></h1>
        <!-- News Subtext !-->
        <div class="about-text">
            Thanks for upgrading! Many new features and improvements are available that you will enjoy.
        </div>
        <!-- Settings Tabs -->
        <h2 class="nav-tab-wrapper" id="wbcr-inp-tab-nav">
            <a href="<?php echo $this->getActionUrl( 'index' ); ?>" class="nav-tab"><?php _e( 'What&#8217;s New', 'insert-php' ); ?></a>
            <a href="<?php echo WINP_Plugin::app()->get_support()->get_docs_url( true, 'about' ) ?>" target="_blank" class="nav-tab"><?php _e( 'Documentation', 'insert-php' ); ?></a>
        </h2>
        <!-- Latest News !-->
        <div id="wbcr-inp-news-tab">
            <div class="headline">
                <h3 class="headline-title">PHP snippets (Insert php) Evolution to Woody ad snippets</h3>
                <div class="featured-image">
                    <img src="https://woodysnippet.com/images/about/change-plugin-name.jpg" alt="">
                </div>
                <p class="introduction">If you are a long-term user of the <code>Insert php 1.3.0</code> plugin, you may
                    be confused
                    about the new plugin’s name and branding. We will try to clear this out for you.</p>
                <p>The way how WordPress editor handled PHP code from <code>Insert php</code> before was incorrect and
                    unsafe. We
                    wanted to find a solution that will be suitable for both old and new <code>Insert php</code> users.
                    And we wanted
                    to give them a useful tool to work with snippets (pieces of code) and plain text. </p>
                <p>Storing PHP in individual snippets was a more elegant solution because only administrators were able
                    to modify the snippets. The plugin evolution to a snippet format has solved several important
                    problems:</p>
                <ul>
                    <li>- Security improvements. Snippets can be modified by administrators only. Your PHP code is not
                        available to other users.
                    </li>
                    <li>- No code duplication. You can use 1 snippet on 100 pages instead of copying the code to each
                        one of them
                    </li>
                    <li>- Edit snippet in one place. All changes will be applied to the snippet on all pages.</li>
                    <li>- You can place snippets automatically.</li>
                    <li>- The code editor highlights syntax of the snippet and checks PHP code for errors. Your website
                        is safe now.
                    </li>
                </ul>
                <p>We considered all of the above when decided to update the <code>Insert php</code> plugin. Now our
                    plugin supports
                    not only a PHP code but HTML. JS, CSS code and text as well. Obviously, the name <code>Php snippets
                        (Insert php)</code> no longer suited the plugin. </p>
                <p>So we’ve created a new name – Woody ad snippets. We are hoping that the new name didn’t cause you any
                    pain. We won’t change the plugin name anymore, as we’ve scheduled a solid roadmap of the plugin
                    development and plan to stick to it!</p>
            </div>
            <div class="feature-section one-col">
                <div class="col">
                    <h2>Gutenberg Editor Support</h2>
                </div>
            </div>
            <div style="text-align:center;">
                <picture>
                    <img src="https://woodysnippet.com/images/about/gutenberg.gif" alt="" style="max-width:800px;box-shadow: 0 0 15px rgba(0,0,0,0.3);">
                </picture>
            </div>
            <div class="feature-section one-col">
                <div class="col">
                    <p>Hello Gutenberg! Creating the content becomes more simple and easy. You can forget about
                        shortcodes to locate snippet and switch to the user-friendly blocks instead. Just create a
                        snippet with the location scope through the shortcode, go to Gutenberg Editor and add a new
                        Woody ad snippet block. Super easy!</p>
                </div>
            </div>
            <hr/>
            <div class="feature-section one-col">
                <div class="col">
                    <h2>New Snippet types</h2>
                </div>
            </div>
            <div class="<?php echo( version_compare( $wp_version, '5.2', '<' ) ? 'under-the-hood feature-section three-col' : 'is-fullwidth has-3-columns' ) ?>">
                <div class="col" style="text-align: center">
                    <picture>
                        <img src="https://woodysnippet.com/images/about/php-snippet.jpg" alt="" style="width:80%;box-shadow: 0 0 10px rgba(0,0,0,0.3);">
                    </picture>
                    <h3>PHP Snippets</h3>
                    <p>This is a simple snippet type. It executes PHP code. Use this type to register functions,
                        classes, and global variables. You can create false scenarios and use shortcodes to print them
                        on pages.</p>
                </div>
                <div class="col" style="text-align: center">
                    <picture>
                        <img src="https://woodysnippet.com/images/about/text-snippet.jpg" alt="" style="width:80%;box-shadow: 0 0 10px rgba(0,0,0,0.3);">
                    </picture>
                    <h3>Text Snippets</h3>
                    <p>This is the easiest snippet type. Here you can use text and HTML code only. It works as a classic
                        editor TinyMCE. Use this type to create text blocks, media content, links, quotes, and inserts.
                        You can locate this snippet type automatically or via shortcodes.</p>
                </div>
                <div class="col" style="text-align: center">
                    <picture>
                        <img src="https://woodysnippet.com/images/about/universal-snippet.jpg" alt="" style="width:80%;box-shadow: 0 0 10px rgba(0,0,0,0.3);">
                    </picture>
                    <h3>Universal Snippets</h3>
                    <p>This is a complex snippet type where you can combine PHP, HTML, JavaScript, and CSS. We’ve
                        created this type to insert ad codes, external widgets, complex HTML forms, Google analytics,
                        Facebook pixels and so on. You can locate these snippets automatically or by shortcodes.</p>
                </div>
            </div>
            <hr/>
            <div class="feature-section one-col">
                <div class="col">
                    <h2>Snippet Auto placement</h2>
                </div>
            </div>
            <div class="feature-section one-col">
                <div class="col">
                    <p>This is a new feature. It will save you many hours of hard work. Now you can locate a snippet on
                        all or certain pages in a few clicks.
                    </p>
                    <p>We’ve added a new way of snippet placement. Just set up the location spot and placement
                        condition. The plugin will automatically install the snippet on the necessary pages. You can
                        also remove snippet in a few clicks.</p>
                </div>
            </div>
            <hr/>
            <div class="feature-section one-col">
                <div class="col">
                    <h2>Snippet Conditional Logic</h2>
                </div>
            </div>
            <div style="text-align:center;">
                <picture>
                    <img src="https://woodysnippet.com/images/about/conditions.gif" alt="" style="max-width:800px;box-shadow: 0 0 15px rgba(0,0,0,0.3);">
                </picture>
            </div>
            <div class="feature-section one-col">
                <div class="col">
                    <p>With the new auto location options the conditional logic features have been released. They allow
                        you to hide or show snippets depend on conditions. For example, you can show a snippet to
                        registered users only. Or, show it just to mobile users. Conditional logic helps you to filter
                        pages where the snippet shouldn’t be used.</p>
                </div>
            </div>
            <hr/>
            <div class="feature-section one-col">
                <div class="col">
                    <h2>Snippet Export/Import</h2>
                </div>
            </div>
            <div class="full-width">
                <picture>
                    <img src="https://woodysnippet.com/images/about/import-export2.png" alt="" style="box-shadow: 0 0 10px rgba(0,0,0,0.3);">
                </picture>
            </div>
            <div class="feature-section one-col">
                <div class="col">
                    <p>Snippet Export/Import is more user-friendly now. You don’t need to install additional plugins
                        anymore. Just export all the necessary plugins or import them on another website. Additional
                        import features prevent snippet duplication in case your website has already had this
                        snippet.</p>
                </div>
            </div>
        </div>
		<?php
	}
}
