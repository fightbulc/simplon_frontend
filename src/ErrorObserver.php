<?php

namespace Simplon\Frontend;

use Simplon\Error\ErrorContext;
use Simplon\Error\ErrorHandler;
use Simplon\Frontend\Responses\ErrorResponse;
use Simplon\Phtml\Phtml;
use Simplon\Phtml\PhtmlException;

/**
 * ErrorObserver
 * @package Simplon\Frontend
 * @author  Tino Ehrich (tino@bigpun.me)
 */
class ErrorObserver
{
    /**
     * @var string
     */
    private $pathErrorTemplate;

    /**
     * @var \Closure[]
     */
    private $callbacks = [];

    /**
     * @param string $pathErrorTemplate
     */
    public function __construct($pathErrorTemplate = null)
    {
        if ($pathErrorTemplate === null)
        {
            $pathErrorTemplate = __DIR__ . '/Errors/ErrorTemplate';
        }

        $this->pathErrorTemplate = $pathErrorTemplate;
    }

    /**
     * @return $this
     */
    public function observe()
    {
        $this->observeScriptErrors();
        $this->observeFatalErrors();
        $this->observeExceptions();

        return $this;
    }

    /**
     * @param \Closure $callback
     *
     * @return ErrorObserver
     */
    public function addCallback(\Closure $callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * @param ErrorContext $errorContext
     *
     * @return string
     * @throws PhtmlException
     */
    public function handleErrorResponse(ErrorContext $errorContext)
    {
        // set http status
        http_response_code($errorContext->getHttpCode());

        // handle context response
        switch ($errorContext->getResponseType())
        {
            case ErrorResponse::RESPONSE_TYPE_JSON:
                header('Content-type: application/json');

                return $this->handleErrorJsonResponse($errorContext);

            default:
                return Phtml::render($this->pathErrorTemplate, ['errorContext' => $errorContext]);
        }
    }

    /**
     * @param ErrorContext $errorContext
     *
     * @return string
     */
    private function handleErrorJsonResponse(ErrorContext $errorContext)
    {
        $data = [
            'error' => [
                'code'    => $errorContext->getHttpCode(),
                'message' => $errorContext->getMessage(),
            ]
        ];

        // set code
        if ($errorContext->getCode() !== null)
        {
            $data['error']['code'] = $errorContext->getCode();
        }

        // set data
        if ($errorContext->hasData() === true)
        {
            $data['error']['data'] = $errorContext->getData();
        }

        return json_encode($data);
    }

    /**
     * @param ErrorContext $errorContext
     *
     * @return $this
     */
    private function handleCallbacks(ErrorContext $errorContext)
    {
        foreach ($this->callbacks as $callback)
        {
            $callback($errorContext);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function observeScriptErrors()
    {
        ErrorHandler::handleScriptErrors(
            function (ErrorContext $errorContext)
            {
                $this->handleCallbacks($errorContext);

                return $this->handleErrorResponse($errorContext);
            }
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function observeFatalErrors()
    {
        ErrorHandler::handleFatalErrors(
            function (ErrorContext $errorContext)
            {
                $this->handleCallbacks($errorContext);

                return $this->handleErrorResponse($errorContext);
            }
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function observeExceptions()
    {
        ErrorHandler::handleExceptions(
            function (ErrorContext $errorContext)
            {
                $this->handleCallbacks($errorContext);

                return $this->handleErrorResponse($errorContext);
            }
        );

        return $this;
    }
}