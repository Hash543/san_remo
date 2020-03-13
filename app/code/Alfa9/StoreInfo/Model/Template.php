<?php

namespace Alfa9\StoreInfo\Model;

class Template
{
	
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'full_width_sidebar',
                'label' => 'Full Width with Sidebar Options',
            ),
            array(
                'value' => 'page_width_sidebar',
                'label' => 'Page Width with Sidebar Options',
            ),
            array(
                'value' => 'page_width_top',
                'label' => 'Page Width with Top Options',
            )
        );
    }
    
}
