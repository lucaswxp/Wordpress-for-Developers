<?php
/**
 * Create taxonomy fields
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Creator
 */

class WD_Creator_TaxonomyFieldsCreator extends WD_Creator_AbstractCreator {
	
/**
 * Taxonomy
 * 
 * @var string
 */
	protected $taxonomy;

/**
 * Inits the field collection
 * 
 * @param string $title For what taxonomy?
 */
	public function __construct($taxonomy){
		$this->taxonomy = $taxonomy;
		parent::__construct();
	}
	
/**
 * Echoes the self::renderForEdit() returned value
 * 
 * @return string
 */
	public function outputsForEdit(){
		echo $this->renderForEdit();
	}
	
/**
 * Returns the fields
 * 
 * @return string
 */
	public function render(){
		$html = array();
		foreach($this->dataHandler->getContent() as $content){
			if(is_a($content, 'FG_HTML_Form_Input_Fillable')){
				$html[] = Html::tag('div', $content)->setClass('form-field wd-taxonomy-fieldcreator');
			}else{
				$html[] = $content;
			}
		}
		
		return join($html);
	}
	
/**
 * Returns the fields for edit screen
 * 
 * @return string
 */
	public function renderForEdit(){
		global $tag;
		
		$data = array();
			
		foreach($this->dataHandler->getFillable() as $field){
			$data[$field->getBaseName()] = apply_filters('wd-fieldcreator-get-field-' . $this->prefix . $field->getBaseName(), wd_get_term_meta($tag->term_id, ($this->prefix . $field->getBaseName())));
		}
		
		$this->dataHandler->populate($data)->execute();
	        
		foreach($this->dataHandler->getContent() as $content){
			if(is_a($content, 'FG_HTML_Form_Input_Fillable')){
				$html[] = Html::tag('tr', array(Html::tag('th', $content->getLabel()), Html::tag('td', $content->getField())))->setClass('form-field wd-taxonomy-fieldcreator');
			}else{
				$html[] = $content;
			}
		}
		
		return join('', $html);	
	}
	
/**
 * Adds the necessary hooks and populate fields if needed
 * 
 * @return self
 */
	public function init(){
		// add fields
		add_action($this->taxonomy . '_add_form_fields', array($this, 'outputs'));
		add_action($this->taxonomy . '_edit_form_fields', array($this, 'outputsForEdit'));
		
		// save fields
		add_action('created_' . $this->taxonomy, array($this, 'saveFields'));
		add_action('edited_' . $this->taxonomy, array($this, 'saveFields'));
        return $this;
	}
	
/**
 * Saves fields in database
 * 
 * @param int $termID
 * @return self
 */
	public function saveFields($termID){
        foreach($this->dataHandler->getFillable() as $field){
	        if(isset($_POST[$field->getBaseName()])){
				wd_update_term_meta($termID, $name = ($this->prefix . $field->getBaseName()), apply_filters('wd-fieldcreator-save-' . $name, $_POST[$field->getBaseName()]));
	        }
        }
	}
}