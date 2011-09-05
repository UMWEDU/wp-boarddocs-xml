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
	$('.boarddocs-type-selector').live( 'change click', function( e ) { 
		console.log( e );
		return_boarddocs_sections( $(e.target).closest( '.widget-content' ) ); 
	} );
	$('.boarddocs-type-selector').each( function() { return_boarddocs_sections( $(this).closest( '.widget-content' ) ); } );
	
	function return_boarddocs_sections( el ) {
		var prefix = $(el).find( '.boarddocs_prefix_val' ).attr('value');
		var feed_type = $(el).find( '.boarddocs-type-selector option:selected' ).attr('value');
		
		if( typeof( prefix ) === 'undefined' || prefix == '' || typeof( feed_type ) === 'undefined' || feed_type == '' ) {
			$(el).find('select.boarddocs_sections').remove();
			return null;
		}
		
		jQuery.get( boarddocs_widget.ajax_url, {'xml-url':prefix + feed_type,'feed_type':feed_type}, function(data) {
			var inputname = boarddocs_widget_parse_input_name( $(el).find('.boarddocs-type-selector').attr('name') );
			if( $('select.boarddocs_sections').length <= 0 ) {
				$(el).append( '<select name="' + inputname + '" class="widefat boarddocs_sections"></select>' );
			}
			var opts = boarddocs_build_section_options( data, inputname );
			console.log( opts );
			$(el).find('select.boarddocs_sections').html( opts );
		} );
		
		function boarddocs_build_section_options( data, inputname ) {
			$xml = $(data);
			var books = $xml.find( 'book' );
			var sects = null;
			var opts = ['<option value="">-- Please choose a section to show --</option>'];
			books.each( function() { 
				console.log( $(this) );
				opts.push( '<option value="[book]' + $(this).attr('name') + '">' + $(this).attr('name') + '</option>' );
				sects = $(this).find( 'section' );
				for( var i=0; i<sects.length; i++ ) {
					opts.push( '<option value="[section]' + $(sects[i]).attr('name') + '">' + '- ' + $(sects[i]).attr('name') + '</option>' );
				}
			} );
			
			return opts.join( '' );
		}
		
		function boarddocs_widget_parse_input_name( inName ) {
			return inName.replace( /([a-z0-9\-\_]+)\[([0-9]+)\]\[([a-z0-9\-\_]+)\]/i, '$1[$2][show_what]' );
		}
	}
} );