<?php

namespace Simplon\Frontend;

class Frontend
{
    /** @var  array */
    protected static $config;

    /** @var  string */
    protected static $rootPath;

    /** @var  bool */
    protected static $nativeTemplates;

    /**
     * @param array $config
     *
     * @return bool
     * @throws Exception
     */
    public static function start(array $config)
    {
        // set error handler
        self::setErrorHandler();

        // set exception handler
        self::setExceptionHandler();

        // --------------------------------------

        if (isset($config['rootPath']) === false)
        {
            throw new Exception('Config misses: "rootPath" => ""');
        }

        if (isset($config['nativeTemplates']) === false)
        {
            throw new Exception('Config misses: "nativeTemplates" => true/false');
        }

        if (isset($config['routes']) === false)
        {
            throw new Exception('Config misses: "routes" => []');
        }

        // --------------------------------------

        // set root path
        self::$rootPath = rtrim($config['rootPath'], '/') . '/../src/App';

        // set templates type
        self::$nativeTemplates = $config['nativeTemplates'];

        // set config
        self::setConfig($config);

        // handle locale
        self::handleLocale();

        // observe routes
        echo Router::observe($config['routes']);

        return true;
    }

    /**
     * @return bool
     */
    protected static function handleLocale()
    {
        if (isset(self::$config['locale']) && isset(self::$config['locale']['default']))
        {
            // set available by default
            $availableLocales = [
                self::$config['locale']['default']
            ];

            // set available if defined
            if (isset(self::$config['locale']['available']) && is_array(self::$config['locale']['available']))
            {
                $availableLocales = self::$config['locale']['available'];
            }

            // init locale
            Locale::init(self::$rootPath . '/Locales', $availableLocales, self::$config['locale']['default']);

            // enable auto parsing locale strings in templates
            Template::setParseLocale(true);
        }

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
     * @param array $config
     *
     * @return bool
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;

        return true;
    }

    /**
     * @param array $keys
     *
     * @return array|bool
     * @throws Exception
     */
    public static function getConfigByKeys(array $keys)
    {
        $config = self::getConfig();
        $keysString = join('-->', $keys);

        while ($key = array_shift($keys))
        {
            if (isset($config[$key]) === false)
            {
                throw new Exception('Config entry for "' . $keysString . '" is missing.');
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
     * @param $pathTemplate
     * @param array $params
     *
     * @return string
     */
    public static function renderTemplate($pathTemplate, $params = [])
    {
        return Template::render(
            self::$rootPath . '/Views/Templates/' . $pathTemplate,
            $params,
            self::$nativeTemplates
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