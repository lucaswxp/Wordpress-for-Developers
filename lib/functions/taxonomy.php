<?php
/**
 * Creates a new instance of WD_Creator_TaxonomyFieldsCreator
 * 
 * @param string $type Type of taxonomy where the fields will be created
 */
function wd_taxonomy($type){
	return new WD_Creator_TaxonomyFieldsCreator($type);
}

/**
 * Saves some information about a term
 * 
 * @param int $termID
 * @param string $key
 * @param mixed $value
 */
function wd_update_term_meta($termID, $key, $value){
	return update_option("wdtermmeta-$termID-$key", serialize($value));
}

/**
 * Gets some information saved by wd_update_term_meta function
 * 
 * @param int $termID
 * @param string $key
 */
function wd_get_term_meta($termID, $key){
	return unserialize(get_option("wdtermmeta-$termID-$key"));
}

/**
 * Returns a instance of WD_Helper_Taxonomy
 * 
 * This class helps the user retrieve data saved by the creator
 * 
 * @return WD_Helper_Taxonomy
 */
function wd_tax(){
	return WD_Helper_Taxonomy::getInstance();
}