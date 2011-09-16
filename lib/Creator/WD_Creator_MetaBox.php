<?php
/**
 * Create MetaBox
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Creator
 */

class WD_Creator_MetaBox extends WD_Creator_AbstractCreator {
	
/**
 * Meta box title
 * 
 * @var string
 */
	protected $title;
	
/**
 * Meta box ID
 * 
 * @var string
 */
	protected $id;
	
/**
 * Meta box page
 * 
 * @var string|array
 */
	protected $page = 'post';
	
/**
 * Meta box context
 * 
 * @var string
 */
	protected $context = 'advanced';
	
/**
 * Meta box priority
 * 
 * @var string
 */
	protected $priority = 'default';

/**
 * Inits meta box title, id and data handler
 * 
 * @param string $title Meta box title
 */
	public function __construct($title){
		$this->title = $title;
		$this->id  = sanitize_title($title);
		parent::__construct();
	}
	
/**
 * Data used as param for add_meta_box function
 * 
 * @param string $id
 * @return self
 */
	public function setId($id){
		$this->id = $id;
		return $this;
	}
	
/**
 * Data used as param for add_meta_box function
 * 
 * @param string|array $page
 * @return self
 */
	public function setPage($page){
		$this->page = $page;
		return $this;
	}

/**
 * Data used as param for add_meta_box function
 * 
 * @param string $context
 * @return self
 */
	public function setContext($context){
		$this->context = $context;
		return $this;
	}
	
/**
 * Data used as param for add_meta_box function
 * 
 * @param string $priority
 * @return self
 */
	public function setPriority($priority){
		$this->priority = $priority;
		return $this;
	}
	
/**
 * Used for add a prefix to the name that will be save
 * 
 * @param string $prefix
 * @return self
 */
	public function setPrefix($prefix){
		$this->prefix = $prefix;
		return $this;
	}
	
/**
 * Hook triggered in admin_init or add_meta_boxes hooks
 * 
 * @return self
 */
	public function _actionAddMetaBox(){
		add_meta_box($this->id, $this->title, array($this, 'outputs'), $this->page, $this->context, $this->priority);
	}
	
/**
 * Echoes the self::render() returned value
 * 
 * @return string
 */
	public function outputs(){
		echo $this->render();
	}
	
/**
 * Returns the added data
 * 
 * @return string
 */
	public function render(){
		global $post;
	
		if(isset($post->ID)){
			
			$data = array();
			
	        foreach($this->dataHandler->getFillable() as $field){
		        $data[$field->getBaseName()] = apply_filters('wd-meta-box-get-field-' . $this->prefix . $field->getBaseName(), get_post_meta($post->ID, ($this->prefix . $field->getBaseName()), true));
	        }
	        
	        $this->dataHandler->populate($data);
		}
		
		return Html::tag('div', $this->dataHandler->render())->attr('class', 'wd-meta-box')->render();
	}
	
/**
 * Adds the necessary hooks and populate fields if needed
 * 
 * @return self
 */
	public function init(){
		global $wp_version;
		
		// adds meta boxes
		$callback = array($this, '_actionAddMetaBox');
		if($wp_version >= 3){
			add_action('add_meta_boxes', $callback);
		}else{
			add_action('admin_init', $callback, 1);
		}
		
		// saves posts
        add_action('save_post', $this->saveCallback);        
        return $this;
	}
	
/**
 * Saves fields in database
 * 
 * @param int $postid
 * @return self
 */
	public function saveFields($postid){
        foreach($this->dataHandler->getFillable() as $field){
	        if(isset($_POST[$field->getBaseName()])){
	             update_post_meta($postid, $name = ($this->prefix . $field->getBaseName()), apply_filters('wd-meta-box-save-' . $name, $_POST[$field->getBaseName()]));
	        }
        }
	}
}