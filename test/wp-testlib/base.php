<?php

// abstract most of the unit test framework stuff, so we're not too dependent on one particular test library

#require_once('PHPUnit.php');
require_once('PHPUnit/Autoload.php');
require_once('PHPUnit/Util/ErrorHandler.php');

// test cases should extend WPTestCase instead of PHPUnit_TestCase for three reasons:

// 1. it makes it easier to switch to a different unit test framework if necessary
// 2. you get a bunch of helper methods (see below)
// 3. The wp-test runner only runs tests that inherit from WPTestCase

class WPTestCase extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	var $_time_limit = 120; // max time in seconds for a single test function

	function setUp() {
		// error types taken from PHPUnit_Framework_TestResult::run
		$this->_phpunit_err_mask = E_USER_ERROR | E_NOTICE | E_STRICT;
		$this->_old_handler = set_error_handler(array(&$this, '_error_handler'));
		if (is_null($this->_old_handler)) {
			restore_error_handler();
		}
		_enable_wp_die();

		set_time_limit($this->_time_limit);
	}

	function tearDown() {
		if (!is_null($this->_old_handler)) {
			restore_error_handler();
		}
		_enable_wp_die();
	}
	
	/**
	 * Treat any error, which wasn't handled by PHPUnit as a failure
	 */
	function _error_handler($errno, $errstr, $errfile, $errline) {
		// @ in front of statement
		if (error_reporting() == 0) {
			return;
		}
		// notices and strict warnings are passed on to the phpunit error handler but don't trigger an exception
		if ($errno | $this->_phpunit_err_mask) {
			PHPUnit_Util_ErrorHandler::handleError($errno, $errstr, $errfile, $errline);
		}
		// warnings and errors trigger an exception, which is included in the test results
		else {
			error_log("Testing: $errstr in $errfile on line $errline");
			//TODO: we should raise custom exception here, sth like WP_PHPError
			throw new PHPUnit_Framework_Error(
				$errstr,
				$errno,
				$errfile,
				$errline,
				$trace
			);
		}
	}

	function _current_action() {
		global $wp_current_action;
		if (!empty($wp_current_action))
			return $wp_current_action[count($wp_current_action)-1];
	}

	function _query_filter($q) {
		$now = microtime(true);
		$delta = $now - $this->_q_ts;
		$this->_q_ts = $now;

		$bt = debug_backtrace();
		$caller = '';
		foreach ($bt as $trace) {
			if (strtolower(@$trace['class']) == 'wpdb')
				continue;
			elseif (strtolower(@$trace['function']) == __FUNCTION__)
				continue;
			elseif (strtolower(@$trace['function']) == 'call_user_func_array')
				continue;
			elseif (strtolower(@$trace['function']) == 'apply_filters')
				continue;

			$caller = $trace['function'];
			break;
		}

		#$this->_queries[] = array($caller, $q);
		$delta = sprintf('%0.6f', $delta);
		echo "{$delta} {$caller}: {$q}\n";
		@++$this->_queries[$caller];
		return $q;
	}

	// call these to record and display db queries
	function record_queries() {
		#$this->_queries = array();
		#$this->_q_ts = microtime(true);
		#add_filter('query', array(&$this, '_query_filter'));
		#define('SAVEQUERIES', true);
		global $wpdb;
		$wpdb->queries = array();
	}

	function dump_queries() {
		#remove_filter('query', array(&$this, '_query_filter'));
		#asort($this->_queries);
		#dmp($this->_queries);
		#$this->_queries = array();
		global $wpdb;
		dmp($wpdb->queries);
	}

	function dump_query_summary() {
		$out = array();
		global $wpdb;
		foreach ($wpdb->queries as $q) {
				@$out[$q[2]][0] += 1; // number of queries
				@$out[$q[2]][1] += $q[1]; // query time
		}
		dmp($out);
	}

	// pretend that a given URL has been requested
	function http($url) {
		// note: the WP and WP_Query classes like to silently fetch parameters
		// from all over the place (globals, GET, etc), which makes it tricky
		// to run them more than once without very carefully clearing everything
		$_GET = $_POST = array();
		foreach (array('query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow') as $v)
			unset($GLOBALS[$v]);
		$parts = parse_url($url);
		if (isset($parts['scheme'])) {
			$req = $parts['path'];
			if (isset($parts['query'])) {
				$req .= '?' . $parts['query'];
				// parse the url query vars into $_GET
				parse_str($parts['query'], $_GET);
			}
		}
		else {
			$req = $url;
		}

		$_SERVER['REQUEST_URI'] = $req;
		unset($_SERVER['PATH_INFO']);

		wp_cache_flush();
		unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
		$GLOBALS['wp_the_query'] =& new WP_Query();
		$GLOBALS['wp_query'] =& $GLOBALS['wp_the_query'];
		$GLOBALS['wp'] =& new WP();

		// clean out globals to stop them polluting wp and wp_query
		foreach ($GLOBALS['wp']->public_query_vars as $v) {
			unset($GLOBALS[$v]);
		}
		foreach ($GLOBALS['wp']->private_query_vars as $v) {
			unset($GLOBALS[$v]);
		}

		$GLOBALS['wp']->main($parts['query']);
	}

	// various helper functions for creating and deleting posts, pages etc

	// as it suggests: delete all posts and pages
	function _delete_all_posts() {
		global $wpdb;

		$all_posts = $wpdb->get_col("SELECT ID from {$wpdb->posts}");
		if ($all_posts) {
			foreach ($all_posts as $id)
				wp_delete_post( $id, true );
		}
	}

	// insert a given number of trivial posts, each with predictable title, content and excerpt
	function _insert_quick_posts($num, $type='post', $more = array()) {
		for ($i=0; $i<$num; $i++)
			$this->post_ids[] = wp_insert_post(array_merge(array(
				'post_author' => $this->author->ID,
				'post_status' => 'publish',
				'post_title' => "{$type} title {$i}",
				'post_content' => "{$type} content {$i}",
				'post_excerpt' => "{$type} excerpt {$i}",
				'post_type' => $type
				), $more));
	}

	function _insert_quick_comments($post_id, $num=3) {
		for ($i=0; $i<$num; $i++) {
			wp_insert_comment(array(
				'comment_post_ID' => $post_id,
				'comment_author' => "Commenter $i",
				'comment_author_url' => "http://example.com/$i/",
				'comment_approved' => 1,
				));
		}
	}

	// insert a given number of trivial pages, each with predictable title, content and excerpt
	function _insert_quick_pages($num) {
		$this->_insert_quick_posts($num, 'page');
	}

	/**
	 * Import a WXR file.
	 *
	 * The $users parameter provides information on how users specified in the import
	 * file should be imported. Each key is a user login name and indicates if the user
	 * should be mapped to an existing user, created as a new user with a particular login
	 * or imported with the information held in the WXR file. An example of this:
	 *
	 * <code>
	 * $users = array(
	 *   'alice' => 1, // alice will be mapped to user ID 1
	 *   'bob' => 'john', // bob will be transformed into john
	 *   'eve' => false // eve will be imported as is
	 * );</code>
	 *
	 * @param string $filename Full path of the file to import
	 * @param array $users User import settings
	 * @param bool $fetch_files Whether or not do download remote attachments
	 */
	function _import_wp( $filename, $users = array(), $fetch_files = true ) {
		$importer = new WP_Import();
		$file = realpath( $filename );
		assert('!empty($file)');
		assert('is_file($file)');

		$authors = $mapping = array();
		$i = 0;

		// each user is either mapped to a given ID, mapped to a new user
		// with given login or imported using details in WXR file
		foreach ( $users as $user => $map ) {
			$authors[$i] = $user;
			if ( is_int( $map ) )
				$mapping[$i] = $map;
			else if ( is_string( $map ) )
				$new[$i] = $map;

			$i++;
		}

		$_POST = array( 'imported_authors' => $authors, 'user_map' => $mapping, 'user_new' => $new );

		ob_start();
		$importer->fetch_attachments = $fetch_files;
		$importer->import( $file );
		ob_end_clean();

		$_POST = array();
	}

	// Generate PHP source code containing unit tests for the current blog contents.
	// When run, the tests will check that the content of the blog exactly matches what it is now,
	// with a separate test function for each post.
	function _generate_post_content_test(&$posts, $separate_funcs = true) {
		global $wpdb;
		
		$out = '';
		if (!$separate_funcs)
			$out .= "\n\tfunction test_all_posts() {\n";
		foreach ($posts as $i=>$post) {
			if ($separate_funcs)
				$out .= "\n\tfunction test_post_{$i}() {\n";
			$out .= "\t\t\$post = \$this->posts[{$i}];\n";
			foreach (array_keys(get_object_vars($post)) as $field) {
				if ($field == 'guid') {
					if ($post->post_type == 'attachment')
						$out .= "\t\t".'$this->assertEquals(wp_get_attachment_url($post->ID), $post->guid);'."\n";
					else
						$out .= "\t\t".'$this->assertEquals("'.addcslashes($post->guid, "\$\n\r\t\"\\").'", $post->guid);'."\n";
				}
				elseif ($field == 'post_parent' and !empty($post->post_parent)) {
					$parent_index = 0;
					foreach (array_keys($posts) as $index) {
						if ( $posts[$index]->ID == $post->post_parent ) {
							$parent_index = $index;
							break;
						}
					}
					$out .= "\t\t".'$this->assertEquals($this->posts['.$parent_index.']->ID, $post->post_parent);'."\n";
				}
				elseif ($field == 'post_author')
					$out .= "\t\t".'$this->assertEquals(get_profile(\'ID\', \''.($wpdb->get_var("SELECT user_login FROM {$wpdb->users} WHERE ID={$post->post_author}")).'\'), $post->post_author);'."\n";
				elseif ($field != 'ID')
					$out .= "\t\t".'$this->assertEquals("'.addcslashes($post->$field, "\$\n\r\t\"\\").'", $post->'.$field.');'."\n";
			}
			$cats = wp_get_post_categories($post->ID, array('fields'=>'all'));
			$out .= "\t\t".'$cats = wp_get_post_categories($post->ID, array("fields"=>"all"));'."\n";
			$out .= "\t\t".'$this->assertEquals('.count($cats).', count($cats));'."\n";
			if ($cats) {
				foreach ($cats as $j=>$cat) {
					$out .= "\t\t".'$this->assertEquals(\''.addslashes($cat->name).'\', $cats['.$j.']->name);'."\n";
					$out .= "\t\t".'$this->assertEquals(\''.addslashes($cat->slug).'\', $cats['.$j.']->slug);'."\n";
				}
			}

			$tags = wp_get_post_tags($post->ID);
			$out .= "\t\t".'$tags = wp_get_post_tags($post->ID);'."\n";
			$out .= "\t\t".'$this->assertEquals('.count($tags).', count($tags));'."\n";
			if ($tags) {
				foreach ($tags as $j=>$tag) {
					$out .= "\t\t".'$this->assertEquals(\''.addslashes($tag->name).'\', $tags['.$j.']->name);'."\n";
					$out .= "\t\t".'$this->assertEquals(\''.addslashes($tag->slug).'\', $tags['.$j.']->slug);'."\n";
				}
			}
			
			$meta = $wpdb->get_results("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id = $post->ID");
			#$out .= "\t\t".'$this->assertEquals('.count($postmeta).', count($meta));'."\n";
			foreach ($meta as $m) {
				#$out .= "\t\t".'$meta = get_post_meta($post->ID, \''.addslashes($m->meta_key).'\', true);'."\n";
				$out .= "\t\t".'$this->assertEquals('.var_export(get_post_meta($post->ID, $m->meta_key, false), true).', get_post_meta($post->ID, \''.addslashes($m->meta_key).'\', false));'."\n";
			}
			
						
			$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d ORDER BY comment_date DESC", $post->ID));

			$out .= "\t\t".'$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d ORDER BY comment_date DESC", $post->ID));'."\n";
			$out .= "\t\t".'$this->assertEquals('.count($comments).', count($comments));'."\n";
			foreach ($comments as $k=>$comment) {
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_author).'\', $comments['.$k.']->comment_author);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_author_email).'\', $comments['.$k.']->comment_author_email);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_author_url).'\', $comments['.$k.']->comment_author_url);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_author_IP).'\', $comments['.$k.']->comment_author_IP);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_date).'\', $comments['.$k.']->comment_date);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_date_gmt).'\', $comments['.$k.']->comment_date_gmt);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_karma).'\', $comments['.$k.']->comment_karma);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_approved).'\', $comments['.$k.']->comment_approved);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_agent).'\', $comments['.$k.']->comment_agent);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_type).'\', $comments['.$k.']->comment_type);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_parent).'\', $comments['.$k.']->comment_parent);'."\n";
				$out .= "\t\t".'$this->assertEquals(\''.addslashes($comment->comment_user_id).'\', $comments['.$k.']->comment_user_id);'."\n";
			}


			if ($separate_funcs)
				$out .= "\t}\n\n";
			else
				$out .= "\n\n";
		}
		if (!$separate_funcs)
			$out .= "\t}\n\n";
		return $out;
	}

	function _dump_tables($table /*, table2, .. */) {
		$args = func_get_args();
		$table_list = join(' ', $args);
		system('mysqldump -u '.DB_USER.' --password='.DB_PASSWORD.' -cqnt '.DB_NAME.' '.$table_list);
	}
	
	function _load_sql_dump($file) {
		$lines = file($file);
		
		global $wpdb;
		foreach ($lines as $line) {
			if ( !trim($line) or preg_match('/^-- /', $line) )
				continue;
			$wpdb->query($line);
		}
	}

	// add a user of the specified type
	function _make_user($role = 'administrator', $user_login = '', $pass='', $email='') {
		if (!$user_login)
			$user_login = rand_str();
		if (!$pass)
			$pass = rand_str();
		if (!$email)
			$email = rand_str().'@example.com';

		// we're testing via the add_user()/edit_user() functions, which expect POST data
		$_POST = array(
			'role' => $role,
			'user_login' => $user_login,
			'pass1' => $pass,
			'pass2' => $pass,
			'email' => $email,
		);

		$this->user_ids[] = $id = add_user();

		$_POST = array();
		return $id;
	}

	/**
	 * Checks if track ticket #$ticket_id is resolved 
	 *
	 * @return bool|null true if the ticket is resolved, false if not resolved, null on error
	 */
	function isTracTicketClosed($trac_url, $ticket_id) {
		#TODO: cache it
		$trac_url = rtrim($trac_url, '/');
		$ticket_tsv = file_get_contents("$trac_url/ticket/$ticket_id?format=tab");
		if (false === $ticket_tsv) {
			return null;
		}
		$lines = explode("\n", $ticket_tsv, 2);
		if (!is_array($lines) || count($lines) < 2) {
			return null;
		}
		$titles = explode("\t", $lines[0]);
		$status_idx = array_search('status', $titles);
		if (false === $status_idx) {
			return null;
		}
		$tabs = explode("\t", $lines[1]);
		return 'closed' === $tabs[$status_idx];
	}

	/**
	 * Skips the current test if there is open WordPress ticket with id $ticket_id
	 */
	function knownWPBug($ticket_id) {
		if (!TEST_FORCE_KNOWN_BUGS && (TEST_SKIP_KNOWN_BUGS || !$this->isTracTicketClosed('http://trac.wordpress.org', $ticket_id))) {
			$this->markTestSkipped( sprintf('WordPress Ticket #%d is not fixed', $ticket_id) );
		}
	}

	/**
	 * Skips the current test if there is open WordPress MU ticket with id $ticket_id
	 */
	function knownMUBug($ticket_id) {
		if (!TEST_FORCE_KNOWN_BUGS && (TEST_SKIP_KNOWN_BUGS || !$this->isTracTicketClosed('http://trac.mu.wordpress.org', $ticket_id))) {
			$this->markTestSkipped( sprintf('WordPress MU Ticket #%d is not fixed', $ticket_id) );
		}
	}

	/**
	 * Skips the current test if there is open plugin ticket with id $ticket_id
	 */
	function knownPluginBug($ticket_id) {
		if (!TEST_FORCE_KNOWN_BUGS && (TEST_SKIP_KNOWN_BUGS || !$this->isTracTicketClosed('http://dev.wp-plugins.org', $ticket_id))) {
			$this->markTestSkipped( sprintf('WordPress Plugin Ticket #%d is not fixed', $ticket_id) );
		}
	}
	/**
	 * Skips the current test if the PHP version is not high enough
	 */
	function checkAtLeastPHPVersion($ver) {
		if ( version_compare(PHP_VERSION, $ver, '<') ) {
			$this->markTestSkipped();
		}
	}
	
	
	// convenience function: return the # of posts associated with a tag
	function _tag_count($name) {
		$t = get_term_by('name', $name, 'post_tag');
		if ($t)
			return $t->count;
	}
	
	// convenience function: return the # of posts associated with a category
	function _category_count($name) {
		$t = get_term_by('name', $name, 'category');
		if ($t)
			return $t->count;
	}
	
}

