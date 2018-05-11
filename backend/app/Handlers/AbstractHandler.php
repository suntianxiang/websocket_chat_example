<?php

namespace App\Handlers;

use App\ChatRoomServer;
use Support\WebSocket\Connection;

abstract class AbstractHandler implements HandlerInterface
{
    protected $server;

    protected $conn;

    public function __construct(ChatRoomServer $server, Connection $conn)
    {
        $this->server = $server;
        $this->conn = $conn;
    }
}
