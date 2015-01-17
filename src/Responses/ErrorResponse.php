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
}