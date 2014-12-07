<?php
/*
 Plugin Name: Choose Searchable Post Types
 Plugin URI: https://github.com/latublu/blu-choose-searchable-post-types
 Description: This plugin provides the ability to choose which WordPress post types are allowed in search results.
 Version: 1.0.0
 Author: Aeon Blu
 Author URI: http://aeonblu.com
 License: GPL2
*/

/**
 * BluSearchablePostTypes Class
 *
*/
class BluSearchablePostTypes 
{
	public $className;
	
	public $pluginName = 'Choose Searchable Post Types';
	public $pluginShortName = 'Searchable Post Types';
	
	public $debug = 0;
	
	public $publicPostTypes;
	
	public $allowedPostTypes;
		
	/*----------------------------------------------------------------------
	CONSTRUCTOR: __construct()
	----------------------------------------------------------------------*/
	/**
     * BluSearchablePostTypes Class Constructor
     *
     * Initializes class attributes
     *
     */
	public function __construct()
	{
		$this->className = get_class();
		
		$this->setDebug();
		
		// Define Constants
		define( 'BLU_SEARCH_PLUGIN_PATH', trailingslashit( WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__ ),"",plugin_basename( __FILE__ ) ) ) );
        
        if( is_admin() )
        {
			if ($this->debug)
			{
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
				{
					
				}
				else
				{
					$this->outputToConsole('is enabled');
				}			
			}
			
			if ( function_exists('register_activation_hook') ) 
			{
				// Register WordPress Activation Hook
				register_activation_hook( __FILE__, array( $this, 'activate' ) );
			}
		
			if ( function_exists('register_deactivation_hook') ) 
			{
				// Register WordPress Deactivation Hook
				register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
			}
			
			if ( function_exists('add_action') ) 
			{
				// Add WordPress action for the admin menu
				add_action( 'admin_menu', array($this,'adminMenu') );
       		}
        }
        
        if( !(is_admin()) )
        {
			add_action( 'pre_get_posts', array($this,'preGetPosts'));
        }
		
	}
	
	/*----------------------------------------------------------------------
	METHOD: setDebug()
	----------------------------------------------------------------------*/
	/**
	 *  Sets value of debug class variable based on option value.
     *
     */
	public function setDebug()
	{
		if ( function_exists('get_option') ) 
		{
			$this->debug = get_option('blusearch_debug');
		}
	}
	
	/*----------------------------------------------------------------------
	METHOD: getDebug()
	----------------------------------------------------------------------*/
	/**
	 *  Gets debug option value.
     *
	 * @return boolean
     *
     */
	public function getDebug()
	{
		if ( function_exists('get_option') ) 
		{
			$debug = get_option('blusearch_debug');
		}
		
		return $debug;
	}
	
	/*----------------------------------------------------------------------
	METHOD: outputToConsole(note)
	----------------------------------------------------------------------*/
	/**
	 *  Outputs debug note to console log.
     *
	 * @param string $note
     *
     */
	private function outputToConsole($note='')
	{
		if ( strlen($note) ) 
		{
			echo '<script>' . 'console.log("' . $this->className . ' debug: ' . $note . '");' . ' </script>';
		} 
	}
	
	/*----------------------------------------------------------------------
	METHOD: activate()
	----------------------------------------------------------------------*/
	/**
     * Activates Plugin
     *
     * Called by WordPress Register Activation Hook
     *
     */
	public function activate()
	{
		$post_types = $this->getPublicPostTypes('names');
		
		$post_types_keys = array_keys($post_types);
		
		$post_types_keys_json = json_encode($post_types_keys);
		
		if ( function_exists('update_option') ) 
		{
			// Set WordPress option
			update_option('blusearch_debug',false);
			update_option('blusearch_allowed_post_types',$post_types_keys_json);
    	}
 	}
	
	/*----------------------------------------------------------------------
	METHOD: deactivate()
	----------------------------------------------------------------------*/
	/**
     * Deactivates Plugin
     *
     * Called by WordPress Register Deactivation Hook
     *
     */
	public function deactivate()
	{
		if ( function_exists('delete_option') ) 
		{
			// Remove WordPress option
			delete_option('blusearch_debug');
			delete_option('blusearch_allowed_post_types');
   		}
 	}
 	
	/*----------------------------------------------------------------------
	METHOD: adminMenu()
	----------------------------------------------------------------------*/
	/**
     * Add an options page to the setting menu.
     *
     * 
     *
     */
	public function adminMenu()
	{
		if ( function_exists('add_options_page') ) 
		{
			$page = add_options_page($this->pluginName.' Settings', $this->pluginShortName, 'manage_options', dirname(__FILE__), array($this,'optionsPage'));
		}
	}
	
 	/*----------------------------------------------------------------------
	METHOD: optionsPage()
	----------------------------------------------------------------------*/
	/**
     * Display options page
     *
     */
	public function optionsPage()
	{
		
        include(BLU_SEARCH_PLUGIN_PATH . 'includes/admin/optionsPage.php');
		
	}
	
	/*----------------------------------------------------------------------
	METHOD: preGetPosts(query)
	----------------------------------------------------------------------*/
	/**
	 *  Pre-process get posts method for pre_get_posts action.
     *
	 * @param object $query
     *
     */
	public function preGetPosts($query=null)
	{
		if ( $query->is_search() && $query->is_main_query() ) 
		{
			if ($this->debug)
			{
				echo $this->className.' debug '.' preGetPosts - <br />'.PHP_EOL;
			}
						
			$allowed_post_types_opt = $this->getAllowedPostTypes();
			
			if ( count($allowed_post_types_opt) ) 
			{
				$allowed_post_types = $allowed_post_types_opt;
			} 
			else 
			{
				$post_types = $this->getPublicPostTypes('names');
			
				$allowed_post_types = array_keys($post_types);
			}
			
			if ($this->debug)
			{
				echo "allowed_post_types: <pre>".print_r($allowed_post_types,true)."</pre>".PHP_EOL;
			}
			
			$query->set('post_type', $allowed_post_types);
			
			if ($this->debug)
			{
				echo $this->className.' debug '."query->is_search(): ".print_r($query->is_search(), true)."<br />".PHP_EOL;
				echo $this->className.' debug '."query->is_main_query(): ".print_r($query->is_main_query(), true)."<br />".PHP_EOL;
				echo $this->className.' debug '."query: <pre>".print_r($query, true)."</pre>".PHP_EOL;
			}
		} 
	}
	
	/*----------------------------------------------------------------------
	METHOD: getPostTypes(output)
	----------------------------------------------------------------------*/
	/**
	 *  Gets public post types.
     *
	 * @param string $note
	 * @return array
     *
     */
	public function getPublicPostTypes($output='names')
	{
		switch ($output)
		{
		    case 'objects':
		        
		        break;
		        
		    default:
		    
		        $output = 'names';
		        
		        break;
		}
		
		$post_types_get = get_post_types(array( 'public' => true, 'exclude_from_search' => false ), $output, 'and');
		
		$this->publicPostTypes = array();
		
		foreach ($post_types_get as $key=>$value)
		{
		    if ( $key != 'attachment' ) 
		    {
		        $this->publicPostTypes[$key] = $value;
		    } 
		}
		
		return $this->publicPostTypes;
	}
	
	/*----------------------------------------------------------------------
	METHOD: getAllowedPostTypes()
	----------------------------------------------------------------------*/
	/**
	 *  Gets blusearch_allowed_post_types option value as array.
     *
	 * @return array
     *
     */
	public function getAllowedPostTypes()
	{
		$this->allowedPostTypes = array('post');
		
		if ( function_exists('get_option') ) 
		{
			$blusearch_allowed_post_types = get_option('blusearch_allowed_post_types');
	
			if ( !empty($blusearch_allowed_post_types) ) 
			{
				$post_types = json_decode($blusearch_allowed_post_types);
	
				if ( is_array($post_types) ) 
				{
					$this->allowedPostTypes = $post_types;
				} 
				else 
				{
					$this->allowedPostTypes = array($post_types);
				}
			}
		}
		
		return $this->allowedPostTypes;
	}
	
	/*----------------------------------------------------------------------
	DESTRUCTOR: __destruct()
	----------------------------------------------------------------------*/
	/**
	 * BluSearchablePostTypes Class Destructor
	 *
	 */
    public function __destruct()
    {
		
    }
}

$bluSearchablePostTypes = new BluSearchablePostTypes();

?>
