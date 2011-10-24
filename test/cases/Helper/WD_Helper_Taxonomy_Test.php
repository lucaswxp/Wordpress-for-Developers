<?php
/**
 * test case of WD_Helper_Taxonomy
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test.Helper
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/load.php';

class WD_Helper_Taxonomy_Test extends WPTestCase{

/**
 * test method
 */
	public function testSingleton(){
		$this->assertInstanceOf('WD_Helper_Taxonomy', WD_Helper_Taxonomy::getInstance());
		$this->assertSame(WD_Helper_Taxonomy::getInstance(), WD_Helper_Taxonomy::getInstance());
	}
	
/**
 * test method
 */
	public function testGetMethod(){
		$helper = WD_Helper_Taxonomy::getInstance();
		wd_update_term_meta(1, 'key', 'myvalue');
		$this->assertEquals('myvalue', $helper->get('key', 1));
	}
}