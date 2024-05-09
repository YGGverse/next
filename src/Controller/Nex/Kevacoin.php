<?php

namespace Yggverse\Next\Controller\Nex;

class Kevacoin extends \Yggverse\Next\Abstract\Type\Nex
{
    private \Yggverse\Next\Model\Kevacoin $_kevacoin;

    public function init()
    {
        // Validate environment arguments defined for this type
        if (!$this->_environment->get('rpcscheme'))
        {
            throw new \Exception(
                _('rpcscheme argument required!')
            );
        }

        if (!$this->_environment->get('rpchost'))
        {
            throw new \Exception(
                _('rpchost argument required!')
            );
        }

        if (!$this->_environment->get('rpcport'))
        {
            throw new \Exception(
                _('rpcport argument required!')
            );
        }

        if (!$this->_environment->get('rpcuser'))
        {
            throw new \Exception(
                _('rpcuser argument required!')
            );
        }

        if (!$this->_environment->get('rpcpassword'))
        {
            throw new \Exception(
                _('rpcpassword argument required!')
            );
        }

        if (!$this->_environment->get('namespace'))
        {
            throw new \Exception(
                _('namespace argument required!')
            );
        }

        // Init KevaCoin
        $this->_kevacoin = new \Yggverse\Next\Model\Kevacoin(
            $this->_environment->get('rpcscheme'),
            $this->_environment->get('rpcuser'),
            $this->_environment->get('rpcpassword'),
            $this->_environment->get('rpchost'),
            $this->_environment->get('rpcport'),
            $this->_environment->get('namespace')
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
                        '{rpcscheme}',
                        '{rpchost}',
                        '{rpcport}',
                        '{namespace}'
                    ],
                    [
                        (string) date('c'),
                        (string) $this->_environment->get('host'),
                        (string) $this->_environment->get('port'),
                        (string) $this->_environment->get('port'),
                        (string) $this->_environment->get('rpcscheme'),
                        (string) $this->_environment->get('rpchost'),
                        (string) $this->_environment->get('rpcport'),
                        (string) $this->_environment->get('namespace')
                    ],
                    _('[{time}] [init] kevacoin server at nex://{host}:{port} connected to {rpcscheme}://{rpchost}:{rpcport}/{namespace}')
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

        // Route
        switch (true)
        {
            // Try single record
            case $value = $this->_kevacoin->getValue($request):

                $response = $value;

            break;

            // Try virtual FS tree listing
            case $list = $this->_kevacoin->getTree($request):

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
                        '{size}'
                    ],
                    [
                        (string) date('c'),
                        (string) $connection->remoteAddress,
                        (string) $connection->resourceId,
                        (string) $request,
                        (string) mb_strlen(
                            $response
                        )
                    ],
                    _('[{time}] [message] incoming connection {host}#{crid} "{path}" {size} bytes')
                ) . PHP_EOL
            );
        }

        // Noting to return?
        if (empty($response))
        {
            // Try failure file on defined
            /* @TODO fix
            if ($fail = $this->_filesystem->file($this->_environment->get('fail')))
            {
                $response = $fail;
            }
            */
        }

        // Send response
        $connection->send(
            $response
        );

        // Disconnect
        $connection->close();
    }
}