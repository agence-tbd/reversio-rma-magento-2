<?php

namespace ReversIo\RMA\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallData implements InstallDataInterface
{
    protected $eavSetupFactory;

    protected $eavConfig;

    protected $attributeFactory;

    protected $attributeSetFactory;

    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeFactory = $attributeFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this-> eavConfig =$eavConfig;
    }


    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $productEntityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        $categoryEntityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Category::ENTITY)->getId();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, \ReversIo\RMA\Helper\Constants::REVERSIO_MODEL_TYPE_ATTRIBUTE_CODE, [
            'type'         => 'varchar',
            'label'        => 'ReversIo ModelType',
            'input'        => 'select',
            'source'       => 'ReversIo\RMA\Model\Entity\Attribute\Source\ModelType',
            'visible'      => true,
            'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order'   => 2000,
            'required'     => false,
            'user_defined' => true,
        ]);

        $attributeData = $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, \ReversIo\RMA\Helper\Constants::REVERSIO_MODEL_TYPE_ATTRIBUTE_CODE);

        foreach($this->attributeSetFactory->create()->getResourceCollection()->addFieldToFilter('entity_type_id', $productEntityTypeId) as $attributeSet) {
            $attributeSetId = $attributeSet->getId();

            $eavSetup->addAttributeGroup(
                $productEntityTypeId, $attributeSetId, \ReversIo\RMA\Helper\Constants::REVERSIO_RMA_ATTRIBUTE_GROUP_NAME
            );
            $groupData = $eavSetup->getAttributeGroup(
                \Magento\Catalog\Model\Product::ENTITY, $attributeSetId, \ReversIo\RMA\Helper\Constants::REVERSIO_RMA_ATTRIBUTE_GROUP_NAME
            );
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $groupData['attribute_group_id'], $attributeData['attribute_id']
            );
        }

        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, \ReversIo\RMA\Helper\Constants::REVERSIO_MODEL_TYPE_ATTRIBUTE_CODE, [
            'type'         => 'varchar',
            'label'        => 'ReversIo ModelType',
            'input'        => 'select',
            'source'       => 'ReversIo\RMA\Model\Entity\Attribute\Source\ModelType',
            'visible'      => true,
            'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order'   => 2000,
            'required'     => false,
            'user_defined' => true,
        ]);

        $attributeData = $eavSetup->getAttribute(\Magento\Catalog\Model\Category::ENTITY, \ReversIo\RMA\Helper\Constants::REVERSIO_MODEL_TYPE_ATTRIBUTE_CODE);
        $attributeSetId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Category::ENTITY)->getDefaultAttributeSetId();

        $eavSetup->addAttributeGroup(
            $categoryEntityTypeId, $attributeSetId, \ReversIo\RMA\Helper\Constants::REVERSIO_RMA_ATTRIBUTE_GROUP_NAME
        );
        $groupData = $eavSetup->getAttributeGroup(
            \Magento\Catalog\Model\Category::ENTITY, $attributeSetId, \ReversIo\RMA\Helper\Constants::REVERSIO_RMA_ATTRIBUTE_GROUP_NAME
        );
        $eavSetup->addAttributeToGroup(
            \Magento\Catalog\Model\Category::ENTITY, $attributeSetId, $groupData['attribute_group_id'], $attributeData['attribute_id']
        );
    }
}

