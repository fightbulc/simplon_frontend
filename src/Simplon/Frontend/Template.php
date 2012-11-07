<?php

  namespace Simplon\Frontend;

  class Template
  {
    /** @var \Mustache_Engine */
    protected $_mustacheInstance;

    protected $_templateRootPath;

    // ##########################################

    /**
     * @return Template
     */
    public static function init()
    {
      return new Template();
    }

    // ##########################################

    /**
     * @return \Mustache_Engine
     */
    protected function _getMustacheInstance()
    {
      if(! $this->_mustacheInstance)
      {
        $this->_mustacheInstance = new \Mustache_Engine();
      }

      return $this->_mustacheInstance;
    }

    // ##########################################

    /**
     * @param $rootPath
     * @return Template
     */
    public function setTemplateRootPath($rootPath)
    {
      $this->_templateRootPath = rtrim($rootPath, '/');

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    protected function _getTemplateRootPath()
    {
      return $this->_templateRootPath;
    }

    // ##########################################

    /**
     * @param $templatePath
     * @param array $templateVars
     * @return string
     */
    public function renderTemplate($templatePath, array $templateVars)
    {
      $rootPath = $this->_getTemplateRootPath();
      $cleanedTemplatePath = ltrim($templatePath, '/');

      $template = $this->_fetchTemplate($rootPath . '/' . $cleanedTemplatePath);

      return $this
        ->_getMustacheInstance()
        ->render($template, $templateVars);
    }

    // ##########################################

    /**
     * @param $templatePath
     * @return string
     */
    protected function _fetchTemplate($templatePath)
    {
      return join('', file($templatePath));
    }
  }