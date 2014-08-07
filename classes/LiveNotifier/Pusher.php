<?php
namespace LiveNotifier;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\MessageComponentInterface;

class Pusher implements MessageComponentInterface {
	/**
	 * A lookup of all the topics clients have subscribed to
	 */
	protected $subscribers;

	protected $clients;

	private $tokens;

	public function __construct($tokens) {
		$this->tokens = $tokens;

		$this->clients = new \SplObjectStorage;

		$this->subscribers = array();
	}

	public function getSubProtocols() {

	}

	public function onOpen(ConnectionInterface $conn) {
		// Store the new connection to send messages to later
		$this->clients->attach($conn);

		echo "New connection from a client! ({$conn->resourceId})\n";
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		$data = json_decode($msg);

		echo "Connection {$from->resourceId} is subscribing for notifications\n";

		if ($this->tokens->validateToken($data->token)) {
			// TODO Should the storage be injected?
			// TODO Remove users from the storage when they log out.
			$this->subscribers[$data->guid] = $from;
		}
	}

	public function onSubscribe(ConnectionInterface $conn, $topic) {
		error_log("Subscribing to a topic");
		$this->subscribedTopics[$topic->getId()] = $topic;
	}

	/**
	 * @param string JSON'ified string we'll receive from ZeroMQ
	 */
	public function onNotificationMessage($entry) {
		$data = json_decode($entry);

		echo "Sending a notification to user GUID {$data->recipient_guid}\n";

		if (isset($this->subscribers[$data->recipient_guid])) {
			$connection = $this->subscribers[$data->recipient_guid];
			$connection->send($entry);
		}
	}

	public function onUnSubscribe(ConnectionInterface $conn, $topic) {

	}

	public function onClose(ConnectionInterface $conn) {
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($conn);

		echo "Connection {$conn->resourceId} has disconnected\n";
	}

	public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
		// In this application if clients send data it's because the user hacked around in console
		$conn->callError($id, $topic, 'You are not allowed to make calls')->close();
	}

	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
		// In this application if clients send data it's because the user hacked around in console
		$conn->close();
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}
}