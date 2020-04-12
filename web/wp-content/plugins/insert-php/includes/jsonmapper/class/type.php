<?php
/**
 * Type class
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 05.03.2019, Webcraftic
 * @version       1.0
 */

namespace WINP\JsonMapper;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Type {

	/**
	 * @var integer Type id
	 */
	public $id;

	/**
	 * @var string Type title
	 */
	public $title;

	/**
	 * @var string Type description
	 */
	public $description;

	/**
	 * @var integer Type author id
	 */
	public $admin_id;

	/**
	 * @var string Type slug
	 */
	public $slug;

	/**
	 * @var integer Type updated time (timestamp)
	 */
	public $updated_at;

	/**
	 * @var integer Type created time (timestamp)
	 */
	public $created_at;

}
