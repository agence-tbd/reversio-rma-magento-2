<?php

namespace ReversIo\RMA\Gateway\Request;

abstract class SaveBrand extends AbstractRequest
{
    protected $brandName;

    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
        return $this;
    }

    public function getData()
    {
        return ['name' => $this->brandName];
    }
}
