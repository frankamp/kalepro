<?php
/**
 * PHPUnit bootstrap file
 *
 * @package recipe-pro
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

function var_log() {
    ob_start();
    call_user_func_array( 'var_dump', func_get_args() );
    error_log( ob_get_clean() );
}

function import_data( $datafile ) {
	$file = dirname( dirname( __FILE__ ) ) . "/tests/" . $datafile;

	//error_log( "attempting to import $file" );
	if ( class_exists('WP_Import') && defined('WP_LOAD_IMPORTERS')  ) {
		$WP_Import = new WP_Import();
		// Not sure why these wouldn't be loaded
		if ( ! function_exists ( 'wp_insert_category' ) )
			include ( ABSPATH . 'wp-admin/includes/taxonomy.php' );
		if ( ! function_exists ( 'post_exists' ) )
			include ( ABSPATH . 'wp-admin/includes/post.php' );
		if ( ! function_exists ( 'comment_exists' ) )
			include ( ABSPATH . 'wp-admin/includes/comment.php' );
		ob_start();
		// if ( defined('WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS') && WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS == true ) {
		// 	$WP_Import->fetch_attachments = true;
		// 	$WP_Import->allow_fetch_attachments();
		// }
		$WP_Import->import( $file );
		ob_end_clean();
	}
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
// require_once( dirname( dirname( __FILE__ ) ) . 'wp-admin/includes/plugin.php' )
// activate_plugin( dirname( dirname( __FILE__ ) ) . '/recipe-pro.php' );
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/recipe-pro.php';
	define('WP_LOAD_IMPORTERS', true);
	require dirname( dirname( __FILE__ ) ) . '/../wordpress-importer/wordpress-importer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
