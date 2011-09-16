<?php
/**
 * Creates a new instance of WD_Creator_MetaBox
 * 
 * @param string $title Title of meta box
 */
function wd_meta_box($title){
	return new WD_Creator_MetaBox($title);
}

/**
 * Returns a instance of WD_Helper_MetaBox
 * 
 * This class helps the user retrieve data saved by the meta box creator
 * 
 * @return WD_Helper_MetaBox
 */
function wd_mb(){
	return WD_Helper_MetaBox::getInstance();
}