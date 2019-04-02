<?php

namespace ReversIo\RMA\Model;

class BrandRepository
{
    protected $reversIoClient;

    protected $cache;

    public function __construct(
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->reversIoClient = $reversIoClient;
        $this->cache = $cache;
    }

    public function getList()
    {
        $result = $this->cache->load(\ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_BRANDS);

        if (empty($result)) {
            $result = $this->reversIoClient->retrieveBrands();
            $this->cache->save(
                serialize($result),
                \ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_BRANDS,
                [\Magento\Config\App\Config\Type\System::CACHE_TAG],
                false
            );
        } else {
            $result = unserialize($result);
        }

        return $result;
    }

    public function saveBrand($brandName)
    {
        if (empty($brandName)) {
            throw new \Exception(__('Cannot save a brand with empty name.'));
        }

        $brands = $this->getList();

        foreach ($brands as $brand) {
            if ($brand['name'] == $brandName) {
                return $brand;
            }
        }

        $result = $this->reversIoClient->createBrand($brandName);
        $this->cache->remove(\ReversIo\RMA\Helper\Constants::CACHE_KEY_REVERSIO_BRANDS);
        return $result;
    }
}
