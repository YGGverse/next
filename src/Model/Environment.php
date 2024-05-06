<?php

declare(strict_types=1);

namespace Yggverse\Next\Model;

class Environment
{
    private array $_config;

    public function __construct(
        array $argv,
        array $default = []
    ) {
        foreach ($default as $key => $value)
        {
            $this->_config[$key] = (string) $value;
        }

        foreach ($argv as $value)
        {
            if (preg_match('/^(?<key>[^=]+)=(?<value>.*)$/', $value, $argument))
            {
                $this->_config[mb_strtolower($argument['key'])] = (string) $argument['value'];
            }
        }
    }

    public function get(
        string $key
    ): mixed
    {
        $key = mb_strtolower(
            $key
        );

        return isset($this->_config[$key]) ? $this->_config[$key]
                                           : null;
    }

    public function set(
        string $key,
        string $value,
        bool   $semantic = true
    ): void
    {
        if ($semantic)
        {
            $_value = mb_strtolower(
                $value
            );

            switch (true)
            {
                case in_array(
                    $_value,
                    [
                        '1',
                        'yes',
                        'true',
                        'enable'
                    ]
                ): $value = true;

                break;

                case in_array(
                    $_value,
                    [
                        '0',
                        'no',
                        'null',
                        'false',
                        'disable'
                    ]
                ): $value = false;

                break;
            }
        }

        $this->_config[mb_strtolower($key)] = $value;
    }
}