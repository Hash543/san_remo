<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;

class NewAction extends AbstractPolicy
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
