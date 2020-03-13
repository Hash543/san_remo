<?php

namespace Firebear\ConfigurableProducts\Model\Bundle\ResourceModel\Option;

/**
 * Bundle Options Resource Collection
 */
class Collection extends \Magento\Bundle\Model\ResourceModel\Option\Collection
{
    /**
     * Append selection to options
     * stripBefore - indicates to reload
     * appendAll - indicates do we need to filter by saleable and required custom options
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool                                                     $stripBefore
     * @param bool                                                     $appendAll
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function appendSelections($selectionsCollection, $stripBefore = false, $appendAll = true)
    {
        if ($stripBefore) {
            $this->_stripSelections();
        }

        if (!$this->_selectionsAppended) {
            foreach ($selectionsCollection->getItems() as $key => $selection) {
                $option = $this->getItemById($selection->getOptionId());
                if ($option) {
                    if ($appendAll || $selection->isSalable()) {
                        $selection->setOption($option);
                        $option->addSelection($selection);
                    } else {
                        $selectionsCollection->removeItemByKey($key);
                    }
                }
            }
            $this->_selectionsAppended = true;
        }

        return $this->getItems();
    }
}
