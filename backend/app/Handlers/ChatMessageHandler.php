<?php

namespace App\Handlers;

class ChatMessageHandler extends AbstractHandler
{
    public function handle($data)
    {
        $this->server->broadcast([
            'action' => 'chatMessage',
            'data' => [
                'userName' => $this->server->getUser($this->conn)['userName'],
                'content' => $data['content']
            ]
        ]);

        return [
            'action' => 'chatMessage_back',
            'code' => 0
        ];
    }
}
