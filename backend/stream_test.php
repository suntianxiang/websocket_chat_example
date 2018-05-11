<?php

use React\Stream\ThroughStream;

require __DIR__."/vendor/autoload.php";

$stream = new ThroughStream();

$stream->on('data', function ($data) {
    echo $data;
});

$stream->write('hello world');
