<?php

use React\EventLoop\Factory;
use App\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use App\WebSocket\WebSocketServer as WebSocketServer;

require __DIR__."/../vendor/autoload.php";

$loop = Factory::create();
$socket = new SocketServer('0.0.0.0:8080', $loop);
$httpServer = new HttpServer($socket);
$websocketServer = new WebSocketServer($httpServer);

$websocketServer->on('open', function ($conn) {
    $conn->send('hello');
    echo "A client connect, ip: ".$conn->getIp()."\n";
});

$websocketServer->on('message', function ($conn, $data) {
    $conn->send("server get the message: $data");
    echo "A client send message: $data \n";
});

$websocketServer->on('close', function ($conn) {
    echo "A client close ip: ".$conn->getIp()."\n";
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
