<?php

namespace ReversIo\RMA\Gateway\Request;

class ImportOrder extends AbstractRequest
{
    protected $order;

    protected $customer;

    protected $modelIds;

    public function setModelIds($modelIds)
    {
        $this->modelIds = $modelIds;
        return $this;
    }

    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setCustomer(\Magento\Customer\Model\Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    protected function getServiceEndpoint()
    {
        return 'orders';
    }

    protected function getServiceMethod()
    {
        return \Zend\Http\Request::METHOD_PUT;
    }

    public function getData()
    {
        if (!isset($this->order)) {
            throw new \Exception('Unkown order.');
        }

        return array_merge([
            'orderReference' => $this->order->getIncrementId(),
            'phoneNumber' => $this->getPhoneNumber(),
            'address' => $this->getAddress(),
            'purchaseDateUtc' => $this->order->getCreatedAt(),
            'products' => $this->getProducts(),
            'shippingPrice' => $this->getShippingPrice(),
        ], $this->getCustomerData());
    }

    public function getProducts()
    {
        $result = [];

        foreach ($this->order->getAllVisibleItems() as $item) {
            for ($i = 1; $i <= $item->getQtyOrdered(); $i++) {
                $result[] = [
                    'modelId' => isset($this->modelIds[$item->getSku()]) ? $this->modelIds[$item->getSku()] : null,
                    'price' => [
                        'amount' => $item->getPriceInclTax() - ($item->getDiscountAmount() / $item->getQtyOrdered()),
                        'currency' => $this->order->getBaseCurrencyCode(),
                    ],
                    'orderLineReference' => $item->getId(),
                ];
            }
        }

        return $result;
    }

    public function getCustomerData()
    {
        if ($this->customer && $this->customer->getId()) {
            return [
                'civility' => $this->customer->getPrefix(),
                'customerLastName' => $this->customer->getLastname(),
                'customerFirstName' => $this->customer->getFirstname(),
                'customerMail' => $this->order->getCustomerEmail(),
            ];
        } else {
            return [
                'civility' => $this->order->getCustomerPrefix(),
                'customerLastName' => $this->order->getCustomerLastname(),
                'customerFirstName' => $this->order->getCustomerFirstname(),
                'customerMail' => $this->order->getCustomerEmail(),
            ];
        }
    }
    
    public function getAddress()
    {
        $address = $this->order->getShippingAddress()
            ? $this->order->getShippingAddress()
            : $this->order->getBillingAddress();
        
        return [
            'companyName' => '',
            'streetAddress' => $address->getStreetLine(1),
            'additionalAddress' => $address->getStreetLine(2),
            'doorCode' => '',
            'floor' => '',
            'zipCode' => $address->getPostcode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryId(),
        ];
    }
    
    public function getPhoneNumber()
    {
        if ($this->order->getShippingAddress()) {
            return $this->order->getShippingAddress()->getTelephone();
        } else {
            return $this->order->getBillingAddress()->getTelephone();
        }
    }
    
    public function getShippingPrice()
    {
        return  [
            'amount' => $this->order->getBaseShippingAmount(),
            'currency' => $this->order->getBaseCurrencyCode(),
        ];
    }
}
