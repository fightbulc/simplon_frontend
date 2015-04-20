<?php

namespace Simplon\Frontend\Utils;

use Simplon\Frontend\Interfaces\SessionStorageInterface;

/**
 * SessionStorage
 * @package Simplon\Frontend\Utils
 * @author  Tino Ehrich (tino@bigpun.me)
 */
class SessionStorage implements SessionStorageInterface
{
    /**
     * @param int $sessionTimeoutSeconds
     */
    public function __construct($sessionTimeoutSeconds = 1800)
    {
        // max session lifetime
        ini_set("session.gc_maxlifetime", $sessionTimeoutSeconds);

        // max session cookie lifetime
        ini_set("session.cookie_lifetime", $sessionTimeoutSeconds);

        // start session
        session_start();

        // renew cookie lifetime
        if (isset($_COOKIE[session_name()]))
        {
            setcookie(
                session_name(),
                $_COOKIE[session_name()],
                time() + $sessionTimeoutSeconds,
                '/'
            );
        }
    }

    /**
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    public function set($key, $data)
    {
        $_SESSION[$key] = $data;

        if (isset($_SESSION[$key]) === false)
        {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     *
     * @return null|mixed
     */
    public function get($key)
    {
        if (isset($_SESSION[$key]) === false)
        {
            return null;
        }

        return $_SESSION[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function del($key)
    {
        if (isset($_SESSION[$key]) === true)
        {
            unset($_SESSION[$key]);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        return session_destroy();
    }
}