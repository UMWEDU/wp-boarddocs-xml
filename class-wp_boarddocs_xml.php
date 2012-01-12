<?php
/**
 * WP BoardDocs XML class
 * A class to assist in parsing and displaying an XML file from BoardDocs in WordPress
 */
class wp_boarddocs_xml {
	var $feed             = null;
	var $feed_data        = null;
	var $feed_types       = null;
	var $feed_prefix      = null;
	var $adopted_text     = 'Adopted on ';
	var $not_adopted_text = 'Not yet adopted';
	var $transient_time   = 1800;
	
	/**
	 * Build our object
	 */
	function __construct() {
		add_shortcode( 'boarddocs-feed', array( $this, 'display_feed' ) );
		$this->get_feed_prefix();
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
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
	}
	
	function get_feed_prefix() {
		$this->feed_prefix = esc_url( get_mnetwork_option( 'wp-boarddocs-feed-prefix', false ) );
	}
	
	/**
	 * Set up our settings fields
	 */
	function admin_init() {
		register_setting( 'general', 'wp-boarddocs-feed-prefix', array( &$this, 'save_settings_prefix' ) );
		register_setting( 'general', 'wp-boarddocs-feed-default', array( &$this, 'save_settings_default' ) );
		add_settings_section( 'boarddocs-xml', __( 'BoardDocs XML' ), array( &$this, 'settings_section' ), 'general' );
		add_settings_field( 'wp-boarddocs-feed-prefix', __( 'Feed Prefix:' ), array( &$this, 'settings_field' ), 'general', 'boarddocs-xml', array( 'label_for' => 'wp-boarddocs-feed-prefix' ) );
		add_settings_field( 'wp-boarddocs-feed-default', __( 'Default Feed Type:' ), array( &$this, 'settings_field' ), 'general', 'boarddocs-xml', array( 'label_for' => 'wp-boarddocs-feed-default' ) );
	}
	
	/**
	 * Output any information that should appear before the settings section
	 */
	function settings_section() {
?>
<p><?php _e( 'The options in this section will be updated for <strong>all sites</strong> in this installation.' ) ?></p>
<?php
	}
	
	/**
	 * Output the HTML of the appropriate settings field
	 */
	function settings_field( $args ) {
		if( 'wp-boarddocs-feed-default' == $args['label_for'] )
			return $this->settings_field_default( $args );
		else
			return $this->settings_field_prefix( $args );
	}
	
	/**
	 * Output the HTML for the Prefix field
	 */
	function settings_field_prefix( $args ) {
		if ( empty( $this->feed_prefix ) )
			$this->get_feed_prefix();
?>
	<input class="regular-text" type="url" name="<?php echo $args['label_for'] ?>" id="<?php echo $args['label_for'] ?>" value="<?php echo $this->feed_prefix ?>"/>
    <p class="description"><?php printf( __( 'Please enter the beginning of the URL that leads to the XML feeds. This URL should be similar to %s. You can get this prefix by visiting one of your BoardDocs feeds and removing the part of the URL that appears after the last hyphen.' ), 'http://www.boarddocs.com/[state]/[organization]/Board.nsf/XML-' ) ?></p>
<?php
	}
	
	/**
	 * Output the HTML for the Default-type field
	 */
	function settings_field_default( $args ) {
		if ( empty( $this->feed_default ) )
			$this->feed_default = get_mnetwork_option( 'wp-boarddocs-feed-default', false );
?>
	<select name="wp-boarddocs-feed-default" id="wp-boarddocs-feed-default">
    	<option value=""><?php _e( '-- Please choose one --' ) ?></option>
<?php
		foreach( $this->feed_types as $ft=>$lbl ) {
?>
		<option value="<?php echo $ft ?>"<?php selected( $this->feed_default, $ft ) ?>><?php echo $lbl ?></option>
<?php
		}
?>
    </select>
<?php
	}
	
	/**
	 * Save the Prefix setting
	 */
	function save_settings_prefix( $input ) {
		if ( ! isset( $GLOBALS['updating_mnetwork_option'] ) || !$GLOBALS['updating_mnetwork_option'] ) {
			update_mnetwork_option( 'wp-boarddocs-feed-prefix', $input );
			return false;
		} else {
			$input = esc_url( $input );
			
			if ( empty( $input ) )
				return null;
			
			foreach ( $this->feed_types as $ft=>$ignore ) {
				$type_len = 0 - strlen( $ft );
				if( strtolower( $ft ) == strtolower( substr( $input, $type_len ) ) ) {
					$input = substr( $input, 0, $type_len );
					break;
				}
			}
			if ( '-' != substr( $input, -1 ) )
				$input .= '-';
			
			return $input;
		}
		
		return false;
	}
	
