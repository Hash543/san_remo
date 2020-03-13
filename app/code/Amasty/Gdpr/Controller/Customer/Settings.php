<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Framework\Controller\ResultFactory;

class Settings extends AbstractAccountAction
{
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
