> This repository hasn't been updated for 4+ year now, but all components still work flawlessly. I just don't have time to mantain and add new functionality anymore. 

Goal
============
Make simple the creation of CRUD elements in wordpress, such as meta boxes, extra fields in terms and option pages.



Loading library
===============
If you put the library in the wp-content dir, you can load it by the following way:

```php
<?php
include_once(WP_CONTENT_DIR . '/WD/lib/load.php');
```

Adding a meta box
=================
You can create a meta box with the function wd_meta_box():

```php
<?php
function register_meta_boxes(){
	wd_meta_box('Extra post options') // meta box name
		->setPage('post') // the post type where the meta box will appear (post, page etc)
		->setContext('side') // side, advanced etc...
		->add(Form::text('my_extra_option')->setLabel('Extra option: ')) // add a field to the meta box
		->add(Form::select('author_box')->setLabel('Author box: ')
				->add('top', 'Show author box in top')
				->add('bottom', 'Show author box in bottom')
		)
		->add(Form::checkbox('is_featured')->setLabel('Post featured? '))
		->init(); // init the necessary hooks
}
add_action('admin_init', 'register_meta_boxes');
```

You can get information of a post by the following way:

```php
<?php
// get
wd_mb()->getAuthorBox($postID); // or wd_mb()->get('author_box', $postID);

// prints
wd_mb()->theAuthorBox($postID); // or wd_mb()->the('author_box', $postID);

// check if is empty
wd_mb()->isMyExtraOptionEmpty($postID); // or wd_mb()->isEmpty('my_extra_option', $postID);

// example:
while(have_posts()): the_post()
	$authorBoxPosition = wd_mb()->getAuthorBox(); // $postID is not necessary in this case
	
	if($authorBoxPosition == 'top')
		call_some_function_that_display_the_author_box();
		
	echo 'Title: '; the_title(); // post title
	
	if($authorBoxPosition == 'bottom')
		call_some_function_that_display_the_author_box();
	
	if(!wd_mb()->isMyExtraOptionEmpty()){
		echo 'My extra option: '; wd_mb()->theMyExtraOption(); // prints
	}
endwhile;
```

Adding extra taxonomy fields
=================
```php
<?php
wd_taxonomy('category') // which taxonomy you wanna affect
	->add(Form::textarea('extra_info')->setLabel('Extra information: ')) // add field
	->add(Form::text('order')->setLabel('Order')) // another
	->init();
```


To get data from a term:

```php
<?php
// get
wd_tax()->getOrder($termID); // or wd_tax()->get('order', $termID);

// prints
wd_tax()->theAuthorBox($termID); // or wd_tax()->the('order', $termID);

// check if is empty
wd_tax()->isExtraInfoEmpty($termID); // or wd_tax()->isEmpty('extra_info', $termID);
```

Creating admin menus
=================
```php
<?php
function register_admin_pages(){
	wd_page('Site options') // Page title
		->add(Form::text('video_limit')->setLabel('Video pages show at most: '))
		->init();
		
	wd_page('Social Network', 'site-options') // this page is child of Site options ("site-options" is the slug of "Site options")
		->add(Form::text('facebook_link')->setLabel('Facebook: '))
		->add(Form::text('twitter_link')->setLabel('Twitter: '))
		->add(Form::text('flickr_link')->setLabel('Flickr: '))
		->add(Form::text('youtube_link')->setLabel('Youtube: '))
		->init();
}
add_action('_admin_menu', 'register_admin_pages'); // the hook _admin_menu happens before the admin_menu hook
```

To get data from a page option:

```php
<?php
// get
wd_opt()->getVideoLimit(); // or wd_opt()->get('video_limit');

// prints
wd_opt()->theVideoLimit(); // or wd_opt()->the('video_limit');

// check if is empty
wd_opt()->isYoutubeLinkEmpty(); // or wd_opt()->isEmpty('youtube_link');
```
