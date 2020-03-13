<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Ui\DataProvider\Form;

use Amasty\GdprCookie\Model\CookieGroupDescription;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupDescription\Collection as DescriptionCollection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\Collection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection as LinkCollection;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CookieGroupDataProvider extends AbstractDataProvider
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
     * @var DescriptionCollection
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
            $data[$id]['cookiegroup'] = $data['items'][0];
            $linkedCookies = $this->linkCollection->getCookiesByGroup($id);

            if (!empty($linkedCookies)) {
                $cookieIds = [];
                foreach ($linkedCookies as $cookie) {
                    array_push($cookieIds, $cookie->getData('cookie_id'));
                }
                $data[$id]['cookiegroup']['cookies'] = $cookieIds;
            }

            if ($description = $this->getStoreData()) {
                $data[$id]['cookiegroup']['description'] = $description['description']
                    ? : $data[$id]['cookiegroup']['description'];
                $data[$id]['cookiegroup']['name'] = $description['name']
                    ? : $data[$id]['cookiegroup']['name'];
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
        $configDescription = $configName = [
            'scopeLabel' => __('[STORE VIEW]')
        ];

        if ($storeId = $this->request->getParam('store')) {
            $configDescription['service'] = $configName['service'] = [
                'template' => 'ui/form/element/helper/service',
            ];
            $storeData = $this->getStoreData();
            $configDescription['disabled'] = !$storeData['description'];
            $configName['disabled'] = !$storeData['name'];
        }
        $meta['settings']['children']['description']['arguments']['data']['config'] = $configDescription;
        $meta['settings']['children']['name']['arguments']['data']['config'] = $configName;
        $id = $this->request->getParam('id');

        if (!$id) {
            return $meta;
        }

        if ($storeId) {
            $meta['settings']['children']['is_essential']['arguments']['data']['config']['disabled'] = true;
            $meta['settings']['children']['is_enabled']['arguments']['data']['config']['disabled'] = true;
            $meta['settings']['children']['cookies']['arguments']['data']['config']['disabled'] = true;
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

        /** @var CookieGroupDescription $content */
        $description = $this->descriptionCollection->getDescriptionByStore($cookieId, $storeId);

        if ($description->getId()) {
            return $description->getData();
        } else {
            return false;
        }
    }
}
