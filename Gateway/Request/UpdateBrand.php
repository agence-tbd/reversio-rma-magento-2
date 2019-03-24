<?php

namespace ReversIo\RMA\Gateway\Request;

class UpdateBrand extends SaveBrand
{
    protected $brandId;

    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;
        return $this;
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_POST;
    }

    protected function getServiceEndpoint()
    {
        return 'catalog/brands/{id}';
    }

    public function getId()
    {
        return $this->brandId;
    }
}