<?php

namespace Bayfront\SessionManager\Handlers;

use SessionHandlerInterface;

class LocalHandler implements SessionHandlerInterface
{

    protected string $root;

    public function __construct(string $root = '')
    {
        $this->root = '/' . trim($root, '/'); // Trim slashes
    }

    /**
     * @param string $path
     * @param string $name (Name of cookie to be set)
     * @return bool
     */
    public function open(string $path, string $name): bool
    {

        if (!is_dir($this->root)) {

            $dir = mkdir($this->root, 0755, true);

            if (false === $dir) {

                return false;

            }
        }

        if (is_writable($this->root)) {

            return true;

        }

        return false;

    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read(string $id): string
    {

        if (file_exists($this->root . '/sess_' . $id)) {

            $read = file_get_contents($this->root . '/sess_' . $id);

            if ($read) {
                return $read;
            }

        }

        return '';

    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        $result = file_put_contents($this->root . '/sess_' . $id, $data);
        return is_int($result);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {

        if (file_exists($this->root . '/sess_' . $id)) {
            unlink($this->root . '/sess_' . $id);
        }

        return true;

    }

    /**
     * This method should always return TRUE, even if no file was destroyed.
     *
     * @param int $max_lifetime
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {

        $i = 0;

        foreach (glob($this->root . '/sess_*') as $file) {

            if (filemtime($file) < time() - $max_lifetime) {

                unlink($file);
                $i++;

            }

        }

        return $i;

    }

}