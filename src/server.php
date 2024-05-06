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
    $environment->get('root')
);

// Start server
try
{
    switch ($environment->get('type'))
    {
        case 'nex':

            switch ($environment->get('mode'))
            {
                case 'fs':

                    $controller = new \Yggverse\Next\Controller\Nex\Filesystem(
                        $environment,
                        $filesystem
                    );

                break;

                default:

                    throw new \Exception(
                        _('unsupported mode for nex server type!')
                    );
            }

            $server = \Ratchet\Server\IoServer::factory(
                $controller,
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
    print(
        $exception->getMessage()
    ) . PHP_EOL;
}
