<?php

namespace ReversIo\RMA\Gateway\Request;

class GetToken extends AbstractRequest
{
    protected $secret;

    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    protected function getServiceEndpoint()
    {
        return 'token';
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_GET;
    }

    public function getData()
    {
        return ['secret' => $this->secret];
    }
}