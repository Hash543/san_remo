<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Full import process
 */
class Run extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    public $module = "MassStockUpdate";

    public function execute()
    {
        $request = $this->getRequest();
        $id = $this->getRequest()->getParam('id');
        try {
            $this->_logger->notice("-------------------------------------------------");
            $this->_logger->notice(">> " . __METHOD__ . "()");

            $data = $this->getRequest()->getPost();

            if ($this->getRequest()->getParam("isAjax")) {
                session_write_close();
            }


            if ($data) {
                $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');


                if ($id) {
                    $model->load($id);
                }

                $this->_logger->notice(__("Running Profile #%1", $id));
                $rtn = $model->multipleImport();

                $this->messageManager->addSuccess(__('The profile %1 [ID:%2] has been processed.', $model->getName(), $model->getId()));


                if (is_string($rtn["success"])) {
                    $this->messageManager->addSuccess(__('%1', $rtn["success"]));
                }
                if (is_string($rtn["notice"])) {
                    $this->messageManager->addNotice(__('%1', $rtn["notice"]));
                }
                if (is_string($rtn["warning"])) {
                    $this->messageManager->addWarning(__('%1', $rtn["warning"]));
                }
                $this->_logger->addNotice(__("Profile #%1 has been processed", $id));

                if ($model->getSql()) {
                    $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, false);
                    $this->messageManager->addSuccess(__('The SQL file has been generated (<a href="%1" target="_blank">%1</a>)', $url . $model->getSqlPath() . $model->getSqlFile()));

                }

                if ($request->getParam('run_i')) {
                    return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            if ($request->getParam('run_i')) {
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $id, "_current" => true]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            if ($request->getParam('run_i')) {
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $id, "_current" => true]);
            }
        }
    }

}
