<?php

namespace ReversIo\RMA\Gateway\Request;

class RetrieveBrands extends AbstractRequest
{
    protected function getServiceEndpoint()
    {
        return 'catalog/brands';
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_GET;
    }
}
