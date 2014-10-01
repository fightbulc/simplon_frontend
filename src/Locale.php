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
     * @param $rootPathLocale
     * @param array $availableLocales
     * @param string $defaultLocale
     *
     * @return bool
     */
    public static function init($rootPathLocale, $availableLocales = [], $defaultLocale = 'en')
    {
        // set root path
        self::$rootPathLocale = rtrim($rootPathLocale, '/');

        // list of valid locales
        self::$availableLocales = $availableLocales;

        // default/starting locale
        self::$defaultLocale = $defaultLocale;

        // load default
        self::setLocale($defaultLocale);

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
     * @param $locale
     * @param string $group
     *
     * @return bool
     * @throws Exception
     */
    protected static function loadLocaleFile($locale, $group = 'default')
    {
        $localeFileCacheKey = $locale . '/' . $group;

        // is locale already cached
        if (isset(self::$localeContent[$localeFileCacheKey]))
        {
            return true;
        }

        // file path
        $pathLocale = self::$rootPathLocale . '/' . $locale . '/' . $group . '-locale.php';

        if (file_exists($pathLocale) === true)
        {
            self::$localeContent[$locale . '-' . $group] = require $pathLocale;

            return true;
        }

        throw new Exception('Missing locale "' . $locale . '/' . $group . '" (assumed path: ' . $pathLocale . ')');
    }

    /**
     * @param $locale
     *
     * @return bool
     * @throws Exception
     */
    public static function setLocale($locale)
    {
        // validated locale
        if (self::isValidLocale($locale) === false)
        {
            return false;
        }

        // cache locale
        self::$currentLocale = $locale;

        return true;
    }

    /**
     * @param string $group
     * @param string $key
     * @param array $params
     *
     * @return string
     */
    public static function get($group, $key, $params = [])
    {
        // make sure that we have the locale content
        self::loadLocaleFile(self::$currentLocale, $group);

        // build locale/group key
        $localeFileCacheKey = self::$currentLocale . '-' . $group;

        // return key if we don't have anything
        if (isset(self::$localeContent[$localeFileCacheKey][$key]) === false)
        {
            return $key;
        }

        // get string
        $string = self::$localeContent[$localeFileCacheKey][$key];

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