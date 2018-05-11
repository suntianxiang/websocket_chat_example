<?php
declare(strict_types=1);

namespace Support\WebSocket;

use Support\Http\Server;
use Support\WebSocket\HandShake\ServerNegotiator;
use Support\WebSocket\HandShake\RequestVerifier;
use Support\WebSocket\Message\Frame;
use Support\WebSocket\Connection;
use Psr\Http\Message\RequestInterface;
use React\Socket\ConnectionInterface;
use Evenement\EventEmitter;

class WebSocketServer extends EventEmitter
{

    private $httpServer;

    public function __construct(Server $httpServer)
    {
        $this->httpServer = $httpServer;
        $this->httpServer->onRequest([$this, 'handleRequest']);
        $this->httpServer->on('upgrade', [$this, 'handleUpgrade']);
    }

    /**
     * handle the http request
     *
     * @internal
     * @param RequestInterface $request
     * @return Psr\Http\Message\ResponseInterface
     */
    public function handleRequest(RequestInterface $request)
    {
        $negotiator = new ServerNegotiator(new RequestVerifier());
        $response = $negotiator->handshake($request);

        return $response;
    }

    /**
     * upgrade the protocol
     * @internal
     * @param ConnectionInterface $conn
     * @return void
     */
    public function handleUpgrade(ConnectionInterface $conn)
    {
        $connection = new Connection($conn);
        $this->emit('open', [$connection]);
        $buffer = '';
        $conn->on('data', function ($data) use (&$buffer, $connection) {
            $frame = Frame::fromString($data);
            switch ($frame->opcode) {
                case Frame::OP_CLOSE:
                    return $this->handleClose($connection, $frame);
                case Frame::OP_PING:
                    return $this->handlePing($connection, $frame);
                case Frame::OP_PONG:
                    return $this->handlePong($connection, $frame);
            }

            $buffer .= $frame->getPayload();

            if (1 == $frame->fin) {
                $data = substr($buffer, 0);
                $this->emit('message', [$connection, $data]);
                $buffer = '';
            }
        });
    }

    /**
     * handle close frame
     *
     * @param Connection $conn
     * @param Frame $frame
     * @return void
     */
    public function handleClose(Connection $conn, Frame $frame)
    {
        $conn->write((string) (new Frame('', 1, Frame::OP_CLOSE)));
        $conn->close();
        $this->emit('close', [$conn]);
    }

    /**
     * handle ping frame
     *
     * @param Connection $conn
     * @param Frame $frame
     * @return void
     */
    public function handlePing(Connection $conn, Frame $frame)
    {
        $conn->write((string) (new Frame('', 1, Frame::OP_PONG)));
    }

    /**
     * handle pong frame
     *
     * @param Connection $conn
     * @param Frame $frame
     * @return void
     */
    public function handlePong(Connection $conn, Frame $frame)
    {
        $this->emit('pong', [$conn, $frame]);
    }
}
