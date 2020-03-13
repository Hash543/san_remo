<?php
/**
 * @copyright (c) 2018. Alfa9 (http://www.alfa9.com)
 * @author Xavier Sanz <xsanz@alfa9.com>
 * @package pss_crm
 */

namespace PSS\CRM\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Status
 * @package Alfa9\IeseInvoices\Ui\Component\Listing\Grid\Columns
 */
class Status extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        return $dataSource;
    }
}
