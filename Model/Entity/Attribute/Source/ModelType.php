<?php

namespace ReversIo\RMA\Model\Entity\Attribute\Source;

class ModelType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $modelTypeRepository;

    public function __construct(
        \ReversIo\RMA\Model\ModelTypeRepository $modelTypeRepository
    )
    {
        $this->modelTypeRepository = $modelTypeRepository;
    }

    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [];
            foreach ($this->modelTypeRepository->getList() as $modelType) {
                $this->_options[] = ['value' => $modelType['key'], 'label' => $modelType['label']];
            }
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a modelType...')]);
        }

        return $this->_options;
    }
}