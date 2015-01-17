<?php

namespace Simplon\Frontend\Responses;

use Simplon\Frontend\Interfaces\ResponseInterface;
use Simplon\Helper\Helper;

/**
 * RedirectResponse
 * @package Simplon\Frontend\Responses
 * @author Tino Ehrich (tino@bigpun.me)
 */
class RedirectResponse implements ResponseInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @param string|array $url
     * @param array $params
     */
    public function __construct($url, array $params = [])
    {
        $this->url = Helper::urlRender($url, $params);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return (string)$this->url;
    }
}