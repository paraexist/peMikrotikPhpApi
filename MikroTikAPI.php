<?php

class MikroTikAPI
{
    private $socket;

    /**
     * Connect to the MikroTik RouterOS API.
     *
     * @param string $host The MikroTik host (IP address or hostname).
     * @param int $port The API port (default: 8728).
     * @throws Exception If the connection fails.
     */
    public function connect($host, $port = 8728)
    {
        $this->socket = fsockopen($host, $port, $errno, $errstr, 5);
        if (!$this->socket) {
            throw new Exception("Unable to connect: $errstr ($errno)");
        }
    }

    /**
     * Disconnect from the MikroTik RouterOS API.
     */
    public function disconnect()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    /**
     * Send a single word to the MikroTik API.
     *
     * @param string $word The word to send.
     * @throws Exception If the socket write fails.
     */
    private function sendWord($word)
    {
        $length = strlen($word);

        if ($length < 0x80) {
            fwrite($this->socket, chr($length));
        } elseif ($length < 0x4000) {
            fwrite($this->socket, chr(($length >> 8) | 0x80) . chr($length & 0xFF));
        } elseif ($length < 0x200000) {
            fwrite($this->socket, chr(($length >> 16) | 0xC0) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        } else {
            fwrite($this->socket, chr(($length >> 24) | 0xE0) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        }

        fwrite($this->socket, $word);
    }

    /**
     * Read a single word from the MikroTik API.
     *
     * @return string The word read from the API.
     * @throws Exception If reading from the socket fails.
     */
    private function readWord()
    {
        $lengthByte = fread($this->socket, 1);

        if ($lengthByte === false || $lengthByte === '') {
            throw new Exception("Failed to read length byte from the API response.");
        }

        $length = ord($lengthByte);

        if (($length & 0x80) == 0x80) {
            $length &= ~0x80;
            $length = ($length << 8) + ord(fread($this->socket, 1));
        } elseif (($length & 0xC0) == 0xC0) {
            $length &= ~0xC0;
            $length = ($length << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        } elseif (($length & 0xE0) == 0xE0) {
            $length &= ~0xE0;
            $length = ($length << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        }

        if ($length <= 0) {
            return ''; // End of sentence or empty word
        }

        $word = fread($this->socket, $length);

        if ($word === false || strlen($word) !== $length) {
            throw new Exception("Failed to read a complete word. Expected length: $length.");
        }

        return $word;
    }

    /**
     * Read a full response (multiple sentences) from the MikroTik API.
     *
     * @return array An array of sentences, each being an array of words.
     * @throws Exception If reading fails.
     */
    private function readResponse()
    {
        $responses = [];

        while (true) {
            $sentence = [];
            while (true) {
                $word = $this->readWord();
                if ($word === '') {
                    break; // End of a sentence
                }
                $sentence[] = $word;
            }

            $responses[] = $sentence;

            // Check for the end of the response (!done)
            if (!empty($sentence) && $sentence[0] === '!done') {
                break;
            }
        }

        return $responses;
    }

    /**
     * Send a command with optional attributes to the MikroTik API.
     *
     * @param string $command The command to execute.
     * @param array $attributes Optional key-value pairs for the command.
     * @return array The API response.
     * @throws Exception If sending the command fails.
     */
    public function sendCommand($command, $attributes = [])
    {
        $this->sendWord($command);

        foreach ($attributes as $key => $value) {
            $this->sendWord('=' . $key . '=' . $value);
        }

        $this->sendWord(''); // End of sentence

        return $this->readResponse();
    }

    /**
     * Log in to the MikroTik RouterOS API.
     *
     * @param string $username The API username.
     * @param string $password The API password.
     * @throws Exception If login fails.
     */
    public function login($username, $password)
    {
        $response = $this->sendCommand('/login', [
            'name' => $username,
            'password' => $password,
        ]);

        if (empty($response) || !isset($response[0]) || $response[0][0] !== '!done') {
            throw new Exception('Login failed: ' . print_r($response, true));
        }
    }
}
