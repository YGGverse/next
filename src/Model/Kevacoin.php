<?php

declare(strict_types=1);

namespace Yggverse\Next\Model;

class Kevacoin
{
    private \Kevachat\Kevacoin\Client $_client;

    private string $_namespace;

    public function __construct(
        string $scheme,
        string $user,
        string $password,
        string $host,
        int    $port,
        string $namespace
    ) {
        // Connect wallet
        $this->_client = new \Kevachat\Kevacoin\Client(
            $scheme,
            $host,
            $port,
            $username,
            $password
        );

        // Check namespace given exists
        if (!$this->_namespace = $this->_client->kevaFilter($namespace))
        {
            throw new \Exception(
                _('could not find requested namespace!')
            );
        }
    }
}