<?php

namespace Spoof\Tools;

class Signer
{
    /**
     * @var string The secret key to use when signing.
     */
    private $secret;

    /**
     * Signer constructor.
     */
    public function __construct()
    {
        $this->secret = $_ENV['GITHUB_SECRET'];
    }

    /**
     * Create a signature for the input with the configured secret.
     *
     * @param string $input The string to sign.
     *
     * @return string The sha1 signature for the input, signed with the secret.
     */
    public function sign(string $input): string
    {
        return hash_hmac('sha1', $input, $this->secret);
    }
}
