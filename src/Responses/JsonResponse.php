<?php

namespace Simplon\Frontend\Responses;

use Simplon\Frontend\Interfaces\ResponseInterface;

/**
 * JsonResponse
 * @package Simplon\Frontend\Responses
 * @author Tino Ehrich (tino@bigpun.me)
 */
class JsonResponse implements ResponseInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @return array
     */
    public function getData()
    {
        return (array)$this->data;
    }

    /**
     * @param string $key
     * @param mixed $val
     *
     * @return JsonResponse
     */
    public function addData($key, $val)
    {
        $this->data[$key] = $val;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}