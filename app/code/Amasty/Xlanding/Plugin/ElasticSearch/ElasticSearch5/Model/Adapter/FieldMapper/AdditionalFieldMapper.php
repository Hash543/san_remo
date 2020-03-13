<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Plugin\ElasticSearch\ElasticSearch5\Model\Adapter\FieldMapper;

class AdditionalFieldMapper
{
    const FIELD_NAME = 'landing_page_id';
    const ATTRIBUTE_TYPE_INTEGER = 'integer';

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        $result[self::FIELD_NAME] = ['type' => self::ATTRIBUTE_TYPE_INTEGER];
        return $result;
    }
}
