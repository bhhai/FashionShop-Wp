/**
 * Admin code for dismissing notifications.
 *
 */
(function( $ ) {
    'use strict';
    $( function() {
        const plugin_slug = 'call-now-button';
        $( '.notice-' + plugin_slug ).on( 'click', '.notice-dismiss', function( event, el ) {
            const $notice = $(this).parent('.notice.is-dismissible');
            const dismiss_url = $notice.attr('data-dismiss-url');
            if ( dismiss_url ) {
                $.get( dismiss_url );
            }
        });
    } );
})( jQuery );
