<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://cookielawinfo.com/
 * @since      1.6.6
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cookie_Law_Info
 * @subpackage Cookie_Law_Info/public
 * @author     WebToffee <info@webtoffee.com>
 */
class Cookie_Law_Info_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.6.6
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	public $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.6.6
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $version;

	public $plugin_obj;

	/*
	 * module list, Module folder and main file must be same as that of module name
	 * Please check the `register_modules` method for more details
	 */
	private $modules=array(
		'script-blocker',
		'geo-ip',
		'shortcode',
		'visitor-report', //vistor report 
	);
	public static $existing_modules=array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.6.6
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_obj) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_obj = $plugin_obj;
		register_activation_hook(CLI_PLUGIN_FILENAME,array($this,'activator'));
	}
	public static function get_cookie_categories()
	{
		$cookie_categories = array(
			'necessary'=>__('Necessary','cookie-law-info'),
			'non-necessary' => __('Non-necessary','cookie-law-info'),
		);
		$cookie_categories = apply_filters('wt_cli_add_custom_cookie_categories_name', $cookie_categories);
		return $cookie_categories;
	}
	public static function wt_cli_check_thirdparty_state()
	{
		
		$wt_cli_default_state = false;
		
		$third_party_cookie_options = get_option('cookielawinfo_thirdparty_settings');
		$wt_cli_default_state_field =  isset($third_party_cookie_options['third_party_default_state']) ? $third_party_cookie_options['third_party_default_state'] : true;
		$wt_cli_default_state = Cookie_Law_Info::sanitise_settings('third_party_default_state',$wt_cli_default_state_field);

		return $wt_cli_default_state;
	}
	/**
	* Set Default Privacy overview and Cookie Sensitivity Contents
	*
	* @since 1.7.7
	*/
	public function cli_set_default_contents()
	{
		$privacy_settings = get_option('cookielawinfo_privacy_overview_content_settings');
		$necessary_settings = get_option('cookielawinfo_necessary_settings');
		$thirdparty_settings = get_option('cookielawinfo_thirdparty_settings');
		$privacy_defaults = array(
			'privacy_overview_content' => 'This website uses cookies to improve your experience while you navigate through the website. Out of these cookies, the cookies that are categorized as necessary are stored on your browser as they are essential for the working of basic functionalities of the website. We also use third-party cookies that help us analyze and understand how you use this website. These cookies will be stored in your browser only with your consent. You also have the option to opt-out of these cookies. But opting out of some of these cookies may have an effect on your browsing experience.','privacy_overview_title' => 'Privacy Overview'
		); 
		$thirdparty_defaults = array(
			'thirdparty_on_field' => true,
			'third_party_default_state' => true,
			'thirdparty_description'=> 'Any cookies that may not be particularly necessary for the website to function and is used specifically to collect user personal data via analytics, ads, other embedded contents are termed as non-necessary cookies. It is mandatory to procure user consent prior to running these cookies on your website.',
			'thirdparty_head_section' => '',
			'thirdparty_body_section' => '',
		);
		$necessary_defaults = array('necessary_description'=>'Necessary cookies are absolutely essential for the website to function properly. This category only includes cookies that ensures basic functionalities and security features of the website. These cookies do not store any personal information.'
	  );
		($privacy_settings===false) ? update_option('cookielawinfo_privacy_overview_content_settings',$privacy_defaults) : false ;
		($necessary_settings===false) ? update_option('cookielawinfo_necessary_settings',$necessary_defaults) : false ;
		if($thirdparty_settings===false)
		{	
			update_option('cookielawinfo_thirdparty_settings',$thirdparty_defaults);
		}
		else
		{	
			if(!isset($thirdparty_settings['thirdparty_description']))
			{
				$thirdparty_settings['thirdparty_description']=$thirdparty_defaults['thirdparty_description'];
				update_option('cookielawinfo_thirdparty_settings',$thirdparty_settings);
			}				
		}		
	}
	public function activator()
	{
		$this->cli_set_default_contents();
	}
	/**
	* Set Category Cookies If Empty
	*
	* @since 1.7.7
	*/
	public function cli_set_category_cookies()
	{	
		$temp = array();
		
		$cookie_categories = self::get_cookie_categories();
		$the_options = Cookie_Law_Info::get_settings();
		$thirdparty_on_field = false;
		$third_party_cookie_options = get_option('cookielawinfo_thirdparty_settings');
		$thirdparty_on_field = isset($third_party_cookie_options['thirdparty_on_field']) ? $third_party_cookie_options['thirdparty_on_field'] : false;
		$wt_cli_is_thirdparty_enabled = Cookie_Law_Info::sanitise_settings('thirdparty_on_field',$thirdparty_on_field);
		$wt_non_necessary_cookie_value = 'yes';
		if(! self::wt_cli_check_thirdparty_state())
		{
			$wt_non_necessary_cookie_value = 'no';
		}
		if ( $the_options['is_on'] == true )
		{	
			
			foreach ($cookie_categories as $key => $value) 
			{ 	
				if( empty($_COOKIE["cookielawinfo-checkbox-$key"]) ) 
				{	
					if($key === 'non-necessary' ) {

						if($wt_cli_is_thirdparty_enabled == false) {

							return false;

						}
						else
						{	
							
							@setcookie("cookielawinfo-checkbox-$key",$wt_non_necessary_cookie_value,time()+3600,'/');
						}
					}
					else {

						@setcookie("cookielawinfo-checkbox-$key",'yes',time()+3600,'/');

					}
					
				}
			}
		}
	}
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.6.6
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cookie_Law_Info_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cookie_Law_Info_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$the_options = Cookie_Law_Info::get_settings();
		if ( $the_options['is_on'] == true ) 
		{
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cookie-law-info-public.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name.'-gdpr', plugin_dir_url( __FILE__ ) . 'css/cookie-law-info-gdpr.css', array(),$this->version, 'all' );
			//this style will include only when shortcode is called
			wp_register_style( $this->plugin_name.'-table', plugin_dir_url( __FILE__ ) . 'css/cookie-law-info-table.css', array(),$this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.6.6
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cookie_Law_Info_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cookie_Law_Info_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$the_options = Cookie_Law_Info::get_settings();
		if ( $the_options['is_on'] == true ) 
		{
			$non_necessary_cookie_ids = Cookie_Law_Info::get_non_necessary_cookie_ids();             	        
	        $cli_cookie_datas = array(
	            'nn_cookie_ids' => !empty($non_necessary_cookie_ids) ? $non_necessary_cookie_ids : array(),
	            'cookielist' => array(),
	            );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cookie-law-info-public.js', array( 'jquery' ),$this->version, false );
			wp_localize_script( $this->plugin_name, 'Cli_Data', $cli_cookie_datas );
	        wp_localize_script( $this->plugin_name, 'log_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    	}

	}

	/**
	 Registers modules: public+admin	 
	 */
	public function common_modules()
	{
		foreach ($this->modules as $module) //loop through module list and include its file
		{
			$module_file=plugin_dir_path( __FILE__ )."modules/$module/$module.php";
			if(file_exists($module_file))
			{
				self::$existing_modules[]=$module; //this is for module_exits checking
				require_once $module_file;
			} 
		}
	}
	public static function module_exists($module)
	{
		return in_array($module,self::$existing_modules);
	}

	public function register_custom_post_type()
	{
		$labels = array(
			'name'					=> __('GDPR Cookie Consent','cookie-law-info'),
	        'all_items'             => __('Cookie List','cookie-law-info'),
			'singular_name'			=> __('Cookie','cookie-law-info'),
			'add_new'				=> __('Add New','cookie-law-info'),
			'add_new_item'			=> __('Add New Cookie Type','cookie-law-info'),
			'edit_item'				=> __('Edit Cookie Type','cookie-law-info'),
			'new_item'				=> __('New Cookie Type','cookie-law-info'),
			'view_item'				=> __('View Cookie Type','cookie-law-info'),
			'search_items'			=> __('Search Cookies','cookie-law-info'),
			'not_found'				=> __('Nothing found','cookie-law-info'),
			'not_found_in_trash'	=> __('Nothing found in Trash','cookie-law-info'),
			'parent_item_colon'		=> ''
		);
		$args = array(
			'labels'				=> $labels,
			'public'				=> false,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'show_ui'				=> true,
			'query_var'				=> true,
			'rewrite'				=> true,
			'capabilities' => array(
				'publish_posts' => 'manage_options',
				'edit_posts' => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'delete_posts' => 'manage_options',
				'delete_others_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'edit_post' => 'manage_options',
				'delete_post' => 'manage_options',
				'read_post' => 'manage_options',
			),
			/** done editing */
			'menu_icon'				=>plugin_dir_url( __FILE__ ).'images/cli_icon.png',
			'hierarchical'			=> false,
			'menu_position'			=> null,
			'supports'				=> array( 'title','editor' )
		); 
		register_post_type(CLI_POST_TYPE, $args );
	}

	/** Removes leading # characters from a string */
	public static function cookielawinfo_remove_hash( $str ) 
	{	
	  if( $str[0] == "#" ) 
	  {
	    $str = substr( $str, 1, strlen($str) );
	  }
	  else {
	    return $str;
	  }
	  return self::cookielawinfo_remove_hash( $str );
	}

	/**
	 Outputs the cookie control script in the footer
	 N.B. This script MUST be output in the footer.
	 
	 This function should be attached to the wp_footer action hook.
	*/
	public function cookielawinfo_inject_cli_script() 
	{		
		global $wp_customize;
	    $the_options = Cookie_Law_Info::get_settings();
	  	if ( $the_options['is_on'] == true )
	  	{ 	
			// Output the HTML in the footer:
			$message =nl2br($the_options['notify_message']);
	    	$str = do_shortcode( stripslashes ( $message ) );
	        $str = __($str,'cookie-law-info');
	        $head= __($the_options['bar_heading_text'],'cookie-law-info');
	        $head= trim(stripslashes($head));        
	                
		    $notify_html = '<div id="' .$this->cookielawinfo_remove_hash( $the_options["notify_div_id"] ) . '">'.
		    ($head!="" ? '<h5 class="cli_messagebar_head">'.$head.'</h5>' : '')
		    .'<span>' . $str . '</span></div>';
		    
		    //if($the_options['showagain_tab'] === true) 
		    //{
		    	$show_again=__( stripslashes( $the_options["showagain_text"] ),'cookie-law-info');
		      	$notify_html .= '<div id="' . $this->cookielawinfo_remove_hash( $the_options["showagain_div_id"] ) . '" style="display:none;"><span id="cookie_hdr_showagain">'.$show_again.'</span></div>';
		    //}
		    global $wp_query;
		    $current_obj = get_queried_object();
		    $post_slug ='';
		    if(is_object($current_obj))
		    {
			    if(is_category() || is_tag())
			    {
			    	$post_slug =isset($current_obj->slug) ? $current_obj->slug : '';
			    }
			    elseif(is_archive())
			    {
			    	$post_slug =isset($current_obj->rewrite) && isset($current_obj->rewrite['slug']) ? $current_obj->rewrite['slug'] : '';
			    }
			    else
			    {
			    	if(isset($current_obj->post_name))
			    	{
			    		$post_slug =$current_obj->post_name;
			    	}			    	
			    }
			}		    
			if( isset( $wp_customize ) ) {
				$notify_html = '';
			}
		    $notify_html = apply_filters('cli_show_cookie_bar_only_on_selected_pages',$notify_html,$post_slug);
		    require_once plugin_dir_path( __FILE__ ).'views/cookie-law-info_bar.php';
	  	}
	}
	
	/* Print scripts or data in the head tag on the front end. */
	public function include_user_accepted_cookielawinfo()
	{
	     $the_options = Cookie_Law_Info::get_settings();	      
	     if($the_options['is_on'] == true && !is_admin()) 
	     {
			$third_party_cookie_options = get_option('cookielawinfo_thirdparty_settings');
	        if(!empty($third_party_cookie_options))
	        {	
				$thirdparty_on_field = isset($third_party_cookie_options['thirdparty_on_field']) ? $third_party_cookie_options['thirdparty_on_field'] : false;
				$wt_cli_is_thirdparty_enabled = Cookie_Law_Info::sanitise_settings('thirdparty_on_field',$thirdparty_on_field);
				if($wt_cli_is_thirdparty_enabled == true && isset( $_COOKIE['viewed_cookie_policy'] ) && isset( $_COOKIE["cookielawinfo-checkbox-non-necessary"] ))
				{
					if($_COOKIE['viewed_cookie_policy']=='yes' && $_COOKIE["cookielawinfo-checkbox-non-necessary"] =='yes')
					{   
						echo wp_unslash( isset($third_party_cookie_options['thirdparty_head_section']) ? $third_party_cookie_options['thirdparty_head_section'] : '' );
					}
				}	           
	       	}
	     }
	}

	/* Print scripts or data in the body tag on the front end. */
	public function include_user_accepted_cookielawinfo_in_body()
	{
	   $the_options = Cookie_Law_Info::get_settings();	    
	    if($the_options['is_on'] == true && !is_admin()) 
	    {	        
			$third_party_cookie_options=get_option('cookielawinfo_thirdparty_settings');
	        if(!empty($third_party_cookie_options))
	        {	
				$thirdparty_on_field = isset($third_party_cookie_options['thirdparty_on_field']) ? $third_party_cookie_options['thirdparty_on_field'] : false;
				$wt_cli_is_thirdparty_enabled = Cookie_Law_Info::sanitise_settings('thirdparty_on_field',$thirdparty_on_field);
		        if( $wt_cli_is_thirdparty_enabled == true  && isset($_COOKIE['viewed_cookie_policy']) && isset( $_COOKIE["cookielawinfo-checkbox-non-necessary"] ) )
		        {		
	               if($_COOKIE['viewed_cookie_policy'] == 'yes' && $_COOKIE["cookielawinfo-checkbox-non-necessary"] =='yes')
	               {                   
						echo wp_unslash( isset($third_party_cookie_options['thirdparty_body_section']) ? $third_party_cookie_options['thirdparty_body_section'] : '' );
	               }
		        }		           
		    }
	    }
	}

	public function other_plugin_compatibility()
	{
		if(!is_admin())
		{
			add_action('wp_head',array($this,'other_plugin_clear_cache'));
			//cache clear===========
			if(isset($_GET['cli_action']))
			{
		        // Clear Litespeed cache
				if(class_exists('LiteSpeed_Cache_API') && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ))
				{
					LiteSpeed_Cache_API::purge_all();
				}

		        // WP Super Cache
		        if(function_exists('wp_cache_clear_cache')) 
		        {
		          	wp_cache_clear_cache();
		        }

		        // W3 Total Cache
		        if(function_exists('w3tc_flush_all')) 
		        {
		          	w3tc_flush_all();
		        }

		        // Site ground
		        if(class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
		        	SG_CachePress_Supercacher::purge_cache(true);
		        }

		        // Endurance Cache
		        if(class_exists('Endurance_Page_Cache') && method_exists('Endurance_Page_Cache','purge_all')) 
		        {
		          $e = new Endurance_Page_Cache;
		          $e->purge_all();
		        }

		        // WP Fastest Cache
		        if(isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'],'deleteCache')) 
		        {
		          $GLOBALS['wp_fastest_cache']->deleteCache(true);
		        }
			}
			//cache clear============
		}
	}
	public function other_plugin_clear_cache()
	{
	
		$cli_flush_cache=2;
		// Clear Litespeed cache
		if(class_exists('LiteSpeed_Cache_API') && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ))
		{
			$cli_flush_cache=1;
		}

        // WP Super Cache
        if(function_exists('wp_cache_clear_cache')) 
        {
          	$cli_flush_cache=1;
        }

        // W3 Total Cache
        if(function_exists('w3tc_flush_all')) 
        {
          	$cli_flush_cache=1;
        }

        // Site ground
        if(class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
        	$cli_flush_cache=1;
        }

        // Endurance Cache
        if(class_exists('Endurance_Page_Cache') && method_exists('Endurance_Page_Cache','purge_all')) 
        {
          $cli_flush_cache=1;
        }

        // WP Fastest Cache
        if(isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'],'deleteCache')) 
        {
          	$cli_flush_cache=1;
		}
		if($cli_flush_cache == 1):
		?>
		<script type="text/javascript">
			var cli_flush_cache=<?php echo $cli_flush_cache; ?>;
		</script>
		<?php
		endif;
	}


}