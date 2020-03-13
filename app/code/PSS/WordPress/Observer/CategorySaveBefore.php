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
namespace PSS\WordPress\Observer;

class CategorySaveBefore implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageInterface;

    /**
     * @var \PSS\WordPress\Helper\Data
     */
    private $helperData;

    /**
     * ProductSaveAfter constructor.
     * @param \PSS\WordPress\Helper\Data $helperData
     * @param \Magento\Framework\Message\ManagerInterface $messageInterface
     */
    public function __construct(
        \PSS\WordPress\Helper\Data $helperData,
        \Magento\Framework\Message\ManagerInterface $messageInterface
    ) {
        $this->helperData = $helperData;
        $this->messageInterface = $messageInterface;
    }

    /**
     * Observer after save the product to save the relation between the post and product
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getData('request');
        if($request === null) {
            return;
        }
        $params = $request->getParams();
        $typeId = $this->helperData->getTypeIdFromCategoryToPost();
        $categoryId = null;
        $storeId = 0;
        $relatedPosts = null;
        if(isset($params['entity_id']) && !empty($params['entity_id'])) {
            $categoryId = $params['entity_id'];
        }
        if(isset($params['store'])) {
            $storeId = $params['store'];
        }
        if($typeId === null || $categoryId === null ) {
            return;
        }

        if(isset($params['related_post']) && !empty($params['related_post'])) {
            $relatedPosts = explode('&', $params['related_post']);
            $this->helperData->saveRelationWithPost($typeId, $relatedPosts, $categoryId, $storeId);
        } else {
            $this->helperData->deleteRelationNotFound($typeId, $categoryId, []); //clean all the relations
        }
    }

}