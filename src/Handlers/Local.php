<?php

/**
 * @package sesson-manager
 * @link https://github.com/bayfrontmedia/sesson-manager
 * @author John Robinson <john@bayfrontmedia.com>
 * @copyright 2020 Bayfront Media
 */

namespace Bayfront\SessionManager\Handlers;

use SessionHandlerInterface;

class Local implements SessionHandlerInterface
{

    protected $root;

    public function __construct(string $root = '')
    {
        $this->root = '/' . trim($root, '/'); // Trim slashes
    }

    /**
     * @param string $save_path
     * @param string $session_name (Name of cookie to be set)
     *
     * @return bool
     */

    public function open($save_path, $session_name): bool
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
     * @param string $session_id
     *
     * @return string
     */

    public function read($session_id)
    {

        if (file_exists($this->root . '/sess_' . $session_id)) {

            $read = file_get_contents($this->root . '/sess_' . $session_id);

            if ($read) {
                return $read;
            }

        }

        return '';

    }

    /**
     * @param string $session_id
     * @param string $data
     *
     * @return bool
     */

    public function write($session_id, $data): bool
    {
        return file_put_contents($this->root . '/sess_' . $session_id, $data);
    }

    /**
     * @param string $session_id
     *
     * @return bool
     */

    public function destroy($session_id): bool
    {

        if (file_exists($this->root . '/sess_' . $session_id)) {

            unlink($this->root . '/sess_' . $session_id);

        }

        return true;

    }

    /**
     * This method should always return TRUE, even if no file was destroyed.
     *
     * @param int $lifetime
     *
     * @return bool
     */

    public function gc($lifetime): bool
    {

        foreach (glob($this->root . '/sess_*') as $file) {

            if (filemtime($file) < time() - $lifetime) {

                unlink($file);

            }

        }

        return true;

    }

}