<?php

namespace App\Handlers;

use App\RandomNamePool;
use App\Exceptions\PoolEmptyException;

class LoginHandler extends AbstractHandler
{
    public function handle($data)
    {
        try {
            $name = RandomNamePool::getInstance()->get();
        } catch (PoolEmptyException $e) {
            return [
                'action' => 'wait',
                'message' => '房间人满了 ...'
            ];
        }

        $this->server->connections[$this->conn->connectionId]['userName'] = $name;

        $this->server->broadcast([
            'action' => 'otherLogin',
            'code' => 0,
            'data' => [
                'userName' => $name
            ]
        ]);

        return [
            'action' => 'login_back',
            'code' => 0,
            'data' => [
                'userName' => $name,
            ]
        ];
    }
}
