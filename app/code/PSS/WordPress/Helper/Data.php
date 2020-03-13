<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Helper;

use Magento\Framework\Exception\LocalizedException;
use PSS\WordPress\Model\AssociationType;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \PSS\WordPress\Model\AssociationTypeFactory
     */
    protected $associationTypeFactory;

    /**
     * @var \PSS\WordPress\Model\ResourceModel\Association\CollectionFactory
     */
    protected $associationCollectionFactory;
    /**
     * @var \PSS\WordPress\Model\AssociationFactory
     */
    protected $associationFactory;
    /**
     * @var \PSS\WordPress\Model\ResourceModel\Association
     */
    protected $associationResource;

    /**
     * Data constructor.
     * @param \PSS\WordPress\Model\AssociationTypeFactory $associationTypeFactory
     * @param \PSS\WordPress\Model\ResourceModel\Association\CollectionFactory $associationCollectionFactory
     * @param \PSS\WordPress\Model\AssociationFactory $associationFactory
     * @param \PSS\WordPress\Model\ResourceModel\Association $associationResource
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \PSS\WordPress\Model\AssociationTypeFactory $associationTypeFactory,
        \PSS\WordPress\Model\ResourceModel\Association\CollectionFactory $associationCollectionFactory,
        \PSS\WordPress\Model\AssociationFactory $associationFactory,
        \PSS\WordPress\Model\ResourceModel\Association $associationResource,
        \Magento\Framework\App\Helper\Context $context) {
        parent::__construct($context);
        $this->associationFactory = $associationFactory;
        $this->associationResource = $associationResource;
        $this->associationTypeFactory = $associationTypeFactory;
        $this->associationCollectionFactory = $associationCollectionFactory;
    }

    /**
     * Get the id of the association Type
     * @return integer|null
     */
    public function getTypeIdFromProductToPost() {
        return $this->getTypeFromPost(AssociationType::OBJECT_VALUE_PRODUCT);
    }
    /**
     * Get the id of the association Type
     * @return integer|null
     */
    public function getTypeIdFromCategoryToPost() {
       return $this->getTypeFromPost(AssociationType::OBJECT_VALUE_CATEGORY); 
    }
    /**
     * Get the id of the association Type
     * @return integer|null
     */
    public function getTypeIdFromCmsPageToPost() {
        return $this->getTypeFromPost(AssociationType::OBJECT_VALUE_CMS_PAGE);
    }
    /**
     * @param string $objectValue
     * @return int|null
     */
    private function getTypeFromPost($objectValue) {
        $typeId = null;
        try {
            /** @var  \PSS\WordPress\Model\AssociationType $associationType */
            $associationType = $this->associationTypeFactory->create()
                ->loadAssociationTypeByObjectName($objectValue, AssociationType::WORDPRESS_OBJECT_POST);
            if ($associationType && $associationType->getId()) {
                $typeId = $associationType->getId();
            }
        }catch (\Exception $e) {
            $typeId = null;
        }
        return $typeId;
    }
    /**
     * @param int $associationTypeId
     * @param int $productId
     * @return array
     */
    public function getPostIdsRelated($associationTypeId,  $productId){
        $postIds = [];
        /** @var \PSS\WordPress\Model\ResourceModel\Association\Collection $associationCollection */
        $associationCollection = $this->associationCollectionFactory->create();
        $associationCollection = $associationCollection->getRelationWordpressObjects($associationTypeId, $productId);
        foreach ($associationCollection as $item) {
            $postIds[] = $item->getData('wordpress_object_id');
        }
        return $postIds;
    }
    /**
     * Save the relation
     * @param int $typeId
     * @param array $relatedPostId
     * @param int $productId
     * @param int $storeId
     * @throws LocalizedException
     */
    public function saveRelationWithPost( $typeId, array $relatedPostId,  $productId,  $storeId){

        $foundPostIds = $this->deleteRelationNotFound($typeId, $productId, $relatedPostId);
        foreach ($relatedPostId as $postId) {
            if(in_array($postId, $foundPostIds)){
                continue; //Todo: the relation was saved before
            }
            $data = [
                'type_id' => $typeId,
                'object_id' => $productId,
                'wordpress_object_id' => $postId,
                'position' => 0, //Todo: for the moment is not being used
                'store' =>   $storeId
            ];
            try {
                $associationModel = $this->getAssociationObject()->setData($data);
                $this->associationResource->save($associationModel);
            } catch (\Exception $e) {
                throw new LocalizedException(__('Error saving the post relation ....'));
            }
        }
    }
    /**
     * Delete the relation was not found
     * @param int $typeId
     * @param int $productId
     * @param array $postIds
     * @return array
     */
    public function deleteRelationNotFound( $typeId,  $productId, array $postIds) {
        $foundRelationIds = [];
        /** @var \PSS\WordPress\Model\ResourceModel\Association\Collection $associationCollectionByProduct */
        $associationCollectionByProduct = $this->associationCollectionFactory->create();
        $associationCollectionByProduct = $associationCollectionByProduct->getRelationWordpressObjects($typeId, $productId);
        foreach ($associationCollectionByProduct as $association){
            $postId = $association->getData('wordpress_object_id');
            if (!in_array($postId, $postIds)){
                try {
                    $this->associationResource->delete($association);
                } catch (\Exception $e) {
                    continue;
                }
            } else {
                $foundRelationIds[] = $postId;
            }
        }
        return $foundRelationIds;
    }
    /**
     * @return \PSS\WordPress\Model\Association
     */
    private function getAssociationObject() {
        return $this->associationFactory->create();
    }

}