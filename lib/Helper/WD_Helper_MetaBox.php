<?php
/**
 * Helper for dealing with data saved by meta box creator
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Helper
 */

class WD_Helper_MetaBox extends WD_Helper_AbstractHelper {

/**
 * Singleton
 * 
 * @var WD_Helper_MetaBox
 */
	protected static $instance;
	
/**
 * Gets a field value for a given name. If the field have a prefix, you must insert it too:
 * ->get('name', $post->ID);
 * 
 * with a prefix called "userdata_":
 * ->get('userdata_name', $post->ID);
 * 
 * @param string $fieldName
 * @param int $postID
 * @return mixed
 */
	public function get($fieldName, $postID = null){
		global $post;
		
		$thePost = $post;
		
		if($postID)
			$thePost = get_post($postID);
			
		return get_post_meta($thePost->ID, $fieldName, true);
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