	/**
	 * Save the Default-type setting
	 */
	function save_settings_default( $input ) {
		if ( !isset( $GLOBALS['updating_mnetwork_option'] ) || !$GLOBALS['updating_mnetwork_option'] ) {
			update_mnetwork_option( 'wp-boarddocs-feed-default', $input );
			return false;
		} else {
			if ( empty( $input ) || !array_key_exists( $input, $this->feed_types ) )
				return null;
			
			return $input;
		}
		
		return false;
	}
	
	/**
	 * Determine what type of field is being shown
	 * @deprecated since 0.2
	 */
	protected function _determine_type( $feed ) {
		$feed = strtolower( array_pop( explode( '/', $feed ) ) );
		return array_pop( explode( '-', $feed ) );
	}
	
	/**
	 * Parse and output the XML feed
	 */
	function display_feed( $atts ) {
		if ( empty( $this->feed_prefix ) )
			$this->get_feed_prefix();
		
		if ( ! array_key_exists( 'type', $atts ) || empty( $atts['type'] ) )
			$atts['type'] = 'ActivePolicies';
		if ( ! array_key_exists( 'feed', $atts ) )
			$atts['feed'] = $this->feed_prefix . $atts['type'];
		
		if ( array_key_exists( 'show_what', $atts ) && ! empty( $atts['show_what'] ) ) {
			if ( preg_match( '/\[([a-z]+)\](.+)/i', $atts['show_what'], $show_what ) ) {
				$atts[$show_what[1]] = $show_what[2];
			}
			/*if ( '[book]' == substr( $atts['show_what'], 0, strlen( '[book]' ) ) )
				$atts['book'] = str_replace( '[book]', '', $atts['show_what'] );
			if ( '[section]' == substr( $atts['show_what'], 0, strlen( '[section]' ) ) )
				$atts['section'] = str_replace( '[section]', '', $atts['show_what'] );*/
		}
		
		$this->_retrieve_feed( $atts['feed'] );
		if ( empty( $this->feed_data ) )
			return '';
		if ( ! array_key_exists( 'type', $atts ) || empty( $atts['type'] ) )
			$atts['type'] = $this->_determine_type( $atts['feed'] );
		
		if ( method_exists( $this, 'display_' . strtolower( $atts['type'] ) ) )
			return call_user_method( 'display_' . strtolower( $atts['type'] ), $this, $atts );
		
		return '';
	}
	
	/**
	 * Output the ActivePolicies XML feed
	 */
	function display_activepolicies( $atts=array() ) {
		print( "\n<!--\n" );
		var_dump( $atts );
		print( "\n-->\n" );
		$show_book 		= array_key_exists( 'book', $atts ) ? html_entity_decode( $atts['book'] ) : false;
		$show_section 	= array_key_exists( 'section', $atts ) ? html_entity_decode( $atts['section'] ) : false;
		$link_policy	= array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		$adopted_text 	= array_key_exists( 'adopted_text', $atts ) ? $atts['adopted_text'] : $this->adopted_text;
		$not_adopted_text 	= array_key_exists( 'not_adopted_text', $atts ) ? $atts['not_adopted_text'] : $this->not_adopted_text;
		
		$out = '
		<article class="policy-list">';
		$xml = simplexml_load_string( $this->feed_data );
		foreach ( $xml as $book ) {
			if ( $show_book && $book['name'] != $show_book )
				continue;
				
			if ( false === $show_book && false === $show_section ) {
				/* Only show the book heading if we are showing multiple books */
				$out .= '
			<header class="policy-book-title">
				<h2>
					' . $book['name'] . '
				</h2>
			</header>';
			}
			
			foreach ( $book->section as $section ) {
				if ( $show_section && $section['name'] != $show_section )
					continue;
				$out .= '
			<section class="policy-section">';
				
				if ( false === $show_section ) {
					/* Only show the section heading if we are showing multiple sections */
					$out .= '
				<header class="policy-section-title">
					<h3>
						' . $section['name'] . '
					</h3>
				</header>';
				}
				
				$out .= '
				<ul class="policy-items">';
				foreach ( $section->policy as $policy ) {
					$out .= '
					<li class="policy" id="policy-' . $policy['id'] . '">';
					$out .= $link_policy ? '
						<a href="' . $policy->link[0] . '">' . $policy->title[0] . '</a> ' : 
						$policy->title[0];
					$out .= empty( $policy->adopted[0]->date[0] ) ? '
						<p class="policy-adopted-date">' . $not_adopted_text . '</p>' : 
						'
						<p class="policy-adopted-date">' . $adopted_text . '<time datetime="' . $policy->adopted[0]->date[0] . '">' . $this->ap_date( $policy->adopted[0]->date[0] ) . '</time></p>';
					$out .= '
					</li>';
				}
				$out .= '
				</ul>';
				$out .= '
			</section>';
			}
		}
		$out .= '
		</article>';
		
		return $out;
	}
	
