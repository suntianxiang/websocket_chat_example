<?php

use React\EventLoop\Factory;
use App\RandomNamePool;
use Support\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use App\ChatRoomServer;

require __DIR__."/../vendor/autoload.php";

$loop = Factory::create();
$socket = new SocketServer('0.0.0.0:8080', $loop);
$httpServer = new HttpServer($socket);
$roomServer = new ChatRoomServer($httpServer);
$roomServer->on('open', [$roomServer, 'onOpen']);
$roomServer->on('message', [$roomServer, 'onMessage']);
$roomServer->on('close', [$roomServer, 'onClose']);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
