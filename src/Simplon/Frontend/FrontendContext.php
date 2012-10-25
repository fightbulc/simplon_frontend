<?php

  namespace Simplon\Frontend;

  class FrontendContext implements ContextInterface
  {
    /** @var FrontendContext */
    private static $_instance;

    // ##########################################

    /**
     * @return FrontendContext
     */
    public static function getInstance()
    {
      if(! isset(FrontendContext::$_instance))
      {
        FrontendContext::$_instance = new FrontendContext();
      }

      return FrontendContext::$_instance;
    }

    // ##########################################

    /**
     * @return string
     */
    public function getConfigPath()
    {
      return __DIR__ . '/../../config/common.config.php';
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
