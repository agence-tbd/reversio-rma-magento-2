<?php

namespace ReversIo\RMA\Ui\DataProvider\Entity\Form\Modifier;

class ModelType implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    protected $modelTypeSource;

    public function __construct(
        \ReversIo\RMA\Model\Entity\Attribute\Source\ModelType $modelTypeSource
    ) {
        $this->modelTypeSource = $modelTypeSource;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        if ($name = $this->getGeneralPanelName($meta)) {
            $meta[$name]['children']['reversio_modeltype']['arguments']['data']['config']  = [
                'component' => 'Magento_Ui/js/form/element/ui-select',
                'disableLabel' => true,
                'filterOptions' => true,
                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                'formElement' => 'select',
                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                'options' => $this->modelTypeSource->getAllOptions(),
                'visible' => 1,
                'required' => 1,
                'label' => __('ReversIo ModelType'),
                'source' => $name,
                'dataScope' => 'reversio_modeltype',
                'filterUrl' => $this->urlBuilder->getUrl('catalog/product/suggestAttributeSets', ['isAjax' => 'true']),
                'sortOrder' => 10,
                'multiple' => false,
                'disabled' => false,
            ];
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
