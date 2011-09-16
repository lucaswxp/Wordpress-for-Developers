<?php
/**
 * Abstract page creator
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Creator.Page
 */

abstract class WD_Creator_Page_AbstractPage extends WD_Creator_AbstractCreator {
	
/**
 * Page title
 * 
 * @var string
 */
	protected $pageTitle;
	
/**
 * Submit button text
 * 
 * @var string
 */
	protected $submitText;
	
/**
 * Menu title
 * 
 * @var string
 */
	protected $menuTitle;
		
/**
 * Success message
 * 
 * @var string
 */
	protected $successMessage;
	
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
	protected $capability = 'update_themes';

/**
 * Inits page with user-defined title
 * 
 * @param string $title Page title
 */
	public function __construct($title){
		$this->pageTitle = $title;
		$this->menuTitle = $title;
		$this->id  = sanitize_title($title);
		$this->successMessage = __('Settings saved.');
		$this->submitText = __('Save Changes');
		parent::__construct();
	}

/**
 * Sets menu title (title attribute, not title head)
 * 
 * @param string $title
 * @return self
 */
	public function setMenuTitle($title){
		$this->menuTitle = $title;
		return $this;
	}

/**
 * Sets success message
 * 
 * @param string $text
 * @return self
 */
	public function setSuccessMessage($text){
		$this->succecssMessage = $text;
		return $this;
	}
	
/**
 * Set submit text
 * 
 * @param string $text
 * @return self
 */
	public function setSubmitText($text){
		$this->submitText = $text;
		return $this;
	}
	
/**
 * Sets user role necessary to see the page
 * 
 * @param string $capability
 * @return self
 */
	public function setCapability($capability){
		$this->capability = $capability;
		return $this;
	}
	
/**
 * Sets id (slug)
 * 
 * @param string $id
 * @return self
 */
	public function setId($id){
		$this->id = $id;
		return $this;
	}
	
/**
 * Render form
 * 
 * @return self
 */
	public function render(){
		if(!empty($_POST)){
			$this->saveFields();
			$html[] = Html::tag('div', Html::tag('p', $this->successMessage))->setClass('updated');
		}
		$html[] = Html::tag('h2')->setContent($this->pageTitle);
		
		$html[] = '<form method="POST" action="' . $_SERVER['REQUEST_URI'] . '"><table class="form-table"><tbody>';

		$data = array();
		foreach($this->dataHandler->getFillable() as $field){
			$data[$field->getBaseName()] = apply_filters('wd-page-get-field-' . $this->prefix . $field->getBaseName(), get_option($this->prefix . $field->getBaseName()));
		}
		$this->dataHandler->populate($data)->execute();
		
		foreach($this->dataHandler->getContent() as $content){
			if(is_a($content, 'FG_HTML_Form_Input_Fillable')){
				$label = Html::tag('th', $content->getLabel())->setScope('row');
				$field = Html::tag('td', $content->getEntry());
				$current = $label . $field;
			}else{
				$current = Html::tag('td', $content)->setColspan('2');
			}
			$html[] = Html::tag('tr', $current);
		}
		$html[] = '</tboy></table>';
		
		$html[] = Form::submit($this->submitText)->setClass('button-primary');
		$html[] = '</form>';
		
		
		return Html::tag('div', join($html))->setClass('wrap wd-page')->setId('wd-page-' . $this->id);
	}
	
/**
 * Inits hooks
 * 
 * @return self
 */
	public function init(){
		add_action('admin_menu', array($this, '_actionAddMenus'));
	}

/**
 * Saves fields in database
 * 
 * @return self
 */
	public function saveFields(){
        foreach($this->dataHandler->getFillable() as $field){
	        if(isset($_POST[$field->getBaseName()])){
	             update_option($name = ($this->prefix . $field->getBaseName()), apply_filters('wd-page-field-save-' . $name, $_POST[$field->getBaseName()]));
	        }
        }
	}
	
/**
 * Adds menu
 * 
 * @return self
 */
	abstract public function _actionAddMenus();
}