<?php

namespace App\Services\Mikrotik;

use RuntimeException;

class RouterOsApiClient
{
    private $socket = null;

    public function connect(string $host, int $port, string $user, string $pass, int $timeoutSeconds = 10): void
    {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeoutSeconds);

        if (!$this->socket) {
            throw new RuntimeException("RouterOS API connect failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, $timeoutSeconds);

        // Login (RouterOS API)
        $this->writeSentence(['/login', '=name='.$user, '=password='.$pass]);

        $response = $this->readResponse();
        if (! $this->hasDone($response)) {
            throw new RuntimeException('RouterOS API login failed.');
        }
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->socket = null;
    }

    public function command(string $path, array $params = []): array
    {
        $sentence = [$path];
        foreach ($params as $k => $v) {
            $sentence[] = '=' . $k . '=' . $v;
        }

        $this->writeSentence($sentence);
        return $this->readResponse();
    }

    public function resource(): array
    {
        // /system/resource/print
        $resp = $this->command('/system/resource/print');
        return $this->firstRe($resp);
    }

    private function firstRe(array $resp): array
    {
        foreach ($resp as $block) {
            if (($block['type'] ?? null) === '!re') {
                return $block['data'] ?? [];
            }
        }
        return [];
    }

    private function hasDone(array $resp): bool
    {
        foreach ($resp as $block) {
            if (($block['type'] ?? null) === '!done') return true;
        }
        return false;
    }

    private function writeSentence(array $words): void
    {
        foreach ($words as $w) {
            $this->writeWord($w);
        }
        $this->writeWord(''); // end sentence
    }

    private function writeWord(string $word): void
    {
        $len = strlen($word);
        $this->writeLen($len);
        fwrite($this->socket, $word);
    }

    private function writeLen(int $len): void
    {
        if ($len < 0x80) {
            fwrite($this->socket, chr($len));
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            fwrite($this->socket, chr(($len >> 8) & 0xFF));
            fwrite($this->socket, chr($len & 0xFF));
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            fwrite($this->socket, chr(($len >> 16) & 0xFF));
            fwrite($this->socket, chr(($len >> 8) & 0xFF));
            fwrite($this->socket, chr($len & 0xFF));
        } elseif ($len < 0x10000000) {
            $len |= 0xE0000000;
            fwrite($this->socket, chr(($len >> 24) & 0xFF));
            fwrite($this->socket, chr(($len >> 16) & 0xFF));
            fwrite($this->socket, chr(($len >> 8) & 0xFF));
            fwrite($this->socket, chr($len & 0xFF));
        } else {
            fwrite($this->socket, chr(0xF0));
            fwrite($this->socket, chr(($len >> 24) & 0xFF));
            fwrite($this->socket, chr(($len >> 16) & 0xFF));
            fwrite($this->socket, chr(($len >> 8) & 0xFF));
            fwrite($this->socket, chr($len & 0xFF));
        }
    }

    private function readResponse(): array
    {
        $out = [];

        while (true) {
            $sentence = $this->readSentence();
            if ($sentence === null) break;

            $type = $sentence[0] ?? '';
            $data = [];

            for ($i = 1; $i < count($sentence); $i++) {
                $w = $sentence[$i];
                if (str_starts_with($w, '=')) {
                    $w = substr($w, 1);
                    $pos = strpos($w, '=');
                    if ($pos !== false) {
                        $k = substr($w, 0, $pos);
                        $v = substr($w, $pos + 1);
                        $data[$k] = $v;
                    }
                }
            }

            $out[] = ['type' => $type, 'data' => $data];

            if ($type === '!done') break;
        }

        return $out;
    }

    private function readSentence(): ?array
    {
        $words = [];
        while (true) {
            $w = $this->readWord();
            if ($w === null) return null;
            if ($w === '') break;
            $words[] = $w;
        }
        return $words;
    }

    private function readWord(): ?string
    {
        $len = $this->readLen();
        if ($len === null) return null;
        if ($len === 0) return '';
        $word = '';
        while (strlen($word) < $len) {
            $chunk = fread($this->socket, $len - strlen($word));
            if ($chunk === false || $chunk === '') break;
            $word .= $chunk;
        }
        return $word;
    }

    private function readLen(): ?int
    {
        $c = fread($this->socket, 1);
        if ($c === false || $c === '') return null;

        $len = ord($c);

        if (($len & 0x80) === 0x00) {
            return $len;
        }

        if (($len & 0xC0) === 0x80) {
            $c2 = ord(fread($this->socket, 1));
            return (($len & ~0xC0) << 8) + $c2;
        }

        if (($len & 0xE0) === 0xC0) {
            $c2 = ord(fread($this->socket, 1));
            $c3 = ord(fread($this->socket, 1));
            return (($len & ~0xE0) << 16) + ($c2 << 8) + $c3;
        }

        if (($len & 0xF0) === 0xE0) {
            $c2 = ord(fread($this->socket, 1));
            $c3 = ord(fread($this->socket, 1));
            $c4 = ord(fread($this->socket, 1));
            return (($len & ~0xF0) << 24) + ($c2 << 16) + ($c3 << 8) + $c4;
        }

        // 0xF0
        $c2 = ord(fread($this->socket, 1));
        $c3 = ord(fread($this->socket, 1));
        $c4 = ord(fread($this->socket, 1));
        $c5 = ord(fread($this->socket, 1));
        return ($c2 << 24) + ($c3 << 16) + ($c4 << 8) + $c5;
    }
}
