<?php
/**
 * BoardDocs XML Widget Class
 */
class wp_board_docs_widget extends WP_Widget {
	var $bdxml_obj = null;
	var $feed_types = array();
	
	function wp_board_docs_widget() {
		self::__construct();
	}
	
	function __construct() {
		parent::WP_Widget( 'boarddocs-xml', __( 'BoardDocs XML Feeds' ), array( 'classname' => 'boarddocs-xml', 'description' => __( 'Displays the output from a BoardDocs XML feed' ) ) );
		$this->feed_types = apply_filters( 'bdxml-feed-types', array( 
			'activepolicies' => __( 'Active Policies' ), 
			'board' => __( 'Board Members' ), 
			'events' => __( 'Events' ), 
			'general' => __( 'General' ), 
			'goals' => __( 'Goals' ), 
			'activemeetings' => __( 'Active Meetings' ), 
			'currentmeetings' => __( 'Current Meetings' ), 
			'policiesunderconsideration' => __( 'Policies Under Consideration' ), 
			'minutes' => __( 'Minutes' ) 
		) );
		
		if( isset( $GLOBALS['wp_boarddocs_xml'] ) ) {
			$this->bdxml_obj = $GLOBALS['wp_boarddocs_xml'];
		} else {
			global $wp_boarddocs_xml;
			$wp_boarddocs_xml = new wp_boarddocs_xml();
			$this->bdxml_obj = $wp_boarddocs_xml;
		}
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		if( empty( $instance['feed'] ) )
			return;
		
		echo $before_widget;
		
		$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;
		unset( $instance['title'] );
		if( !empty( $title ) )
			echo $before_title . $title . $after_title;
			
		echo $this->bdxml_obj->display_feed( $instance );
		
		echo $after_widget;
	}
	
	function update( $new, $old ) {
		$instance = $old;
		$instance['title'] = empty( $new['title'] ) ? null : esc_attr( $new['title'] );
		$instance['feed'] = esc_url( $new['feed'] );
		$instance['type'] = array_key_exists( $new['type'], $this->feed_types ) ? $new['type'] : null;
		
		return $instance;
	}
	
	function form( $instance ) {
?>
<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title' ) ?></label>
	<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" class="widefat"/></p>
<p><label for="<?php echo $this->get_field_id( 'feed' ) ?>"><?php _e( 'Feed URL' ) ?></label>
	<input type="url" name="<?php echo $this->get_field_name( 'feed' ) ?>" id="<?php echo $this->get_field_id( 'feed' ) ?>" value="<?php echo esc_url( $instance['feed'] ) ?>" class="widefat"/></p>
<p><label for="<?php echo $this->get_field_id( 'type' ) ?>"><?php _e( 'Feed Type' ) ?></label>
	<select name="<?php echo $this->get_field_name( 'type' ) ?>" id="<?php echo $this->get_field_id( 'type' ) ?>" class="widefat">
		<option value=""><?php _e( '-- Please select one --' ) ?></option>
<?php
		foreach( $this->feed_types as $type=>$name ) {
?>
		<option value="<?php echo $type ?>"<?php selected( $type, $instance['type'] ) ?>><?php echo $name ?></option>
<?php
		}
?>
	</select></p>
<?php
	}
}
?>