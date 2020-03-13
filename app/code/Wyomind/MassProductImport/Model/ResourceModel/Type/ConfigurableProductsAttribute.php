<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class ConfigurableProductsAttribute extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Attribute
{

    public function getFields($fieldset = null, $form = false, $class = null)
    {
        return null;
    }



    public function prepareQueries($productId, $profile)
    {

        parent::prepareQueries($productId, $profile);
    }
}
