<?php

  namespace Simplon\Frontend;

  class FrontendContext
  {
    /** @var static */
    protected static $_instance;

    // ##########################################

    /**
     * @return static
     */
    public static function getInstance()
    {
      if(! isset(static::$_instance))
      {
        static::$_instance = new static();
      }

      return static::$_instance;
    }

    // ##########################################

    /**
     * @return string
     */
    public function getConfigPath()
    {
      return __DIR__ . '/../../../../../../app/config/common.config.php';
    }

    // ##########################################

    /**
     * @param $keys
     * @return array
     */
    public function getConfigByKeys(array $keys)
    {
      return \Simplon\Config\Config::getInstance()
        ->setConfigPath($this->getConfigPath())
        ->getConfigByKeys($keys);
    }
  }
