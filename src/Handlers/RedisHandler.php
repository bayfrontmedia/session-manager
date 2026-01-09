<?php

namespace Bayfront\SessionManager\Handlers;

use Bayfront\SessionManager\HandlerException;
use Redis;
use SessionHandlerInterface;

class RedisHandler implements SessionHandlerInterface
{

    protected Redis $redis;
    protected int $max_lifetime; // In seconds
    protected string $key_prefix;

    public function __construct(Redis $redis, int $max_lifetime, string $key_prefix = '')
    {
        $this->redis = $redis;
        $this->max_lifetime = $max_lifetime;
        $this->key_prefix = $key_prefix;
    }

    /**
     * Get key prefix.
     *
     * @return string
     */
    protected function getKeyPrefix(): string
    {

        $prefix = $this->key_prefix;

        if ($prefix !== '') {
            return rtrim($prefix, ':') . ':';
        }

        return $prefix;

    }

    /**
     * @param string $path
     * @param string $name (Name of cookie to be set)
     *
     * @return bool
     */
    public function open(string $path, string $name): bool
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
     * @param string $id
     * @return string
     */
    public function read(string $id): string
    {
        return $this->redis->get($this->getKeyPrefix() . $id) ?? '';
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        return $this->redis->setex($this->getKeyPrefix() . $id, $this->max_lifetime, $data);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        if ($this->redis->exists($this->getKeyPrefix() . $id)) {
            if ($this->redis->del($this->getKeyPrefix() . $id) !== 1) {
                throw new HandlerException('Redis unable to delete: ' . $this->getKeyPrefix() . $id);
            }
        }

        return true;

        //return $this->redis->del($this->getKeyPrefix() . $id) > 0;
    }

    /**
     * @param int $max_lifetime
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

}