jQuery(function($){
	// remove label style from radios and checkboxes
	$('.wd-fieldcreator :checkbox, .wd-fieldcreator :radio, .wd-meta-box :checkbox, .wd-meta-box :radio').each(function(){
		$('label[for=' + $(this).attr('id') + ']').css('display', 'inline');
	});
});