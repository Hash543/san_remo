<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class MixedProduct extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    const FIELD_SEPARATOR = ",";
    const LINK_TYPE_ID = 3;

    public function _construct()
    {
        $this->tableCpr = $this->getTable("catalog_product_relation");
        $this->tableCpl = $this->getTable("catalog_product_link");
        $this->tableCpe = $this->getTable("catalog_product_entity");

        parent::_construct();
    }

    function collect($productId, $value, $strategy, $profile)
    {



        list($field) = $strategy['option'];
        switch ($field) {
            case "parentSku":
                $values = explode(self::FIELD_SEPARATOR, $value);
                foreach ($values as $value) {
                    if ($value != "") {
                        $data = [
                            "parent_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku='$value' LIMIT 1)",
                            "child_id" => $productId
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data);
                        $data = [
                            "linked_product_id" => $productId,
                            "product_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku='$value' LIMIT 1)",
                            "link_type_id" => self::LINK_TYPE_ID
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpl, $data);
                    }
                }
                return;
                break;
            case "childrenSkus":
                $values = explode(self::FIELD_SEPARATOR, $value);
                foreach ($values as $value) {
                    if ($value != "") {
                        $data = [
                            "parent_id" => $productId,
                            "child_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku='$value' LIMIT 1)"
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data);
                        $data = [
                            "linked_product_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku='$value' LIMIT 1)",
                            "product_id" => $productId,
                            "link_type_id" => self::LINK_TYPE_ID
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpl, $data);
                    }
                }
                return;
                break;
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    public function getDropdown()
    {
        $dropdown = [];
        $i = 0;


        $dropdown['Grouped Products'][$i]['label'] = __("Parent SKU");
        $dropdown['Grouped Products'][$i]["id"] = "MixedProduct/parentSku";
        $dropdown['Grouped Products'][$i]['style'] = "mixed-product no-configurable";
        $dropdown['Grouped Products'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Grouped Products'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";

        $i++;
        $dropdown['Grouped Products'][$i]['label'] = __("Children SKUs");
        $dropdown['Grouped Products'][$i]["id"] = "MixedProduct/childrenSkus";
        $dropdown['Grouped Products'][$i]['style'] = "mixed-product no-configurable";
        $dropdown['Grouped Products'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Grouped Products'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";
        return $dropdown;
    }

    public function getIndexes($mapping = [])
    {
        return [1 => "catalogrule_rule", 2 => "catalogrule_product"];
    }
}
