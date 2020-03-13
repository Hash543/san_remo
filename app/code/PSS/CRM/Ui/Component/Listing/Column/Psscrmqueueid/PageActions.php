<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Ui\Component\Listing\Column\Psscrmqueueid;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {

        $routeEdit = 'crm/queue/edit';
        $routeRetry = 'crm/queue/process';

        foreach ($dataSource['data']['items'] as &$item) {
            $name = $this->getData('name');
            if (isset($item['pss_crm_queue_id'])) {
                $item[$name]['edit'] = [
                    "href" => $this->getContext()->getUrl(
                        $routeEdit,["id"=>$item['pss_crm_queue_id']]),
                    "label"=>__("View")
                ];
            }
            if (isset($item['process_status']) && $item['process_status']!='1') {
                if (isset($item['pss_crm_queue_id'])) {
                    $item[$name]['retry'] = [
                        "href" => $this->getContext()->getUrl(
                            $routeRetry,["id"=>$item['pss_crm_queue_id']]),
                        "label"=>__("Retry")
                    ];
                }
            }
        }

        return $dataSource;
    }    
    
}
