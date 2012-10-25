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
     * @param $message
     * @throws \Exception
     */
    protected function _throwError($message)
    {
      throw new \Exception(__NAMESPACE__ . '/' . __CLASS__ . ': ' . $message, 500);
    }

    // ##########################################

    /**
     * @return mixed
     */
    protected function _fetchFromApi()
    {
      $response = \CURL::init($this->_getUrl())
        ->addHttpHeader('Content-type', 'application/json')
        ->setPost(TRUE)
        ->setPostFields($this->_getData())
        ->setReturnTransfer(TRUE)
        ->execute();

      if(! isset($response['result']))
      {
        $this->_throwError('Invalid JSON-RPC response');
      }

      return json_decode($response['result'], TRUE);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return JsonRpcApi
     */
    protected function _setByKey($key, $value)
    {
      $this->_data[$key] = $value;

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|string
     */
    protected function _getByKey($key)
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
      $this->_setByKey('url', $url);

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getUrl()
    {
      return $this->_getByKey('url');
    }

    // ##########################################

    /**
     * @param $method
     * @return JsonRpcApi
     */
    protected function _setRequestMethod($method)
    {
      $this->_setByKey('requestMethod', $method);

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getRequestMethod()
    {
      return $this->_getByKey('requestMethod');
    }

    // ##########################################

    /**
     * @param $id
     * @return JsonRpcApi
     */
    public function setId($id)
    {
      $this->_setByKey('id', $id);

      return $this;
    }

    // ##########################################

    /**
     * @param $method
     * @return JsonRpcApi
     */
    public function setMethod($method)
    {
      $this->_setByKey('method', $method);

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getId()
    {
      return $this->_getByKey('id');
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getMethod()
    {
      return $this->_getByKey('method');
    }

    // ##########################################

    /**
     * @param $data
     * @return JsonRpcApi
     */
    public function setData($data)
    {
      $this->_setByKey('data', $data);

      return $this;
    }

    // ##########################################

    /**
     * @return array|bool
     */
    protected function _getData()
    {
      $data = $this->_getByKey('data');

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
     * @return mixed
     * @throws \Exception
     */
    public function run()
    {
      if($this->_getUrl() === FALSE)
      {
        $this->_throwError('missing <url>');
      }

      if($this->_getId() === FALSE)
      {
        $this->_throwError('missing <id>');
      }

      if($this->_getMethod() === FALSE)
      {
        $this->_throwError('missing <method>');
      }

      if($this->_getData() === FALSE)
      {
        $this->_throwError('missing <data>');
      }

      // all cool, fetch now data
      return $this->_fetchFromApi();
    }
  }