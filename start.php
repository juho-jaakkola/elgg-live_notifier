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
function live_notifier_init() {
	elgg_require_js('live_notifier/live_notifier');

	elgg_register_plugin_hook_handler('send', 'notification:notifier', 'live_notifier_notification_send');

	elgg_register_plugin_hook_handler('view', 'js/initialize_elgg', 'live_notifier_add_token');
}

/**
 * Extend javascript config with token usable for subscribing
 *
 * @param string $hook    'view'
 * @param string $type    'js/initialize_elgg'
 * @param string $content Current content
 * @param array  $params  Array containing view, vars and viewtype
 * @return string $content Modified content
 */
function live_notifier_add_token($hook, $type, $content, $params) {
	if (!elgg_is_logged_in()) {
		return $content;
	}

	$user = elgg_get_logged_in_user_entity();
	$site_guid = elgg_get_site_entity()->guid;

	//$token = live_notifier_get_token($user_guid);
	$tokens = live_notifier_get_token_service();
	$token = $tokens->getToken($user);

	if (!$token) {
		$token = $tokens->createToken($user);
	}

	// TODO Check if user has enabled notifier. There's no need for
	// a token if user doesn't want to receive notifications.

	$content .= "elgg.config.site_guid = {$site_guid};";
	$content .= "elgg.live_notifier_token = '{$token}';";

	return $content;
}

/**
 *
 */
function live_notifier_get_token_service() {
	global $CONFIG;

	$db = _elgg_services()->db;

	return new \LiveNotifier\Tokens($db, $CONFIG);
}

/**
 * Send real-time notifications to subscribed users
 *
 * @param string $hook
 * @param string $type
 * @param
 * @param array $params
 */
function live_notifier_notification_send($hook, $type, $object, $params) {
	$notification = $params['notification'];
	$event = $params['event'];

	if (!$event) {
		// The notification would be incomplete without the event
		return false;
	}

	$ia = elgg_set_ignore_access(true);

	$action = $event->getAction();
	$object = $event->getObject();
	$string = "river:{$action}:{$object->getType()}:{$object->getSubtype()}";
	$recipient = $notification->getRecipient();
	$actor = $event->getActor();
	switch ($object->getType()) {
		case 'annotation':
			// Get the entity that was annotated
			$entity = $object->getEntity();
			break;
		case 'relationship':
			$entity = get_entity($object->guid_two);
			break;
		default:
			if ($object instanceof ElggComment) {
				// Use comment's container as notification target
				$entity = $object->getContainerEntity();

				// Check the action because this isn't necessarily a new comment,
				// but e.g. someone being mentioned in a comment
				if ($action == 'create') {
					$string = "river:comment:{$entity->getType()}:{$entity->getSubtype()}";
				}
			} else {
				// This covers all other entities
				$entity = $object;
			}
	}
	elgg_set_ignore_access($ia);


	require __DIR__ . '/vendor/autoload.php';

	$context = new ZMQContext();
	$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
	$socket->connect("tcp://localhost:5555");

	$msg = new \stdClass();
	$msg->recipient_guid = $recipient->guid;
	$msg->subject_name = $actor->getDisplayName();
	$msg->subject_url = $actor->getURL();
	$msg->target_name = $entity->getDisplayName();
	$msg->target_url = $entity->getURL();
	$msg->text = $string;
	$msg->icon_url = $actor->getIconURL();

	$socket->send(json_encode($msg));
}
