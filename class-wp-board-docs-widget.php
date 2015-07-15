<?php
/**
 * BoardDocs XML Widget Class
 * @package wp-boarddocs-xml
 * @version 0.3
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
			'ActivePolicies' => __( 'Active Policies' ), 
			'Board' => __( 'Board Members' ), 
			'Events' => __( 'Events' ), 
			'General' => __( 'General' ), 
			'Goals' => __( 'Goals' ), 
			'ActiveMeetings' => __( 'Active Meetings' ), 
			'CurrentMeetings' => __( 'Current Meetings' ), 
			'PoliciesUnderConsideration' => __( 'Policies Under Consideration' ), 
			'Minutes' => __( 'Minutes' ) 
		) );
		
		if ( is_admin() ) {
			wp_register_script( 'boarddocs-widget-ajax', plugins_url( '/scripts/widget-ajax.js', __FILE__ ), array( 'jquery' ), '0.2.30', true );
			wp_enqueue_script( 'boarddocs-widget-ajax' );
			wp_localize_script( 'boarddocs-widget-ajax', 'boarddocs_widget', array( 'ajax_url' => plugins_url( '/scripts/widget-ajax-xml.php', __FILE__ ) ) );
		}
		
		if ( isset( $GLOBALS['wp_boarddocs_xml'] ) ) {
			$this->bdxml_obj = $GLOBALS['wp_boarddocs_xml'];
		} else {
			global $wp_boarddocs_xml;
			$wp_boarddocs_xml = new wp_boarddocs_xml();
			$this->bdxml_obj = $wp_boarddocs_xml;
		}
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		/*if ( empty( $instance['feed'] ) ) {
			print( "\n<!-- For some reason, the instance param was empty, so no output is being generated -->\n" );
			return;
		}*/
		
		echo $before_widget;
		
		$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;
		unset( $instance['title'] );
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
			
		echo $this->bdxml_obj->display_feed( $instance );
		
		echo $after_widget;
	}
	
	function update( $new, $old ) {
		$instance = $old;
		$instance['title'] = empty( $new['title'] ) ? null : esc_attr( $new['title'] );
		/*$instance['feed'] = esc_url( $new['feed'] );*/
		$instance['type'] = array_key_exists( $new['type'], $this->feed_types ) ? $new['type'] : null;
		if ( isset( $new['show_what'] ) )
			$instance['show_what'] = $new['show_what'];
		else
			$instance['show_what'] = null;
		$instance['show_description'] = isset( $new['show_description'] );
		$instance['show_content'] = isset( $new['show_content'] );
		
		return $instance;
	}
	
	function form( $instance ) {
		if ( empty( $this->bdxml_obj->feed_prefix ) )
			$this->bdxml_obj->get_feed_prefix();
?>
<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title' ) ?></label>
	<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" class="widefat"/></p>
<p><label for="<?php echo $this->get_field_id( 'type' ) ?>"><?php _e( 'Feed Type' ) ?></label>
	<select name="<?php echo $this->get_field_name( 'type' ) ?>" id="<?php echo $this->get_field_id( 'type' ) ?>" class="widefat boarddocs-type-selector">
		<option value=""><?php _e( '-- Please select one --' ) ?></option>
<?php
		foreach ( $this->feed_types as $type=>$name ) {
?>
		<option value="<?php echo $type ?>"<?php selected( $type, $instance['type'] ) ?>><?php echo $name ?></option>
<?php
		}
?>
	</select></p>
    <p><label for="<?php echo $this->get_field_id( 'show_what' ) ?>">Show What?</label>
    	<select class="widefat boarddocs_sections" name="<?php echo $this->get_field_name( 'show_what' ) ?>" id="<?php echo $this->get_field_id( 'show_what' ) ?>"><option value="" selected="selected">Show All Sections</option></select>
        <span style="display: none;" class="bdPreviousValue"><?php echo isset( $instance['show_what'] ) ? $instance['show_what'] : '' ?></span></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_description' ) ?>" id="<?php echo $this->get_field_id( 'show_description' ) ?>" value="1"<?php checked( $instance['show_description'] ) ?>/> 
		<label for="<?php echo $this->get_field_id( 'show_description' ) ?>"><?php _e( 'Show Description (if applicable)?' ) ?></label></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_content' ) ?>" id="<?php echo $this->get_field_id( 'show_content' ) ?>" value="1"<?php checked( $instance['show_content'] ) ?>/> 
		<label for="<?php echo $this->get_field_id( 'show_content' ) ?>"><?php _e( 'Show Meeting Agenda (if applicable)?' ) ?></label></p>
<input class="boarddocs_prefix_val" type="hidden" name="<?php echo $this->get_field_name( 'prefix' ) ?>" value="<?php echo $this->bdxml_obj->feed_prefix ?>"/>
<?php
	}
}
?>