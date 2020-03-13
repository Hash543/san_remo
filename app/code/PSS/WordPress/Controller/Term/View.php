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

namespace PSS\WordPress\Controller\Term;

use PSS\WordPress\Controller\Action;

class View extends Action
{
    /**
     * @return bool
     */
    protected function _getEntity()
    {
        $object = $this->factory->create('Term')->load((int)$this->getRequest()->getParam('id'));

        return $object->getId() ? $object : false;
    }

    /**
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        $crumbs = parent::_getBreadcrumbs();
        $term = $this->_getEntity();

        if ($taxonomy = $term->getTaxonomyInstance()) {
            if ($taxonomy->isHierarchical()) {
                $buffer = $term;

                while ($buffer->getParentTerm()) {
                    $buffer = $buffer->getParentTerm();

                    $crumbs['term_' . $buffer->getId()] = [
                        'label' => __($buffer->getName()),
                        'title' => __($buffer->getName()),
                        'link' => $buffer->getUrl(),
                    ];
                }

            }
        }

        $crumbs['term'] = [
            'label' => __($term->getName()),
            'title' => __($term->getName())
        ];

        return $crumbs;
    }

    /**
     * @return array
     */
    public function getLayoutHandles()
    {
        $taxonomyType = $this->_getEntity()->getTaxonomyType();

        return array_merge(
            parent::getLayoutHandles(),
            array(
                'wordpress_term_view',
                'wordpress_' . $taxonomyType . '_view',
                'wordpress_' . $taxonomyType . '_view_' . $this->_getEntity()->getId(),
            )
        );
    }
}
