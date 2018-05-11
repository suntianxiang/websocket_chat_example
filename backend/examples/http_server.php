<?php

use App\Http\Server as HttpServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response\HtmlResponse;


require __DIR__."/../vendor/autoload.php";

$loop = Factory::create();

$socket = new Server('0.0.0.0:8000', $loop);
$httpServer = new HttpServer($socket);
$httpServer->onRequest(function (RequestInterface $request) {
    return new HtmlResponse(file_get_contents(__DIR__.'/html/websocket.html'));
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
