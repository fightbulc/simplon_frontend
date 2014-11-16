<?php

namespace Simplon\Frontend;

use Simplon\Error\ErrorHandler;
use Simplon\Error\ErrorResponse;
use Simplon\Helper\Config;
use Simplon\Locale\Locale;
use Simplon\Phtml\Phtml;
use Simplon\Router\Router;
use Simplon\Template\Template;

class Frontend
{
    const TEMPLATE_MUSTACHE = 'mustache';
    const TEMPLATE_PHTML = 'phtml';

    /**
     * @var Locale
     */
    private static $locale;

    /**
     * @var Template
     */
    private static $template;

    /**
     * @param array $routes
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return string
     * @throws \Simplon\Router\RouterException
     */
    public static function start(array $routes, array $configCommon, array $configEnv = [])
    {
        // handle errors
        self::handleScriptErrors();
        self::handleFatalErrors();
        self::handleExceptions();

        // setup config
        self::setConfig($configCommon, $configEnv);

        // setup locale
        self::setupLocale();

        // observe routes
        $response = Router::observe($routes);

        // render error page
        if ($response instanceof ErrorResponse)
        {
            return self::handleErrorTemplate($response);
        }

        // --------------------------------------

        return (string)$response;
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return Config::getConfig();
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    public static function hasConfigKeys(array $keys)
    {
        return Config::hasConfigKeys($keys);
    }

    /**
     * @param array $keys
     *
     * @return array|null
     * @throws \Simplon\Helper\HelperException
     */
    public static function getConfigByKeys(array $keys)
    {
        return Config::getConfigByKeys($keys);
    }

    /**
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderMustacheTemplate($pathTemplate, $params = [])
    {
        return self::renderTemplate(self::TEMPLATE_MUSTACHE, $pathTemplate, $params);
    }

    /**
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderPhtmlTemplate($pathTemplate, $params = [])
    {
        return self::renderTemplate(self::TEMPLATE_PHTML, $pathTemplate, $params);
    }

    /**
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     */
    private static function setConfig(array $configCommon, array $configEnv = [])
    {
        return Config::setConfig($configCommon, $configEnv);
    }

    /**
     * @param $type
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     * @throws \Simplon\Helper\HelperException
     */
    private static function renderTemplate($type, $pathTemplate, array $params = [])
    {
        if (self::$template === null)
        {
            self::$template = new Template();
        }

        // set complete path
        $pathTemplate = rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Views/Templates/' . $pathTemplate;

        switch ($type)
        {
            case self::TEMPLATE_MUSTACHE:
                $customParsers = [
                    [
                        'pattern'  => '{{lang:(.*?):(.*?)}}',
                        'callback' => function ($template, array $match)
                        {
                            foreach ($match[1] as $index => $key)
                            {
                                $langKey = 'lang:' . $match[1][$index] . ':' . $match[2][$index];
                                $langString = self::$locale->get($match[1][$index], $match[2][$index]);
                                $template = str_replace('{{' . $langKey . '}}', $langString, $template);
                            }

                            return $template;
                        },
                    ]
                ];

                $template = self::$template->renderMustache($pathTemplate, $params, $customParsers);
                break;

            case self::TEMPLATE_PHTML:
                $template = self::$template->renderPhtml($pathTemplate, $params);
                break;

            default:
                throw new FrontendException('Unknown template type: ' . $type);
        }

        return $template;
    }

    /**
     * @return bool
     */
    private static function setupLocale()
    {
        if (self::hasConfigKeys(['locales']) === true && self::hasConfigKeys(['locales', 'default']) === true)
        {
            $availableLocales = [];

            // set available if defined
            if (self::hasConfigKeys(['locales', 'available']) && is_array(self::getConfigByKeys(['locales', 'available'])))
            {
                $availableLocales = self::getConfigByKeys(['locales', 'available']);
            }

            // fill up default locale
            if (empty($availableLocales) === true)
            {
                $availableLocales = [
                    self::getConfigByKeys(['locales', 'default'])
                ];
            }

            // init locale
            self::$locale = new Locale(
                rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Locales',
                $availableLocales,
                self::getConfigByKeys(['locales', 'default'])
            );
        }

        return true;
    }

    /**
     * @return void
     */
    private static function handleScriptErrors()
    {
        ErrorHandler::handleScriptErrors(
            function (ErrorResponse $errorResponse) { return self::handleErrorTemplate($errorResponse); }
        );
    }

    /**
     * @return void
     */
    private static function handleFatalErrors()
    {
        ErrorHandler::handleFatalErrors(
            function (ErrorResponse $errorResponse) { return self::handleErrorTemplate($errorResponse); }
        );
    }

    /**
     * @return void
     */
    private static function handleExceptions()
    {
        ErrorHandler::handleExceptions(
            function (ErrorResponse $errorResponse) { return self::handleErrorTemplate($errorResponse); }
        );
    }

    /**
     * @param ErrorResponse $errorResponse
     *
     * @return string
     */
    private static function handleErrorTemplate(ErrorResponse $errorResponse)
    {
        // set http status
        http_response_code($errorResponse->getHttpCode());

        return Phtml::render(__DIR__ . '/ErrorTemplate', ['errorResponse' => $errorResponse]);
    }
}