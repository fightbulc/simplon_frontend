<?php

namespace Simplon\Frontend\Interfaces;

/**
 * SessionStorageInterface
 * @package Simplon\Frontend\Interfaces
 * @author  Tino Ehrich (tino@bigpun.me)
 */
interface SessionStorageInterface
{
    /**
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    public function set($key, $data);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function del($key);

    /**
     * @return bool
     */
    public function destroy();
}