<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Ui\DataProvider\Form;

use Amasty\GdprCookie\Model\CookieDescription;
use Amasty\GdprCookie\Model\ResourceModel\Cookie\Collection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection as LinkCollection;
use Amasty\GdprCookie\Model\ResourceModel\CookieDescription\Collection as DescriptionCollection;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CookieDataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    /**
     * @var descriptionCollection
     */
    private $descriptionCollection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        LinkCollection $linkCollection,
        DescriptionCollection $descriptionCollection,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        UrlInterface $url,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collection;
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->url = $url;
        $this->linkCollection = $linkCollection;
        $this->descriptionCollection = $descriptionCollection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        if ($data['totalRecords'] > 0) {
            $id = (int)$data['items'][0]['id'];
            $data[$id]['cookie'] = $data['items'][0];
            $linkedGroup = $this->linkCollection->getGroupByCookie($id);

            if ($linkedGroup) {
                $data[$id]['cookie']['group'] = $linkedGroup->getData('group_id');
            }

            if ($description = $this->getStoreData()) {
                $data[$id]['cookie']['description'] = $description;
            }
        }

        if ($savedData = $this->dataPersistor->get('formData')) {
            $id = isset($savedData['id']) ? $savedData['id'] : null;
            if (isset($data[$id])) {
                $data[$id] = array_merge($data[$id], $savedData);
            } else {
                $data[$id] = $savedData;
            }
            $this->dataPersistor->clear('formData');
        }

        return $data;
    }

    public function getMeta()
    {
        $this->data['config']['submit_url'] = $this->url->getUrl('*/*/save', ['_current' => true]);
        $meta = parent::getMeta();
        $config = [
            'scopeLabel' => __('[STORE VIEW]')
        ];

        if ($storeId = $this->request->getParam('store')) {
            $config['service'] = [
                'template' => 'ui/form/element/helper/service',
            ];

            $config['disabled'] = !$this->getStoreData();
        }
        $meta['settings']['children']['description']['arguments']['data']['config'] = $config;
        $id = $this->request->getParam('id');

        if (!$id) {
            return $meta;
        }

        if ($storeId) {
            $meta['settings']['children']['name']['arguments']['data']['config']['disabled'] = true;
            $meta['settings']['children']['group']['arguments']['data']['config']['disabled'] = true;
        }

        return $meta;
    }

    /**
     * @return bool|mixed
     */
    protected function getStoreData()
    {
        $storeId = $this->request->getParam('store');
        $cookieId = $this->request->getParam($this->getRequestFieldName());

        if (!$storeId || !$cookieId) {
            return false;
        }

        /** @var CookieDescription $content */
        $description = $this->descriptionCollection->getDescriptionByStore($cookieId, $storeId);

        if ($description->getId()) {
            return $description->getData('description');
        } else {
            return false;
        }
    }
}
