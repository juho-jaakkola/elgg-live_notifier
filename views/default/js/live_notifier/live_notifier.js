// <script>

/**
 * Javascript for the notifier plugin
 */
define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');

	var connection = function() {
		var url_segments = elgg.parse_url(elgg.get_site_url());
		var url = 'ws://' + url_segments.host + ':1234';

		// TODO Check whether the client supports WebSockets
		var conn = new WebSocket(url);

		conn.onopen = function(e) {
			console.log("Connection established!");

			// Tell the server who we are and what we want
			var msg = {
				guid: elgg.get_logged_in_user_guid(),
				token: elgg.live_notifier_token,
				site_guid: elgg.config.site_guid,
			};

			conn.send(JSON.stringify(msg));
		};

		conn.onmessage = function(e) {
			var counter = $('#notifier-new');
			var count = parseInt(counter.html());

			if (isNaN(count)) {
				count = 0;
			}

			var data = JSON.parse(e.data);

			elgg.ajax("mod/live_notifier/views/default/live_notifier/notification.html", {
				success: function(html) {
					// Add the template as part of DOM so it'll be easier to manipulate
					$('.elgg-list-notifier').prepend(html);

					// The notification text
					var text = elgg.echo(data.text, [data.subject_name, data.target_name]);

					// We may be on the notifier/all page, so let's make sure we update
					// contents of both the main list and the popup module
					var lists = $('.elgg-list-notifier');

					// TODO Is it necessary to add the stuff to the list at all?
					// Instead we could let them to fetched on demand. Then again
					// this would cause notifications counter to have an incorrect
					// value in case some notifications are combined.
					lists.each(function(key, list) {
						var note = $(this).children().first();

						// Populate the tempate with the notification data
						note.find('.elgg-image img').attr('src', data.icon_url);
						note.find('.elgg-image a').attr('href', data.subject_url);
						note.find('.elgg-body > .elgg-subtext > a').attr('href', data.target_url).text(text);
					});

					// Draw attention by displaying it also as a system message
					elgg.system_message(text);
				}
			});

			count++;
			counter.html(count).removeAttr('hidden');

			$('#notifier-view-all').removeAttr('hidden');
			$('#notifier-dismiss-all').removeAttr('hidden');
			$('.notifier-none').attr('hidden', '');
		};
	};

	connection();

	var init = function() {

	};

	elgg.register_hook_handler('init', 'system', init);
});
