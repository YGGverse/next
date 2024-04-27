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

if (!defined('NEXT_DUMP')) define('NEXT_DUMP', '[{time}] [{code}] {host}:{port} {path} {real}');

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
        // Define response
        $response = null;

        // Filter request
        $request = trim(
            $request
        );

        // Build realpath
        $realpath = realpath(
            NEXT_PATH . filter_var(
                urldecode(
                    $request
                ),
                FILTER_SANITIZE_URL
            )
        );

        // Make sure directory path ending with slash
        if (is_dir($realpath))
        {
            $realpath = rtrim(
                $realpath,
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR;
        }

        // Validate realpath exists, started with path defined and not contains hidden entities
        if ($realpath && str_starts_with($realpath, NEXT_PATH) && false === strpos($realpath, DIRECTORY_SEPARATOR . '.'))
        {
            // Try directory
            if (is_dir($realpath))
            {
                // Try index file first on enabled
                if (NEXT_FILE && file_exists($realpath . NEXT_FILE) && is_readable($realpath . NEXT_FILE))
                {
                    $response = file_get_contents(
                        $realpath . NEXT_FILE
                    );
                }

                // Try build directory listing on enabled
                else if (NEXT_LIST)
                {
                    $links = [];

                    foreach ((array) scandir($realpath) as $link)
                    {
                        // Process system entities
                        if (str_starts_with($link, '.'))
                        {
                            // Parent navigation
                            if ($link == '..' && $parent = realpath($realpath . $link))
                            {
                                $parent = rtrim(
                                    $parent,
                                    DIRECTORY_SEPARATOR
                                ) . DIRECTORY_SEPARATOR;

                                if (str_starts_with($parent, NEXT_PATH))
                                {
                                    $links[] = '=> ../';
                                }
                            }

                            continue; // skip everything else
                        }

                        // Directory
                        if (is_dir($realpath . $link))
                        {
                            if (is_readable($realpath . $link))
                            {
                                $links[] = sprintf(
                                    '=> %s/',
                                    urlencode(
                                        $link
                                    )
                                );
                            }

                            continue;
                        }

                        // File
                        if (is_readable($realpath . $link))
                        {
                            $links[] = sprintf(
                                '=> %s',
                                urlencode(
                                    $link
                                )
                            );
                        }
                    }

                    $response = implode(
                        PHP_EOL,
                        $links
                    );
                }
            }

            // Try file
            else
            {
                $response = file_get_contents(
                    $realpath
                );
            }
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
                        '{real}',
                    ],
                    [
                        (string) date('c'),
                        (string) (int) !empty($response),
                        (string) parse_url($connect, PHP_URL_HOST),
                        (string) parse_url($connect, PHP_URL_PORT),
                        (string) str_replace('%', '%%', empty($request) ? '/' : $request),
                        (string) str_replace('%', '%%', $realpath)
                    ],
                    NEXT_DUMP
                ) . PHP_EOL
            );
        }

        // Send response
        return is_null($response) ? NEXT_FAIL : $response;
    }
);