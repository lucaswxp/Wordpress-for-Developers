<?php
/**
 * test case of functions.php files
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test
 */

require_once dirname(dirname(dirname(__FILE__))) . '/lib/load.php';

class FunctionsTest extends WPTestCase{

/**
 * test method
 */
	public function testAddTermMeta(){
		$this->assertTrue(wd_update_term_meta(1, 'metakey', 'metavalue'));
	}
/**
 * test method
 */
	public function testUpdateTermMeta(){
		wd_update_term_meta(1, 'metakey', 'metavalue');
		wd_update_term_meta(1, 'metakey', 'metavalue2');
		$this->assertEquals('metavalue2', wd_get_term_meta(1, 'metakey'));
	}
	
/**
 * test method
 */
	public function testAddGetMeta(){
		$this->assertTrue(wd_update_term_meta(1, 'mykey', array('myvalue')));
		$this->assertEquals(array('myvalue'), wd_get_term_meta(1, 'mykey'));
	}
	
/**
 * test method
 */
	public function testCreateMetaBoxCreatorObject(){
		$this->assertInstanceOf('WD_MetaBox_Creator', wd_meta_box('My title'));
	}
	
/**
 * test method
 */
	public function testCreateTaxonomyFieldCreatorObject(){
		$this->assertInstanceOf('WD_Taxonomy_FieldCreator', wd_taxonomy('taxonomyType'));
	}
}