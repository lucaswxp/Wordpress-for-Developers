<?php
/**
 * Helper for dealing with data saved by creators
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package WD.Helper
 */

abstract class WD_Helper_AbstractHelper {
	
/**
 * Gets singleton instance
 * 
 * This method needs to be abstract because of the late static binding problem
 * 
 * @return WD_Helper_AbstractHelper
 */
	abstract public static function getInstance();

/**
 * Singleton class
 */
	protected function __construct(){
	}
	
/**
 * Gets a field value for a given name. If the field have a prefix, you must insert it too:
 * ->get('name', $post->ID);
 * 
 * with a prefix called "userdata_":
 * ->get('userdata_name', $post->ID);
 * 
 * @param string $fieldName
 * @return mixed
 */
	abstract public function get($fieldName);
	
/**
 * Checks if the returned value for ->get($fieldName) is empty
 * 
 * @param string $fieldName
 * @return bool
 */
	public function isEmpty($fieldName){
		$value = trim($this->get($fieldName));
		return empty($value);
	}
	
/**
 * Prints the result returned by ->get($fieldName).
 * 
 * You can pass a wrapper, like:
 * ->the('username', Html::tag('span')->setClass('username'));
 * prints, if the username is "lucas123": <span class="username">lucas123</span>
 * 
 * Or, if you prefer, this will do the same:
 * ->the('username', '<span class="username">', '</span>');
 * 
 * The reason to use the span element as an attribute, and not just as simple plain text, is
 * that in this way it will be displayed only if the content is not empty, so, if the username
 * is empty, the span tag would not appear.
 * 
 * 
 * @param string $fieldName
 * @param FG_HTML_Element|string $wrapperOrBefore
 * @param string $after
 */
	public function the($fieldName, $wrapperOrBefore = null, $after = null){
		$output = '';
		if(!$this->isEmpty($fieldName)){
			$output = $this->get($fieldName);
			
			if(is_a($wrapperOrBefore, 'FG_HTML_Element')){
				$output = $wrapperOrBefore->setContent($output);
			}elseif(!is_null($wrapperOrBefore)){
				$output	= $wrapperOrBefore . $output;
			}
			
			if(!is_null($after)){
				$output .= $after;
			}
		}
		
		echo $output;
	}
	
/**
 * Creates "magic" method for ->get(), ->the() and ->isEmpty()
 * 
 * @param string $name
 * @param array $args
 * @throws RuntimeException
 */
	public function __call($name, $args){
		if(strpos($name, 'get') === 0 || strpos($name, 'the') === 0){
			$method = substr($name, 0, 3);
			$subname = substr($name, 3);
		}elseif(strpos($name, 'is') === 0 && strpos(strrev($name), strrev('Empty')) === 0){
			$method = 'isEmpty';
			$subname = substr(substr($name, 2), 0, -5);
		}else{
			throw new RuntimeException(sprintf('Method %s not found', $name));
		}
		
		$subname = substr(strtolower(trim(preg_replace('/([A-Z]+)/', '_$1', $subname))), 1);
		array_unshift($args, $subname);
		
		return call_user_func_array(array($this, $method), $args);
	}
}