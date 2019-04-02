<?php

namespace ReversIo\RMA\Gateway\Request;

class CreateBrand extends SaveBrand
{
    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_PUT;
    }

    protected function getServiceEndpoint()
    {
        return 'catalog/brands';
    }
}
