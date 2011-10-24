<?php
/**
 * top level page creator
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Creator.Page
 */

class WD_Creator_Page_TopLevel extends WD_Creator_Page_AbstractPage {
	
/**
 * Icon
 * 
 * @var string
 */
	protected $icon;
	
/**
 * Position
 * 
 * @var int
 */
	protected $position;
	
/**
 * Adds menu
 * 
 * @return self
 */
	public function _actionAddMenus(){
		add_menu_page($this->pageTitle, $this->menuTitle, $this->capability, $this->id, array($this, 'outputs'), $this->icon, $this->position);
	}

/**
 * Sets page icon url
 * 
 * @param string $url
 * @return self
 */
	public function setIcon($url){
		$this->icon = $url;
		return $this;
	}
	
/**
 * Sets page menu position
 * 
 * @param string $position
 * @return self
 */
	public function setPosition($position){
		$this->position = $position;
		return $this;
	}
}