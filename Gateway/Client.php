<?php

namespace ReversIo\RMA\Gateway;

class Client
{
    protected $scopeConfig;

    protected $cache;

    protected $genericResponseFactory;

    protected $getTokenRequestFactory;

    protected $retrieveModelTypesRequestFactory;

    protected $retrieveBrandsRequestFactory;

    protected $createBrandRequestFactory;

    protected $updateBrandRequestFactory;

    protected $retrieveModelBySKURequestFactory;

    protected $createModelRequestFactory;

    protected $updateModelRequestFactory;

    protected $importOrderRequestFactory;

    protected $retrieveOrderRequestFactory;

    protected $createSignedInLinkRequestFactory;

    protected $encryptor;

    protected $logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\CacheInterface $cache,
        \ReversIo\RMA\Gateway\Response\GenericResponseFactory $genericResponseFactory,
        \ReversIo\RMA\Gateway\Request\GetTokenFactory $getTokenRequestFactory,
        \ReversIo\RMA\Gateway\Request\RetrieveModelTypesFactory $retrieveModelTypesRequestFactory,
        \ReversIo\RMA\Gateway\Request\RetrieveBrandsFactory $retrieveBrandsRequestFactory,
        \ReversIo\RMA\Gateway\Request\CreateBrandFactory $createBrandRequestFactory,
        \ReversIo\RMA\Gateway\Request\UpdateBrandFactory $updateBrandRequestFactory,
        \ReversIo\RMA\Gateway\Request\RetrieveModelBySKUFactory $retrieveModelBySKURequestFactory,
        \ReversIo\RMA\Gateway\Request\CreateModelFactory $createModelRequestFactory,
        \ReversIo\RMA\Gateway\Request\UpdateModelFactory $updateModelRequestFactory,
        \ReversIo\RMA\Gateway\Request\ImportOrderFactory $importOrderRequestFactory,
        \ReversIo\RMA\Gateway\Request\RetrieveOrderFactory $retrieveOrderRequestFactory,
        \ReversIo\RMA\Gateway\Request\CreateSignedInLinkFactory $createSignedInLinkRequestFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->cache = $cache;
        $this->genericResponseFactory = $genericResponseFactory;
        $this->getTokenRequestFactory = $getTokenRequestFactory;
        $this->retrieveModelTypesRequestFactory = $retrieveModelTypesRequestFactory;
        $this->retrieveBrandsRequestFactory = $retrieveBrandsRequestFactory;
        $this->createBrandRequestFactory = $createBrandRequestFactory;
        $this->updateBrandRequestFactory = $updateBrandRequestFactory;
        $this->retrieveModelBySKURequestFactory = $retrieveModelBySKURequestFactory;
        $this->createModelRequestFactory = $createModelRequestFactory;
        $this->updateModelRequestFactory = $updateModelRequestFactory;
        $this->importOrderRequestFactory = $importOrderRequestFactory;
        $this->retrieveOrderRequestFactory = $retrieveOrderRequestFactory;
        $this->createSignedInLinkRequestFactory = $createSignedInLinkRequestFactory;
        $this->logger = $logger;
    }

    protected function initRequest(\ReversIo\RMA\Gateway\Request\AbstractRequest $request)
    {
        $apiUrl = null;
        $environment = $this->scopeConfig->getValue('reversio_rma/api/environment');

        switch ($environment) {
            case \ReversIo\RMA\Helper\Constants::REVERSIO_ENVIRONMENT_TEST:
                $apiUrl = \ReversIo\RMA\Helper\Constants::REVERSIO_TEST_API_URL;
                break;
            case \ReversIo\RMA\Helper\Constants::REVERSIO_ENVIRONMENT_PROD:
                $apiUrl = \ReversIo\RMA\Helper\Constants::REVERSIO_PROD_API_URL;
                break;
            case \ReversIo\RMA\Helper\Constants::REVERSIO_ENVIRONMENT_CUSTOM:
                $apiUrl = $this->scopeConfig->getValue('reversio_rma/api/custom_url');
                break;
            default:
                break;
        }

        $request->init(
            $apiUrl,
            $this->scopeConfig->getValue('reversio_rma/api/subscription_key'),
            $request instanceof \ReversIo\RMA\Gateway\Request\GetToken ? null : $this->getToken()
        );

        return $this;
    }

    public function sendRequest(\ReversIo\RMA\Gateway\Request\AbstractRequest $request)
    {
        $client = new \Zend\Http\Client();
        $options = [
           'adapter'   => 'Zend\Http\Client\Adapter\Curl',
           'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
           'maxredirects' => 0,
           'timeout' => 30
        ];
        $client->setOptions($options);

        $response = $client->send($request->getGatewayRequest());

        // FALLBACK IN CASE JWT TOKEN HAS EXPIRED
        if (!$request instanceof \ReversIo\RMA\Gateway\Request\GetToken
         && $response->getStatusCode() == \Zend\Http\Response::STATUS_CODE_401) {
            $request->setToken($this->getToken(true));
            $response = $client->send($request->getGatewayRequest());
        }

        return $response;
    }

    public function getToken($forceRecall = false)
    {
        // RETRIEVE JWT TOKEN FROM CACHE TO AVOID RELOAD IT FOR EACH REQUEST
        $token = $this->cache->load(\ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_API_JWT_TOKEN);

        if (!$token || $forceRecall) {
            $request = $this->getTokenRequestFactory->create()
                ->setSecret($this->encryptor->decrypt($this->scopeConfig->getValue('reversio_rma/api/secret')));
            $response = $this->genericResponseFactory->create();

            try {
                $token = $this->handleSendRequestAndEvalResponse($response, $request, 'getToken');
                $this->cache->save(
                    $token,
                    \ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_API_JWT_TOKEN,
                    [\Magento\Config\App\Config\Type\System::CACHE_TAG],
                    false
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $token;
    }

    public function retrieveModelTypes()
    {
        $request = $this->retrieveModelTypesRequestFactory->create();
        $response = $this->genericResponseFactory->create();

        try {
            $modelTypes = $this->handleSendRequestAndEvalResponse($response, $request, 'retrieveModelTypes');
        } catch (\Exception $e) {
            throw $e;
        }

        return $modelTypes;
    }

    public function retrieveBrands()
    {
        $request = $this->retrieveBrandsRequestFactory->create();
        $response = $this->genericResponseFactory->create();

        try {
            $brands = $this->handleSendRequestAndEvalResponse($response, $request, 'retrieveBrands');
        } catch (\Exception $e) {
            throw $e;
        }

        return $brands;
    }

    public function createBrand($brandName)
    {
        $request = $this->createBrandRequestFactory->create()
            ->setBrandName($brandName);
        $response = $this->genericResponseFactory->create();

        try {
            $brand = $this->handleSendRequestAndEvalResponse($response, $request, 'createBrand');
        } catch (\Exception $e) {
            throw $e;
        }

        return $brand;
    }

    public function updateBrand($brandName)
    {
        $request = $this->updateBrandRequestFactory->create()
            ->setBrandName($brandName);
        $response = $this->genericResponseFactory->create();

        try {
            $brand = $this->handleSendRequestAndEvalResponse($response, $request, 'updateBrand');
        } catch (\Exception $e) {
            throw $e;
        }

        return $brand;
    }

    public function retrieveModelBySKU($sku)
    {
        $request = $this->retrieveModelBySKURequestFactory->create()
            ->setSku($sku);
        $response = $this->genericResponseFactory->create();

        try {
            $model = $this->handleSendRequestAndEvalResponse($response, $request, 'retrieveModelBySKU');
        } catch (\Exception $e) {
            throw $e;
        }

        return $model;
    }

    public function createModel(\Magento\Catalog\Model\Product $product, $brandId, $modelTypeId)
    {
        $request = $this->createModelRequestFactory->create()
            ->setProduct($product)
            ->setBrandId($brandId)
            ->setModelTypeId($modelTypeId);
        $response = $this->genericResponseFactory->create();

        try {
            $model = $this->handleSendRequestAndEvalResponse($response, $request, 'createModel');
        } catch (\Exception $e) {
            throw $e;
        }

        return $model;
    }

    public function updateModel($modelId, \Magento\Catalog\Model\Product $product, $brandId, $modelTypeId)
    {
        $request = $this->updateModelRequestFactory->create()
            ->setModelId($modelId)
            ->setProduct($product)
            ->setBrandId($brandId)
            ->setModelTypeId($modelTypeId);
        $response = $this->genericResponseFactory->create();

        try {
            $model = $this->handleSendRequestAndEvalResponse($response, $request, 'updateModel');
        } catch (\Exception $e) {
            throw $e;
        }

        return $model;
    }

    public function importOrder(\Magento\Sales\Model\Order $order, \Magento\Customer\Model\Customer $customer, $modelIds)
    {
        $request = $this->importOrderRequestFactory->create()
            ->setOrder($order)
            ->setCustomer($customer)
            ->setModelIds($modelIds);
        $response = $this->genericResponseFactory->create();

        try {
            $orderId = $this->handleSendRequestAndEvalResponse($response, $request, 'importOrder');
        } catch (\Exception $e) {
            throw $e;
        }

        return $orderId;
    }

    public function retrieveOrder($orderReference)
    {
        $request = $this->retrieveOrderRequestFactory->create()
            ->setOrderReference($orderReference);
        $response = $this->genericResponseFactory->create();

        try {
            $order = $this->handleSendRequestAndEvalResponse($response, $request, 'retrieveOrder');
        } catch (\Exception $e) {
            throw $e;
        }

        return $order;
    }

    public function createSignedInLink($orderId)
    {
        $request = $this->createSignedInLinkRequestFactory->create()
            ->setOrderId($orderId);
        $response = $this->genericResponseFactory->create();

        try {
            $link = $this->handleSendRequestAndEvalResponse($response, $request, 'createSignedInLink');
        } catch (\Exception $e) {
            throw $e;
        }

        return $link;
    }

    protected function handleSendRequestAndEvalResponse(
        \ReversIo\RMA\Gateway\Response\AbstractResponse $response,
        \ReversIo\RMA\Gateway\Request\AbstractRequest $request,
        $serviceName
    )
    {
        $response->fromGatewayResponse($this
            ->initRequest($request)
            ->sendRequest($request)
        );

        if ($this->scopeConfig->getValue('reversio_rma/api/debug')) {
            $this->logger->log(\Monolog\Logger::DEBUG, $request->__toString());
            $this->logger->log(\Monolog\Logger::DEBUG, $response->__toString());
        }

        if (!$response->isSuccess()) {
            if (!$this->scopeConfig->getValue('reversio_rma/api/debug')) {
                $this->logger->log(\Monolog\Logger::DEBUG, $request->__toString());
                $this->logger->log(\Monolog\Logger::DEBUG, $response->__toString());
            }
            throw new \Exception(__('Cannot call Revers.io %1 service for reason : %2', $serviceName, $response->getErrorMessage()));
        } else {
            return $response->getValue();
        }
    }
}
