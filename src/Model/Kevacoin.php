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

    // Get record by key
    public function get(
        string $key
    ): ?array
    {
        // Skip meta entities
        if (str_starts_with($key, '_'))
        {
            return null;
        }

        // Get record
        return $this->_client->kevaGet(
            $this->_namespace,
            $key
        );
    }

    // Find records by key query
    public function find(
        string $query
    ): array
    {
        $records = [];

        foreach ((array) $this->_client->kevaFilter($this->_namespace, $query) as $record)
        {
            // Skip meta entities
            if (str_starts_with($record['key'], '_'))
            {
                continue;
            }

            // Append record
            $records[] = $record;
        }

        return $records;
    }

    // Get record value by key
    public function getValue(
        string $key
    ): ?string
    {
        // Use common method
        if ($record = $this->get($key))
        {
            return $record['value'];
        }

        return null;
    }

    // Build records as the FS tree by virtual path prefix in keys
    public function getTree(
        string $prefix
    ): array
    {
        $list = [];

        foreach ($this->find($prefix) as $record)
        {
            // Remove prefix from path
            $path = trim(
                str_replace(
                    $prefix,
                    '',
                    $record['key']
                ),
                '/'
            );

            // Parse segments
            if ($part = explode('/', $path))
            {
                // Append this level segments only
                $list[] =
                [
                    'name' => $part[0],
                    'link' => urlencode(
                        $part[0]
                    ),
                    // if more than 1 segment, the item is virtual directory
                    'file' => count(
                        $part
                    ) == 1
                ];
            }
        }

        return $list;
    }
}