<?php

namespace Simplon\Frontend\Responses;

use Simplon\Error\ErrorContext;
use Simplon\Frontend\Interfaces\ResponseInterface;

/**
 * ErrorResponse
 * @package Simplon\Frontend\Responses
 * @author Tino Ehrich (tino@bigpun.me)
 */
class ErrorResponse extends ErrorContext implements ResponseInterface
{
    const RESPONSE_TYPE_HTML = 'html';
    const RESPONSE_TYPE_JSON = 'json';

    /**
     * @var string
     */
    private $responseType;

    /**
     * @param string $responseType
     */
    public function __construct($responseType = self::RESPONSE_TYPE_HTML)
    {
        $this->responseType = $responseType;
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return parent::getHttpCode();
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return parent::getMessage();
    }

    /**
     * @param string $message
     *
     * @return ErrorResponse
     */
    public function setMessage($message)
    {
        return parent::setMessage($message);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return parent::getCode();
    }

    /**
     * @param string $code
     *
     * @return ErrorResponse
     */
    public function setCode($code)
    {
        return parent::setCode($code);
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return parent::hasData();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return parent::getData();
    }

    /**
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function setData($data)
    {
        return parent::setData($data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestMalformed($message = 'Request malformed', $code = null, array $data = [])
    {
        return parent::requestMalformed($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestUnauthorised($message = 'Request unauthorised', $code = null, array $data = [])
    {
        return parent::requestUnauthorised($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestForbidden($message = 'Request forbidden', $code = null, array $data = [])
    {
        return parent::requestForbidden($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestNotFound($message = 'Request not found', $code = null, array $data = [])
    {
        return parent::requestNotFound($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestMethodNotAllowed($message = 'Request method not allowed', $code = null, array $data = [])
    {
        return parent::requestMethodNotAllowed($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestConflict($message = 'Request would cause a conflict', $code = null, array $data = [])
    {
        return parent::requestConflict($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function requestUnprocessableEntity($message = 'Request unprocessable', $code = null, array $data = [])
    {
        return parent::requestUnprocessableEntity($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function internalError($message = 'Internal error', $code = null, array $data = [])
    {
        return parent::internalError($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function badGateway($message = 'Bad gateway', $code = null, array $data = [])
    {
        return parent::badGateway($message, $code, $data);
    }

    /**
     * @param string $message
     * @param null $code
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function unavailable($message = 'Unavailable', $code = null, array $data = [])
    {
        return parent::unavailable($message, $code, $data);
    }
}