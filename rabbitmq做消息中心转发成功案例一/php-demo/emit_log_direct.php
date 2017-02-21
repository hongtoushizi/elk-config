<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

date_default_timezone_set('Asia/Shanghai');
$exchangeName = 'logstash-rabbitmq';
$queueName = 'logstash-queue';
$routeKey = 'logstash-key';

$connection = new AMQPStreamConnection('139.224.132.240', 5672, 'admin', '＊＊＊＊＊');
$channel = $connection->channel();

$channel->exchange_declare($exchangeName, 'direct', true, true, true);

$severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';

$message = implode(' ', array_slice($argv, 2));
if(empty($message)) $message = "Hello World!";

//$data = "{\"message\":\"66666hello\",\"@version\":\"1\",\"@timestamp\":\"2016-07-09T17:25:28.259Z\",\"path\":\"/var/log/messages\",\"host\":\"0.0.0.0\",\"type\":\"subject-access-log\"}";
//$ip = getIP();

$time = date("Y-m-d H:i:s",time());
$data = array(
        "message"=> $message,
        "path"=>"rabbitmq消息中心",
        "host"=>"127.0.0.1",
        "type"=>"subject-access-log"
);

$data1 = json_encode($data);

$msg = new AMQPMessage(
    $data1,
    array(
        'content_type' => 'application/json',
        'delivery_mode' => 2
    )
);

//$channel->basic_publish($msg, $exchangeName, $severity);
$channel->basic_publish($msg, $exchangeName, $routeKey);

echo " [x] Sent ",$severity,':',$data1," \n";

$channel->close();
$connection->close();

?>