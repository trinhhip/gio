<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Api\Data;

use Amasty\GdprCookie\Model\EntityVersion\UpdateSensitiveEntityInterface;

interface CookieInterface extends UpdateSensitiveEntityInterface
{
    const ID = 'id';
    const GROUP_ID = 'group_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const IS_ENABLED = 'is_enabled';
    const LIFETIME = 'lifetime';
    const PROVIDER = 'provider';
    const TYPE = 'type';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param int $groupId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setGroupId($groupId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getLifetime();

    /**
     * @param string $lifetime
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setLifetime($lifetime);

    /**
     * @return string
     */
    public function getProvider();

    /**
     * @param string $provider
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setProvider($provider);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param int $type
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setType($type);

    /**
     * @return int
     */
    public function isEnabled();

    /**
     * @param $isEnabled
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function setIsEnabled($isEnabled);
}
