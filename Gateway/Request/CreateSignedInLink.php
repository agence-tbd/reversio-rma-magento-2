<?php

namespace ReversIo\RMA\Gateway\Request;

class CreateSignedInLink extends SaveBrand
{
    protected $orderId;

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_POST;
    }

    protected function getServiceEndpoint()
    {
        return 'links';
    }

    public function getData()
    {
        return ['orderId' => $this->orderId];
    }
}