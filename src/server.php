<?php

// Load dependencies
require_once __DIR__ .
             DIRECTORY_SEPARATOR . '..'.
             DIRECTORY_SEPARATOR . 'vendor' .
             DIRECTORY_SEPARATOR . 'autoload.php';

// Init environment
$environment = new \Yggverse\Next\Model\Environment(
    $argv,
    json_decode(
        file_get_contents(
            __DIR__ .
            DIRECTORY_SEPARATOR . '..'.
            DIRECTORY_SEPARATOR . 'default.json'
        ),
        true
    )
);

// Init filesystem
$filesystem = new \Yggverse\Next\Model\Filesystem(
    $environment->get('path')
);

// Start server
try
{
    switch ($environment->get('type'))
    {
        case 'nex':

            $server = \Ratchet\Server\IoServer::factory(
                new \Yggverse\Next\Controller\Nex(
                    $environment,
                    $filesystem
                ),
                $environment->get('port'),
                $environment->get('host')
            );

            $server->run();

        break;

        default:

            throw new \Exception(
                _('valid server type required!')
            );
    }
}

// Show help
catch (\Exception $exception)
{
    // @TODO
}
