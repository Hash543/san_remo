<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;
/**
 * Class Image
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class Image extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    /**
     *
     */
    const SEPARATOR = "*";
    /**
     *
     */
    const FILENAME_SEPARATOR = "~";
    /**
     *
     */
    const DEST_DIR = "pub/media/catalog/product";
    /**
     *
     */
    const IMPORT_DIR = "/imp/ort/";
    /**
     * @var array
     *
     */
    protected $separator = [",", ";"];
    /**
     * @var null|\Wyomind\MassProductImport\Helper\Storage
     */
    protected $_storageHelper = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Ftp
     */
    protected $_ftpHelper = null;
    /**
     * @var \Magento\Framework\Filesystem\Driver\FileFactory
     */
    protected $_driverFileFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var array
     */
    public $imagesToMove = [];
    /**
     * @var array
     */
    private $deleteQueries = [];
    /**
     * @var array
     */
    private $insertgalleryQueries = [];

    /**
     * Image constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\MassProductImport\Helper\Data $helperData
     * @param \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory
     * @param \Wyomind\MassProductImport\Helper\Storage $storageHelper
     * @param \Wyomind\MassStockUpdate\Helper\Ftp $ftpHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\MassProductImport\Helper\Data $helperData,
        \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory,
        \Wyomind\MassProductImport\Helper\Storage $storageHelper,
        \Wyomind\MassStockUpdate\Helper\Ftp $ftpHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        $connectionName = null
    )
    {

        $this->_driverFileFactory = $driverFileFactory;
        $this->_storageHelper = $storageHelper;
        $this->_ftpHelper = $ftpHelper;
        $this->_messageManager = $messageManager;

        parent::__construct($context, $coreHelper, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     *
     */
    public function _construct()
    {
        $this->table = $this->getTable("catalog_product_entity_media_gallery");
        $this->tableValue = $this->getTable("catalog_product_entity_media_gallery_value");
        $this->tableValueVideo = $this->getTable("catalog_product_entity_media_gallery_value_video");
        $this->tableValueToEntity = $this->getTable("catalog_product_entity_media_gallery_value_to_entity");
        $this->tableEavAttr = $this->getTable("eav_attribute");
        parent::_construct();
    }

    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function collect($productId, $value, $strategy, $profile)
    {


        if ($strategy["option"][0] == "media_gallery") {
            if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                $tableCpe = $this->getTable("catalog_product_entity");
                $query = "DELETE FROM `" . $this->tableValueToEntity . "` WHERE row_id=(SELECT MAX(row_id) from " . $tableCpe . " where entity_id=" . $productId . ");";
            } else {
                $query = "DELETE FROM `" . $this->tableValueToEntity . "` WHERE entity_id=" . $productId . ";";
            }
            if (!isset($this->deleteQueries[$productId])) {
                $this->deleteQueries[$productId] = true;
                $this->queries[$this->queryIndexer][] = $query;
            }

            $images = preg_split("/" . implode("|", $this->separator) . "/", $value);


            foreach ($images as $img) {
                $this->addImage($img, $productId, $strategy["option"][1], $strategy["storeviews"]);
            }

        } else {
            list($entityType, $attributeId) = $strategy['option'];
            $table = $this->getTable("catalog_product_entity_" . $entityType);

            $this->addImage($value, $productId, $strategy["option"][1], $strategy["storeviews"]);

            foreach ($strategy["storeviews"] as $storeview) {
                if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                    $tableCpe = $this->getTable("catalog_product_entity");
                    $data = [
                        "row_id" => "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)",
                        "store_id" => $storeview,
                        "attribute_id" => "$attributeId",
                        "value" => "'" . self::IMPORT_DIR . $this->basename($value) . "'"
                    ];
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($table, $data);
                } else {
                    foreach ($strategy['storeviews'] as $storeview) {
                        $data = [
                            "entity_id" => "$productId",
                            "store_id" => $storeview,
                            "attribute_id" => "$attributeId",
                            "value" => "'" . self::IMPORT_DIR . $this->basename($value) . "'"
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($table, $data);
                    }
                }
            }

        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    private function addImage($value, $productId, $attributeId, $storeviews)
    {

        $image = $this->_helperData->getValue($value);


        if (trim($image) != "") {
//            if (!isset($this->insertgalleryQueries[$this->basename($image)])) {
                $fields = array("attribute_id" => $attributeId, "value" => self::IMPORT_DIR . $this->basename($image), "media_type" => 'image');
                $data = $this->_helperData->prepareFields($fields, $value);
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);
                $this->queries[$this->queryIndexer][] = "SELECT @value_id:= LAST_INSERT_ID();";
                if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                    $tableCpe = $this->getTable("catalog_product_entity");
                    $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableValueToEntity . "` (value_id, row_id) values(@value_id  , (SELECT MAX(row_id) from " . $tableCpe . " where entity_id=" . $productId . "));";
                } else {
                    $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableValueToEntity . "` (value_id, entity_id) values(@value_id  , " . $productId . ");";
                }

                if (!in_array($image, $this->imagesToMove)) {
                    $this->imagesToMove[] = $image;
                }
                foreach ($storeviews as $storeview) {

                    if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                        $tableCpe = $this->getTable("catalog_product_entity");
                        $fields = ["value_id" => new \Zend_Db_Expr("@value_id"), "store_id" => $storeview, "row_id" => new \Zend_Db_Expr("(SELECT MAX(row_id) from " . $tableCpe . " where entity_id=" . $productId . ")"), "label" => null, "disabled" => 0, "position" => 0];
                        $data = $this->_helperData->prepareFields($fields, $value);
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableValue, $data);


                    } else {

                        $fields = ["value_id" => new \Zend_Db_Expr("@value_id"), "store_id" => 0, "entity_id" => new \Zend_Db_Expr($productId), "label" => null, "disabled" => 0, "position" => 0];

                        $data = $this->_helperData->prepareFields($fields, $value);
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableValue, $data);
                    }
                    if (isset($data["media_type"]) && $data["media_type"] == "external_video") {
                        $fields = ["value_id" =>  new \Zend_Db_Expr("@value_id"), "store_id" => 0, "provider" => null, "url" => null, "title" => null, "description" => null, "metadata" => null];
                        $data = $this->_helperData->prepareFields($fields, $value);
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableValueVideo, $data);
                    }
                }

                $this->insertgalleryQueries[$this->basename($image)] = true;

            }
