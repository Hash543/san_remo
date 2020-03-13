<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block;

class Js extends \Magento\Framework\View\Element\Template {
    /**
     * @var \Alfa9\MDirector\Helper\Data
     */
    private $helperData;
    /**
     * Js constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Alfa9\MDirector\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Alfa9\MDirector\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getRegExp()
    {
        return $this->helperData->getPixelRegularExpression();
    }

    /**
     * Prevent displaying if the pixel is not available
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helperData->isPixelEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}