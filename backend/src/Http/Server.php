<?php

namespace Support\Http;

use React\EventLoop\LoopInterface;
use React\Socket\Server as SocketServer;
use React\Socket\ConnectionInterface;
use Zend\Diactoros\Request;
use Evenement\EventEmitter;
use Zend\Diactoros\Response\EmptyResponse;

class Server extends EventEmitter
{
    protected $socket;

    protected $callback;

    public function __construct(SocketServer $socket)
    {
        $this->socket = $socket;
        $this->socket->on('connection', [$this, 'handleConnection']);
    }

    public function handleConnection(ConnectionInterface $conn)
    {
        $conn->once('data', function ($data) use ($conn) {
            $content = explode("\r\n", $data);
            $requestLine = array_shift($content);
            if (!preg_match('/^(GET|POST) (.+) (HTTP[S]{0,1})\/([0-9.]+)/', $requestLine, $info)) {
                return $conn->end('HTTP/1.1 400 Bad Request');
            }

            $headers = [];
            $count = count($content);
            do {
                $line = array_shift($content);
                if (!preg_match('/^(.+): (.+)/', $line, $header)) {
                    continue;
                }
                $headers[$header[1]] = $header[2];
            } while ("" !== $line);

            $body = implode('', $content);

            $request = new Request(
                strtolower($info[3])."://{$headers['Host']}{$info[2]}",
                $info[1],
                'php://memory',
                $headers
            );
            $request->getBody()->write($body);
            $request->withProtocolVersion($info[4]);
            if (is_callable($this->callback)) {
                $response = call_user_func_array($this->callback, [$request, $this]);
            }
            // default return empty response
            else {
                $response = new EmptyResponse();
            }


            $statusLine = strtoupper($request->getUri()->getScheme()).'/'.$request->getProtocolVersion().' '.$response->getStatusCode().' '.$response->getReasonPhrase();
            $conn->write("$statusLine\r\n");

            foreach ($response->getHeaders() as $key => $values) {
                $conn->write(implode(': ', [$key, implode(', ', $values)])."\r\n");
            }
            $conn->write("\r\n");

            if (101 !== $response->getStatusCode()) {
                return $conn->end((string) $response->getBody());
            } else {
                $this->emit('upgrade', [$conn, $request]);
            }
        });
    }

    /**
     * onRequest
     * desc
     * @param mixed $closure
     * @return void
     */
    public function onRequest($closure)
    {
        if (!is_callable($closure)) {
            throw new \InvalidArgumentException('Argument 1 must callable', -1);
        }

        $this->callback = $closure;
    }
}
