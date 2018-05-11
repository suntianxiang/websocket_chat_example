<?php

use React\EventLoop\Factory;
use App\RandomNamePool;
use Support\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Support\WebSocket\WebSocketServer as WebSocketServer;

require __DIR__."/../vendor/autoload.php";

$loop = Factory::create();
$socket = new SocketServer('0.0.0.0:8080', $loop);
$httpServer = new HttpServer($socket);
$websocketServer = new WebSocketServer($httpServer);
$connections = [];

$websocketServer->on('open', function ($conn) {
    // send the unused names
    echo "A client connect, ip: ".$conn->getIp()."\n";
});

$websocketServer->on('message', function ($conn, $data) use (&$connections) {
    $json = json_decode($data, true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return;
    }

    switch ($json['action']) {
        case 'chatMessage':
            $current = $connections[$conn->connectionId];
            $content = $json['content'];
            $send_connections = array_filter($connections, function ($item) use ($current) {
                return $item['connection']->connectionId != $current['connection']->connectionId;
            });
            foreach ($connections as $key => $value) {
                $value['connection']->send(json_encode([
                    'action' => 'chatMessage',
                    'code' => 0,
                    'data' => [
                        'userName' => $current['name'],
                        'content' => $json['content']
                    ]
                ]));
            }
            $conn->send(json_encode([
                'action' => 'chatMessageReturn',
                'code' => 0,
            ]));
            break;
        case 'login':
            $name = RandomNamePool::getInstance()->get();
            $connections[$conn->connectionId] = [
                'name' => $name,
                'connection' => $conn
            ];
            foreach ($connections as $key => $value) {
                $value['connection']->send(json_encode([
                    'action' => 'otherLogin',
                    'code' => 0,
                    'data' => [
                        'userName' => $name
                    ]
                ]));
            }
            $conn->send(json_encode([
                'action' => 'login',
                'code' => 0,
                'name' => $name
            ]));
            break;
        case 'getUserList':
            $current = $connections[$conn->connectionId];
            $userList = [];
            foreach ($connections as $key => $value) {
                $userList[] = $value['name'];
            }
            $conn->send(json_encode([
                'action' => 'getUserList',
                'code' => 0,
                'data' => [
                    'userList' => $userList
                ]
            ]));
            break;
    }
});

$websocketServer->on('close', function ($conn) use (&$connections) {
    $object = $connections[$conn->connectionId];
    RandomNamePool::getInstance()->recycle($object['name']);
    unset($connections[$conn->connectionId]);
    foreach ($connections as $key => $value) {
        $value['connection']->send(json_encode([
            'action' => 'logout',
            'code' => 0,
            'data' => [
                'userName' => $object['name']
            ]
        ]));
    }
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
