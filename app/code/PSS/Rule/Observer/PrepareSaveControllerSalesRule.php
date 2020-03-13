<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Observer;

class PrepareSaveControllerSalesRule implements \Magento\Framework\Event\ObserverInterface {

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getData('request');
        if($request) {
            $params = $request->getParams();
            if(isset($params['banner']) && isset($params['banner'][0]) && isset($params['banner'][0]['name'])) {
                $request->setPostValue('banner', $params['banner'][0]['name']);
            }
            if(isset($params['marketing_list']) && !empty($params['marketing_list'])) {
                $request->setPostValue('marketing_list', implode(',',$params['marketing_list']));
            }
        }
    }
}