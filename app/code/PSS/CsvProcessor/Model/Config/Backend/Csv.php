<?php

namespace Pss\CsvProcessor\Model\Config\Backend;

class Csv extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['csv'];
    }
}