<?php

namespace Firebear\ConfigurableProducts\Plugin;

class AfterCheckQty
{
    public function afterCheckQty(\Magento\CatalogInventory\Model\StockStateProvider $stockStateProvider, $result)
    {
        //Todo: we should check this part because I am not sure why Firebear is not controlling the stock
        return $result;
    }
}
