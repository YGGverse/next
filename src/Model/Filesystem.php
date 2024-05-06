<?php

declare(strict_types=1);

namespace Yggverse\Next\Model;

class Filesystem
{
    private string $_root;

    public function __construct(?string $path)
    {
        // Require path value to continue
        if (empty($path))
        {
            throw new \Exception(
                _('root path required!')
            );
        }

        // Require absolute path
        if (!str_starts_with($path, DIRECTORY_SEPARATOR))
        {
            throw new \Exception(
                _('root path not absolute!')
            );
        }

        // Exclude symlinks and relative entities, append slash
        if (!$realpath = $this->_realpath($path))
        {
            throw new \Exception(
                _('could not build root realpath!')
            );
        }

        // Root must be directory
        if (!is_dir($realpath))
        {
            throw new \Exception(
                _('root path is not directory!')
            );
        }

        // Root is readable
        if (!is_readable($realpath))
        {
            throw new \Exception(
                _('root path is not readable!')
            );
        }

        // Check root path does not contain hidden context
        if (str_contains($realpath, DIRECTORY_SEPARATOR . '.'))
        {
            throw new \Exception(
                _('root path must not contain hidden context!')
            );
        }

        // Done!
        $this->_root = $realpath;
    }

    public function root(): string
    {
        return $this->_root;
    }

    public function file(?string $realpath): ?string
    {
        if (!$this->valid($realpath))
        {
            return null;
        }

        if (!is_file($realpath))
        {
            return null;
        }

        return file_get_contents(
            $realpath
        );
    }

    public function list(
        ?string $realpath,
         string $sort   = 'name',
         int    $order  = SORT_ASC,
         int    $method = SORT_STRING | SORT_NATURAL | SORT_FLAG_CASE
    ): ?array
    {
        // Validate requested path
        if (!$this->valid($realpath))
        {
            return null;
        }

        // Make sure requested path is directory
        if (!is_dir($realpath))
        {
            return null;
        }

        // Begin list builder
        $directories = [];
        $files = [];

        foreach ((array) scandir($realpath) as $name)
        {
            // Skip system locations
            if (empty($name) || $name == '.')
            {
                continue;
            }

            // Build destination path
            if (!$path = $this->_realpath($realpath . $name))
            {
                continue;
            }

            // Validate destination path
            if (!$this->valid($path))
            {
                continue;
            }

            // Context
            switch (true)
            {
                case is_dir($path):

                    $directories[] =
                    [
                        'file' => false,
                        'path' => $path,
                        'name' => $name,
                        'link' => urlencode(
                            $name
                        ),
                        'time' => filemtime(
                            $path
                        )
                    ];

                break;

                case is_file($path):

                    $files[] =
                    [
                        'file' => true,
                        'path' => $path,
                        'name' => $name,
                        'link' => urlencode(
                            $name
                        ),
                        'time' => filemtime(
                            $path
                        )
                    ];

                break;
            }
        }

        // Sort order
        array_multisort(
            array_column(
                $directories,
                $sort
            ),
            $order,
            $method,
            $directories
        );

        // Sort files by name ASC
        array_multisort(
            array_column(
                $directories,
                $sort
            ),
            $order,
            $method,
            $directories
        );

        // Merge list
        return array_merge(
            $directories,
            $files
        );
    }

    public function valid(?string $realpath): bool
    {
        if (empty($realpath))
        {
            return false;
        }

        if ($realpath != $this->_realpath($realpath))
        {
            return false;
        }

        if (!str_starts_with($realpath, $this->_root))
        {
            return false;
        }

        if (str_contains($realpath, DIRECTORY_SEPARATOR . '.'))
        {
            return false;
        }

        if (!is_readable($realpath))
        {
            return false;
        }

        return true;
    }

    // Return absolute realpath with root constructed
    public function absolute(?string $path): ?string
    {
        if (!$realpath = $this->_realpath($this->_root . $path))
        {
            return null;
        }

        if (!$this->valid($realpath))
        {
            return null;
        }

        return $realpath;
    }

    // PHP::realpath extension appending slash to dir paths
    private function _realpath(?string $path): ?string
    {
        if (empty($path))
        {
            return null;
        }

        if (!$realpath = realpath($path))
        {
            return null;
        }

        if (is_dir($realpath))
        {
            $realpath = rtrim(
                $realpath,
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR;
        }

        return $realpath;
    }
}