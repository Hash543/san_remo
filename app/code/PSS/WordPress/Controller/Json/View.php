<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PSS\WordPress\Controller\Json;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use PSS\WordPress\Helper\Core as CoreHelper;

class View extends Action
{
    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * View constructor.
     * @param Context $context
     * @param CoreHelper $coreHelper
     */
    public function __construct(Context $context, CoreHelper $coreHelper)
    {
        $this->coreHelper = $coreHelper;

        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            if (!($coreHelper = $this->coreHelper->getHelper())) {
                throw new \Exception("No core helpers defined.");
            }

            if (!$coreHelper->isActive()) {
                throw new \Exception("Core helper not active.");
            }

            exit;
        } catch (\Exception $e) {
            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
                ->setModule('cms')
                ->setController('noroute')
                ->forward('index');
        }
    }
}
