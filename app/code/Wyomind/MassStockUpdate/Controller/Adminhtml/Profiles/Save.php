<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Save extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    public $module = "MassStockUpdate";

    public function execute()
    {
        $this->_logger->notice("-------------------------------------------------");
        $this->_logger->notice(">> " . __METHOD__ . "()");


        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');

            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
            }

            $this->_logger->notice(__("Saving Profile #%1", $id));

            foreach ($data as $index => $value) {
                $model->setData($index, $value);
            }

            if (!$this->_validatePostData($data)) {
                return $this->_resultRedirectFactory->create()->setPath($this->module . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The profile %1 [ID:%2] has been saved.', $model->getName(), $model->getId()));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }

                if ($this->getRequest()->getParam('run_i')) {
                    $this->getRequest()->setParam('profile_id', $model->getId());
                    return $this->_resultForwardFactory->create()->forward("run");
                }

                $this->_getSession()->setFormData($data);
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to save the profile.') . '<br/><br/>' . $e->getMessage());
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
            }
        }
        return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
    }

    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                            $data['custom_layout_update_xml']
                    )
            ) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->_messageManager->addError($message);
            }
        }
        return $errorNo;
    }

}
