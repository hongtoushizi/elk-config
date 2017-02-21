<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;


$exchangeName = 'logstash-rabbitmq';
$queueName = 'logstash-queue';
$routeKey = 'logstash-key';

$connection = new AMQPStreamConnection('139.224.132.240', 5672, 'admin', '＊＊＊＊＊');
$channel = $connection->channel();

$channel->exchange_declare($exchangeName, 'direct', true, true, true);

list($queue_name, ,) = $channel->queue_declare($queueName, true, true, true, true);

$severities = array_slice($argv, 1);
if(empty($severities )) {
	file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
	exit(1);
}

//foreach($severities as $severity) {
//    $channel->queue_bind($queue_name, $exchangeName, $severity);
//}
echo "queue_name-----<br>";
var_dump($queue_name);
$channel->queue_bind($queue_name, $exchangeName, $routeKey);

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

$callback = function($msg){
    var_dump($msg);
  echo ' [x] '.$msg->delivery_info['routing_key'].':'.$msg->body."\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
