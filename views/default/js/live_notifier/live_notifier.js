// <script>

/**
 * Javascript for the notifier plugin
 */
define(function(require) {
	var $ = require('jquery');
	var autobahn = require('autobahn');
	//var ab = require('autobahn');
	var elgg = require('elgg');

	/*
	console.log(ab);

	// WAMP server
	var wsuri = "ws://localhost:1234";

	var init = function() {
		ab.connect(wsuri,
			// WAMP session was established
			function (session) {
				// subscribe to topic
				session.subscribe("http://example.com/event#myevent1",
					// on event publication callback
					function (topic, event) {
						console.log("got event1: " + event);
					}
				);
				// publish event on a topic
				session.publish("http://example.com/event#myevent1", {a: 23, b: "foobar"});
			},

			// WAMP session is gone
			function (code, reason) {
				console.log(reason);
			}
		);
	};
	*/

/*
	var url_segments = elgg.parse_url(elgg.get_site_url());
	var url = 'ws://' + url_segments.host + ':8080';

	//console.log(url_segments);
	//console.log(url);

	//AUTOBAHN_DEBUG = true;
	//autobahn.debug(true, true);

	ab.Session('ws://localhost:1234',
		function() {
		  	console.log('Connection established.');
		},
		function() {
		  	console.log('Connection closed.');
		},
		{'skipSubprotocolCheck': true}
	);
*/

/*
	console.log(autobahn.version);



	 //autobahn.connection.close();
*/

	// AUTOBAHN implementations
	var conn = new autobahn.Session('ws://localhost:1234',
		  function(test) {
		  	console.log(test);
		  	console.log('Connection established.');
				conn.subscribe('testCategory', function(topic, data) {
					 // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
					 console.log('New article published to category "' + topic + '" : ' + data.title);
				});
		  },
		  function(reason, details) {
				console.warn('WebSocket connection closed: ' + reason);
		  },
		  {'skipSubprotocolCheck': true}
	 );

	console.log($(conn));


	// WEBSOCKETS implementation
	var chat_connection = function() {
		var url = 'ws://localhost:8080';

		// TODO Check whether the client supports WebSockets
		var conn = new WebSocket(url);
		conn.onopen = function(e) {
			 console.log("Connection established!");

			message = JSON.stringify([
				{
				 	'username': 'test.user',
				 	'text': 'Hello world!',
				 	'image_url': elgg.get_site_url() + '/mod/profile/icondirect.php?lastcache=1358256119&joindate=1349703717&guid=42&size=tiny',
				},
				{
				 	'username': 'test.user2',
				 	'text': 'Hello world 2!',
				 	'image_url': elgg.get_site_url() + '/mod/profile/icondirect.php?lastcache=1358256119&joindate=1349703717&guid=42&size=tiny',
				},
		 	]);

			 conn.send(message);
		};

		conn.onmessage = function(e) {
			console.log(e.data);
			//return;

			var counter = $('#notifier-new');

			var count = parseInt(counter.html());

			if (isNaN(count)) {
				count = 0;
			}

			var data = JSON.parse(e.data);

			console.log(e.data);
			console.log(data);

			$.each(data, function(key, notification) {
				 $('#notifier-popup .elgg-body .elgg-list').prepend(notification.text);
				 elgg.system_message(notification.text);
				count++;
			});
			console.log(count);

			counter.html(count).removeAttr('hidden');
		};
	};

	chat_connection();

	var init = function() {

	};

	elgg.register_hook_handler('init', 'system', init);
});
