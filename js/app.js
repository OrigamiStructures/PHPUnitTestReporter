$(document).ready(function(){
	initToggles();
})

/**
 * Set up the click on a node to control the display-toggle of another node
 * 
 * Any <item class=toggle id=unique_name> will toggle <item class=unique_name> on click
 */
function initToggles() {
    $('.toggle').unbind('click').bind('click', function(e) {
		var id = e.currentTarget.id;
        $('.' + $(this).attr('id')).toggle(50, function() {
            // animation complete.
			if (typeof(statusMemory) == 'function') {
				statusMemory(id, e);
			}
        });
    })
}
