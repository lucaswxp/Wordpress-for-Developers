<?php
/**
 * Helper for dealing with data saved by page creator
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Helper
 */

class WD_Helper_Page extends WD_Helper_AbstractHelper {

/**
 * Singleton
 * 
 * @var WD_Helper_Page
 */
	protected static $instance;
	
/**
 * Gets a field value for a given name. If the field have a prefix, you must insert it too:
 * ->get('name');
 * 
 * with a prefix called "userdata_":
 * ->get('userdata_name');
 * 
 * @param string $name
 * @return mixed
 */
	public function get($name){
		return get_option($name);
	}

/**
 * Singleton
 * 
 * @return self
 */
	public static function getInstance(){
		return self::$instance ? self::$instance : self::$instance = new self;
	}
}