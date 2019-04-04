<?php

namespace ReversIo\RMA\Model\Entity\Attribute\Source;

class ModelType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $modelTypeRepository;

    protected $removeAccentsFilter;

    public function __construct(
        \ReversIo\RMA\Model\ModelTypeRepository $modelTypeRepository,
        \Magento\Framework\Filter\RemoveAccents $removeAccentsFilter
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->removeAccentsFilter = $removeAccentsFilter;
    }

    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [];
            try {
                $modelTypes = $this->modelTypeRepository->getList();
            } catch (\Exception $e) {
                $modelTypes = [];
            }

            foreach ($modelTypes as $modelType) {
                $this->_options[] = ['value' => $modelType['key'], 'label' => $modelType['label']];
            }
            usort($this->_options, function ($a, $b) {
                $a['label'] = $this->removeAccentsFilter->filter($a['label']);
                $b['label'] = $this->removeAccentsFilter->filter($b['label']);
                if ($a['label'] == $b['label']) {
                    return 0;
                }
                return $a['label'] < $b['label'] ? -1 : 1;
            });
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a modelType...')]);
        }

        return $this->_options;
    }
}
