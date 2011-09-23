<?php
/**
 * Creates a new instance of WD_Creator_Page_TopLevel or WD_Creator_Page_Sub
 * 
 * @param string $title Title page
 * @param string $parent Parent page
 * @return WD_Creator_Page_TopLevel|WD_Creator_Page_Sub
 */
function wd_page($title, $parent = false){
	return $parent ? new WD_Creator_Page_Sub($title, $parent) : new WD_Creator_Page_TopLevel($title);
}

/**
 * Returns a instance of WD_Helper_Page
 * 
 * This class helps the user retrieve data saved by the page creator
 * 
 * @return WD_Helper_Page
 */
function wd_opt(){
	return WD_Helper_Page::getInstance();
}