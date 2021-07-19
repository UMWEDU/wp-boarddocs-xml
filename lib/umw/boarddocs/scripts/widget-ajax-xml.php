<?php
if( !isset( $_GET['xml-url'] ) || empty( $_GET['xml-url'] ) )
	die();

header( "Content-Type:text/xml" );

require_once( '../../../../wp-load.php' );
if( !class_exists( 'WP_Http' ) )
	include_once( ABSPATH . WPINC . '/class-http.php' );

/*if( false !== ( $xml = get_site_transient( 'wp-boarddocs-feed-' . $_GET['feed_type'], false ) ) )
	die( $xml );*/

$feed_url = esc_url( $_GET['xml-url'] );
$xml_doc = new WP_Http;
$xml = $xml_doc->request( $feed_url );

if( 200 != $xml['response']['code'] ) {
	die( '<?xml version="1.0" encoding="utf-8"?><error>Fail<code>' . $xml['response']['code'] . '</code><message>' . $xml['response']['message'] . '</message></error>' );
}

set_site_transient( 'wp-boarddocs-feed-' . $_GET['feed_type'], $xml['body'] );

echo $xml['body'];
?>