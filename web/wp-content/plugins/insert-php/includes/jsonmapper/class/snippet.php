<?php
/**
 * Snippet class
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 05.03.2019, Webcraftic
 * @version 1.0
 */

namespace WINP\JsonMapper;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Snippet {

	/**
	 * @var integer Snippet id
	 */
	public $id;

	/**
	 * @var string Snippet title
	 */
	public $title;

	/**
	 * @var string Snippet description
	 */
	public $description;

	/**
	 * @var string Snippet content
	 */
	public $content;

	/**
	 * @var string|null Snippets Library: video link
	 */
	public $video_link;

	/**
	 * @var string|null Snippet scope
	 */
	public $execute_everywhere;

	/**
	 * @var boolean Snippet status
	 */
	public $status;

	/**
	 * @var integer Type id
	 */
	public $type_id;

	/**
	 * @var boolean|null Snippet premium marker
	 */
	public $is_premium;

	/**
	 * @var integer Snippet updated time (timestamp)
	 */
	public $updated_at;

	/**
	 * @var integer Snippet created time (timestamp)
	 */
	public $created_at;

	/**
	 * @var Type
	 */
	public $type;
}
