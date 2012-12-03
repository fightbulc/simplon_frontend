<?php

  namespace Simplon\Frontend;

  use Phly\Mustache\Mustache;

  class Template
  {
    /** @var \Phly\Mustache\Mustache */
    protected $_mustacheInstance;
    protected $_templatePath;
    protected $_templateSuffix = 'html';
    protected $_templateName;
    protected $_templateDataItems = [];
    protected $_templatePartialAliases = [];

    // ##########################################

    /**
     * @return \Phly\Mustache\Mustache
     */
    protected function _getMustacheInstance()
    {
      if(! $this->_mustacheInstance)
      {
        $this->_mustacheInstance = new Mustache();
      }

      return $this->_mustacheInstance;
    }

    // ##########################################

    /**
     * @param $templatePath
     * @return Template
     */
    public function setTemplatePath($templatePath)
    {
      $this->_templatePath = $templatePath;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function _getTemplatePath()
    {
      if(empty($this->_templatePath))
      {
        throw new \Exception('Simplon\Frontend\Template: missing templatePath', 500);
      }

      return $this->_templatePath;
    }

    // ##########################################

    /**
     * @param $templateName
     * @return Template
     */
    public function setTemplate($templateName)
    {
      $this->_templateName = $templateName;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function _getTemplate()
    {
      if(empty($this->_templateName))
      {
        throw new \Exception('Simplon\Frontend\Template: missing template.', 500);
      }

      return $this->_templateName;
    }

    // ##########################################

    /**
     * @param $suffix
     * @return Template
     */
    public function setTemplateSuffix($suffix)
    {
      $this->_templateSuffix = $suffix;

      return $this;
    }

    // ##########################################

    /**
     * @return string
     * @throws \Exception
     */
    protected function _getTemplateSuffix()
    {
      if(empty($this->_templateSuffix))
      {
        throw new \Exception('Simplon\Frontend\Template: missing templateSuffix (e.g. html)', 500);
      }

      return $this->_templateSuffix;
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return Template
     */
    public function addDataItem($key, $value)
    {
      $this->_templateDataItems[$key] = $value;

      return $this;
    }

    // ##########################################

    /**
     * @param array $items
     * @return Template
     */
    public function setDataItems(array $items)
    {
      $this->_templateDataItems = $items;

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getDataItems()
    {
      return $this->_templateDataItems;
    }

    // ##########################################

    /**
     * @param $partialTemplateName
     * @param $partialAliasName
     * @return Template
     */
    public function addPartialAlias($partialTemplateName, $partialAliasName)
    {
      $this->_templatePartialAliases[$partialTemplateName] = $partialAliasName;

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getPartialAliases()
    {
      return $this->_templatePartialAliases;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _resetData()
    {
      $this->_templateName = '';
      $this->_templateDataItems = [];
      $this->_templatePartialAliases = [];

      return TRUE;
    }

    // ##########################################

    /**
     * @return string
     */
    public function render()
    {
      // render template
      $renderedTemplate = $this
        ->_getMustacheInstance()
        ->setSuffix($this->_getTemplateSuffix())
        ->setTemplatePath($this->_getTemplatePath())
        ->render($this->_getTemplate(), $this->_getDataItems(), $this->_getPartialAliases());

      // reset template data
      $this->_resetData();

      return $renderedTemplate;
    }
  }