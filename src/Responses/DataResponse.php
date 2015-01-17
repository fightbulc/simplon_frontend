<?php

namespace Simplon\Frontend\Responses;

use Simplon\Frontend\Interfaces\ResponseInterface;

/**
 * DataResponse
 * @package Simplon\Frontend\Responses
 * @author Tino Ehrich (tino@bigpun.me)
 */
class DataResponse implements ResponseInterface
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
     * @return DataResponse
     */
    public function addData($key, $val)
    {
        $this->data[$key] = $val;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return DataResponse
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}