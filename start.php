<?php
/**
 * Live Notifier
 *
 * @package LiveNotifier
 */

elgg_register_event_handler('init', 'system', 'live_notifier_init');

/**
 * Initialize the plugin
 *
 * @return void
 */
function live_notifier_init () {
	elgg_require_js('live_notifier/live_notifier');


	elgg_define_js('autobahn', array(
        'src' => 'mod/live_notifier/vendor/autobahn/autobahn.min.js',
        //'src' => 'mod/live_notifier/vendor/autobahn/autobahn.js',
    ));
	//elgg_require_js('autobahn');


	//elgg_register_js('autobahn', 'mod/live_notifier/vendor/autobahn/autobahn.js');
	//elgg_load_js('autobahn');
}
