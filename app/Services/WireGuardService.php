<?php

namespace App\Services;

class WireGuardService
{
    public function generateKeys(): array
    {
        $private = trim(shell_exec('wg genkey'));
        $public  = trim(shell_exec("echo $private | wg pubkey"));

        return [
            'private' => $private,
            'public'  => $public,
        ];
    }
}
