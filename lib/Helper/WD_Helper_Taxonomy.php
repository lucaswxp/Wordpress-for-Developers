<?php
/**
 * Helper for dealing with data saved by taxonomy field creators
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Helper
 */

class WD_Helper_Taxonomy extends WD_Helper_AbstractHelper {

/**
 * Singleton
 * 
 * @var WD_Helper_Taxonomy
 */
	protected static $instance;
	
/**
 * Gets a field value for a given name. If the field have a prefix, you must insert it too:
 * ->get('name', $term->term_id);
 * 
 * with a prefix called "userdata_":
 * ->get('userdata_name', $term->term_id);
 * 
 * @param string $fieldName
 * @param int $termID
 * @return mixed
 */
	public function get($fieldName, $termID = null){
		global $term, $wpdb;
		
		$theTerm = $term;
		
		if($termID)
			$theTerm = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.term_id = %s LIMIT 1", $termID));
		
		if(is_wp_error($theTerm))
			return false;
			
		return wd_get_term_meta($theTerm->term_id, $fieldName);
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