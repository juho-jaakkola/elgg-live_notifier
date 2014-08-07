<?php
/**
 * This can be used to send test data through ZMQ
 */

require __DIR__ . '/vendor/autoload.php';

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");

$entryData = array(
	'category' => 'testCategory',
	'title'    => 'Test',
	'article'  => 'Lorem ipsum dolor sit amet',
	'when'     => time(),
);

$socket->send(json_encode($entryData));
