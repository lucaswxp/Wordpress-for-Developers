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