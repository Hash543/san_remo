<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Adminhtml\Cookie;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookie;
use Amasty\GdprCookie\Model\CookieFactory;
use Amasty\GdprCookie\Model\CookieGroupLink;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink as LinkResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection as LinkCollection;
use Amasty\GdprCookie\Model\ResourceModel\CookieDescription as DescriptionResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieDescription\Collection as DescriptionCollection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;

class Save extends AbstractCookie
{
    /**
     * @var CookieRepository
     */
    private $cookieRepository;

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
     * @var CookieFactory
     */
    private $cookieFactory;

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
        CookieRepository $cookieRepository,
        LinkResource $linkResource,
        LinkCollection $linkCollection,
        DescriptionResource $descriptionResource,
        DescriptionCollection $descriptionCollection,
        DataPersistorInterface $dataPersistor,
        CookieFactory $cookieFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cookieRepository = $cookieRepository;
        $this->linkResource = $linkResource;
        $this->linkCollection = $linkCollection;
        $this->dataPersistor = $dataPersistor;
        $this->cookieFactory = $cookieFactory;
        $this->logger = $logger;
        $this->descriptionResource = $descriptionResource;
        $this->descriptionCollection = $descriptionCollection;
    }

    /**
     * Save action
     */
    public function execute()
    {
        $formData = $this->getRequest()->getPostValue('cookie');
        $storeId = $this->getRequest()->getParam('store');

        try {
            if (isset($formData['id'])) {
                $model = $this->cookieRepository->getById($formData['id']);
            } else {
                $model = $this->cookieFactory->create();
            }
            $data = $formData;
            unset($data['group']);

            if ($storeId) {
                $this->saveCookieStoreData($data, $storeId);
            }
            $model->setData($data);
            $this->cookieRepository->save($model);
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
    private function saveCookieStoreData(&$data, $storeId)
    {
        $cookieDescription = $this->descriptionCollection->getDescriptionByStore($data['id'], $storeId);

        $useDefault = $this->getRequest()->getPostValue('use_default');
        $useDefaultDescription = isset($useDefault['description']) ? (bool)$useDefault['description'] : false;

        if (!$useDefaultDescription) {
            $cookieDescription->setData(
                [
                    'cookie_id'   => $data['id'],
                    'store_id'    => $storeId,
                    'description' => $data['description']
                ]
            );
        } elseif ($cookieDescription->getId()) {
            $this->descriptionResource->delete($cookieDescription);
        }
        $this->descriptionResource->save($cookieDescription);
        unset($data['description']);
    }

    /**
     * @param array $formData
     * @param CookieGroupLink $model
     */
    private function saveCookieLinks($formData, $model)
    {
        $cookieLink = $this->linkCollection->getGroupByCookie($model->getId());

        if ($formData['group']) {
            $cookieLink = $this->linkCollection->getGroupByCookie($model->getId());
            $cookieLink->setData('cookie_id', $model->getId())->setData('group_id', $formData['group']);

            $this->linkResource->save($cookieLink);
        } elseif ($cookieLink->getId()) {
            $this->linkResource->delete($cookieLink);
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
