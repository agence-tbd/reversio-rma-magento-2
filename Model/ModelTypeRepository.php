<?php

namespace ReversIo\RMA\Model;

class ModelTypeRepository
{
    protected $reversIoClient;

    public function __construct(
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        $this->reversIoClient = $reversIoClient;
        $this->cache = $cache;
    }

    public function getList()
    {
        $result = $this->cache->load(\ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_MODELTYPES);

        if (empty($result)) {
            $result = $this->reversIoClient->retrieveModelTypes();
            $this->cache->save(
                serialize($result),
                \ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_MODELTYPES,
                [\Magento\Config\App\Config\Type\System::CACHE_TAG],
                7200
            );
        } else {
            $result = unserialize($result);
        }

        return $result;
    }

    public function getModelTypeByKey($modelTypeKey)
    {
        $modelTypes = $this->getList();

        foreach ($modelTypes as $modelType) {
            if ($modelTypeKey == $modelType['key']) {
                return $modelType;
            }
        }

        return null;
    }
}

