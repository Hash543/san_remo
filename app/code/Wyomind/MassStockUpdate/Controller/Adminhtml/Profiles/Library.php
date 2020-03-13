<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Library extends \Wyomind\MassStockUpdate\Controller\Adminhtml\AbstractController
{

    protected $_dataHelper = null;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Wyomind\MassStockUpdate\Logger\Logger $logger,
            \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Wyomind\MassStockUpdate\Helper\Data $dataHelper
    )
    {
        parent::__construct($context, $logger, $resultForwardFactory, $resultRawFactory, $resultPageFactory);

        $this->_dataHelper = $dataHelper;
    }

    public function execute()
    {
        try {

            $library['error'] = 'false';
            $library['data'] = array();
            $library['color'] = array("rgba(0,0,0,0.1)","rgba(50,255,0,0.1)","rgba(50,255,0,0.1)");
            $library['tag'] = array("Values","Values","");
            $library['header'] = array("Attribute", "Type", "Example");

            foreach ($this->_dataHelper->getJsonAttributes() as $name => $group) {
                if ($name == "storeviews") {
                    continue;
                }

                foreach ($group as $attribute) {

                    $value = isset($attribute["value"]) ? $attribute["value"] : "-";
                    $library['data'][] = array("<b>" . $name . "</b> | " . $attribute["label"], $attribute["type"], $value);
                }
            }


            return $this->getResponse()->representJson(json_encode($library));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getResponse()->representJson('{"error":"true","message":"' . preg_replace("/\r|\n|\t|\\\\/", "", nl2br(htmlentities($e->getMessage()))) . '"}');
        }
    }

}
