<?php
/**
 * test case of WD_Helper_MetaBox
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test.Helper
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/load.php';

class WD_Helper_MetaBox_Test extends WPTestCase{

/**
 * test method
 */
	public function testSingleton(){
		$this->assertInstanceOf('WD_Helper_MetaBox', WD_Helper_MetaBox::getInstance());
		$this->assertSame(WD_Helper_MetaBox::getInstance(), WD_Helper_MetaBox::getInstance());
	}

/**
 * test method
 */
	public function testGetMethod(){
		$helper = WD_Helper_MetaBox::getInstance();
		update_post_meta(1, 'key', 'myvalue');
		$this->assertEquals(get_post_meta(1, 'key', true), $helper->get('key', 1));
	}
}