<?php
/**
 * Submenu page
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Creator.Page
 */

class WD_Creator_Page_Sub extends WD_Creator_Page_AbstractPage {
	
/**
 * Parent page
 * 
 * @var string
 */
	protected $parentSlug;
	
/**
 * Inits sub page
 * 
 * @param string $title
 * @param string $parent
 */
	public function __construct($title, $parentSlug){
		$this->parentSlug = $parentSlug;
		parent::__construct($title);
	}
	
/**
 * Adds menu
 * 
 * @return self
 */
	public function _actionAddMenus(){
		add_submenu_page($this->parentSlug, $this->pageTitle, $this->menuTitle, $this->capability, $this->id, array($this, 'outputs'));
	}
}