<?php
/**
 * test case of WD_Creator_TaxonomyFieldsCreator
 * 
 * @author Lucas Pelegrino <lucas.wxp@gmail.com
 * @package test.Taxonomy
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/load.php';

class WD_Creator_TaxonomyFieldsCreator_Test extends WPTestCase{

/**
 * test method
 */
	public function testAddField(){
		global $post;
		query_posts('post_type=post');
		the_post();
		
		$creator = new WD_Creator_TaxonomyFieldsCreator('category');
		
		$this->assertEquals(
			$this->output('<div class="form-field wd-taxonomy-fieldcreator">
								<input type="text" name="iname" />
							</div>'), $creator->add(Form::text('iname'))->init()->render());
			
		$this->assertEquals(
			$this->output('<tr class="form-field wd-taxonomy-fieldcreator">
								<th></th>
								<td><input type="text" name="iname" value="" /></td>
							</tr>
							<tr class="form-field wd-taxonomy-fieldcreator">
								<th><label for="iname2">yo</label></th>
								<td><input type="text" name="iname2" id="iname2" value="" /></td>
							</tr>'), $creator->add(Form::text('iname2')->setLabel('yo'))->renderForEdit());
	}
	
/**
 * test method
 */
	public function testSaveField(){
		global $tag;
		
		$creator = new WD_Creator_TaxonomyFieldsCreator('category');
		
		$_POST['iname'] = 'mvalue';
		$creator->add(Form::text('iname'))->init();
		do_action('edited_category', $tag->term_id = 1);
		
		$this->assertEquals($this->output('<tr class="form-field wd-taxonomy-fieldcreator">
								<th></th>
								<td><input type="text" name="iname" value="mvalue" /></td>
							</tr>'), $creator->renderForEdit());
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