	/**
	 * Output the Board XML feed
	 */
	function display_board( $atts=array() ) {
		$show_member = array_key_exists( 'member', $atts ) ? html_entity_decode( $atts['member'] ) : false;
		$link_member = array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		
		$out = '
		<article class="board-member-list">
			<ul class="members">';
		$xml = simplexml_load_string( $this->feed_data );
		
		foreach ( $xml as $member ) {
			$out .= '
				<li class="board-member" id="member-' . $member['id'] . '">';
			if ( $link_member )
				$out .= '
					<a href="' . $member->link[0] . '">' . $member->name[0] . '</a>';
			else
				$out .= $member->name[0];
			$out .= '
				</li>';
		}
		
		$out .= '
			</ul>
		</article>';
		
		return $out;
	}
	
	/**
	 * Output the Events XML feed
	 */
	function display_events( $atts=array() ) {
		$show_category = array_key_exists( 'category', $atts ) ? html_entity_decode( $atts['category'] ) : false;
		$show_event = array_key_exists( 'event', $atts ) ? html_entity_decode( $atts['event'] ) : false;
		$link_event = array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		
		$out = '
		<article class="board-event-lists">';
		$xml = simplexml_load_string( $this->feed_data );
		
		foreach ( $xml as $cat ) {
			if ( $show_category && $cat['name'] != $show_category )
				continue;
			
			if ( false === $show_category ) {
				/* Only display the category heading if we are displaying multiple categories */
				$out .= '
				<header>
					<h2>
						' . $cat['name'] . '
					</h2>
				</header>';
			}
			
			$out .= '
				<ul class="event-list">';
			
			foreach ( $cat as $event ) {
				if ( $show_event && $event['id'] != $show_event )
					continue;
				
				$out .= '
					<li class="board-event" id="board-event-' . $event['id'] . '">';
				$out .= $link_event ?
					'
						<a href="' . $event->link[0] . '">' . $event->name[0] . '</a>' :
					$event->name[0];
				
				/* Determine whether we have the start and end times for the event */
				$show_time_delimiter = property_exists( $event, 'start' ) && property_exists( $event, 'end' );
				
				/* Display the start time */
				$out .= property_exists( $event, 'start' ) ? '<br/>' . ( $show_time_delimiter ? '<strong>From:</strong> ' : '' ) . '<time class="event-date event-start" datetime="' . $event->start[0]->datetime[0] . '">' . $this->ap_date( $event->start[0]->datetime[0] ) . '</time>' : '';
				/* Display the end time */
				$out .= property_exists( $event, 'end' ) ? '<br/>' . ( $show_time_delimieter ? '<strong>Until:</strong> ' : '' ) . '<time class="event-date event-end" datetime="' . $event->end[0]->datetime[0] . '">' . $this->ap_date( $event->end[0]->datetime[0] ) . '</time>' : '';
				
				$out .= '
					</li>';
			}
			
			$out .= '
				</ul>';
		}
		
		$out .= '
		</article>';
		
		return $out;
	}
	
