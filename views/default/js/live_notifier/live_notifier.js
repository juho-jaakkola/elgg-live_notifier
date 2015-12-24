// <script>

/**
 * Javascript for the notifier plugin
 */
define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	var pusher = require('pusher');

	pusher.registerConsumer('live_notifier', function(data) {
		var counter = $('#notifier-new');
		var count = parseInt(counter.html());

		if (isNaN(count)) {
			count = 0;
		}

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
		counter.html(count).removeClass('hidden');

		$('#notifier-view-all').removeClass('hidden');
		$('#notifier-dismiss-all').removeClass('hidden');
		$('.notifier-none').addClass('hidden');
	});
});
