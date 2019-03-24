<?php

namespace ReversIo\RMA\Model\Config\Source;

class BrandAttributeCode implements \Magento\Framework\Option\ArrayInterface
{
    protected $attributeCollectionFactory;

    protected $eavConfig;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
    )
    {
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    public function toOptionArray()
    {
        $result = [];

        $attributeCollection = $this->attributeCollectionFactory->create()
                ->addFieldToFilter('frontend_input', ['in' => ['select']])
                ->addFieldToFilter('entity_type_id', $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId());

        foreach ($attributeCollection as $attribute) {
            $result[] = ['value' => $attribute->getAttributeCode(), 'label' => $attribute->getAttributeCode()];
        }
        array_unshift($result, ['value' => '', 'label' => __('Please select a brand attribute code...')]);

        return $result;
    }
}

