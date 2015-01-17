<?php

namespace Simplon\Frontend;

use Simplon\Error\ErrorHandler;
use Simplon\Error\ErrorResponse;
use Simplon\Form\Form;
use Simplon\Form\Renderer\MustacheFormRenderer;
use Simplon\Form\Renderer\PhtmlFormRenderer;
use Simplon\Frontend\Responses\RedirectResponse;
use Simplon\Helper\Config;
use Simplon\Helper\HelperException;
use Simplon\Locale\Locale;
use Simplon\Phtml\Phtml;
use Simplon\Request\Request;
use Simplon\Router\Router;
use Simplon\Router\RouterException;
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
     * @param array $configCommon
     * @param array $configEnv
     * @param array $routes
     * @param null|\Closure $routingDispatcher
     *
     * @return string
     * @throws RouterException
     */
    public static function start(array $configCommon, array $configEnv, array $routes, $routingDispatcher = null)
    {
        // handle errors
        self::handleScriptErrors();
        self::handleFatalErrors();
        self::handleExceptions();

        // setup config
        self::setConfig($configCommon, $configEnv);

        // setup locale
        self::setupLocale();

        // setup template
        self::$template = new Template();

        // observe routes
        $response = Router::observe($routes, null, $routingDispatcher);

        // render error page
        if ($response instanceof ErrorResponse)
        {
            return self::handleErrorTemplate($response);
        }

        // handle redirects
        if ($response instanceof RedirectResponse)
        {
            Request::redirect($response->getUrl());
        }

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
     * @throws HelperException
     */
    public static function getConfigByKeys(array $keys)
    {
        return Config::getConfigByKeys($keys);
    }

    /**
     * @return string
     */
    public static function getLocale()
    {
        return self::$locale->getCurrentLocale();
    }

    /**
     * @param $locale
     *
     * @return void
     */
    public static function setLocale($locale)
    {
        self::$locale->setLocale($locale);
    }

    /**
     * @param $group
     * @param $key
     * @param array $params
     *
     * @return string
     */
    public static function getTranslation($group, $key, array $params = [])
    {
        return self::$locale->get($group, $key, $params);
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public static function addAssetsHeader(array $pathAssets)
    {
        foreach ($pathAssets as $pathAsset)
        {
            self::$template->addAssetHeader($pathAsset);
        }

        return true;
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public static function addAssetsBody(array $pathAssets)
    {
        foreach ($pathAssets as $pathAsset)
        {
            self::$template->addAssetBody($pathAsset);
        }

        return true;
    }

    /**
     * @param string $pathTemplate
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
     * @param string $pathTemplate
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
     * @param Form $form
     * @param string $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderMustacheFormTemplate(Form $form, $pathTemplate, array $params = [])
    {
        return self::renderFormTemplate(self::TEMPLATE_MUSTACHE, $form, $pathTemplate, $params);
    }

    /**
     * @param Form $form
     * @param string $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderPhtmlFormTemplate(Form $form, $pathTemplate, array $params = [])
    {
        return self::renderFormTemplate(self::TEMPLATE_PHTML, $form, $pathTemplate, $params);
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
     * @return array
     */
    private static function getMustacheCustomParserLocale()
    {
        return [
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
    }

    /**
     * @param string $type
     * @param string $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     * @throws HelperException
     */
    private static function renderTemplate($type, $pathTemplate, array $params = [])
    {
        // set complete path
        $pathTemplate = rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Views/Templates/' . $pathTemplate;

        switch ($type)
        {
            case self::TEMPLATE_MUSTACHE:
                $template = self::$template->renderMustache($pathTemplate, $params, self::getMustacheCustomParserLocale());
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
     * @param string $type
     * @param Form $form
     * @param string $pathTemplate
     * @param array $params
     *
     * @return string
     * @throws FrontendException
     */
    private static function renderFormTemplate($type, Form $form, $pathTemplate, array $params = [])
    {
        // set complete path
        $pathTemplate = rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Views/Templates/' . $pathTemplate;

        switch ($type)
        {
            case self::TEMPLATE_MUSTACHE:
                $template = (new MustacheFormRenderer($form))->render($pathTemplate, $params, self::getMustacheCustomParserLocale());
                break;

            case self::TEMPLATE_PHTML:
                $template = (new PhtmlFormRenderer($form))->render($pathTemplate, $params);
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
                rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Views/Locales',
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