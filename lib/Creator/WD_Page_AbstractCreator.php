<?php
/**
 * Create Page
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Page
 */

abstract class WD_Page_AbstractCreator {
	
/**
 * Page title
 * 
 * @var string
 */
	protected $pageTitle;
	
/**
 * Menu title
 * 
 * @var string
 */
	protected $menuTitle;
	
/**
 * Page ID (slug)
 * 
 * @var string
 */
	protected $id;
	
/**
 * Capability
 * 
 * @var string
 */
	protected $page = 'update_themes';

/**
 * Meta box prefix
 * 
 * @var string
 */
	protected $prefix = '';
	
/**
 * Form data handler
 * 
 * @var FG_HTML_Form_DataHandler
 */
	private $dataHandler;
	
/**
 * Save callback
 * 
 * @var array
 */
	private $saveCallback;

/**
 * Inits page with user-defined title
 * 
 * @param string $title Page title
 */
	public function __construct($title){
		$this->pageTitle = $title;
		$this->menuTitle = $title;
		$this->id  = sanitize_title($title);
		$this->dataHandler = new FG_HTML_Form_DataHandler;
		$this->saveCallback = array($this, 'saveFields');
	}
}