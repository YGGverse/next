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
        // Init wallet connection
        $this->_client = new \Kevachat\Kevacoin\Client(
            $scheme,
            $host,
            $port,
            $user,
            $password
        );

        // Check connection using balance request
        if (!is_float($this->_client->getBalance()))
        {
            throw new \Exception(
                _('could not connect kevacoin wallet!')
            );
        }

        // Check namespace given exists
        if (is_null($this->_client->kevaFilter($namespace, '', 0, 0, 1)))
        {
            throw new \Exception(
                _('could not find requested namespace!')
            );
        }

        // Init namespace
        $this->_namespace = $namespace;
    }
}