	/**
	 * Output the General XML feed
	 */
	function display_general( $atts=array() ) {
		$show_category = array_key_exists( 'category', $atts ) ? html_entity_decode( $atts['category'] ) : false;
		$show_item = array_key_exists( 'item', $atts ) ? html_entity_decode( $atts['item'] ) : false;
		$link_item = array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		
		$out = '
		<article class="board-general-list">';
		$xml = simplexml_load_string( $this->feed_data );
		
		foreach ( $xml as $cat ) {
			if ( $show_category && $cat['name'] != $show_category )
				continue;
			
			if ( false === $show_category ) {
				/* Only display the category heading if we are displaying multiple categories */
				$out .= '
				<header>
					<h2>
						' . $cat['name'] . '
					</h2>
				</header>';
			}
			
			
			$out .= '
				<ul class="item-list">';
			
			foreach ( $cat as $item ) {
				if ( $show_item && $item['id'] != $show_item )
					continue;
				
				$out .= '
					<li class="board-item" id="board-item-' . $item['id'] . '">';
				$out .= $link_item ?
					'
						<a href="' . $item->link[0] . '">' . $item->name[0] . '</a>' :
					$item->name[0];
				$out .= '
					</li>';
			}
			
			$out .= '
				</ul>';
		}
		
		$out .= '
		</article>';
		
		return $out;
	}
	
	/**
	 * Output the Goals XML Feed
	 */
	function display_goals( $atts=array() ) {
		$show_category = array_key_exists( 'category', $atts ) ? html_entity_decode( $atts['category'] ) : false;
		$show_goal = array_key_exists( 'goal', $atts ) ? html_entity_decode( $atts['goal'] ) : false;
		$link_goal = array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		
		$out = '
		<article class="board-goal-list">';
		$xml = simplexml_load_string( $this->feed_data );
		
		foreach ( $xml as $cat ) {
			if ( $show_category && $cat['name'] != $show_category )
				continue;
			
			if ( false === $show_category ) {
				/* Only display the category heading if we are displaying multiple categories */
				$out .= '
				<header>
					<h2>
						' . $cat['name'] . '
					</h2>
				</header>';
			}
			
			
			$out .= '
				<ul class="goal-list">';
			
			foreach ( $cat as $goal ) {
				if ( $show_goal && $goal['id'] != $show_goal )
					continue;
				
				$out .= '
					<li class="board-goal" id="board-goal-' . $goal['id'] . '">';
				$out .= $link_goal ?
					'
						<a href="' . $goal->link[0] . '">' . $goal->name[0] . '</a>' :
					$goal->name[0];
				$out .= '
					</li>';
			}
			
			$out .= '
				</ul>';
		}
		
		$out .= '
		</article>';
		
		return $out;
	}
	
	/**
	 * Output the Active Meetings XML Feed
	 */
	function display_activemeetings( $atts=array() ) {
		$show_meeting = array_key_exists( 'meeting', $atts ) ? html_entity_decode( $atts['meeting'] ) : false;
		$link_meeting = array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		
		$out = '
		<article class="board-meeting-list">';
		$xml = simplexml_load_string( $this->feed_data );
		
		foreach ( $xml as $meeting ) {
			if ( $show_meeting && $show_meeting != $meeting['id'] )
				continue;
			
			$out .= '
			<article class="board-meeting" id="board-meeting-' . $meeting['id'] . '">';
			$out .= '
				<header>
					<h2 class="meeting-name">' . ( $link_meeting ? '<a href="' . $meeting->link[0] . '">' . $meeting->name[0] . '</a>' : $meeting->name[0] ) . '
					</h2>';
			$out .= empty( $meeting->start[0]->date[0] ) ? '' : '<p class="meeting-date"><time datetime="' . $meeting->start[0]->date[0] . '">' . $this->ap_date( $meeting->start[0]->date[0] ) . '</time></p>';
			$out .= '
				</header>';
			$out .= '
				<p class="meeting-description">' . nl2br( $meeting->description[0] ) . '</p>';
			$out .= '
				<ol class="meeting-agenda">';
			foreach ( $meeting->category as $agenda ) {
				$out .= '
				<li class="meeting-agenda-category" id="agenda-category' . $agenda['id'] . '">
					<header>
						<h3 class="agenda-title">' . $agenda->name . '</h3>
					</header>
					<ol class="agenda-items">';
				foreach ( $agenda->agendaitems as $item ) {
					$out .= '
						<li class="agenda-item" id="agenda-item-' . $item['id'] . '">
							<a href="' . $item->link[0] . '">' . $item->name[0] . '</a>
						</li>';
				}
				$out .= '
					</ol>
				</li>';
			}
			$out .= '
				</ol>';
			$out .= '
			</article>';
		}
		$out .= '
		</article>';
		
		return $out;
	}
	
