<?php

namespace Bayfront\SessionManager;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Cookies\Cookie;
use SessionHandlerInterface;

class Session
{

    protected SessionHandlerInterface $handler;

    protected array $config;

    protected array $flash_data = [];

    protected static bool $session_started = false;

    public function __construct(SessionHandlerInterface $handler, array $config)
    {

        $this->handler = $handler;

        $default_config = [
            'cookie_name' => 'bfm_sess',
            'cookie_path' => '/',
            'cookie_domain' => '',
            'cookie_secure' => true,
            'cookie_http_only' => true,
            'cookie_same_site' => 'Lax', // None, Lax or Strict
            'sess_regenerate_duration' => 300, // 0 to disable
            'sess_lifetime' => 3600, // 0 "until the browser is closed"
            'sess_gc_probability' => 1, // 0 to disable garbage collection
            'sess_gc_divisor' => 100
        ];

        $this->config = Arr::only(array_merge($default_config, $config), [
            'cookie_name',
            'cookie_path',
            'cookie_domain',
            'cookie_secure',
            'cookie_http_only',
            'cookie_same_site',
            'sess_regenerate_duration',
            'sess_lifetime',
            'sess_gc_probability',
            'sess_gc_divisor'
        ]);

    }

    /**
     * Start new session if an existing session does not already exist.
     *
     * @return self
     */

    protected function _start(): self
    {

        if (!self::$session_started) {

            session_start([
                'name' => $this->config['cookie_name'],
                'gc_probability' => $this->config['sess_gc_probability'],
                'gc_divisor' => $this->config['sess_gc_divisor'],
                'gc_maxlifetime' => $this->config['sess_lifetime']
            ]);

            self::$session_started = true;

        }

        // -------------------- Regenerate --------------------

        if (!$this->has('__sess.last_regenerate')) {

            $this->set('__sess.last_regenerate', time());

        }

        if ($this->config['sess_regenerate_duration'] > 0
            && $this->get('__sess.last_regenerate') < time() - $this->config['sess_regenerate_duration']) { // Needs to regenerate

            $this->regenerate(true);

        }

        // -------------------- Check expired --------------------

        if ($this->has('__sess.last_active')
            && $this->get('__sess.last_active') < time() - $this->config['sess_lifetime']) {

            $this->startNew();

        }

        $this->set('__sess.last_active', time());

        // -------------------- Flash data --------------------

        $this->flash_data = $this->get('__sess.flash_data', []); // Empty array if not existing

        $this->forget('__sess.flash_data'); // Remove array if existing

        return $this;

    }

    /**
     * Start a new session.
     *
     * @return self
     */
    public function start(): self
    {

        session_set_save_handler($this->handler, true);

        session_set_cookie_params([
            'lifetime' => $this->config['sess_lifetime'],
            'path' => $this->config['cookie_path'],
            'domain' => $this->config['cookie_domain'],
            'secure' => $this->config['cookie_secure'],
            'httponly' => $this->config['cookie_http_only'],
            'samesite' => $this->config['cookie_same_site']
        ]);

        return $this->_start();

    }

    /**
     * Destroy existing and start a new session.
     *
     * @return self
     */

    public function startNew(): self
    {
        return $this->destroy()->_start();
    }

    /**
     * Regenerate new session ID.
     *
     * When $delete_old_session = TRUE, the old session file will be deleted.
     *
     * @param bool $delete_old_session
     *
     * @return self
     */

    public function regenerate(bool $delete_old_session = false): self
    {

        session_regenerate_id($delete_old_session);

        $this->set('__sess.last_regenerate', time());

        return $this;

    }

    /**
     * Destroy the current session file and cookie.
     *
     * @return self
     */

    public function destroy(): self
    {

        session_unset();

        session_destroy();

        self::$session_started = false;

        $_SESSION = []; // Manually clear for this request

        Cookie::forget($this->config['cookie_name']); // Remove cookie

        return $this;

    }

    /**
     * Return current session ID
     *
     * @return string
     */

    public function getId(): string
    {
        return session_id();
    }

    /**
     * Return the last active time of the session.
     *
     * @return int
     */

    public function getLastActive(): int
    {
        return $this->get('__sess.last_active');
    }

    /**
     * Return the last regenerated time of the session.
     *
     * @return int
     */

    public function getLastRegenerate(): int
    {
        return $this->get('__sess.last_regenerate');
    }

    /**
     * Returns value of single $_SESSION array key in dot notation, or entire array, with optional default value.
     *
     * @param string|null $key (Returns the entire array when NULL)
     * @param mixed|null $default
     *
     * @return mixed
     */

    public function get(string $key = NULL, mixed $default = NULL): mixed
    {
        if (NULL === $key) {
            return $_SESSION;
        }

        return Arr::get($_SESSION, $key, $default);

    }

    /**
     * Checks if $_SESSION array key exists in dot notation.
     *
     * @param string $key
     *
     * @return bool
     */

    public function has(string $key): bool
    {
        return Arr::has($_SESSION, $key);
    }

    /**
     * Sets a value for a $_SESSION key in dot notation.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */

    public function set(string $key, mixed $value): self
    {

        Arr::set($_SESSION, $key, $value);

        return $this;

    }

    /**
     * Remove a single key, or an array of keys from the $_SESSION array using dot notation.
     *
     * @param array|string $keys
     *
     * @return self
     */

    public function forget(array|string $keys): self
    {

        Arr::forget($_SESSION, $keys);

        return $this;

    }

    /*
     * ############################################################
     * Flash data
     * ############################################################
     */

    /**
     * Sets a value for flash data in dot notation.
     *
     * Flash data is available immediately and during the subsequent request.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */

    public function flash(string $key, mixed $value): self
    {

        $this->set('__sess.flash_data.' . $key, $value);

        $this->flash_data[$key] = $value; // Make available for current request

        return $this;

    }

    /**
     * Returns value of single flash data key in dot notation, or entire array, with optional default value.
     *
     * @param string|null $key (Returns the entire flash array when NULL)
     * @param null $default
     *
     * @return mixed
     */

    public function getFlash(string $key = NULL, $default = NULL): mixed
    {

        if (NULL === $key) {
            return $this->flash_data;
        }

        return Arr::get($this->flash_data, $key, $default);

    }

    /**
     * Checks if flash data key exists in dot notation.
     *
     * @param string $key
     *
     * @return bool
     */

    public function hasFlash(string $key): bool
    {
        return Arr::has($this->flash_data, $key);
    }

    /**
     * Keeps specific flash data keys available for the subsequent request.
     *
     * @param array $keys
     *
     * @return self
     */

    public function keepFlash(array $keys): self
    {

        foreach ($keys as $key) {

            if ($this->hasFlash($key)) {

                $this->flash($key, $this->getFlash($key));

            }

        }

        return $this;

    }

    /**
     * Keeps all flash data keys available for the subsequent request.
     *
     * @return self
     */

    public function reflash(): self
    {

        foreach ($this->flash_data as $k => $v) {

            $this->flash($k, $v);

        }

        return $this;

    }

}