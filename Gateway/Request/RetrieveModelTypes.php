<?php

namespace ReversIo\RMA\Gateway\Request;

class RetrieveModelTypes extends AbstractRequest
{
    protected function getServiceEndpoint()
    {
        return 'catalog/modelTypes';
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_GET;
    }
}
