<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Action implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statusNames = [
            ['label' => __('Consent Given'), 'value' => 'consent_given'],
            ['label' => __('Delete Request Submitted'), 'value' => 'delete_request_submitted'],
            ['label' => __('Delete Request Approved'), 'value' => 'delete_request_approved'],
            ['label' => __('Data Anonymised by Customer'), 'value' => 'data_anonymised_by_customer'],
        ];

        return $statusNames;
    }
}
