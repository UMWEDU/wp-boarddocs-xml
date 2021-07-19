<?php
/*
Plugin Name: BoardDocs XML Parser for WordPress
Version: 0.4
Plugin URI: http://plugins.ten-321.com/boarddocs-xml-parser-for-wordpress/
Description: Allows you to import and display the contents of a BoardDocs XML feed using shortcodes and widgets in a post or page within WordPress. At this time, this plugin only handles the ActivePolicies feed, but it will probably be extended to handle the other feeds available from BoardDocs.
Author: Curtiss Grymala
Author URI: http://ten-321.com/
License: GPL2
Text Domain: wp-boarddocs-xml
Domain Path: /lang/
*/

namespace {
	spl_autoload_register( function ( $class_name ) {
		if ( ! stristr( $class_name, 'UMW\BoardDocs\\' ) && ! stristr( $class_name, 'UMW\Common\\' ) ) {
			return;
		}

		$class_name = str_replace( 'UMW\BoardDocs\\', 'UMW\BoardDocs\/classes/', $class_name );

		$filename = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'lib/' . strtolower( str_replace( array(
				'\\',
				'_'
			), array( '/', '-' ), $class_name ) ) . '.php';

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Attempting to find class definition at: ' . $filename );
		}

		if ( ! file_exists( $filename ) ) {
			return;
		}

		include $filename;
	} );
}

namespace UMW\BoardDocs {
	Plugin::instance();

	add_action( 'plugins_loaded', 'UMW\BoardDocs\load_plugin_textdomain' );

	function load_plugin_textdomain() {
		\load_plugin_textdomain( 'wp-boarddocs-xml', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}