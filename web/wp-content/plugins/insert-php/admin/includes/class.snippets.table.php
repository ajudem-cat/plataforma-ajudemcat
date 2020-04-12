<?php
/**
 * This class is implemented page: snippet table
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @since         1.0.0
 * @package       core
 * @copyright (c) 2019, OnePress Ltd
 *                s
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
class WINP_Snippet_Library_Table extends WP_List_Table {

	/** ************************************************************************
	 * Normally we would be querying data from a database and manipulating that
	 * for use in your list table. For this example, we're going to simplify it
	 * slightly and create a pre-built array. Think of this as the data that might
	 * be returned by $wpdb->query()
	 *
	 * In a real-world scenario, you would make your own custom query inside
	 * this class' prepare_items() method.
	 *
	 * @var array
	 **************************************************************************/
	var $example_data = [];

	/**
	 * Is modal window
	 *
	 * @var bool
	 */
	private $modal;

	/**
	 * Если true, то выводить общие сниппеты без привязки к пользователю
	 *
	 * @var bool
	 */
	private $common;

	/**
	 * @var array
	 *
	 * Array contains slug columns that you want hidden
	 *
	 */
	private $hidden_columns = [
		'id',
	];

	/**
	 * @var integer
	 */
	private $per_page = 10;

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	/**
	 * WINP_Snippet_Library_Table constructor.
	 *
	 * @param bool $modal
	 */
	function __construct( $modal = false ) {
		global $status, $page;
		add_thickbox();
		$this->modal  = $modal;
		$this->common = true;

		// Set parent defaults
		parent::__construct( [
			'singular' => 'snippet',  // singular name of the listed records
			'plural'   => 'snippets', // plural name of the listed records
			'ajax'     => true,       // does this table support ajax?
		] );
	}

	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array  $item          A singular item (one full row's worth of data)
	 * @param string $column_name   The name/slug of the column to be processed
	 *
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'type':
				$class = 'wbcr-inp-type-' . esc_attr( $item[ $column_name ] );
				$type  = 'universal' == $item[ $column_name ] ? 'uni' : esc_attr( $item[ $column_name ] );

				return '<div class="wbcr-inp-snippet-type-label ' . $class . '">' . esc_html( $type ) . '</div>';
			case 'desc':
				$desc = strlen( $item[ $column_name ] ) > 500 ? substr( $item[ $column_name ], 0, 500 ) : $item[ $column_name ];

				return '<div class="wbcr-inp-snippet-description" title="' . esc_attr( $desc ) . '">' . esc_html( $desc ) . '</div>';
			case 'preview':
			case 'datetime':
			case 'insert':
			case 'delete':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @param array $item   A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	public function column_title( $item ) {
		//Build row actions
		$actions = [/*'edit'   => sprintf( '<a href="?page=%s&action=%s&movie=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&movie=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID'] ),*/
		];

		$url = admin_url() . 'post-new.php?post_type=' . WINP_SNIPPETS_POST_TYPE . '&winp_item=' . $item['type'] . '&snippet_id=' . $item['ID'] . ( $this->common ? '&common=1' : '' );

		//Return the title contents
		return sprintf( '<a href="%1$s"><b>%2$s</b></a>%3$s', /*$1%s*/ esc_url( $url ), /*$2%s*/ esc_html( $item['title'] ), /*$3%s*/ $this->row_actions( $actions ) );
	}

	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @param array $item   A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', /*$1%s*/ esc_attr( $this->_args['singular'] ),  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ esc_attr( $item['ID'] )                //The value of the checkbox should be the record's id
		);
	}

	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	public function get_columns() {
		$columns          = [// 'cb'     => '<input type="checkbox" />', //Render a checkbox instead of text
		];
		$columns['type']  = __( 'Type', 'insert-php' );
		$columns['title'] = __( 'Title', 'insert-php' );

		if ( ! $this->modal && $this->common ) {
			$columns['preview'] = __( 'Preview', 'insert-php' );
		}

		$columns['desc']     = __( 'Description', 'insert-php' );
		$columns['datetime'] = __( 'Date', 'insert-php' );
		$columns['insert']   = __( 'Insert', 'insert-php' );

		if ( ! $this->modal && ! $this->common ) {
			$columns['delete'] = __( 'Delete', 'insert-php' );
		}

		return $columns;
	}

	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	public function get_sortable_columns() {
		$sortable_columns = [
			'title'    => [ 'title', false ],     //true means it's already sorted
			'type'     => [ 'type', false ],
			'datetime' => [ 'datetime', false ],
		];

		return $sortable_columns;
	}

	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	public function get_bulk_actions() {
		$actions = [//'sync' => __( 'Synchronization', 'insert-php' ),
		];

		return $actions;
	}

	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		/*if ( 'sync' === $this->current_action() ) {
			wp_die( 'Synchronization' );
		}*/
	}

	/**
	 * Возвращает id youtube видео
	 *
	 * @param $video_link   - ссылка на видео
	 *
	 * @return bool|string - если id сниппета не найден, то вернёт false
	 */
	private function get_video_id( $video_link ) {
		// youtube regex
		preg_match( "#([\/|\?|&]vi?[\/|=]|youtu\.be\/|embed\/)([a-zA-Z0-9_-]+)#", $video_link, $matches );

		return ! empty( $matches ) ? end( $matches ) : false;
	}

	/**
	 * Get snippets data
	 *
	 * @return array
	 */
	public function get_data() {
		$data       = [];
		$saved_data = [];

		$orderby = WINP_Plugin::app()->request->request( 'orderby', 'datetime', true );
		$order   = WINP_Plugin::app()->request->request( 'order', 'desc', true );
		$paged   = WINP_Plugin::app()->request->request( 'paged', 1, 'intval' );

		$order_tags = [
			'title'    => 'title',
			'type'     => 'type_id',
			'datetime' => 'updated_at',
		];

		$args = [
			'per-page=' . $this->per_page,
			'page=' . $paged,
			'sort=' . ( 'asc' == $order ? '' : '-' ) . ( $order_tags[ $orderby ] ),
		];

		$snippets = WINP_Plugin::app()->get_api_object()->get_all_snippets( $this->common, $args );

		if ( ! empty( $snippets ) ) {
			foreach ( (array) $snippets as $snippet ) {
				$_data = [
					'ID'       => (int) $snippet->id,
					'title'    => esc_html( $snippet->title ),
					'desc'     => esc_html( $snippet->description ),
					'type'     => $snippet->type->title,
					'datetime' => date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $snippet->updated_at ),
					'insert'   => '<a class="wbcr-inp-enable-snippet-button button" data-snippet="' . esc_attr( $snippet->id ) . '" data-common="' . ( $this->common ? 1 : 0 ) . '" href="javascript: void(0)"><span class="dashicons dashicons-plus"></span></a>',
					'delete'   => '<a class="wbcr-inp-delete-snippet-button button" data-snippet="' . esc_attr( $snippet->id ) . '" href="javascript: void(0)"><span class="dashicons dashicons-no"></span></a>',
				];

				if ( $this->common ) {
					$_data['preview'] = '';
				}

				$video_id = $this->get_video_id( $snippet->video_link );

				if ( $video_id ) {
					$_data['preview'] = '<a class="thickbox" href="https://www.youtube.com/embed/' . esc_attr( $video_id ) . '?autoplay=1&rel=0&TB_iframe=true">' . '<img src="' . WINP_PLUGIN_URL . '/admin/assets/img/video.png" class="winp-library-image-preview" data-videoid="' . esc_attr( $video_id ) . '" alt="' . __( 'View the video', 'insert-php' ) . '">' . '</a>';
				}

				$data[] = $_data;

				$saved_data[ $snippet->id ] = [
					'title'   => esc_html( $snippet->title ),
					'desc'    => esc_html( $snippet->description ),
					'type'    => $snippet->type->slug,
					'content' => $snippet->content,
					'type_id' => (int) $snippet->type_id,
					'scope'   => $snippet->execute_everywhere ? 'evrywhere' : 'shortcode',
				];
			}

			update_user_meta( get_current_user_id(), WINP_Plugin::app()->getPrefix() . 'current_snippets', $saved_data );
		}

		return $data;
	}

	/**
	 * Get total items for last query
	 *
	 * @return int
	 */
	public function get_total_items() {
		return WINP_Plugin::app()->get_api_object()->get_total_items();
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @param bool  $common   - если true, то выводить общие сниппеты без привязки к пользователю
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 *                        *************************************************************************/
	public function prepare_items( $common = false ) {
		/**
		 * First, lets decide how many records per page to show
		 */
		$this->per_page = 10;

		/**
		 * @param bool $common   - если true, то выводить общие сниппеты без привязки к пользователю
		 */
		$this->common = $common;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = $this->hidden_columns;
		$sortable = $this->get_sortable_columns();

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->get_data();

		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 *
		 * @param $a
		 * @param $b
		 *
		 * @return int
		 */ /*function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'title'; // If no sort, default to title
			$order   = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; // If no order, default to asc
			$result  = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order

			return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort
		}

		usort( $data, 'usort_reorder' );*/

		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/

		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */ // $current_page = $this->get_current_page();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = $this->get_total_items();

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */ // $data = array_slice( $data, ( ( $current_page - 1 ) * $this->per_page ), $this->per_page );

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total_items / $this->per_page ),
			'orderby'     => WINP_Plugin::app()->request->request( 'orderby', 'title', true ),
			'order'       => WINP_Plugin::app()->request->request( 'order', 'asc', true ),
		] );
	}

	/**
	 * @Override of display method
	 */
	public function display() {
		/**
		 * Adds a nonce field
		 */
		wp_nonce_field( 'winp-ajax-custom-list-nonce', 'winp_ajax_custom_list_nonce' );

		if ( ! empty( $this->items ) && ! $this->common ) {
			foreach ( $this->items as $item ) {
				wp_nonce_field( 'winp-ajax-snippet-delete-' . $item['ID'], 'winp_ajax_snippet_delete_' . $item['ID'] );
			}
		}

		/**
		 * Adds field order and orderby
		 */
		echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';
		parent::display();
	}

	/**
	 * @Override ajax_response method
	 */
	public function ajax_response() {

		$this->prepare_items();
		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );
		ob_start();
		$no_placeholder = WINP_Plugin::app()->request->request( 'no_placeholder', '' );
		if ( ! empty( $no_placeholder ) ) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}
		$rows = ob_get_clean();
		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();
		ob_start();
		$this->pagination( 'top' );
		$pagination_top = ob_get_clean();
		ob_start();
		$this->pagination( 'bottom' );
		$pagination_bottom                = ob_get_clean();
		$response                         = [ 'rows' => $rows ];
		$response['pagination']['top']    = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers']       = $headers;
		if ( isset( $total_items ) ) {
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );
		}
		if ( isset( $total_pages ) ) {
			$response['total_pages']      = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}
		die( json_encode( $response ) );
	}

}