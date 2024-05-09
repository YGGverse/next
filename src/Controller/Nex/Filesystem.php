<?php

namespace Yggverse\Next\Controller\Nex;

class Filesystem extends \Yggverse\Next\Abstract\Type\Nex
{
    private \Yggverse\Next\Model\Filesystem $_filesystem;

    public function init()
    {
        // Validate environment arguments defined for this type
        if (!$this->_environment->get('root'))
        {
            throw new \Exception(
                _('filesystem root path required!')
            );
        }

        // Init filesystem
        $this->_filesystem = new \Yggverse\Next\Model\Filesystem(
            $this->_environment->get('root')
        );

        // Dump event
        if ($this->_environment->get('dump'))
        {
            print(
                str_replace(
                    [
                        '{time}',
                        '{host}',
                        '{port}',
                        '{root}'
                    ],
                    [
                        (string) date('c'),
                        (string) $this->_environment->get('host'),
                        (string) $this->_environment->get('port'),
                        (string) $this->_filesystem->root()
                    ],
                    _('[{time}] [init] filesystem server started at nex://{host}:{port}{root}')
                ) . PHP_EOL
            );
        }
    }

    public function onMessage(
        \Ratchet\ConnectionInterface $connection,
        $request
    ) {
        // Define response
        $response = null;

        // Filter request
        $request = trim(
            urldecode(
                filter_var(
                    $request,
                    FILTER_SANITIZE_URL
                )
            )
        );

        // Build absolute realpath
        $realpath = $this->_filesystem->absolute(
            $request
        );

        // Route
        switch (true)
        {
            // File request
            case $file = $this->_filesystem->file($realpath):

                // Return file content
                $response = $file;

            break;

            // Directory request
            case $list = $this->_filesystem->list($realpath):

                // Try index file on defined
                if ($index = $this->_filesystem->file($realpath . $this->_environment->get('file')))
                {
                    // Return index file content
                    $response = $index;
                }

                // Listing enabled
                else if ($this->_environment->get('list'))
                {
                    // FS map
                    $line = [];

                    foreach ($list as $item)
                    {
                        // Build gemini text link
                        if ($item['link'])
                        {
                            $link =
                            [
                                '=>', // gemtext format
                                $item['file'] ? $item['link']
                                              : $item['link'] . '/'
                            ];

                            // Append modification time on enabled
                            if ($item['time'] && $this->_environment->get('date'))
                            {
                                $link[] = date(
                                    'Y-m-d', // gemfeed format
                                    $item['time']
                                );
                            }

                            // Append alt name on link urlencoded
                            if ($item['name'] != $item['link'])
                            {
                                $link[] = $item['name'];
                            }

                            // Append link to the new line
                            $line[] = implode(
                                ' ',
                                $link
                            );
                        }
                    }

                    // Merge lines to response
                    $response = implode(
                        PHP_EOL,
                        $line
                    );
                }

            break;
        }

        // Dump event
        if ($this->_environment->get('dump'))
        {
            // Print debug from template
            print(
                str_ireplace(
                    [
                        '{time}',
                        '{host}',
                        '{crid}',
                        '{path}',
                        '{real}',
                        '{size}'
                    ],
                    [
                        (string) date('c'),
                        (string) $connection->remoteAddress,
                        (string) $connection->resourceId,
                        (string) $request,
                        (string) $realpath,
                        (string) mb_strlen(
                            $response
                        )
                    ],
                    _('[{time}] [message] incoming connection {host}#{crid} "{path}" > "{real}" {size} bytes')
                ) . PHP_EOL
            );
        }

        // Noting to return?
        if (empty($response))
        {
            // Try failure file on defined
            if ($fail = $this->_filesystem->file($this->_environment->get('fail')))
            {
                $response = $fail;
            }
        }

        // Send response
        $connection->send(
            $response
        );

        // Disconnect
        $connection->close();
    }
}