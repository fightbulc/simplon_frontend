<?php

namespace Simplon\Frontend;

class Frontend
{
    /** @var  array */
    protected static $config;

    /**
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     */
    public static function start(array $configCommon, array $configEnv = [])
    {
        // set config
        self::setConfig($configCommon, $configEnv);

        // set error handler
        self::setErrorHandler();

        // set exception handler
        self::setExceptionHandler();

        // handle locale
        self::handleLocale();

        // observe routes
        echo Router::observe(self::getConfigByKeys(['routes']));

        return true;
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return (array)self::$config;
    }

    /**
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     */
    public static function setConfig(array $configCommon, array $configEnv = [])
    {
        self::$config = array_merge($configCommon, $configEnv);

        return true;
    }

    /**
     * @param array $keys
     *
     * @return mixed|bool
     * @throws Exception
     */
    public static function getConfigByKeys(array $keys)
    {
        $config = self::getConfig();
        $keysString = join(' => ', $keys);

        while ($key = array_shift($keys))
        {
            if (isset($config[$key]) === false)
            {
                throw new Exception('Config entry for [' . $keysString . '] is missing.');
            }

            $config = $config[$key];
        }

        if (!empty($config))
        {
            return $config;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected static function handleLocale()
    {
        if (isset(self::$config['templates']['locale']) && isset(self::$config['templates']['locale']['default']))
        {
            // set available by default
            $availableLocales = [
                self::$config['templates']['locale']['default']
            ];

            // set available if defined
            $hasAvailableLocales =
                isset(self::$config['templates']['locale']['available'])
                && is_array(self::$config['templates']['locale']['available'])
                && empty(self::$config['templates']['locale']['available']) === false;

            if ($hasAvailableLocales)
            {
                $availableLocales = self::$config['templates']['locale']['available'];
            }

            // init locale
            Locale::init(
                rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Locales',
                $availableLocales,
                self::$config['templates']['locale']['default']
            );

            // enable auto parsing locale strings in templates
            Template::setParseLocale(true);
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function hasRequestGetData()
    {
        return empty($_GET) === false;
    }

    /**
     * @return array
     */
    public static function getRequestGetData()
    {
        if (isset($_GET))
        {
            return (array)$_GET;
        }

        return [];
    }

    /**
     * @return bool
     */
    public static function hasRequestPostData()
    {
        return empty($_POST) === false;
    }

    /**
     * @return array
     */
    public static function getRequestPostData()
    {
        if (isset($_POST))
        {
            return (array)$_POST;
        }

        return [];
    }

    /**
     * @return bool
     */
    public static function hasSessionData()
    {
        return empty($_SESSION) === false;
    }

    /**
     * @return array
     */
    public static function getSessionData()
    {
        if (isset($_SESSION))
        {
            return (array)$_SESSION;
        }

        return [];
    }

    /**
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     */
    public static function renderTemplate($pathTemplate, $params = [])
    {
        return Template::render(
            rtrim(self::getConfigByKeys(['paths', 'src']), '/') . '/Views/Templates/' . $pathTemplate,
            $params,
            self::getConfigByKeys(['templates', 'isNative'])
        );
    }

    /**
     * @return bool
     */
    public static function setErrorHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline)
        {
            switch ($errno)
            {
                case E_USER_ERROR:
                    $error = [
                        'message' => $errstr,
                        'code'    => null,
                        'data'    => [
                            'type' => 'ERROR',
                            'file' => $errfile,
                            'line' => $errline,
                        ],
                    ];
                    break;

                case E_USER_WARNING:
                    $error = [
                        'message' => "WARNING: $errstr",
                        'code'    => $errno,
                        'data'    => [
                            'type' => 'WARNING'
                        ],
                    ];
                    break;

                case E_USER_NOTICE:
                    $error = [
                        'message' => $errstr,
                        'code'    => $errno,
                        'data'    => [
                            'type' => 'NOTICE',
                        ],
                    ];
                    break;

                default:
                    $error = [
                        'message' => $errstr,
                        'code'    => null,
                        'data'    => [
                            'type' => 'UNKNOWN',
                            'file' => $errfile,
                            'line' => $errline,
                        ],
                    ];
                    break;
            }

            die(var_dump($error));
        });

        return true;
    }

    /**
     * @return bool
     */
    public static function setExceptionHandler()
    {
        set_exception_handler(function (\Exception $e)
        {
            $error = [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'data'    => [
                    'type' => 'EXCEPTION',
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ];

            die(var_dump($error));
        });

        return true;
    }
}