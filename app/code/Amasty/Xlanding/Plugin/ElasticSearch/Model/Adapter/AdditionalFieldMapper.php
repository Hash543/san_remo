<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Plugin\ElasticSearch\Model\Adapter;

/**
 * Class AdditionalFieldMapper
 * @package Amasty\Shopby\Plugin\ElasticSearch\Model\Adapter
 */
class AdditionalFieldMapper
{
    const ES_DATA_TYPE_INTEGER = 'integer';
    const FIELD_NAME = 'landing_page_id';

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        $result[self::FIELD_NAME] = ['type' => self::ES_DATA_TYPE_INTEGER];
        return $result;
    }

    /**
     * Amasty Elastic entity builder plugin
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildEntityFields($subject, array $result)
    {
        return $this->afterGetAllAttributesTypes($subject, $result);
    }
}
