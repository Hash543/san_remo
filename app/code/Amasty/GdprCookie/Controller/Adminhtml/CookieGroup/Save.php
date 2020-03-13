<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Adminhtml\CookieGroup;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieGroup;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\CookieGroupLink;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink as LinkResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupDescription as DescriptionResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupDescription\Collection as DescriptionCollection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection as LinkCollection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;

class Save extends AbstractCookieGroup
{
    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupRepository;

    /**
     * @var LinkResource
     */
    private $linkResource;

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CookieGroupFactory
     */
    private $cookieGroupFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DescriptionResource
     */
    private $descriptionResource;

    /**
     * @var DescriptionCollection
     */
    private $descriptionCollection;

    public function __construct(
        Context $context,
        CookieGroupsRepository $cookieGroupRepository,
        LinkResource $linkResource,
        LinkCollection $linkCollection,
        DescriptionResource $descriptionResource,
        DescriptionCollection $descriptionCollection,
        DataPersistorInterface $dataPersistor,
        CookieGroupFactory $cookieGroupFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cookieGroupRepository = $cookieGroupRepository;
        $this->linkResource = $linkResource;
        $this->linkCollection = $linkCollection;
        $this->dataPersistor = $dataPersistor;
        $this->cookieGroupFactory = $cookieGroupFactory;
        $this->logger = $logger;
        $this->descriptionResource = $descriptionResource;
        $this->descriptionCollection = $descriptionCollection;
    }

    /**
     * Save action
     */
    public function execute()
    {
        $formData = $this->getRequest()->getPostValue('cookiegroup');
        $storeId = $this->getRequest()->getParam('store');

        try {
            if (isset($formData['id'])) {
                $model = $this->cookieGroupRepository->getById($formData['id']);
            } else {
                $model = $this->cookieGroupFactory->create();
            }
            $data = $formData;
            unset($data['cookies']);

            if ($storeId) {
                $this->saveCookieGroupStoreData($data, $storeId);
            }
            $model->setData($data);
            $this->cookieGroupRepository->save($model);
            $this->saveCookieLinks($formData, $model);
            $this->messageManager->addSuccessMessage(__('You saved the item.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);

                return;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->redirectIfError($formData);

            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error has occured'));
            $this->logger->critical($e);
            $this->redirectIfError($formData);

            return;
        }

        $this->_redirect('*/*');
    }

    /**
     * @param array $data
     * @param int $storeId
     */
    private function saveCookieGroupStoreData(&$data, $storeId)
    {
        $groupDescription = $this->descriptionCollection->getDescriptionByStore($data['id'], $storeId);
        $useDefault = $this->getRequest()->getPostValue('use_default');
        $useDefaultDescription = isset($useDefault['description']) ? (bool)$useDefault['description'] : false;
        $useDefaultName = isset($useDefault['name']) ? (bool)$useDefault['name'] : false;
        $storeData = !empty($groupDescription->getData()) ? $groupDescription->getData() : [];

        if (!$useDefaultDescription) {
            $storeData['description'] = $data['description'];
        } else {
            $storeData['description'] = '';
        }

        if (!$useDefaultName) {
            $storeData['name'] = $data['name'];
        } else {
            $storeData['name'] = '';
        }

        if ((bool)$storeData['name'] || (bool)$storeData['description']) {
            $storeData['group_id'] = $data['id'];
            $storeData['store_id'] = $storeId;
            $groupDescription->setData($storeData);
        } elseif ($groupDescription->getId()) {
            $this->descriptionResource->delete($groupDescription);
        }
        $this->descriptionResource->save($groupDescription);
        unset($data['description']);
        unset($data['name']);
    }

    /**
     * @param array $formData
     * @param CookieGroupLink $model
     */
    private function saveCookieLinks($formData, $model)
    {
        $assignedCookies = $this->linkCollection->getCookiesByGroup($model->getId());

        if (!empty($formData['cookies'])) {
            foreach ($formData['cookies'] as $cookieId) {
                $cookieLink = $this->linkCollection->getGroupByCookie($cookieId);
                $cookieLink->setData('cookie_id', $cookieId)->setData('group_id', $model->getId());

                $this->linkResource->save($cookieLink);
            }

            foreach ($assignedCookies as $assignedCookie) {
                if (!in_array($assignedCookie->getData('cookie_id'), $formData['cookies'])) {
                    $this->linkResource->delete($assignedCookie);
                }
            }
        } else {
            foreach ($assignedCookies as $assignedCookie) {
                $this->linkResource->delete($assignedCookie);
            }
        }

    }

    /**
     * @param array $formData
     */
    private function redirectIfError($formData)
    {
        $this->dataPersistor->set('formData', $formData);

        if ($id = (int)$this->getRequest()->getParam('id')) {
            $this->_redirect('*/*/edit', ['id' => $id]);
        } else {
            $this->_redirect('*/*/new');
        }
    }
}
