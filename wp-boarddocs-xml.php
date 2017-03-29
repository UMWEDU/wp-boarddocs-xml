<?php
/*
Plugin Name: BoardDocs XML Parser for WordPress
Version: 0.3
Plugin URI: http://plugins.ten-321.com/boarddocs-xml-parser-for-wordpress/
Description: Allows you to import and display the contents of a BoardDocs XML feed using shortcodes and widgets in a post or page within WordPress. At this time, this plugin only handles the ActivePolicies feed, but it will probably be extended to handle the other feeds available from BoardDocs.
Author: Curtiss Grymala
Author URI: http://ten-321.com/
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You do not have permission to access this file directly.' );
}

if ( ! class_exists( 'WP_BoardDocs_XML' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/classes/class-wp-boarddocs-xml.php' );
	global $wp_boarddocs_xml_obj;
	$wp_boarddocs_xml_obj = WP_BoardDocs_XML::instance();
}
