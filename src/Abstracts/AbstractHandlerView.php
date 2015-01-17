<?php

namespace Simplon\Frontend\Abstracts;

use Simplon\Frontend\Interfaces\ResponseInterface;
use Simplon\Frontend\Responses\DataResponse;
use Simplon\Frontend\Responses\ErrorResponse;
use Simplon\Frontend\Responses\RedirectResponse;

/**
 * AbstractHandlerView
 * @package Simplon\Frontend\Abstracts
 * @author Tino Ehrich (tino@bigpun.me)
 */
abstract class AbstractHandlerView
{
    /**
     * @param callable $callback
     * @param ResponseInterface $response
     * @param array $opt
     *
     * @return ResponseInterface
     */
    protected function handleResponseType(\Closure $callback, ResponseInterface $response = null, array $opt = [])
    {
        if ($response instanceof RedirectResponse)
        {
            return $response;
        }

        // --------------------------------------

        if ($response instanceof ErrorResponse)
        {
            $params = [
                'hasErrors' => true,
                'message'   => $response->getMessage(),
                'data'      => $response->getData(),
            ];

            return $callback(array_merge($opt, $params));
        }

        // --------------------------------------

        $params = [];

        if ($response instanceof DataResponse)
        {
            $params = $response->getData();
        }

        return $callback(array_merge($params, $opt));
    }
}