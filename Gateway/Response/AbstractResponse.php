<?php

namespace ReversIo\RMA\Gateway\Response;

abstract class AbstractResponse
{
    protected $data;

    protected $returnCode;

    public function fromGatewayResponse(\Zend\Http\Response $response)
    {
        $this->data = json_decode($response->getBody(), true);
        $this->returnCode = $response->getStatusCode();
    }

    public function getData()
    {
        return $this->data;
    }

    public function isSuccess()
    {
        return $this->returnCode == \Zend\Http\Response::STATUS_CODE_200;
    }

    public function getErrorMessage()
    {
        if (isset($this->data['message'])) {
            return $this->data['message'];
        } elseif ($this->data['errors']) {
            $message = [];

            foreach ($this->data['errors'] as $error) {
                $message[] = $error['message'];
            }

            return implode(', ', $message);
        } else {
            return '';
        }
    }

    public function getValue()
    {
        return $this->data['value'];
    }

    public function __toString()
    {
        return $this->returnCode . ' ' . print_r($this->getData(), true);
    }
}
