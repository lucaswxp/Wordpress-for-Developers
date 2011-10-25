jQuery(function($){
	// remove label style from radios and checkboxes
	$('.wd-fieldcreator :checkbox, .wd-fieldcreator :radio, .wd-meta-box :checkbox, .wd-meta-box :radio').each(function(){
		$('label[for=' + $(this).attr('id') + ']').css('display', 'inline');
	});

	// subtitle js
	$('.wd-meta-box :text.wd-subtitle').closest('.postbox').each(function(i, item){
		var $metaBox = $(item);
		var $subtitles = $metaBox.find('.wd-subtitle');
		var totalSubtitles = $subtitles.size();
		var totalFields = $metaBox.find(':text:not(:hidden)').size();

		$subtitles.each(function(i, item){
			var $subtitle = $(item);
			var $label = $('label[for=' + $subtitle.attr('id') + ']');
			var hasLabel = $label[0] != undefined;
			var labelText;

		
			// remove meta box

			if($metaBox.find(':input:not(:hidden)').size() == totalSubtitles){
				$metaBox.hide();
			}

			if(hasLabel){
				labelText = $label.text();
				$label.remove();
			}


			$subtitle.blur(function(){
				if(hasLabel && $subtitle.val() == ''){
					$subtitle.val(labelText);
				}
			}).blur()
			.focus(function(){
				if(hasLabel && $subtitle.val() == labelText){
					$subtitle.val('');
				}
			});

			$('#titlewrap').append($subtitle.attr('title', $metaBox.find('.hndle span').text()));
		});
		
	});
});
