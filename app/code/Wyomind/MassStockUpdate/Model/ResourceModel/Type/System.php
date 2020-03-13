<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Type;

/**
 * Class System
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Type
 */
class System extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource
{
    /**
     * @param null $fieldset
     * @param bool $form
     * @param null $class
     * @return bool|null
     */
    public function getFields($fieldset = null, $form = false, $class = null)
    {
        if ($fieldset == null) {
            return true;
        }

        $fieldset->addField('has_header', 'select', array(
            'name' => 'has_header',
            'label' => __('The first line is a header'),
            'options' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'class' => 'updateOnChange'
        ));

        $fieldset->addField('line_filter', 'text', array(
            'name' => 'line_filter',
            'label' => __('Filter lines'),
            'note' => __('<ul><li>Leave empty to import/preview all lines</li>'
                . '<li>Type the numbers of the lines you want to import<br/>'
                . '<i>e.g: 5,8  means that only lines number 5 and 8 will be imported</i></li>'
                . '<li>Use a dash (-) to denote a range of lines<br/>'
                . '<i>e.g: 8-10 means lines 8,9,10 will be imported</i></li>'
                . '<li>Use a plus (+) to import all lines from a line number<br/>'
                . '<i>e.g: 4+ means all lines from line 4 will be imported</i></li>'
                . '<li> Separate each line or range with a comma (,)<br/>'
                . '<i>e.g: 2,6-10,15+ means lines 2,6,7,8,9,10,15,16,17,... will be imported</i></li>'
                . '<li>Use regular expressions surrounded by a # before and after to indicate a particular group of identifiers to import<br/>'
                . '<i>e.g: #ABC-[0-9]+# all lines with an identifier matching the regular expression will be imported</i></li></ul>'),
            'class' => 'updateOnChange'
        ));

        return $fieldset;
    }

}
