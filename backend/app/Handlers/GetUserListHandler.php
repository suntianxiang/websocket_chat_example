<?php

namespace App\Handlers;

class GetUserListHandler extends AbstractHandler
{
    public function handle($data)
    {
        $userList = [];
        $connections = array_filter($this->server->connections, function ($item) {
            return !empty($item['userName']);
        });

        foreach ($connections as $key => $value) {
            $userList[] = $value['userName'];
        }

        return [
            'action' => 'getUserList',
            'code' => 0,
            'data' => [
                'userList' => $userList
            ]
        ];
    }
}
