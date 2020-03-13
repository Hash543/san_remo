<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractRequest extends Action
{
    const ADMIN_RESOURCE = 'Amasty_Gdpr::requests';
}