// simple functions for loading and running tests
function wptest_get_all_test_files($dir) {
	$tests = array();
	$dh = opendir($dir);
	while (($file = readdir($dh)) !== false) {
		if ($file{0} == '.')
				continue;
		// skip test loaders from jacob's tests
		if (strtolower($file) == 'alltests.php')
			continue;
		// these tests clash with other things
		if (in_array(strtolower($file), array('testplugin.php', 'testlocale.php')))
			continue;
		$path = realpath($dir . DIRECTORY_SEPARATOR . $file);
		$fileparts = pathinfo($file);
		if (is_file($path) and $fileparts['extension'] == 'php')
			$tests[] = $path;
		elseif (is_dir($path))
			$tests = array_merge($tests, wptest_get_all_test_files($path));
	}
	closedir($dh);

	return $tests;
}

function wptest_is_descendent($parent, $class) {

	$ancestor = strtolower(get_parent_class($class));

	while ($ancestor) {
		if ($ancestor == strtolower($parent)) return true;
		$ancestor = strtolower(get_parent_class($ancestor));
	}

	return false;
}

function wptest_get_all_test_cases() {
	$test_classes = array();
	$all_classes = get_declared_classes();
	// only classes that extend WPTestCase and have names that don't start with _ are included
	foreach ($all_classes as $class)
		if ($class{0} != '_' and wptest_is_descendent('WPTestCase', $class))
			$test_classes[] = $class;
	return $test_classes;
}