	/**
	 * Output the Current Meetings XML Feed
	 */
	function display_currentmeetings( $atts=array() ) {
		return $this->display_activemeetings( $atts );
	}
	
	/**
	 * Output the Policies Under Consideration XML Feed
	 */
	function display_policiesunderconsideration( $atts=array() ) {
		return $this->display_activepolicies( $atts );
	}
	
	/**
	 * Output the Minutes XML Feed
	 */
	function display_minutes( $atts=array() ) {
		$show_meeting = array_key_exists( 'meeting', $atts ) ? html_entity_decode( $atts['meeting'] ) : false;
		$link_meeting = array_key_exists( 'link', $atts ) ? ( 'false' == $atts['link'] ? false : true ) : true;
		
		$out = '
		<article class="board-minutes-list">';
		$xml = simplexml_load_string( $this->feed_data );
		
		foreach ( $xml as $item ) {
			if( $show_meeting && $show_meeting != $item->meeting[0]->name[0] )
				continue;
			
			$out .= '
			<article class="board-meeting">
				<header>
					<h2>' . ( $link_meeting ? '<a href="' . $item->meeting[0]->link[0] . '">' . $item->meeting[0]->name . '</a>' : $item->meeting[0]->name ) . '</h2>
					<p class="meeting-date"><time datetime="' . $item->meeting[0]->date[0] . '">' . $this->ap_date( $item->meeting[0]->date[0] ) . '</time></p>
				</header>';
			
			foreach ( $item->agendaitem as $agendaitem ) {
				$out .= '
					<p class="agenda-item"><span class="item-number">' . $agendaitem->number[0] . '</span> ' . $agendaitem->name[0] . '</p>';
			}
			
			$out .= '
			</article>';
		}
		
		$out .= '
		</article>';
		
		return $out;
	}
	
	/**
	 * Format a date (and, optionally, a time) in AP style
	 * @param string $datestring a date in string format
	 */
	function ap_date( $datestring ) {
		/* Determine whether or not to display the time */
		if ( strstr( $datestring, 'T' ) ) {
			/* Determine whether anything occurs after the "T" in the date string */
			$show_time = array_pop( explode( 'T', $datestring ) );
			$show_time = !empty( $show_time );
		} else {
			$show_time = false;
		}
		
		if ( false === ( $timestamp = strtotime( $datestring ) ) )
			return $datestring;
		
		$date = getdate( $timestamp );
		$month = strlen( $date['month'] ) <= 5 ? $date['month'] : ( $date['month'] == 'September' ? 'Sept.' : substr( $date['month'], 0, 3 ) . '.' );
		
		if ( $date['minutes'] <= 0 )
			$minutes = '';
		else
			$minutes = ':' . substr( '00' . $date['minutes'], -2 );
		
		if ( $date['hours'] >= 12 ) {
			$ap = ' p.m.';
			$hour = $date['hours'] - 12;
		} else {
			if( $date['hours'] == 0 )
				$hour = 12;
			$ap = ' a.m.';
		}
		
		if ( 12 == $hour && empty( $minutes ) ) {
			$hour = $ap == ' a.m.' ? ' midnight' : ' noon';
			$ap = '';
		} else {
			$hour = ' ' . $hour;
		}
		
		return $month . ' ' . $date['mday'] . ', ' . $date['year'] . ( $show_time ? $hour . $minutes . $ap : '' );
	}
	
	protected function _retrieve_feed( $feed ) {
		$feed_key = md5( $feed );
		/*delete_transient( 'wpsxml_' . $feed_key );*/
		if ( false !== ( $this->feed_data = get_transient( 'wpsxml_' . $feed_key ) ) ) {
			print( "\n<!-- Retrieved feed data for {$feed} from transient -->\n" );
			return $this->feed_data;
		}
		
		if ( ! class_exists( 'WP_Http' ) )
			include_once( ABSPATH . WPINC. '/class-http.php' );
		
		$request = new WP_Http;
		$result = $request->request( $feed );
		if ( is_wp_error( $result ) ) {
			print( "\n<!-- There was an error retrieving the feed at {$feed}:\n" . $result->get_error_message() . "\n-->\n" );
			return '';
		}
		
		if ( 200 != $result['response']['code'] )
			return '';
		
		$this->feed_data = $result['body'];
		set_transient( 'wpsxml_' . $feed_key, $this->feed_data, $this->transient_time );
		return $this->feed_data;
	}
}
?>