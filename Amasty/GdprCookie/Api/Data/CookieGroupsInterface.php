<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Api\Data;

use Amasty\GdprCookie\Model\EntityVersion\UpdateSensitiveEntityInterface;

interface CookieGroupsInterface extends UpdateSensitiveEntityInterface
{
    const ID = 'id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const IS_ESSENTIAL = 'is_essential';
    const IS_ENABLED = 'is_enabled';
    const SORT_ORDER = 'sort_order';

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
     * @return string|bool
     */
    public function isEssential();

    /**
     * @param $isEssential
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setIsEssential($isEssential);

    /**
     * @return string|bool
     */
    public function isEnabled();

    /**
     * @param $isEnabled
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setIsEnabled($isEnabled);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function setSortOrder($sortOrder);
}
