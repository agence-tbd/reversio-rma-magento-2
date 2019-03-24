<?php

namespace ReversIo\RMA\Gateway\Request;

class RetrieveModelBySKU extends AbstractRequest
{
    protected $sku;
    
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }
    
    protected function getServiceEndpoint()
    {
        return 'catalog/models/bySku/{id}';
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_GET;
    }
    
    public function getId()
    {
        return $this->sku;
    }
}