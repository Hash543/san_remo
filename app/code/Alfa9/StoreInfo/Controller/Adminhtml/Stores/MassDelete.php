<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use Alfa9\StoreInfo\Model\Stores;

class MassDelete extends MassAction
{
    /**
     * @param Stores $stockist
     * @return $this
     */
    public function massAction(Stores $stockist)
    {
        $this->stockistRepository->delete($stockist);
        return $this;
    }
}
