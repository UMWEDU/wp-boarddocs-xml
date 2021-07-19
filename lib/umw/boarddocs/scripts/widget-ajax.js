/**
 * Retrieves the appropriate items to display in the feed
 * ActivePolicies, PoliciesUnderConsideration - <section name=""> -> <book name="">
 * Board - <member id=""><name>
 * Events - <category name=""> -> <event id=""><name>
 * General - <category name=""> -> <item id=""><name>
 * Goals - <category name=""> -> <goal id=""><name>
 * ActiveMeetings, CurrentMeetings - <meeting id=""><name>
 * Minutes - <meeting><name>
 */
jQuery( function( $ ) {
	$( document ).ajaxSuccess(function(e, xhr, settings) {
		var widget_id_base = 'boarddocs-xml';
	
		if(settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) {
			$('.boarddocs-type-selector').each( function() { 
				return_boarddocs_sections( $(this).closest( '.widget-content' ) ); 
			} );
		}
	});
	$('.boarddocs-type-selector').live( 'change click', function( e ) { 
		/*console.log( e );*/
		return_boarddocs_sections( $(e.target).closest( '.widget-content' ) ); 
	} );
	$('.boarddocs-type-selector').each( function() { return_boarddocs_sections( $(this).closest( '.widget-content' ) ); } );
	
	var get_boarddocs_options = {
		'xml' : null,
		'opts' : ['<option value="0">-- Please choose a section to show --</option>'],
		'policies' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose a section to show --</option>'];
			var books = get_boarddocs_options.xml.find( 'book' );
			var sects = null;
			books.each( function() { 
				/*console.log( $(this) );*/
				get_boarddocs_options.opts.push( '<option value="[book]' + $(this).attr('name') + '">' + $(this).attr('name') + '</option>' );
				sects = $(this).find( 'section' );
				for( var i=0; i<sects.length; i++ ) {
					get_boarddocs_options.opts.push( '<option value="[section]' + $(sects[i]).attr('name') + '">' + '- ' + $(sects[i]).attr('name') + '</option>' );
				}
			} );
		},
		'board' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose a member to show --</option>'];
			var members = get_boarddocs_options.xml.find( 'member' );
			members.each( function() {
				get_boarddocs_options.opts.push( '<option value="[member]' + $(this).find('name').text() + '">' + $(this).find('name').text() + '</option>' );
			} );
		},
		'events' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose an event to show --</option>'];
			var cats = get_boarddocs_options.xml.find( 'category' );
			var events = null;
			cats.each( function() {
				get_boarddocs_options.opts.push( '<option value="[category]' + $(this).attr('name') + '">' + $(this).attr('name') + '</option>' );
				events = $(this).find('event');
				for( var i=0; i<events.length; i++ ) {
					get_boarddocs_options.opts.push( '<option value="[event]' + $(events[i]).attr('id') + '">- ' + $(events[i]).find('name').text() + '</option>' );
				}
			} );
		},
		'general' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose an item to show --</option>'];
			var cats = get_boarddocs_options.xml.find( 'category' );
			cats.each( function() {
				get_boarddocs_options.opts.push( '<option value="[category]' + $(this).attr('name') + '">' + $(this).attr('name') + '</option>' );
				items = $(this).find('item');
				for( var i=0; i<items.length; i++ ) {
					get_boarddocs_options.opts.push( '<option value="[item]' + $(items[i]).attr('id') + '">- ' + $(items[i]).find('name').text() + '</option>' );
				}
			} );
		},
		'goals' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose a goal to show --</option>'];
			var cats = get_boarddocs_options.xml.find( 'category' );
			cats.each( function() {
				get_boarddocs_options.opts.push( '<option value="[category]' + $(this).attr('name') + '">' + $(this).attr('name') + '</option>' );
				items = $(this).find('goal');
				for( var i=0; i<items.length; i++ ) {
					get_boarddocs_options.opts.push( '<option value="[goal]' + $(items[i]).attr('id') + '">- ' + $(items[i]).find('name').text() + '</option>' );
				}
			} );
		},
		'meetings' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose a meeting to show --</option>'];
			var meetings = get_boarddocs_options.xml.find( 'meeting' );
			meetings.each( function() {
				get_boarddocs_options.opts.push( '<option value="[meeting]' + $(this).attr('id') + '">' + $(this).children('name').text() + '</option>' );
			} );
		},
		'minutes' : function() {
			get_boarddocs_options.opts = ['<option value="0">-- Please choose a meeting to show --</option>'];
			var meetings = get_boarddocs_options.xml.find( 'meeting' );
			meetings.each( function() {
				get_boarddocs_options.opts.push( '<option value="[meeting]' + $(this).children('name').text() + '">' + $(this).children('name').text() + '</option>' );
			} );
		}
	};
	
	function return_boarddocs_sections( el ) {
		var prefix = $(el).find( '.boarddocs_prefix_val' ).attr('value');
		var feed_type = $(el).find( '.boarddocs-type-selector option:selected' ).attr('value');
		
		if( typeof( prefix ) === 'undefined' || prefix == '' || typeof( feed_type ) === 'undefined' || feed_type == '' ) {
			$( el ).find( 'select.boarddocs_sections option[value!=""]' ).remove( '' );
			$( el ).find( 'select.boarddocs_sections' ).val( '' );
			return null;
		}
		
		jQuery.get( boarddocs_widget.ajax_url, {'xml-url':prefix + feed_type,'feed_type':feed_type}, function(data) {
			if( $( el ).find( 'select.boarddocs_sections' ).length <= 0 ) {
				var inputname = boarddocs_widget_parse_input_name( $(el).find('.boarddocs-type-selector').attr('name') );
				var inputid = boarddocs_widget_parse_input_id( $(el).find('.boarddocs-type-selector').attr('id') );
				$(el).append( '<select name="' + inputname + '" id="' + inputid + '" class="widefat boarddocs_sections"></select>' );
			} else {
				var inputname = $( el ).find( 'select.boarddocs_sections' ).attr( 'name' );
				var inputid = $( el ).find( 'select.boarddocs_sections' ).attr( 'id' );
			}
			var bdSelected = $( el ).find( 'span.bdPreviousValue' ).html();
			var opts = boarddocs_build_section_options( data, inputname );
			$( el ).find( 'select.boarddocs_sections option[value!=""]' ).remove();
			$( el ).find( 'select.boarddocs_sections' ).html( $( el ).find( 'select.boarddocs_sections' ).html() + opts );
			if ( $( el ).find( 'select.boarddocs_sections' ).val() == '' || $( el ).find( 'select.boarddocs_sections' ).val() == 0 ) {
				var bdCurrOpt = $( el ).find( 'select.boarddocs_sections option[value="' + bdSelected + '"]' );
				bdCurrOpt.attr( 'selected', 'selected' );
			}
		} );
		
		function boarddocs_build_section_options( data, inputname ) {
			get_boarddocs_options.xml = $(data);
			if( 'ActivePolicies' == feed_type || 'PoliciesUnderConsideration' == feed_type ) {
				get_boarddocs_options.policies( data );
			} else if( 'Board' == feed_type ) {
				get_boarddocs_options.board( data );
			} else if( 'Events' == feed_type ) {
				get_boarddocs_options.events( data );
			} else if( 'General' == feed_type ) {
				get_boarddocs_options.general( data );
			} else if( 'Goals' == feed_type ) {
				get_boarddocs_options.goals( data );
			} else if( 'ActiveMeetings' == feed_type || 'CurrentMeetings' == feed_type ) {
				get_boarddocs_options.meetings( data );
			} else if( 'Minutes' == feed_type ) {
				get_boarddocs_options.minutes( data );
			}
			
			var bdOpts = get_boarddocs_options.opts.join( '' );
			return bdOpts;
		}
		
		function boarddocs_widget_parse_input_name( inName ) {
			return inName.replace( /([a-z0-9\-\_]+)\[([0-9]+)\]\[([a-z0-9\-\_]+)\]/i, '$1[$2][show_what]' );
		}
		function boarddocs_widget_parse_input_id( inName ) {
			return inName.replace( /([a-z0-9\-\_]+)-([0-9]+)-([a-z0-9\-\_]+)/i, '$1-$2-show_what' );
		}
	}
} );