/**
 * Simple function to list out all the test cases for command line interfaces
 * 
 * @param $test_classes The test casses array as returned by wptest_get_all_test_cases()
 * @return none
 */
function wptest_listall_testcases($test_classes) {
	echo "\nWordPress Tests available TestCases:\n\n";
	natcasesort( $test_classes );
	echo array_reduce($test_classes, '_wptest_listall_testcases_helper');
	echo "\nUse -t TestCaseName to run individual test cases\n";	
}

function _wptest_listall_testcases_helper( $current, $item ) {
	return $current . "\t{$item}\n";
}

function wptest_run_tests($classes, $classnames = array()) {
	$suite = new PHPUnit_Framework_TestSuite();

	if ( ! is_array($classnames) ) // For strings, Accept a comma separated list, or a space separated list.
		$classnames = preg_split('![,\s]+!', $classnames);

	$classnames = array_map('strtolower', $classnames);
	$classnames = array_filter($classnames); //strip out any empty items

	foreach ( $classes as $testcase ) {
		if ( empty($classnames) || in_array( strtolower($testcase), $classnames ) ) {
			$suite->addTestSuite($testcase);
		}
	}

	#return PHPUnit::run($suite);
	$result = new PHPUnit_Framework_TestResult;
	require_once('PHPUnit/TextUI/ResultPrinter.php');
	$printer = new PHPUnit_TextUI_ResultPrinter(NULL, true, !stristr(PHP_OS, 'WIN') );
	$result->addListener($printer);
	return array($suite->run($result), $printer);
}

function wptest_print_result($printer, $result) {
	$printer->printResult($result, timer_stop());
	/*
	echo $result->toString();
	echo "\n", str_repeat('-', 40), "\n";
	if ($f = intval($result->failureCount()))
		echo "$f failures\n";
	if ($e = intval($result->errorCount()))
		echo "$e errors\n";

	if (!$f and !$e)
		echo "PASS (".$result->runCount()." tests)\n";
	else
		echo "FAIL (".$result->runCount()." tests)\n";
	*/
}

?>
