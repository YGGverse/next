<?php

namespace Yggverse\Next\Abstract\Type;

use \Ratchet\MessageComponentInterface;

abstract class Nex implements MessageComponentInterface
{
    protected \Yggverse\Next\Model\Environment $_environment;

    abstract public function init();

    public function __construct(
        \Yggverse\Next\Model\Environment $environment
    ) {
        if (!$environment->get('port')) $environment->set('port', 1900);

        if ($environment->get('dump'))
        {
            print(
                str_replace(
                    [
                        '{time}',
                        '{host}',
                        '{port}'
                    ],
                    [
                        (string) date('c'),
                        (string) $environment->get('host'),
                        (string) $environment->get('port')
                    ],
                    _('[{time}] [construct] server {host}:{port}')
                ) . PHP_EOL
            );
        }

        $this->_environment = $environment;

        $this->init();
    }

    public function onOpen(
        \Ratchet\ConnectionInterface $connection
    ) {
        if ($this->_environment->get('dump'))
        {
            print(
                str_replace(
                    [
                        '{time}',
                        '{host}',
                        '{crid}'
                    ],
                    [
                        (string) date('c'),
                        (string) $connection->remoteAddress,
                        (string) $connection->resourceId
                    ],
                    _('[{time}] [open] incoming connection {host}#{crid}')
                ) . PHP_EOL
            );
        }
    }

    public function onMessage(
        \Ratchet\ConnectionInterface $connection,
        $request
    ) {
        if ($this->_environment->get('dump'))
        {
            print(
                str_replace(
                    [
                        '{time}',
                        '{host}',
                        '{crid}'
                    ],
                    [
                        (string) date('c'),
                        (string) $connection->remoteAddress,
                        (string) $connection->resourceId
                    ],
                    _('[{time}] [message] incoming connection {host}#{crid}')
                ) . PHP_EOL
            );
        }
    }

    public function onClose(
        \Ratchet\ConnectionInterface $connection
    ) {
        if ($this->_environment->get('dump'))
        {
            print(
                str_replace(
                    [
                        '{time}',
                        '{host}',
                        '{crid}'
                    ],
                    [
                        (string) date('c'),
                        (string) $connection->remoteAddress,
                        (string) $connection->resourceId
                    ],
                    _('[{time}] [close] incoming connection {host}#{crid}')
                ) . PHP_EOL
            );
        }
    }

    public function onError(
        \Ratchet\ConnectionInterface $connection,
        \Exception $exception
    ) {
        if ($this->_environment->get('dump'))
        {
            print(
                str_replace(
                    [
                        '{time}',
                        '{host}',
                        '{crid}',
                        '{info}'
                    ],
                    [
                        (string) date('c'),
                        (string) $connection->remoteAddress,
                        (string) $connection->resourceId,
                        (string) $exception->getMessage()
                    ],
                    _('[{time}] [error] incoming connection {host}#{crid} reason: {info}')
                ) . PHP_EOL
            );
        }

        $connection->close();
    }
}