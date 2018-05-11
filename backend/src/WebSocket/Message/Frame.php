<?php
declare(strict_types=1);

namespace Support\WebSocket\Message;

/**
 * Websocket frame class
 *
 * @author suntianxiang <suntianxiang@sina.cn>
 * @version 1.0
 */
class Frame
{
    const OP_CONTINUE =  0;
    const OP_TEXT     =  1;
    const OP_BINARY   =  2;
    const OP_CLOSE    =  8;
    const OP_PING     =  9;
    const OP_PONG     = 10;

    /**
     * the final bit
     *
     * @var int;
     */
    public $fin = 1;

    /**
     * the rsv1 bit
     *
     * @var int
     */
    public $rsv1 = 0;

    /**
     * the rsv2 bit
     *
     * @var int
     */
    public $rsv2 = 0;

    /**
     * the rsv3 bit
     *
     * @var int
     */
    public $rsv3 = 0;

    /**
     * the opcode of 4 bit
     *
     * @var int
     */
    public $opcode;

    /**
     * the mask bit
     *
     * @var int
     */
    public $mask = 0;

    /**
     * the payload length
     *
     * @var int
     */
    protected $payloadLength;

    /**
     * the payload
     *
     * @var mixed
     */
    protected $payload;

    /**
     * frame contructor
     *
     * @param mixed $payload
     * @param bool $final
     * @param int $opcode
     * @return $this
     */
    public function __construct($payload, $final = true, $opcode = self::OP_TEXT)
    {
        $this->payloadLength   = strlen($payload);
        $this->fin = $final ? 1 : 0;
        $this->payload = $payload;
        $this->opcode = $opcode;
    }

    /**
     * get payload
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * convert the frame to a send string
     *
     * @return string
     */
    public function __toString()
    {
        $string = chr(
            $this->fin << 7
            | $this->rsv1 << 6
            | $this->rsv2 << 5
            | $this->rsv3 << 4
            | $this->opcode
        );

        // 8+64 bit
        if ($this->payloadLength > 65535) {
            $string .= chr($this->mask << 7 | 127).pack('NN', 0, $this->payloadLength);
        }
        // 8+16 bit
        elseif ($this->payloadLength > 125) {
            $string .= chr($this->mask << 7 | 126).pack('n', $this->payloadLength);
        }
        // 8 bit
        else {
            $string .= chr($this->mask << 7 | $this->payloadLength);
        }

        if (0 === $this->mask) {
            $string .= $this->payload;
        } else {
            $maskingKey = $this->getMaskingKey();
            for ($i = 0, $max = strlen($this->payload); $i < $max; ++$i) {
                $this->payload[$i] = chr(ord($this->payload[$i]) ^ $maskingKey[$i % 4]);
            }
            $string .=
                implode('', array_map('chr', $maskingKey)) .
                $this->payload;
        }

        return $string;
    }

    /**
     * create frame from a binary string
     *
     * @param string $string
     * @return Frame
     */
    public static function fromString($string): Frame
    {
        $firstByte = ord($string[0]);
        $fin = ($firstByte >> 7);
        $rsv1 = ($firstByte >> 6) & 0x1;
        $rsv2 = ($firstByte >> 5) & 0x1;
        $rsv3 = ($firstByte >> 4) & 0x1;
        $opcode = $firstByte & 0xf;

        $secondByte = ord($string[1]);
        $mask = ($secondByte >> 7) & 0x1;
        $payloadLength = $secondByte & 0x7f;
        if (0 !== $rsv1 || 0 !== $rsv2 || 0 !== $rsv3) {
            throw new \Exception('Get rsv1: %s, rsv2: %s, rsv3: %s, they all must be equal to 0.', 2);
        }

        $offset = 2;
        if (0 === $payloadLength) {
            $payload = '';
        } elseif (126 === $payloadLength) {
            $payloadLength = unpack('nl', substr($string, 2, 2))['l'];
            $offset += 2;
        } elseif (127 === $payloadLength) {
            $payloadLength = unpack('N*l', substr($string, 2, 8))['l2'];
            if ($payloadLength > 0x7fffffffffffffff) {
                throw new \Exception('Message is too long.');
            }
            $offset += 8;
        }

        if (0 === $mask) {
            $decoded = substr($string, $offset);
        }

        $masks = substr($string, $offset, 4);
        $offset += 4;
        $decoded = '';

        $payload = substr($string, $offset);
        for ($index = 0; $index < strlen($payload); $index++) {
            $decoded .= $payload[$index] ^ $masks[$index % 4];
        }

        $frame = new self($decoded, $fin, $opcode);
        $frame->rsv1 = $rsv1;
        $frame->rsv2 = $rsv2;
        $frame->rsv3 = $rsv3;
        $frame->opcode = $opcode;
        $frame->mask = $mask;

        return $frame;
    }

    /**
     * Get a random masking key.
     *
     * @return string
     */
    public function getMaskingKey(): array
    {
        if (true === function_exists('openssl_random_pseudo_bytes')) {
            $maskingKey = array_map(
                'ord',
                str_split(
                    openssl_random_pseudo_bytes(4)
                )
            );
        } else {
            $maskingKey = [];
            for ($i = 0; $i < 4; ++$i) {
                $maskingKey[] = mt_rand(1, 255);
            }
        }
        return $maskingKey;
    }
}