//        }

    }

    /**
     * @return array
     */
    public function getDropdown()
    {
        /* IMAGES MAPPING */
        $dropdown = [];
        $fields = ["attribute_code"];
        $conditions = [
            ["eq" =>
                [
                    "media_gallery",
                ]
            ],
        ];
        $imageList = $this->getAttributesList($fields, $conditions, false);
        $i = 0;
        foreach ($imageList as $attribute) {
            if (!empty($attribute['frontend_label'])) {
                $dropdown['Media'][$i]['label'] = $attribute['frontend_label'];
                $dropdown['Media'][$i]["id"] = "Image/media_gallery/" . $attribute['attribute_id'];
                $dropdown['Media'][$i]['style'] = "Image";
                $dropdown['Media'][$i]['type'] = "List of image paths separated by on of the following separator:" . stripslashes(implode("  ", $this->separator));
                $dropdown['Media'][$i]['value'] = "path/to/image1.jpg" . stripslashes($this->separator[0]) . "path/to/image2.jpg" . stripslashes($this->separator[0]) . "...";

                $i++;
            }
        }

        $fields = ["frontend_input"];
        $conditions = [
            ["in" =>
                [
                    "media_image",
                ]
            ],
        ];
        $imageList = $this->getAttributesList($fields, $conditions, false);

        foreach ($imageList as $attribute) {
            if (!empty($attribute['frontend_label'])) {
                $dropdown['Media'][$i]['label'] = $attribute['frontend_label'];
                $dropdown['Media'][$i]["id"] = "Image/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'];
                $dropdown['Media'][$i]['style'] = "Image storeviews-dependent";
                $dropdown['Media'][$i]['type'] = "Image path and optionnal label separated by " . self::SEPARATOR;
                $dropdown['Media'][$i]['value'] = "path/to/" . $attribute['frontend_label'] . ".jpg" . self::SEPARATOR . "Optionnal image label";
                $i++;
            }
        }

        $i++;
        return $dropdown;
    }

    /**
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function afterProcess($profile)
    {

        try {
            $images = array_filter($this->imagesToMove);


            $img = 0;
            if ($profile->getImagesSystemType() == 0) {
                $io = $this->_driverFileFactory->create();
                $fromDir = $profile->getImagesFtpDir();
                $toDir = self::DEST_DIR . DIRECTORY_SEPARATOR . self::IMPORT_DIR;


                foreach ($images as $image) {
                    $to = $this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $toDir . DIRECTORY_SEPARATOR . $this->basename($image);
                    $from = $this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $fromDir . DIRECTORY_SEPARATOR . $this->basename($image);

                    $this->_storageHelper->mkdir(substr($to, 0, strrpos($to, "/")));

                    if ($io->isExists($from)) {
                        try {
                            if ($io->copy($from, $to)) {
                                $img++;
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
            } elseif ($profile->getImagesSystemType() == 1) {
                $data = [
                    "use_sftp" => $profile->getImagesUseSftp(),
                    "ftp_active" => $profile->getImagesFtpIsActive(),
                    "ftp_host" => $profile->getImagesFtpHost(),
                    "ftp_login" => $profile->getImagesFtpLogin(),
                    "ftp_password" => $profile->getImagesFtpPassword(),
                    "ftp_dir" => $profile->getImagesFtpDir(),
                    "ftp_port" => $profile->getImagesFtpPort(),
                ];

                $ftp = $this->_ftpHelper->getConnection($data);

                $io = $this->_driverFileFactory->create();
                $toDir = self::DEST_DIR . DIRECTORY_SEPARATOR . self::IMPORT_DIR;
                foreach ($images as $image) {
                    $to = $this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $toDir . DIRECTORY_SEPARATOR . $this->basename($image);

                    $this->_storageHelper->mkdir(substr($to, 0, strrpos($to, "/")));
                    while (in_array(substr($image, 0, 1), ["/", "\\"])) {
                        $image = substr($image, 1);
                    }
                    try {

                        if ($ftp->read($image, $to)) {
                            $img++;
                        }
                    } catch (\Exception $e) {

                    }
                }

                $ftp->close();
            } else {
                $io = $this->_driverFileFactory->create();

                $toDir = self::DEST_DIR . DIRECTORY_SEPARATOR . self::IMPORT_DIR;


                foreach ($images as $image) {
                    $to = preg_replace('#/+#', '/', $this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $toDir . DIRECTORY_SEPARATOR . $this->basename($image));
                    $from = $image;


                    $this->_storageHelper->mkdir(substr($to, 0, strrpos($to, "/")));

                    if (!$io->isExists($to)) {
                        try {
                            if ($io->copy($from, $to)) {
                                $img++;
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            $this->_messageManager->addSuccess(__($img . " images have been imported"));
        } catch (\Exception $e) {
            $this->_messageManager->addError(__("There was an error while importing the images.") . "<br>" . $e->getMessage());
        }
    }

    /** Get base name
     * @param $image
     * @return bool|string
     */
    private function basename($image)
    {
        $image = str_replace(array(" ", "''"), "-", $image);
        preg_match("#(?<domain>(https|sftp|http|ftp):\/\/[a-zA-Z\-\.\_]+)?(?<path>.*)#", $image, $base);

        if (in_array(substr($base["path"], 0, 1), ["\\", "/"])) {
          //  return substr($base["path"], 1);
            return str_replace(array("/", "\\"), "-", substr($base["path"], 1));

        }

        return str_replace(array("/", "\\"), "-", $base["path"]);
    }

    /**
     * Dropdown populate
     * @param null $fieldset
     * @param null $form
     * @param null $class
     * @return bool
     */
    function getFields($fieldset = null, $form = null, $class = null)
    {
        if ($fieldset == null) {
            return true;
        }

        $fieldset->addField('images_system_type', 'select', [
            'name' => 'images_system_type',
            'label' => __('Images location'),
            "required" => true,
            'values' => [
                [
                    'value' => 0,
                    'label' => 'Magento File System'
                ],
                [
                    'value' => 1,
                    'label' => 'Ftp server'
                ],
                [
                    'value' => 2,
                    'label' => 'Http server (url)'
                ],
            ],
            'note' => "<script> 
                require(['jquery'],function($){
                   $('#images_system_type').on('change',function(){updateSystemType()});
                   $(document).ready(function(){updateSystemType()});
                   function updateSystemType(){
             
                        if($('#images_system_type').val()==2){
                            $('#images_ftp_dir').parents('.field').css('display','none')
                        }
                        else{
                            $('#images_ftp_dir').parents('.field').css('display','')
                        }
                    }
                })
                
                </script>
            ",
        ]);

        $fieldset->addField('images_use_sftp', 'select', [
            'label' => __('Use SFTP'),
            'name' => 'images_use_sftp',
            'id' => 'images_use_sftp',
            'required' => true,
            'values' => [
                [
                    'value' => 0,
                    'label' => __('no')
                ],
                [
                    'value' => 1,
                    'label' => __('yes')
                ]
            ],
        ]);
        $fieldset->addField('images_ftp_active', 'select', [
            'label' => __('Use active mode'),
            'name' => 'images_ftp_active',
            'id' => 'images_ftp_active',
            'required' => true,
            'values' => [
                [
                    'value' => 0,
                    'label' => __('no')
                ],
                [
                    'value' => 1,
                    'label' => __('yes')
                ]
            ],
        ]);


        $fieldset->addField('images_ftp_host', 'text', [
            'label' => __('Host'),
            'name' => 'images_ftp_host',
            'id' => 'images_ftp_host',
        ]);

        $fieldset->addField('images_ftp_port', 'text', [
            'label' => __('Port'),
            'name' => 'images_ftp_port',
            'id' => 'images_ftp_port',
        ]);

        $fieldset->addField('images_ftp_login', 'text', [
            'label' => __('Login'),
            'name' => 'images_ftp_login',
            'id' => 'images_ftp_login',
        ]);
        $fieldset->addField('images_ftp_password', 'password', [
            'label' => __('Password'),
            'name' => 'images_ftp_password',
            'id' => 'images_ftp_password',
            'note' => __("<a style='margin:10px; display:block;' href='javascript:void(require([\"wyomind_MassImportAndUpdate_ftp\"], function (ftp) { ftp.test(\"%1\",\"%2\")}))'>Test Connection</a>", $form->getUrl('*/*/ftp'), "images"),
        ]);

        $fieldset->addField('images_ftp_dir', 'text', [
            'name' => 'images_ftp_dir',
            'label' => __('Path to images directory'),
        ]);
    }
}
