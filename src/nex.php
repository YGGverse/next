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

if (!defined('NEXT_DUMP')) define('NEXT_DUMP', '[{time}] [{code}] {host}:{port} {path} {real} {size} bytes');

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
            NEXT_PATH .
            urldecode(
                filter_var(
                    $request,
                    FILTER_SANITIZE_URL
                )
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

        // Validate realpath exists, started with path defined and does not contain hidden entities
        if ($realpath && str_starts_with($realpath, NEXT_PATH) && false === strpos($realpath, DIRECTORY_SEPARATOR . '.'))
        {
            // Try directory
            if (is_dir($realpath))
            {
                // Try index file on enabled
                if (NEXT_FILE && file_exists($realpath . NEXT_FILE) && is_readable($realpath . NEXT_FILE))
                {
                    $response = file_get_contents(
                        $realpath . NEXT_FILE
                    );
                }

                // Try directory listing on enabled
                else if (NEXT_LIST)
                {
                    $directories = [];

                    $files = [];

                    foreach ((array) scandir($realpath) as $filename)
                    {
                        // Process system entities
                        if (str_starts_with($filename, '.'))
                        {
                            // Parent navigation
                            if ($filename == '..' && $parent = realpath($realpath . $filename))
                            {
                                $parent = rtrim(
                                    $parent,
                                    DIRECTORY_SEPARATOR
                                ) . DIRECTORY_SEPARATOR;

                                if (str_starts_with($parent, NEXT_PATH))
                                {
                                    $directories[$filename] = '=> ../';
                                }
                            }

                            continue; // skip everything else
                        }

                        // Directory
                        if (is_dir($realpath . $filename))
                        {
                            if (is_readable($realpath . $filename))
                            {
                                $directories[$filename] = sprintf(
                                    '=> %s/',
                                    urlencode(
                                        $filename
                                    )
                                );
                            }

                            continue;
                        }

                        // File
                        if (is_readable($realpath . $filename))
                        {
                            $files[$filename] = sprintf(
                                '=> %s',
                                urlencode(
                                    $filename
                                )
                            );
                        }
                    }

                    // Sort by keys ASC
                    ksort(
                        $directories,
                        SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL
                    );

                    ksort(
                        $files,
                        SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL
                    );

                    // Merge items
                    $response = implode(
                        PHP_EOL,
                        array_merge(
                            $directories,
                            $files
                        )
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
            // Build connection URL #72811
            $url = sprintf(
                'nex://%s',
                $connect
            );

            // Print dump from template
            printf(
                str_ireplace(
                    [
                        '{time}',
                        '{code}',
                        '{host}',
                        '{port}',
                        '{path}',
                        '{real}',
                        '{size}'
                    ],
                    [
                        (string) date('c'),
                        (string) (int) !empty($response),
                        (string) parse_url($url, PHP_URL_HOST),
                        (string) parse_url($url, PHP_URL_PORT),
                        (string) str_replace('%', '%%', empty($request)  ? '/' : $request),
                        (string) str_replace('%', '%%', empty($realpath) ? '!' : $realpath),
                        (string) mb_strlen((string) $response)
                    ],
                    NEXT_DUMP
                ) . PHP_EOL
            );
        }

        // Send response
        return is_string($response) ? $response : NEXT_FAIL;
    }
);