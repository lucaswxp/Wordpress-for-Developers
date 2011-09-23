<?php
/**
 * Base URL of lib
 * 
 * @var string
 */
define('WD_BASE_URL', get_bloginfo('url') . '/wp-content/WD');

/**
 * Autoloads classes
 */
	function wdAutoloader($class){
		$ds = DIRECTORY_SEPARATOR;
	
		$pieces = explode('_', $class);
		$count = count($pieces);
		$lastIndex = $count-1;
		if($count > 1 && $pieces[0] == 'WD'){
			$last = $pieces[$lastIndex];
			
			if($last != $pieces[1]){
				$pieces[$lastIndex] = $class;
			}
			unset($pieces[0]);
			$file = join($ds, $pieces);
		}
		
		if(isset($file))
			require_once dirname(__FILE__) . $ds . $file . '.php';
	}
	spl_autoload_register('wdAutoloader');

/**
 * requires
 */	
	// meta box functions
	require_once dirname(__FILE__) . '/functions/meta_box.php';
	
	// taxonomy functions
	require_once dirname(__FILE__) . '/functions/taxonomy.php';
	
	// pages functions
	require_once dirname(__FILE__) . '/functions/pages.php';
	
	// form external
	require_once dirname(__FILE__) . '/Externals/Form/fg/load.php';
	
	// wd form, is necessary include manually because the name do not follow the convention
	require_once dirname(__FILE__) . '/Form/WD_Form.php';

/**
 * Loads styles and scripts
 */
	function wd_admin_init(){
		$path = WD_BASE_URL . '/public';
		
		// css
		wp_register_style('wd', $path . '/css/style.css', false);
		wp_enqueue_style('wd');

		// js
		wp_register_script('wd', $path . '/js/load.js', false);
		wp_enqueue_script('wd');
	}
	add_action('admin_init', 'wd_admin_init');