<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Api\Data;

interface CookieGroupsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';

    const NAME = 'name';

    const DESCRIPTION = 'description';

    const IS_ESSENTIAL = 'is_essential';

    const IS_ENABLED = 'is_enabled';

    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setDescription($description);

    /**
     * @return bool
     */
    public function getIsEssential();

    /**
     * @param bool $isEssential
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setIsEssential($isEssential);

    /**
     * @return bool
     */
    public function getIsEnabled();

    /**
     * @param bool $isEnabled
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setIsEnabled($isEnabled);
}
