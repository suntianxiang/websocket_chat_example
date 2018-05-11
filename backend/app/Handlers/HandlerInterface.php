<?php

namespace App\Handlers;

interface HandlerInterface
{
    /**
     * handle message
     *
     * @param mixed $data
     * @return array ['action' => 'xxx', 'data' => 'xxx']
     */
    public function handle($data);
}
