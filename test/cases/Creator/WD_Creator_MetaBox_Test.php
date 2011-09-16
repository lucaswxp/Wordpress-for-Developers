<?php
/**
 * test case of WD_Creator_MetaBox
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test.Creator
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/load.php';

class WD_Creator_MetaBox_Test extends WPTestCase{

/**
 * test method
 */
	public function testAddField(){
		global $post;
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		
		$this->assertEquals('<div class="wd-meta-box"><input type="text" name="iname" value="" /></div>', $creator->add(Form::text('iname'))->init()->render());
	}

/**
 * test method
 */
	public function testFilterGetFieldValue(){
		global $post;
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		
		update_post_meta($post->ID, 'filtername', 'myvalue');
		
		add_filter('wd-meta-box-get-field-filtername', create_function('$value', 'return $value . "yo";'));
		
		$this->assertEquals('<div class="wd-meta-box"><input type="text" name="filtername" value="myvalueyo" /></div>', $creator->add(Form::text('filtername'))->init()->render());
	}
	
/**
 * test method
 */
	public function testPopulateField(){
		global $post;
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		
		update_post_meta($post->ID, 'iname', 'myvalue');
		$this->assertEquals('<div class="wd-meta-box"><input type="text" name="iname" value="myvalue" /></div>', $creator->add(Form::text('iname'))->init()->render());
	}
	
/**
 * test method
 */
	public function testPopulateFieldWithPrefix(){
		global $post;
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		
		update_post_meta($post->ID, 'wd_iname', 'myvalue');
		$this->assertEquals('<div class="wd-meta-box"><input type="text" name="iname" value="myvalue" /></div>', $creator->setPrefix('wd_')->add(Form::text('iname'))->init()->render());
	}
	
/**
 * test method
 */
	public function testComplexFields(){
		global $post;
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		
		update_post_meta($post->ID, 'iname', 'option2');
		update_post_meta($post->ID, 'checks', array('check1', 'check3'));
		
		$this->assertEquals(
			$this->output(
				'<div class="wd-meta-box">
				<input type="hidden" value="" name="iname" />
				<div>
					<input type="radio" value="option1" id="iname1" name="iname" />
				</div>
				<div>
					<input type="radio" value="option2" id="iname2" name="iname" checked="checked" />
				</div>
				<div>
					<input type="checkbox" value="check1" id="checks_1" name="checks[]" checked="checked" />
				</div>
				<div>
					<input type="checkbox" value="check2" id="checks_2" name="checks[]" />
				</div>
				<div>
					<input type="checkbox" value="check3" id="checks_3" name="checks[]" checked="checked" />
				</div>
				</div>
				'
			),
		
			$creator
				->add(Form::radios('iname')
							->add('option1', false)
							->add('option2', false))
				->add(Form::checkboxes('checks[]')
							->add('check1', false)
							->add('check2', false)
							->add('check3', false))
				->init()->render());
	}
	
/**
 * test method
 */
	public function testSaveField(){
		global $post;
		
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		$_POST['iname'] = 'mvalue';
		$creator->add(Form::text('iname'))->init();
		do_action('save_post', $post->ID, $post);
		
		$this->assertEquals('<div class="wd-meta-box"><input type="text" name="iname" value="mvalue" /></div>', $creator->render());
	}
	
/**
 * test method
 */
	public function testFilterFieldValuesWhenSavingField(){
		global $post;
		
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		add_filter('wd-meta-box-save-iname', create_function('$value', 'return $value . "hey";'));
		
		$_POST['iname'] = 'mvalue';
		$creator->add(Form::text('iname'))->init();
		do_action('save_post', $post->ID, $post);
		
		$this->assertEquals('<div class="wd-meta-box"><input type="text" name="iname" value="mvaluehey" /></div>', $creator->render());
	}
	
/**
 * test method
 */
	public function testCustomSaveCallback(){
		global $post;
		
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_MetaBox('My meta box title');
		$_POST['iname'] = 'mvalue';
		$creator->add(Form::text('iname'))->setSaveCallback(array($this, '_saveCallback'))->init();
		//
		ob_start();
		do_action('save_post', $post->ID, $post);
		$content = ob_get_contents();
		ob_end_clean();
		//
		$this->assertEquals('mvalue', $content);
	}
	
	public function _saveCallback($postid){
		echo $_POST['iname'];
	}

/**
 * Gets output with no tabs and newlines
 * 
 * @param string $output
 */
	public function output($output){
		return str_replace(array("\t", "\n"), '', $output);
	}
}