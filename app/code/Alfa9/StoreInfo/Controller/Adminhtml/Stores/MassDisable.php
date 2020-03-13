<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use Alfa9\StoreInfo\Model\Stores;

class MassDisable extends MassAction
{
    /**
     * @var bool
     */
    public $status = false;

    /**
     * @param Stores $stockist
     * @return $this
     */
    public function massAction(Stores $stockist)
    {
        $stockist->setStatus($this->status);
        $this->stockistRepository->save($stockist);
        return $this;
    }
}
