<?php

namespace Simplon\Frontend;

use Simplon\Error\ErrorHandler;
use Simplon\Error\ErrorResponse;
use Simplon\Helper\Config;
use Simplon\Locale\Locale;
use Simplon\Router\Router;
use Simplon\Template\Template;

class Frontend
{
    const TEMPLATE_MUSTACHE = 'mustache';
    const TEMPLATE_PHTML = 'phtml';

    /**
     * @var array
     */
    private static $config;

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
        Config::setConfig($configCommon, $configEnv);

        // setup locale
        self::setupLocale();

        // observe routes
        return Router::observe($routes);
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
        $pathTemplate = rtrim(Config::getConfigByKeys(['paths', 'src']), '/') . '/Views/Templates/' . $pathTemplate;

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
        if (isset(self::$config['locales']) && isset(self::$config['locales']['default']))
        {
            // set available by default
            $availableLocales = [
                self::$config['locales']['default']
            ];

            // set available if defined
            $hasAvailableLocales =
                isset(self::$config['locales']['available'])
                && is_array(self::$config['locales']['available'])
                && empty(self::$config['locales']['available']) === false;

            if ($hasAvailableLocales)
            {
                $availableLocales = self::$config['locales']['available'];
            }

            // init locale
            self::$locale = new Locale(
                rtrim(Config::getConfigByKeys(['paths', 'src']), '/') . '/Locales',
                $availableLocales,
                self::$config['locales']['default']
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
            function (ErrorResponse $errorResponse) { return JsonRpcServer::respond($errorResponse); }
        );
    }

    /**
     * @return void
     */
    private static function handleFatalErrors()
    {
        ErrorHandler::handleFatalErrors(
            function (ErrorResponse $errorResponse) { return JsonRpcServer::respond($errorResponse); }
        );
    }

    /**
     * @return void
     */
    private static function handleExceptions()
    {
        ErrorHandler::handleExceptions(
            function (ErrorResponse $errorResponse) { return JsonRpcServer::respond($errorResponse); }
        );
    }
}