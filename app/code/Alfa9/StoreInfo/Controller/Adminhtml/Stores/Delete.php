<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Alfa9\StoreInfo\Controller\Adminhtml\Stores;

class Delete extends Stores
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('storeinfo_id');
        if ($id) {
            try {
                $this->stockistRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The stockist has been deleted.'));
                $resultRedirect->setPath('storeinfo/*/');
                return $resultRedirect;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The stockist no longer exists.'));
                return $resultRedirect->setPath('storeinfo/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('storeinfo/stores/edit', ['storeinfo_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the stockist'));
                return $resultRedirect->setPath('storeinfo/stores/edit', ['storeinfo_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a stockist to delete.'));
        $resultRedirect->setPath('storeinfo/*/');
        return $resultRedirect;
    }
}
