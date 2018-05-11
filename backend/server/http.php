<?php

use Support\Http\Server as HttpServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;


require __DIR__."/../vendor/autoload.php";

$loop = Factory::create();
$mime_types = require __DIR__."/config/mimes.php";

$socket = new Server('0.0.0.0:8000', $loop);
$httpServer = new HttpServer($socket);
$httpServer->onRequest(function (RequestInterface $request) use (&$mime_types) {
    $path = $request->getUri()->getPath();

    if ($path == '/') {
        $path = '/index.html';
    }

    // filter ../
    $file = __DIR__.'/../public'.str_replace('../', '', $path);

    if (file_exists($file)) {
        $arr = explode('.', $file);
        $ext = array_pop($arr);
        $mime_type = $mime_types[$ext] ?? mime_content_type($file);
        if (is_array($mime_type)) {
            $content_type = array_shift($mime_type);
        } else {
            $content_type = $mime_type;
        }

        return new Response(fopen($file, 'r'), 200, [
            'Content-Type' => $content_type
        ]);
    }

    return new EmptyResponse();
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
