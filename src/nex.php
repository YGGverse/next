<?php

// Load dependencies
require_once __DIR__ .
             DIRECTORY_SEPARATOR . '..'.
             DIRECTORY_SEPARATOR . 'vendor' .
             DIRECTORY_SEPARATOR . 'autoload.php';

// Parse startup arguments
foreach ((array) $argv as $item)
{
    if (preg_match('/^(?<key>[^=]+)=(?<value>.*)$/', $item, $argument))
    {
        switch ($argument['key'])
        {
            case 'host':

                define(
                    'NEXT_HOST',
                    (string) $argument['value']
                );

            break;

            case 'port':

                define(
                    'NEXT_PORT',
                    (int) $argument['value']
                );

            break;

            case 'path':

                $path = rtrim(
                    (string) $argument['value'],
                    DIRECTORY_SEPARATOR
                ) . DIRECTORY_SEPARATOR;

                if (!str_starts_with($path, DIRECTORY_SEPARATOR))
                {
                    print(
                        _('absolute path required')
                    ) . PHP_EOL;

                    exit;
                }

                if (!is_dir($path) || !is_readable($path))
                {
                    print(
                        _('path not accessible')
                    ) . PHP_EOL;

                    exit;
                }

                define(
                    'NEXT_PATH',
                    (string) $path
                );

            break;

            case 'file':

                define(
                    'NEXT_FILE',
                    (string) $argument['value']
                );

            break;

            case 'fail':

                $fail = rtrim(
                    (string) $argument['value'],
                    DIRECTORY_SEPARATOR
                ) . DIRECTORY_SEPARATOR;

                if (!str_starts_with($fail, DIRECTORY_SEPARATOR))
                {
                    print(
                        _('absolute path required')
                    ) . PHP_EOL;

                    exit;
                }

                if (!is_file($fail) || !is_readable($fail))
                {
                    print(
                        _('fail template not accessible')
                    ) . PHP_EOL;

                    exit;
                }

                define(
                    'NEXT_FAIL',
                    (string) file_get_contents(
                        $fail
                    )
                );

            break;

            case 'list':

                define(
                    'NEXT_LIST',
                    in_array(
                        mb_strtolower(
                            (string) $argument['value']
                        ),
                        [
                            'true',
                            'yes',
                            '1'
                        ]
                    )
                );

            break;

            case 'size':

                define(
                    'NEXT_SIZE',
                    (int) $argument['value']
                );

            break;

            case 'dump':

                define(
                    'NEXT_DUMP',
                    (string) $argument['value']
                );

            break;
        }
    }
}

// Validate required arguments and set optional defaults
if (!defined('NEXT_HOST')) define('NEXT_HOST', '127.0.0.1');

if (!defined('NEXT_PORT')) define('NEXT_PORT', 1900);

if (!defined('NEXT_PATH'))
{
    print(
        _('path required')
    ) . PHP_EOL;

    exit;
}

if (!defined('NEXT_FILE')) define('NEXT_FILE', false);

if (!defined('NEXT_LIST')) define('NEXT_LIST', true);

if (!defined('NEXT_SIZE')) define('NEXT_SIZE', 1024);

if (!defined('NEXT_FAIL')) define('NEXT_FAIL', 'fail');

if (!defined('NEXT_DUMP')) define('NEXT_DUMP', '[{time}] [{code}] {host}:{port} {path} {goal}');

// Init server
$server = new \Yggverse\Nex\Server(
    NEXT_HOST,
    NEXT_PORT,
    NEXT_SIZE
);

$server->start(
    function (
        string $request,
        string $connect
    ): ?string
    {
        // Filter goal request
        $goal = preg_replace(
            [
                '/\\\/',         // unify separators
                '/(^|\/)[\.]+/', // hidden items started with dot
                '/[\.]+\//',     // relative directory paths
                '/[\/]+\//',     // remove extra slashes
            ],
            DIRECTORY_SEPARATOR,
            NEXT_PATH . filter_var(
                $request,
                FILTER_SANITIZE_URL
            )
        );

        // Define response
        $response = null;

        // Directory request
        if (is_dir($goal))
        {
            // Try index file first on enabled
            if (NEXT_FILE && is_readable($goal . NEXT_FILE))
            {
                $response = file_get_contents(
                    $goal . NEXT_FILE
                );
            }

            // Try directory listing on enabled
            else if (NEXT_LIST && is_readable($goal))
            {
                $links = [];

                foreach ((array) scandir($goal) as $link)
                {
                    // Skip hidden entities and make sure the destination is accessible
                    if (!str_starts_with($link, '.') && is_readable($goal . $link))
                    {
                        // Directory
                        if (is_dir($link))
                        {
                            $links[] = sprintf(
                                '=> %s/',
                                $link
                            );
                        }

                        // File
                        else
                        {
                            $links[] = sprintf(
                                '=> %s',
                                $link
                            );
                        }
                    }
                }

                $response = implode(
                    PHP_EOL,
                    $links
                );
            }
        }

        // Try file
        else if (is_readable($goal))
        {
            $response = file_get_contents(
                $goal
            );
        }

        // Dump request on enabled
        if (NEXT_DUMP)
        {
            printf(
                str_ireplace(
                    [
                        '{time}',
                        '{code}',
                        '{host}',
                        '{port}',
                        '{path}',
                        '{goal}',
                    ],
                    [
                        date('c'),
                        (int) !empty($response),
                        parse_url($connect, PHP_URL_HOST),
                        parse_url($connect, PHP_URL_PORT),
                        empty($request) ? '/' : trim($request),
                        $goal
                    ],
                    NEXT_DUMP
                ) . PHP_EOL
            );
        }

        // Send response
        return empty($response) ? NEXT_FAIL : $response;
    }
);