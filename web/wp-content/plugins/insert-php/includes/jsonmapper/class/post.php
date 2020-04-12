<?php
/**
 * Post class
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 18.03.2019, Webcraftic
 * @version 1.0
 */
namespace WINP\JsonMapper;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post {

	/**
	 * @var int Unique identifier for the object.
	 */
	public $ID;

	/**
	 * @var string The date the object was published, in the site's timezone.
	 */
	public $date = '0000-00-00 00:00:00';

	/**
	 * @var string The date the object was published, as GMT.
	 */
	public $date_gmt = '0000-00-00 00:00:00';

	/**
	 * @var integer The ID for the author of the object.
	 */
	public $author = 0;

	/**
	 * @var object The content for the object.
	 */
	public $content = array();

	/**
	 * @var object The title for the object.
	 */
	public $title = array();

	/**
	 * @var object The excerpt for the object.
	 */
	public $excerpt = array();

	/**
	 * @var string A named status for the object.
	 */
	public $status = 'publish';

	/**
	 * @var string Whether or not comments are open on the object.
	 */
	public $comment_status = 'open';

	/**
	 * @var string Whether or not the object can be pinged.
	 */
	public $ping_status = 'open';

	/**
	 * @var string The date the object was last modified, in the site's timezone.
	 */
	public $modified = '0000-00-00 00:00:00';

	/**
	 * @var string The date the object was last modified, as GMT.
	 */
	public $modified_gmt = '0000-00-00 00:00:00';

	/**
	 * @var object The globally unique identifier for the object.
	 */
	public $guid = array();

	/**
	 * @var string An alphanumeric identifier for the object unique to its type.
	 */
	public $slug = 'post';

	/**
	 * @var string Type of Post for the object.
	 */
	public $type = 'post';

	/**
	 * @var string URL to the object.
	 */
	public $link = '';

	/**
	 * @var integer The ID of the featured media for the object.
	 */
	public $featured_media = 0;

	/**
	 * @var boolean Whether or not the object should be treated as sticky.
	 */
	public $sticky;

	/**
	 * @var string The theme file to use to display the object.
	 */
	public $template;

	/**
	 * @var string The format for the object.
	 */
	public $format;

	/**
	 * @var object Meta fields.
	 */
	public $meta;

	/**
	 * @var array The terms assigned to the object in the category taxonomy.
	 */
	public $categories;

	/**
	 * @var array The terms assigned to the object in the post_tag taxonomy.
	 */
	public $tags;

	/**
	 * @var array Prominent words.
	 */
	public $yst_prominent_words;

	/**
	 * @var object Links.
	 */
	public $_links;

}
