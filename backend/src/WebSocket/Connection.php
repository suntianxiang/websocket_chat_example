<?php

namespace Support\WebSocket;

use React\Socket\ConnectionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Support\WebSocket\Message\Frame;

/**
 * The WebSocket Connection
 *
 * @author suntianxiang <suntianxiang@sina.cn>
 * @version 1.0
 */
class Connection
{
    /**
     * the socket connection
     *
     * @var ConnectionInterface
     */
    protected $conn;

    /**
     * current connection id
     * auto increment, step 1
     * @var int
     */
    protected static $incrementId = 1;

    /**
     * the connection identity id
     *
     * @var int
     */
    public $connectionId;

    /**
     * Connection Contructor
     *
     * @param ConnectionInterface $conn
     * @return $this
     */
    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
        $this->connectionId = self::$incrementId++;
    }

    /**
     * get client ip
     *
     * @return string
     */
    public function getIp()
    {
        $address = $this->conn->getRemoteAddress();
        $ip = trim(parse_url($address, PHP_URL_HOST), '[]');

        return $ip;
    }

    /**
     * send message to connection
     *
     * @param mixed $message
     * @return bool
     */
    public function send($message)
    {
        $frame = new Frame($message);
        return $this->conn->write((string) $frame);
    }

    /**
     * close the conenction
     *
     * @return bool
     */
    public function close()
    {
        return $this->conn->close();
    }

    /**
     * magic call:
     * 1. call the stream method if exists
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (is_callable([$this->conn, $name])) {
            return \call_user_func([$this->conn, $name], ...$arguments);
        }

        throw new \BadMethodCallException('Call undefined method', -2);
    }
}
