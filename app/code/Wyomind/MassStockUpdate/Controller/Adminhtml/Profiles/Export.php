<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Export extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{
    public $module = "MassStockUpdate";

    public function execute()
    {
        $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');
        $model->load($this->getRequest()->getParam('id'));

        foreach ($model->getData() as $field => $value) {
            $fields[] = $field;
            if ($field == "id") {
                $values[] = "NULL";
            } else {
                $values[] = "'" . str_replace(["'", "\\"], ["''", "\\\\"], $value) . "'";
            }
        }
        $sql = "INSERT INTO {{table}}(`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', $values) . ");";
        $key = $this->module;
        $content = openssl_encrypt($sql, "AES-128-ECB", $key);


        return $this->_coreHelper->sendUploadResponse($model->getName() . ".conf", $content);
    }
}
