<?php

namespace ReversIo\RMA\Gateway\Request;

class UpdateModel extends SaveModel
{
    protected $modelId;

    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
        return $this;
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_POST;
    }

    protected function getServiceEndpoint()
    {
        return 'catalog/models/{id}';
    }

    public function getId()
    {
        return $this->modelId;
    }
}