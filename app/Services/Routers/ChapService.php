<?php

// app/Services/Routers/ChapService.php
namespace App\Services\Routers;

class ChapService
{
    /**
     * RouterOS CHAP response:
     * md5( chap-id (1 byte) + password + chap-challenge (bytes) )
     *
     * @param string $chapId string number like "12" (from $(chap-id))
     * @param string $password plain password
     * @param string $chapChallenge hex string (from $(chap-challenge)) OR raw
     * @param bool $challengeIsHex MikroTik usually provides hex
     */
    public function response(string $chapId, string $password, string $chapChallenge, bool $challengeIsHex = true): string
    {
        $idByte = chr((int)$chapId);

        $challengeBytes = $challengeIsHex
            ? hex2bin(preg_replace('/[^0-9a-fA-F]/', '', $chapChallenge) ?? '') // safe hex cleanup
            : $chapChallenge;

        if ($challengeBytes === false) {
            // fallback: treat as raw
            $challengeBytes = $chapChallenge;
        }

        // raw md5 bytes
        $hash = md5($idByte . $password . $challengeBytes, true);

        // Router expects hex string
        return bin2hex($hash);
    }
}
