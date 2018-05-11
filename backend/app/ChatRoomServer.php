<?php

namespace App;

use Support\WebSocket\WebSocketServer;
use Support\WebSocket\Connection;

class ChatRoomServer extends WebSocketServer
{
    /**
     * all connections
     *
     * @var array
     */
    public $connections;

    /**
     * open event handler
     *
     * @param Connection $conn
     * @return void
     */
    public function onOpen(Connection $conn)
    {
        $this->connections[$conn->connectionId] = [
            'connection' => $conn,
        ];
    }

    /**
     * message event handler
     *
     * @param Connection $conn
     * @param string $data
     * @return void
     */
    public function onMessage(Connection $conn, string $data)
    {
        $json = json_decode($data, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return;
        }
        if (!isset($json['action'])) {
            return;
        }
        $handlerClass = 'App\Handlers\\'.ucfirst($json['action']).'Handler';
        $handler = new $handlerClass($this, $conn);
        $result = $handler->handle($json['data']);
        $conn->send(json_encode($result));
    }

    /**
     * close event handler
     *
     * @param Connection $conn
     * @return void
     */
    public function onClose(Connection $conn)
    {
        $connection = $this->connections[$conn->connectionId];
        RandomNamePool::getInstance()->recycle($connection['userName']);
        unset($this->connections[$conn->connectionId]);

        $this->broadcast([
            'action' => 'logout',
            'code' => 0,
            'data' => [
                'userName' => $connection['userName']
            ]
        ]);
    }

    /**
     * broadcast a message
     *
     * @param \JsonSerializable $data
     * @return void
     */
    public function broadcast($data)
    {
        $clients = array_filter($this->connections, function($item) {
            return !empty($item['userName']);
        });

        foreach ($clients as $key => $value) {
            $value['connection']->send(json_encode($data));
        }
    }

    /**
     * get user by connection
     *
     * @param Connection $conn
     * @return array
     */
    public function getUser(Connection $conn)
    {
        $connection = array_filter($this->connections, function ($item) use ($conn) {
            return $conn->connectionId === $item['connection']->connectionId;
        });

        return array_pop($connection);
    }
}
