<?php

namespace ReversIo\RMA\Gateway\Request;

class CreateModel extends SaveModel
{
    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_PUT;
    }

    protected function getServiceEndpoint()
    {
        return 'catalog/models';
    }
}