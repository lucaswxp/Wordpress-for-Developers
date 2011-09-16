<?php
/**
 * test case of WD_Helper_AbstractHelper
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test.Helper
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/load.php';

class ConcreteHelper extends WD_Helper_AbstractHelper{

	static $instance;
	
	public static function getInstance(){
		return self::$instance ? self::$instance : self::$instance = new self;
	}
	
	public function get($a = 'myvalue', $relatedID = null){
		if($a == 'prefix_name_here') return true;
		elseif($a == 'empty_one') return '';
		return $a;
	}
}

class WD_Helper_AbstractHelper_Test extends WPTestCase{
	
/**
 * test method
 */
	public function testSingleton(){
		$this->assertInstanceOf('ConcreteHelper', ConcreteHelper::getInstance());
		$this->assertSame(ConcreteHelper::getInstance(), ConcreteHelper::getInstance());
	}
	
/**
 * test method
 */
	public function testGetCustom(){
		$helper = ConcreteHelper::getInstance();
		$this->assertTrue($helper->getPrefixNameHere());
	}

/**
 * test method
 */
	public function testPrintsWithTheMethod(){
		$helper = ConcreteHelper::getInstance();
		
		ob_start();
		$helper->the('hey');
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('hey', $content);
	}

/**
 * test method
 */
	public function testPrintsWithTheMethodWithWrapper(){
		$helper = ConcreteHelper::getInstance();
		
		ob_start();
		$helper->the('hey', Html::tag('span'));
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('<span>hey</span>', $content);
	}
	
/**
 * test method
 */
	public function testPrintsWithTheMethodWithBeforeOnly(){
		$helper = ConcreteHelper::getInstance();
		
		ob_start();
		$helper->the('hey', '<span>');
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('<span>hey', $content);
	}
	
/**
 * test method
 */
	public function testPrintsWithTheMethodWithAfterOnly(){
		$helper = ConcreteHelper::getInstance();
		
		ob_start();
		$helper->the('hey', null, '</span>');
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('hey</span>', $content);
	}
	
/**
 * test method
 */
	public function testPrintsWithTheMethodWithBeforeAndAfter(){
		$helper = ConcreteHelper::getInstance();
		
		ob_start();
		$helper->the('hey', '<span>', '</span>');
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('<span>hey</span>', $content);
	}
	
/**
 * test method
 */
	public function testPrintsWithMagicMethod(){
		$helper = ConcreteHelper::getInstance();
		
		ob_start();
		$helper->theHey('<span>', '</span>');
		$content = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('<span>hey</span>', $content);
	}

/**
 * test method
 */
	public function testIsEmpty(){
		$helper = ConcreteHelper::getInstance();
		
		$this->assertTrue($helper->isEmpty(''));
		$this->assertTrue($helper->isEmpty('  '));
		$this->assertFalse($helper->isEmpty('  a'));
	}
/**
 * test method
 */
	public function testIsEmptyWithMagicMethod(){
		$helper = ConcreteHelper::getInstance();
		
		$this->assertFalse($helper->isNotEmptyOneEmpty());
		$this->assertTrue($helper->isEmptyOneEmpty());
	}
}