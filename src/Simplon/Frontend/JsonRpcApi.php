<?php

  namespace Simplon\Frontend;

  class JsonRpcApi
  {
    /** @var array */
    private $_data = array();

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
      // create request
      $curl = \CURL::init($this->_getUrl())
        ->addHttpHeader('Content-type', 'application/json')
        ->setPost(TRUE)
        ->setPostFields($this->_getDataAsJson())
        ->setReturnTransfer(TRUE);

      // use proxy
      if($this->hasProxy())
      {
        $curl
          ->setProxy($this->getProxyHost())
          ->setProxyPort($this->getProxyPort());
      }

      // send request
      $response = $curl->execute();

      // decode json data
      $data = json_decode($response, TRUE);

      // valid json-rpc response
      if(! isset($data['result']))
      {
        $error = 'Unknown error.';

        if(isset($data['error']))
        {
          $error = $data['error'];
        }

        return ['error' => $error];
      }

      // and out
      return $data['result'];
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
     * @return bool
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
    public function setUrl($url)
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
      return rtrim($this->_getByKey('url'), '/') . '/';
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
     * @return bool|string
     */
    protected function _getId()
    {
      $id = $this->_getByKey('id');

      return $id === FALSE ? 1 : $id;
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
     * @return string
     */
    protected function _getDataAsJson()
    {
      return json_encode($this->_getData());
    }

    // ##########################################

    /**
     * @param string $host
     * @param int $port
     * @return JsonRpcApi
     */
    public function setProxy($host = '127.0.0.1', $port = 8888)
    {
      if(isset($host) && isset($port))
      {
        $this->_setByKey('useProxy', TRUE);
        $this->_setByKey('proxyHost', $host);
        $this->_setByKey('proxyPort', $port);
      }

      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function hasProxy()
    {
      $useProxy = $this->_getByKey('useProxy');

      if(isset($useProxy))
      {
        return TRUE;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function getProxyHost()
    {
      $host = $this->_getByKey('proxyHost');

      if(isset($host))
      {
        return $host;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function getProxyPort()
    {
      $port = $this->_getByKey('proxyPort');

      if(isset($port))
      {
        return $port;
      }

      return FALSE;
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