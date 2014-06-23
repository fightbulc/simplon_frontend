<?php

namespace Simplon\Frontend;

class Template
{
    /** @var  array */
    protected static $params;

    /** @var array */
    protected static $templates = [];

    /** @var bool */
    protected static $parseLocale = false;

    /** @var array */
    protected static $assetsHeader = [];

    /** @var array */
    protected static $assetsBody = [];

    /**
     * @param $templateContext
     * @param array $paramsContext
     *
     * @return string
     */
    public static function parse($templateContext, array $paramsContext)
    {
        foreach ($paramsContext as $key => $val)
        {
            if (is_array($val))
            {
                // find loops
                preg_match_all('|{{#' . $key . '}}(.*?){{/' . $key . '}}|s', $templateContext, $foreachPattern);

                // handle loops
                if (isset($foreachPattern[1][0]))
                {
                    foreach ($foreachPattern[1] as $patternId => $patternContext)
                    {
                        $loopContent = '';

                        // handle array objects
                        if (isset($val[0]))
                        {
                            foreach ($val as $loopVal)
                            {
                                $loopContent .= self::parse($patternContext, $loopVal);
                            }
                        }

                        // normal array only
                        else
                        {
                            $loopContent = self::parse($patternContext, $val);
                        }

                        // replace pattern context
                        $templateContext = preg_replace(
                            '|' . preg_quote($foreachPattern[0][$patternId]) . '|s',
                            $loopContent,
                            $templateContext,
                            1
                        );
                    }
                }
            }

            // ----------------------------------

            elseif (is_bool($val))
            {
                // determine true/false
                $conditionChar = $val === true ? '\#' : '\^';

                // find bools
                preg_match_all('|{{' . $conditionChar . $key . '}}(.*?){{/' . $key . '}}|s', $templateContext, $boolPattern);

                // handle bools
                if (isset($boolPattern[1][0]))
                {
                    foreach ($boolPattern[1] as $patternId => $patternContext)
                    {
                        // parse and replace pattern context
                        $templateContext = preg_replace(
                            '|' . preg_quote($boolPattern[0][$patternId]) . '|s',
                            self::parse($patternContext, self::$params),
                            $templateContext,
                            1
                        );
                    }
                }
            }

            // ----------------------------------

            elseif ($val instanceof \Closure)
            {
                // set closure return
                $templateContext = str_replace('{{' . $key . '}}', $val(), $templateContext);
            }

            // ----------------------------------

            else
            {
                // set vars
                $templateContext = str_replace('{{' . $key . '}}', $val, $templateContext);
            }
        }

        return (string)$templateContext;
    }

    /**
     * @param $pathTemplate
     *
     * @return string
     * @throws \Exception
     */
    public static function loadMustacheFile($pathTemplate)
    {
        if (!isset(self::$templates[$pathTemplate]))
        {
            $template = file_get_contents($pathTemplate . '.mustache');

            if ($template === false)
            {
                throw new \Exception('Requested template does not exist: ' . $pathTemplate);
            }

            // cache template for future calls
            self::$templates[$pathTemplate] = $template;
        }

        return (string)self::$templates[$pathTemplate];
    }

    /**
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     */
    public static function loadNativeFile($pathTemplate, array $params)
    {
        ob_start();
        extract($params);
        require $pathTemplate . '.php';
        $template = ob_get_clean();

        return (string)$template;
    }

    /**
     * @param bool $enable
     */
    public static function setParseLocale($enable)
    {
        self::$parseLocale = $enable === true;
    }

    /**
     * @param $template
     *
     * @return string
     */
    protected static function parseLocale($template)
    {
        preg_match_all('|{{lang:(.*?)}}|', $template, $localeKeys);

        if (isset($localeKeys[1][0]))
        {
            foreach ($localeKeys[1] as $key)
            {
                $template = str_replace('{{lang:' . $key . '}}', Locale::get($key), $template);
            }
        }

        return (string)$template;
    }

    /**
     * @param array $pathAssets
     *
     * @return bool
     */
    public static function addAssets(array $pathAssets)
    {
        foreach ($pathAssets as $path)
        {
            // add javascript
            if (strpos($path, '.js') !== false)
            {
                self::$assetsBody[] = '<script type="text/javascript" src="' . $path . '"></script>';
            }

            // add css
            elseif (strpos($path, '.css') !== false)
            {
                self::$assetsHeader[] = '<link rel="stylesheet" href="' . $path . '">';
            }
        }

        return true;
    }

    /**
     * @param $template
     *
     * @return string
     */
    protected static function handleAssets($template)
    {
        if (strpos($template, '</head>') !== false)
        {
            // set header in template
            $template = str_replace('</head>', join("\n", self::$assetsHeader) . '</head>', $template);

            // reset header
            self::$assetsHeader = [];
        }

        if (strpos($template, '</body>') !== false)
        {
            // set header in template
            $template = str_replace('</body>', join("\n", self::$assetsBody) . '</body>', $template);

            // reset header
            self::$assetsBody = [];
        }

        return (string)$template;
    }

    /**
     * @param $pathTemplate
     * @param array $params
     * @param bool $useNative
     *
     * @return string
     * @throws \Exception
     */
    public static function render($pathTemplate, array $params, $useNative = false)
    {
        // keep params in context
        self::$params = $params;

        // --------------------------------------

        // mustache templates
        if ($useNative === false)
        {
            // load template
            $template = self::loadMustacheFile($pathTemplate);

            // parse template
            $template = self::parse($template, $params);

            // parse locale
            if (self::$parseLocale === true)
            {
                $template = self::parseLocale($template);
            }

            // remove left over wrappers
            $template = preg_replace('|{{.*?}}.*?{{/.*?}}\n*|s', '', $template);

            // remove left over varibles
            $template = preg_replace('|{{.*?}}\n*|s', '', $template);
        }

        // native templates
        else
        {
            $template = self::loadNativeFile($pathTemplate, $params);
        }

        // --------------------------------------

        // handle assets
        $template = self::handleAssets($template);

        return (string)$template;
    }
} 