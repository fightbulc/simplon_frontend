<?php

  namespace Simplon\Frontend;

  class JsonRpcApi
  {
    /** @var array */
    private $_data = array();

    // ##########################################

    /**
     * Chaining before PHP 5.4
     *
     * @param $url
     * @return JsonRpcApi
     */
    public static function api($url)
    {
      return new JsonRpcApi($url);
    }

    // ##########################################

    /**
     * @param $url
     */
    protected function __constructor($url)
    {
      // set url
      $this->_setUrl($url);

      // set default method
      $this->_setRequestMethod('POST');
    }

    // ##########################################

    /**
     * @return mixed
     */
    protected function _fetchFromApi()
    {
      return \CURL::init($this->_getUrl())
        ->setCustomRequest($this->_getRequestMethod())
        ->setPostFields($this->_getData())
        ->setReturnTransfer(TRUE)
        ->execute();
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return JsonRpcApi
     */
    protected function setByKey($key, $value)
    {
      $this->_data[$key] = $value;

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|string
     */
    protected function getByKey($key)
    {
      if(! isset($this->_data[$key]))
      {
        return FALSE;
      }

      return $this->_data[$key];
    }

    // ##########################################

    /**
     * @param $url
     * @return JsonRpcApi
     */
    protected function _setUrl($url)
    {
      $this->setByKey('url', $url);

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getUrl()
    {
      return $this->getByKey('url');
    }

    // ##########################################

    /**
     * @param $method
     * @return JsonRpcApi
     */
    protected function _setRequestMethod($method)
    {
      $this->setByKey('requestMethod', $method);

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getRequestMethod()
    {
      return $this->getByKey('requestMethod');
    }

    // ##########################################

    /**
     * @param $id
     * @return JsonRpcApi
     */
    public function setId($id)
    {
      $this->setByKey('id', $id);

      return $this;
    }

    // ##########################################

    /**
     * @param $method
     * @return JsonRpcApi
     */
    public function setMethod($method)
    {
      $this->setByKey('method', $method);

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getId()
    {
      return $this->getByKey('id');
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getMethod()
    {
      return $this->getByKey('method');
    }

    // ##########################################

    /**
     * @param $data
     * @return JsonRpcApi
     */
    public function setData($data)
    {
      $this->setByKey('data', $data);

      return $this;
    }

    // ##########################################

    /**
     * @return array|bool
     */
    protected function _getData()
    {
      $data = $this->getByKey('data');

      if($data === FALSE)
      {
        return FALSE;
      }

      return array(
        "id"     => $this->_getId(),
        "method" => $this->_getMethod(),
        "params" => $data,
      );
    }

    // ##########################################

    /**
     * @param $fieldId
     * @throws \Exception
     */
    protected function _throwError($fieldId)
    {
      throw new \Exception(__NAMESPACE__ . '/' . __CLASS__ . ': missing <' . $fieldId . '>', 500);
    }

    // ##########################################

    /**
     * @return mixed
     * @throws \Exception
     */
    public function run()
    {
      if($this->_getUrl() === FALSE)
      {
        $this->_throwError('url');
      }

      if($this->_getId() === FALSE)
      {
        $this->_throwError('id');
      }

      if($this->_getMethod() === FALSE)
      {
        $this->_throwError('method');
      }

      if($this->_getData() === FALSE)
      {
        $this->_throwError('data');
      }

      // all cool, fetch now data
      return $this->_fetchFromApi();
    }
  }