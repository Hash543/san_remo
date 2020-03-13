<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Controller\Adminhtml;

use Mageplaza\RewardPoints\Model\Source\Direction;

/**
 * Class Spending
 * @package Mageplaza\RewardPoints\Controller\Adminhtml
 */
abstract class Spending extends AbstractReward
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_RewardPoints::spending_rate';

    /**
     * @return int|mixed
     */
    protected function getDirection()
    {
        return Direction::POINT_TO_MONEY;
    }
}
