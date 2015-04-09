<?php

namespace Simplon\Frontend;

use Simplon\Form\Form;
use Simplon\Form\Renderer\MustacheFormRenderer;
use Simplon\Form\Renderer\PhtmlFormRenderer;
use Simplon\Frontend\Responses\ErrorResponse;
use Simplon\Frontend\Responses\JsonResponse;
use Simplon\Frontend\Responses\RedirectResponse;
use Simplon\Helper\Config;
use Simplon\Helper\HelperException;
use Simplon\Locale\Locale;
use Simplon\Request\Request;
use Simplon\Router\Router;
use Simplon\Router\RouterException;
use Simplon\Template\Template;

/**
 * Frontend
 * @package Simplon\Frontend
 * @author  Tino Ehrich (tino@bigpun.me)
 */
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
     * @var ErrorObserver
     */
    private static $errorObserver;

    /**
     * @param Router        $router
     * @param ErrorObserver $errorObserver
     * @param array         $configCommon
     * @param array         $configEnv
     *
     * @return string
     * @throws RouterException
     */
    public static function dispatch(Router $router, ErrorObserver $errorObserver, array $configCommon, array $configEnv = [])
    {
        // handle errors
        self::$errorObserver = $errorObserver->observe();

        // setup config
        self::setConfig($configCommon, $configEnv);

        // setup locale
        self::setupLocale();

        // setup template
        self::$template = new Template();

        // handle routing and its response
        return self::handleRoutingAndResponse($router);
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
     * @param       $group
     * @param       $key
     * @param array $params
     *
     * @return string
     */
    public static function translate($group, $key, array $params = [])
    {
        return self::$locale->get($group, $key, $params);
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public static function addAssetsCss(array $pathAssets)
    {
        foreach ($pathAssets as $pathAsset)
        {
            self::$template->addAssetCss($pathAsset);
        }

        return true;
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public static function addAssetsJs(array $pathAssets)
    {
        foreach ($pathAssets as $pathAsset)
        {
            self::$template->addAssetJs($pathAsset);
        }

        return true;
    }

    /**
     * @param array $codeLines
     *
     * @return bool
     */
    public static function addAssetsCode(array $codeLines)
    {
        foreach ($codeLines as $line)
        {
            self::$template->addAssetCode($line);
        }

        return true;
    }

    /**
     * @param string $pathTemplate
     * @param array  $params
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
     * @param array  $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderPhtmlTemplate($pathTemplate, $params = [])
    {
        // add short syntax for translation
        $params['t'] = self::$locale;

        return self::renderTemplate(self::TEMPLATE_PHTML, $pathTemplate, $params);
    }

    /**
     * @param Form   $form
     * @param string $pathTemplate
     * @param array  $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderMustacheFormTemplate(Form $form, $pathTemplate, array $params = [])
    {
        return self::renderFormTemplate(self::TEMPLATE_MUSTACHE, $form, $pathTemplate, $params);
    }

    /**
     * @param Form   $form
     * @param string $pathTemplate
     * @param array  $params
     *
     * @return string
     * @throws FrontendException
     */
    public static function renderPhtmlFormTemplate(Form $form, $pathTemplate, array $params = [])
    {
        // add short syntax for translation
        $params['t'] = self::$locale;

        return self::renderFormTemplate(self::TEMPLATE_PHTML, $form, $pathTemplate, $params);
    }

    /**
     * @param Router $router
     *
     * @return string
     * @throws RouterException
     */
    private static function handleRoutingAndResponse(Router $router)
    {
        // observe routes
        try
        {
            $response = $router->observe();
        }
        catch (RouterException $e)
        {
            $response = (new ErrorResponse())->requestNotFound();
        }

        // render error page
        if ($response instanceof ErrorResponse)
        {
            return self::$errorObserver->handleErrorResponse($response);
        }

        // --------------------------------------

        // handle redirects
        if ($response instanceof RedirectResponse)
        {
            Request::redirect($response->getUrl());
        }

        // --------------------------------------

        // handle json response
        if ($response instanceof JsonResponse)
        {
            header('Content-type: application/json');
            $response = json_encode($response->getData());
        }

        return (string)$response;
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
     * @param array  $params
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
     * @param Form   $form
     * @param string $pathTemplate
     * @param array  $params
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
}