<?php

namespace ReversIo\RMA\Gateway\Request;

class RetrieveOrder extends AbstractRequest
{
    protected $orderReference;
    
    public function setOrderReference($orderReference)
    {
        $this->orderReference = $orderReference;
        return $this;
    }
    
    protected function getServiceEndpoint()
    {
        return 'orders/{id}/status';
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_GET;
    }
    
    public function getId()
    {
        return $this->orderReference;
    }
}