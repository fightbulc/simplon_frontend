<?php

namespace Simplon\Frontend\Utils;

use Simplon\Frontend\Interfaces\SessionStorageInterface;

/**
 * FlashMessage
 * @package Simplon\Frontend\Utils
 * @author  Tino Ehrich (tino@bigpun.me)
 */
class FlashMessage
{
    const FLASH_MESSAGE_KEY = 'SIMPLON_FRONTEND_FLASH';

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @param SessionStorageInterface $sessionStorage
     */
    public function __construct(SessionStorageInterface $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * @return bool
     */
    public function hasFlash()
    {
        return $this
            ->getSessionStorage()
            ->has(self::FLASH_MESSAGE_KEY);
    }

    /**
     * @return null|string
     */
    public function getFlash()
    {
        // fetch message
        $flash = $this
            ->getSessionStorage()
            ->get(self::FLASH_MESSAGE_KEY);

        // remove from session
        $this
            ->getSessionStorage()
            ->del(self::FLASH_MESSAGE_KEY);

        if ($flash === null)
        {
            return null;
        }

        return '<div class="' . $flash['type'] . '">' . $flash['message'] . '</div>';
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function setFlashNormal($message)
    {
        return $this->setFlash('flash-normal', $message);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function setFlashSuccess($message)
    {
        return $this->setFlash('flash-success', $message);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function setFlashWarning($message)
    {
        return $this->setFlash('flash-warning', $message);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function setFlashError($message)
    {
        return $this->setFlash('flash-error', $message);
    }

    /**
     * @param string $type
     * @param string $message
     *
     * @return bool
     */
    private function setFlash($type, $message)
    {
        return $this
            ->getSessionStorage()
            ->set(self::FLASH_MESSAGE_KEY, ['type' => $type, 'message' => $message]);
    }

    /**
     * @return SessionStorageInterface
     */
    private function getSessionStorage()
    {
        return $this->sessionStorage;
    }
}