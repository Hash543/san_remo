<?php

namespace Alfa9\StoreInfo\Model;

class Unit
{
	
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'default',
                'label' => 'Kilometres',
            ),
            array(
                'value' => 'miles',
                'label' => 'Miles',
            )
        );
    }
    
}
