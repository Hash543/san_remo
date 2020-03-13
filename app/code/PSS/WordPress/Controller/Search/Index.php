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
namespace PSS\WordPress\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use PSS\WordPress\Model\SearchFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    /**
     * @var
     **/
    protected $searchFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param SearchFactory $resultPageFactory
     */
    public function __construct(Context $context, SearchFactory $searchFactory)
    {
        parent::__construct($context);

        $this->searchFactory = $searchFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($this->searchFactory->create()->getUrl());
    }
}
