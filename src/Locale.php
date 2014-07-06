<?php

namespace Simplon\Frontend;

class Locale
{
    /** @var  string */
    protected static $rootPathLocale;

    /** @var  array */
    protected static $availableLocales;

    /** @var  string */
    protected static $defaultLocale;

    /** @var  string */
    protected static $currentLocale;

    /** @var  array */
    protected static $localeContent = [];

    /**
     * @param string $rootPathLocale
     * @param array $availableLocales
     * @param string $defaultLocale
     *
     * @return bool
     */
    public static function init($rootPathLocale, $availableLocales = [], $defaultLocale = 'en', $defaultGroup = 'default')
    {
        // set root path
        self::$rootPathLocale = rtrim($rootPathLocale, '/');

        // list of valid locales
        self::$availableLocales = $availableLocales;

        // default/starting locale
        self::$defaultLocale = $defaultLocale;

        // load default
        self::setLocale($defaultLocale, $defaultGroup);

        return true;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    protected static function isValidLocale($locale)
    {
        return in_array($locale, self::$availableLocales);
    }

    /**
     * @param string $locale
     *
     * @throws Exception
     */
    protected static function loadLocaleFile($locale, $group = 'default')
    {
        $pathLocale = self::$rootPathLocale . '/' . $locale . '/' . $group . '-locale.json';

        if (file_exists($pathLocale))
        {
            self::$localeContent = json_decode(file_get_contents($pathLocale), true);

            return true;
        }

        throw new Exception('Missing locale "' . $locale . '/' . $group . '" (assumed path: ' . $pathLocale . ')');
    }

    /**
     * @param string $locale
     *
     * @return bool
     * @throws Exception
     */
    public static function setLocale($locale, $group = 'default')
    {
        // validated locale
        if (self::isValidLocale($locale) === false)
        {
            return false;
        }

        // cache locale
        self::$currentLocale = $locale;

        // get locale content
        self::loadLocaleFile($locale, $group);

        return true;
    }

    /**
     * @param string $key
     * @param array $params
     *
     * @return string
     */
    public static function get($key, $params = [])
    {
        // return key if we don't have anything
        if (isset(self::$localeContent[$key]) === false)
        {
            return $key;
        }

        // get string
        $string = self::$localeContent[$key];

        // replace params
        if (empty($params) === false)
        {
            foreach ($params as $k => $v)
            {
                $string = str_replace('{{' . $k . '}}', $v, $string);
            }
        }

        return (string)$string;
    }
} 