<?php

namespace Alfa9\StoreInfo\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
/**
 * @method string getImageUrl()
 * @method string getWidth()
 * @method string getHeight()
 * @method string getLabel()
 * @method mixed getResizedImageWidth()
 * @method mixed getResizedImageHeight()
 * @method float getRatio()
 * @method string getCustomAttributes()
 */
class Image extends Template
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    public $entity;

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        if (isset($data['template'])) {
            $this->setTemplate($data['template']);
            unset($data['template']);
        }
        parent::__construct($context, $data);
    }
}
