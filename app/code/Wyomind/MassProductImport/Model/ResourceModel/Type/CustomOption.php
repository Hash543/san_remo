<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class CustomOption extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{


    const OPTIONS_CONTAINER = "container1";

    public function _construct()
    {
        $this->tableCpo = $this->getTable("catalog_product_option");
        $this->tableCpot = $this->getTable("catalog_product_option_title");
        $this->tableCpop = $this->getTable("catalog_product_option_price");
        $this->tableCpotv = $this->getTable("catalog_product_option_type_value");
        $this->tableCpott = $this->getTable("catalog_product_option_type_title");
        $this->tableCpotp = $this->getTable("catalog_product_option_type_price");
        $this->tableCpe = $this->getTable("catalog_product_entity");
        $this->tableCpev = $this->getTable("catalog_product_entity_varchar");
        $this->tableEavAttr = $this->getTable("eav_attribute");
        parent::_construct();
    }


    /**
     * Collect data for each product to udpate/import
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile $profile
     * @return void
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {


        list($type) = $strategy['option'];
        $storeviews = $strategy['storeviews'];
        $title = str_replace("'", "''", $this->_helperData->getValue($value));
        /*
         * Delete previously registered option with the identical seme name
         */
        foreach ($storeviews as $storeId) {
            $this->queries[$this->queryIndexer][] = "SELECT @option_id:=IFNULL((SELECT cpo.option_id FROM " . $this->tableCpo . " AS cpo "
                . "INNER JOIN " . $this->tableCpot . " AS cpot ON cpot.option_id=cpo.option_id AND title='$title'  "
                . "WHERE product_id=$productId AND type='$type' LIMIT 1),0);";

            $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCpo . " WHERE option_id = @option_id;";
        }
        /*
         * Update the options_container attribute
         */
        $this->queries[$this->queryIndexer][] = "UPDATE " . $this->tableCpe . " SET has_options = 1, required_options = 1 WHERE entity_id = $productId;";
        $data = [
            "attribute_id" => "(SELECT attribute_id FROM $this->tableEavAttr WHERE attribute_code='options_container')",
            "entity_id" => $productId,
            "value" => "'" . self::OPTIONS_CONTAINER . "'"
        ];
        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpev, $data);


        /**
         * Insert option base values
         */
        $select = false;
        switch ($type) {



            case "field":
            case "area":
                $fields = array("product_id" => $productId, "type" => $type, "is_require" => 0, "sku" => null, "max_characters" => null, "sort_order" => 0);
                $preparedData = $this->_helperData->prepareFields($fields, $value);

                break;
            case "file":
                $fields = array("product_id" => $productId, "type" => $type, "is_require" => 0, "sku" => null, "sort_order" => 0, "file_extension" => "");
                $preparedData = $this->_helperData->prepareFields($fields, $value);
                break;
            case "multiple":
            case "checkbox":
            case "drop_down":
            case "radio":
                $select = true;
                $fields = array("product_id" => $productId, "type" => $type, "is_require" => 0);
                $preparedData = $this->_helperData->prepareFields($fields, $value);

                break;

        }


        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpo, $preparedData);
        $this->queries[$this->queryIndexer][] = "SELECT @option_id:= LAST_INSERT_ID();";

        /**
         * Insert option title
         */


        $fields = ["option_id" => new \Zend_Db_Expr("@option_id"), "store_id" => 0];
        foreach ($storeviews as $storeview) {
            $fields["store_id"] = $storeview;
            $preparedData = $this->_helperData->prepareFields($fields, $value);
            $preparedData["title"] = "'".$title."'";
            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpot, $preparedData);
        }
        /**
         * if not select type
         */
        if (!$select) {
            /**
             * Insert option price
             */


            $fields = ["option_id" => new \Zend_Db_Expr("@option_id"), "price" => 0.00, "store_id" => 0];
            foreach ($storeviews as $storeview) {
                $data["store_id"] = $storeview;
                $preparedData = $this->_helperData->prepareFields($fields, $value);
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpop, $preparedData);
            }

        } else {
            /**
             * Insert options for select custom options
             */
            $groups = $this->_helperData->prepareFields($fields, $value, null, true);

            foreach ($groups as $group) {


                $fields = ["option_id" => new \Zend_Db_Expr("@option_id"), "sku" => null, "sort_order" => 0];
                $preparedData = $this->_helperData->prepareFields($fields, $group);
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpotv, $preparedData);
                $this->queries[$this->queryIndexer][] = "SELECT @option_type_id:= LAST_INSERT_ID();";

                foreach ($storeviews as $storeview) {
                    $fields = array("option_type_id" => new \Zend_Db_Expr("@option_type_id"), "price" => 0.00, "store_id" => 0);
                    $data["store_id"] = $storeview;
                    $preparedData = $this->_helperData->prepareFields($fields, $group);
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpotp, $preparedData);
                }
                foreach ($storeviews as $storeview) {
                    $data["store_id"] = $storeview;
                    $fields = array("option_type_id" => new \Zend_Db_Expr("@option_type_id"), "title" => null, "store_id" => 0);

                    $preparedData = $this->_helperData->prepareFields($fields, $group);


                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpott, $preparedData);
                }

            }


        }
        return parent::collect($productId, $value, $strategy, $profile);

    }

    /**
     * List of new mapping attributes
     * @return array
     */
    public function getDropdown()
    {
        $type = "Custom option title and option values separated by ";
        $value = " Custom option title " . " option label 1" . "option sku 1" . "option price 1" . "option position 1 " . " option label 2" . "option sku 2" . "option price 2" . "option position 2 " . "...";


        $i = 0;
        $dropdown['Custom Options'][$i]['label'] = __("Dropdown");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/drop_down";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        $dropdown['Custom Options'][$i]['label'] = __("Radio");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/radio";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        $dropdown['Custom Options'][$i]['label'] = __("Checkbox");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/checkbox";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        $dropdown['Custom Options'][$i]['label'] = __("Muli-select");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/multiple";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        $dropdown['Custom Options'][$i]['label'] = __("Text Field");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/field";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        $dropdown['Custom Options'][$i]['label'] = __("Textarea");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/area";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        $dropdown['Custom Options'][$i]['label'] = __("File");
        $dropdown['Custom Options'][$i]["id"] = "CustomOption/file";
        $dropdown['Custom Options'][$i]['style'] = "custom-option";
        $dropdown['Custom Options'][$i]['type'] = $type;
        $dropdown['Custom Options'][$i]['value'] = $value;
        $i++;

        return $dropdown;
    }
}
