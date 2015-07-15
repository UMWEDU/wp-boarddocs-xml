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

if( !class_exists( 'wp_boarddocs_xml' ) )
	require_once( WP_PLUGIN_DIR . '/wp-boarddocs-xml/class-wp_boarddocs_xml.php' );

function inst_bdxml() {
	global $wp_boarddocs_xml;
	$wp_boarddocs_xml = new wp_boarddocs_xml;
}
add_action( 'muplugins_loaded', 'inst_bdxml' );

if( !class_exists( 'wp_board_docs_widget' ) ) {
	if( file_exists( dirname( __FILE__ ) . '/class-wp-board-docs-widget.php' ) )
		require_once( dirname( __FILE__ ) . '/class-wp-board-docs-widget.php' );
	elseif( file_exists( dirname( __FILE__ ) . '/wp-boarddocs-xml/class-wp-board-docs-widget.php' ) )
		require_once( dirname( __FILE__ ) . '/wp-boarddocs-xml/class-wp-board-docs-widget.php' );
}
add_action( 'widgets_init', create_function( '', 'return register_widget("wp_board_docs_widget");' ) );
?>