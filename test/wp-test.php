<?php
/**
 * wp-test.php
 * 
 * WordPress Testrunner
 * 
 * Example:
 * 
 * # php wp-test.php -l
 * 
 */

// parse options
$options = 'v:t:r:sflndq';
if (is_callable('getopt')) {
	$opts = getopt($options);
} else {	
	include( dirname(__FILE__) . '/wp-testlib/getopt.php' );
	$opts = getoptParser::getopt($options);
}

define('DIR_TESTROOT', realpath(dirname(__FILE__)));
if (!defined('DIR_TESTCASE')) {
	define('DIR_TESTCASE', './cases');
}

define('TEST_WP', true);
define('TEST_MU', (@$opts['v'] == 'mu'));
define('TEST_SKIP_KNOWN_BUGS', array_key_exists('s', $opts));
define('TEST_FORCE_KNOWN_BUGS', array_key_exists('f', $opts));
define('WP_DEBUG', array_key_exists('d', $opts) );
define('SAVEQUERIES', array_key_exists('q', $opts) );

if (!empty($opts['r']))
	define('DIR_WP', realpath($opts['r']));
else
	define('DIR_WP', dirname(dirname(__FILE__)));

// make sure all useful errors are displayed during setup
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', true);

require_once(DIR_TESTROOT.'/wp-testlib/base.php');
require_once(DIR_TESTROOT.'/wp-testlib/utils.php');

// configure wp

require_once(DIR_TESTROOT.'/wp-config.php');
define('ABSPATH', realpath(DIR_WP).'/');

if (!defined('DIR_TESTPLUGINS'))
	define('DIR_TESTPLUGINS', './wp-plugins');


// install wp
define('WP_BLOG_TITLE', rand_str());
define('WP_USER_NAME', rand_str());
define('WP_USER_EMAIL', rand_str().'@example.com');


// initialize wp
define('WP_INSTALLING', 1);
$_SERVER['PATH_INFO'] = $_SERVER['SCRIPT_NAME']; // prevent a warning from some sloppy code in wp-settings.php
require_once(ABSPATH.'wp-settings.php');

// override stuff
require_once(DIR_TESTROOT.'/wp-testlib/mock-mailer.php');
$GLOBALS['phpmailer'] = new MockPHPMailer();

// Allow tests to override wp_die
add_filter( 'wp_die_handler', '_wp_die_handler_filter' );

drop_tables();

if (TEST_MU)
	require_once(ABSPATH.'wp-admin/upgrade-functions.php');
else
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
wp_install(WP_BLOG_TITLE, WP_USER_NAME, WP_USER_EMAIL, true);

if (TEST_MU) {
		// wp-settings.php would normally init this stuff, but that doesn't work because we've
		// only just installed
		$GLOBALS['blog_id'] = 1;
		$GLOBALS['wpdb']->blogid = 1;
		$GLOBALS['current_blog'] = $GLOBALS['wpdb']->get_results('SELECT * from wp_blogs where blog_id=1');
}

// make sure we're installed
assert(true == is_blog_installed());

// include plugins for testing, if any
if (is_dir(DIR_TESTPLUGINS)) {
	$plugins = glob(realpath(DIR_TESTPLUGINS).'/*.php');
	foreach ($plugins as $plugin)
		include_once($plugin);
}

// needed for jacob's tests
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . ABSPATH . '/wp-includes');
define('PHPUnit_MAIN_METHOD', false);
$original_wpdb = $GLOBALS['wpdb'];

// include all files in DIR_TESTCASE, and fetch all the WPTestCase descendents
$files = wptest_get_all_test_files(DIR_TESTCASE);
foreach ($files as $file) {
	require_once($file);
}
$classes = wptest_get_all_test_cases();

// some of jacob's tests clobber the wpdb object, so restore it
$GLOBALS['wpdb'] = $original_wpdb;

if ( isset($opts['l']) ) {
	wptest_listall_testcases($classes);
} else {
	do_action('test_start');
	
	// hide warnings during testing, since that's the normal WP behaviour
	if ( !WP_DEBUG ) {
		error_reporting(E_ALL ^ E_NOTICE);
	}
	// run the tests and print the results
	list ($result, $printer) = wptest_run_tests($classes, isset($opts['t']) ? $opts['t'] : array());
	wptest_print_result($printer,$result);
}
if ( !isset($opts['n']) ) {
	// clean up the database
	drop_tables();
}
?>
