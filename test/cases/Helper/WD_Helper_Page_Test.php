<?php
/**
 * test case of WD_Helper_Page
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test.Helper
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/load.php';

class WD_Helper_Page_Test extends WPTestCase{

/**
 * test method
 */
	public function testSingleton(){
		$this->assertInstanceOf('WD_Helper_Page', WD_Helper_Page::getInstance());
		$this->assertSame(WD_Helper_Page::getInstance(), WD_Helper_Page::getInstance());
	}

/**
 * test method
 */
	public function testGetMethod(){
		$helper = WD_Helper_Page::getInstance();
		update_option('key', 'myvalue');
		$this->assertEquals(get_option('key'), $helper->get('key'));
	}
}