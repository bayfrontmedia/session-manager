<?php

/**
 * @package sesson-manager
 * @link https://github.com/bayfrontmedia/sesson-manager
 * @author John Robinson <john@bayfrontmedia.com>
 * @copyright 2020 Bayfront Media
 */

namespace Bayfront\SessionManager\Handlers;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use SessionHandlerInterface;

class Flysystem implements SessionHandlerInterface
{

    protected $storage;

    protected $root;

    public function __construct(Filesystem $storage, string $root = '')
    {

        $this->storage = $storage;

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
        return true;
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

        try {

            $read = $this->storage->read($this->root . '/sess_' . $session_id);

        } catch (FileNotFoundException $e) {
            return '';
        }

        if ($read) {
            return $read;
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
        return $this->storage->put($this->root . '/sess_' . $session_id, $data);
    }

    /**
     * @param string $session_id
     *
     * @return bool
     */

    public function destroy($session_id): bool
    {

        if ($this->storage->has($this->root . '/sess_' . $session_id)) {

            try {

                return $this->storage->delete($this->root . '/sess_' . $session_id);

            } catch (FileNotFoundException $e) {
                return false;
            }

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

        $contents = $this->storage->listContents($this->root);

        foreach ($contents as $content) {

            // If a file and name begins with "sess_"

            if ($content['type'] == 'file' && strpos($content['basename'], 'sess_') !== false) {

                /*
                 * Using $content['basename']:
                 *
                 * $content['filename'] should also work, but it's unknown if available
                 * in all the Flysystem adapters.
                 */

                try {

                    $timestamp = $this->storage->getTimestamp($this->root . '/' . $content['basename']);

                    if ($timestamp && $timestamp < time() - $lifetime) {

                        $this->storage->delete($this->root . '/' . $content['basename']);

                    }

                } catch (FileNotFoundException $e) {

                    break;

                }

            }

        }

        return true;

    }

}