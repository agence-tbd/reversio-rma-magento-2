<?php

namespace ReversIo\RMA\Gateway\Request;

abstract class AbstractRequest
{
    protected $apiUri;

    protected $key;

    protected $token;

    protected $storedData;

    public function init($apiUri, $key, $token = null)
    {
        $this->apiUri = $apiUri;
        $this->key = $key;
        $this->token = $token;

        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    abstract protected function getServiceEndpoint();

    abstract protected function getServiceMethod();

    public function getId()
    {
        return null;
    }

    public function getData()
    {
        return [];
    }

    public function getStoredData()
    {
        if (!isset($this->storedData)) {
            $this->storedData = $this->getData();
        }
        
        return $this->storedData;
    }
    
    public function needToken()
    {
        return true;
    }
    
    public function getGatewayRequest()
    {
        $headers = new \Zend\Http\Headers();
        $headers->addHeaders([
           'Ocp-Apim-Subscription-Key' => $this->key,
           'Accept' => 'application/json',
           'Content-Type' => 'application/json'
        ]);

        if ($this->needToken()) {
            $headers->addHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ]);
        }

        $params = new \Zend\Stdlib\Parameters($this->getStoredData());

        $request = new \Zend\Http\Request();
        $request->setHeaders($headers);
        $request->setUri($this->getServiceUri());
        $request->setMethod($this->getServiceMethod());

        switch ($request->getMethod()) {
            case \Zend\Http\Request::METHOD_GET:
                $request->setQuery($params);
                break;
            default:
                $request->setContent(json_encode($params));
                break;
        }

        return $request;
    }

    protected function getServiceUri()
    {
        $serviceUri = $this->apiUri.$this->getServiceEndpoint();
        return $this->getId() ? str_replace('{id}', $this->getId(), $serviceUri) : $serviceUri;
    }

    public function __toString()
    {
        return $this->getServiceUri() . ' ' . $this->getServiceMethod() . ' ' . print_r($this->getStoredData(), true);
    }
}
